<?php
// +----------------------------------------------------------------------
// | DudaCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2026 https://ask.btbk.org
// +----------------------------------------------------------------------
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统，打造私有社交化问答、内部知识存储
// +----------------------------------------------------------------------
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------

namespace app\common\library\oauth\sdk;

use app\common\library\helper\HttpHelper;

/**
 * 标准 OAuth2 授权码模式基类。
 *
 * 所有 SDK 统一返回以下结构，供 app\common\library\oauth\Service 消费：
 * [
 *   'openid'        => string,
 *   'access_token'  => string,
 *   'refresh_token' => string,
 *   'expires_in'    => int,
 *   'user_info'     => ['nickname' => string, 'avatar' => string, 'sex' => int],
 * ]
 */
abstract class OAuth2
{
    /** @var string 平台标识 */
    protected $platform = '';

    /** @var array 插件配置 */
    protected $config = [];

    /** @var string 授权端点 */
    protected $authorizeUrl = '';

    /** @var string Token 端点 */
    protected $tokenUrl = '';

    /** @var string 用户信息端点 */
    protected $userInfoUrl = '';

    /** @var string 默认 scope */
    protected $defaultScope = '';

    /** @var bool 是否启用 PKCE */
    protected $usePkce = false;

    public function __construct($options = [])
    {
        $thirdConfig = \app\common\library\helper\ThirdAuthHelper::config();
        $thirdConfig = is_array($thirdConfig) ? $thirdConfig : [];
        $this->config = array_merge($thirdConfig, is_array($options) ? $options : []);
    }

    protected function debugLog(string $event, array $context = []): void
    {
        $context['platform'] = $this->platform;
        $state = (string) session('third_state_' . $this->platform);
        $context['trace'] = $state !== '' ? substr(md5($state), 0, 8) : '-';
        \think\facade\Log::write(
            '[oauth][' . $event . '] ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'error'
        );
    }

    /**
     * 读取当前平台的配置项。
     */
    protected function setting(string $key, $default = '')
    {
        $platformConfig = $this->config[$this->platform] ?? [];
        if (!is_array($platformConfig)) {
            return $default;
        }
        $value = $platformConfig[$key] ?? $default;
        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * 回调地址：优先使用框架生成值，避免后台配置里硬编码域名。
     */
    protected function callbackUrl(): string
    {
        $callback = $this->config['callback'] ?? '';
        if (is_string($callback) && $callback !== '') {
            return $callback;
        }
        return (string) url('ThirdAuth/callback', ['platform' => $this->platform], false, true);
    }

    /**
     * 构造授权跳转地址，并写入 state / PKCE 会话。
     */
    public function getAuthorizeUrl(): string
    {
        $state = bin2hex(random_bytes(16));
        session('third_state_' . $this->platform, $state);

        $params = [
            'response_type' => 'code',
            'client_id'     => (string) $this->setting('app_id'),
            'redirect_uri'  => $this->callbackUrl(),
            'scope'         => (string) $this->setting('scope', $this->defaultScope),
            'state'         => $state,
        ];

        if ($this->usePkce) {
            $verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            session('third_pkce_' . $this->platform, $verifier);
            $params['code_challenge']        = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
            $params['code_challenge_method'] = 'S256';
        }

        $this->debugLog('authorize', [
            'redirect_uri'  => $params['redirect_uri'],
            'client_id_tail' => substr($params['client_id'], -12),
            'scope'         => $params['scope'],
        ]);

        return $this->authorizeUrl . (strpos($this->authorizeUrl, '?') === false ? '?' : '&') . http_build_query($params);
    }

    /**
     * 回调换用户信息。
     */
    public function getUserInfo($params = []): array
    {
        $params = $params ?: (array) request()->param();

        $state = (string) ($params['state'] ?? '');
        $expected = (string) session('third_state_' . $this->platform);
        if ($state === '' || $expected === '' || !hash_equals($expected, $state)) {
            $this->debugLog('state_mismatch', [
                'state_hash'       => $state !== '' ? substr(md5($state), 0, 8) : '-',
                'state_len'        => strlen($state),
                'expected_present' => $expected !== '',
            ]);
            return [];
        }
        // state 一次性使用，防止重放
        session('third_state_' . $this->platform, null);

        $code = (string) ($params['code'] ?? '');
        if ($code === '') {
            $this->debugLog('code_missing', ['trace' => substr(md5($state), 0, 8)]);
            return [];
        }

        $token = $this->getAccessToken($code);
        $accessToken = (string) ($token['access_token'] ?? '');
        $this->debugLog('token_exchange', [
            'code_hash'        => substr(md5($code), 0, 8),
            'has_access_token' => $accessToken !== '',
            'error'            => (string) ($token['error'] ?? ''),
            'error_description'=> (string) ($token['error_description'] ?? ''),
            'token_type'       => (string) ($token['token_type'] ?? ''),
            'expires_in'       => (int) ($token['expires_in'] ?? 0),
        ]);
        if ($accessToken === '') {
            return [];
        }

        $profile = $this->fetchProfile($accessToken);
        $this->debugLog('profile_fetch', [
            'has_profile' => !empty($profile),
            'has_openid'  => !empty($profile['id'] ?? ''),
            'nickname'    => (string) ($profile['nickname'] ?? ''),
            'has_avatar'  => !empty($profile['avatar'] ?? ''),
        ]);
        if (!$profile) {
            return [];
        }

        $this->debugLog('userinfo_ok', ['openid_hash' => substr(md5((string) ($profile['id'] ?? '')), 0, 8)]);

        return [
            'openid'        => (string) ($profile['id'] ?? ''),
            'access_token'  => $accessToken,
            'refresh_token' => (string) ($token['refresh_token'] ?? ''),
            'expires_in'    => (int) ($token['expires_in'] ?? 0),
            'user_info'     => [
                'nickname' => (string) ($profile['nickname'] ?? ''),
                'avatar'   => (string) ($profile['avatar'] ?? ''),
                'sex'      => (int) ($profile['sex'] ?? 0),
            ],
        ];
    }

    /**
     * 用授权码换取 access_token。
     */
    protected function getAccessToken(string $code): array
    {
        $params = [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => (string) $this->setting('app_id'),
            'client_secret' => (string) $this->setting('app_secret'),
            'redirect_uri'  => $this->callbackUrl(),
        ];

        if ($this->usePkce) {
            $verifier = (string) session('third_pkce_' . $this->platform);
            if ($verifier === '') {
                return [];
            }
            $params['code_verifier'] = $verifier;
            session('third_pkce_' . $this->platform, null);
        }

        $this->debugLog('token_request', [
            'code_hash'      => substr(md5($code), 0, 8),
            'token_url'      => $this->tokenUrl,
            'redirect_uri'   => $params['redirect_uri'],
            'client_id_tail' => substr($params['client_id'], -12),
        ]);

        $options = $this->tokenRequestOptions($params);
        $response = HttpHelper::post($this->tokenUrl, $params, $options);
        if (empty($response['code'])) {
            $this->debugLog('token_http_error', ['http_code' => (int) ($response['code'] ?? 0)]);
            return [];
        }

        $data = $this->decodeResponse((string) ($response['data'] ?? ''));
        $this->debugLog('token_response', [
            'http_code'         => (int) ($response['code'] ?? 0),
            'has_access_token'  => !empty($data['access_token']),
            'error'             => (string) ($data['error'] ?? ''),
            'error_description' => (string) ($data['error_description'] ?? ''),
        ]);
        return is_array($data) ? $data : [];
    }

    /**
     * Token 请求头：默认请求 JSON 响应。
     */
    protected function tokenRequestOptions(array &$params): array
    {
        return [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ];
    }

    /**
     * 解析接口响应（兼容 JSON 与 querystring 两种返回）。
     */
    protected function decodeResponse(string $body)
    {
        if ($body === '') {
            return [];
        }
        $json = json_decode($body, true);
        if (is_array($json)) {
            return $json;
        }
        parse_str($body, $parsed);
        return is_array($parsed) ? $parsed : [];
    }

    /**
     * 携带 Bearer Token 拉取用户信息。
     */
    protected function fetchJson(string $url, string $accessToken): array
    {
        $response = HttpHelper::get($url, [], [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer ' . $accessToken,
                'User-Agent: DudaCMS-OAuth2',
            ],
        ]);
        if (empty($response['code'])) {
            return [];
        }
        $data = $this->decodeResponse((string) ($response['data'] ?? ''));
        return is_array($data) ? $data : [];
    }

    /**
     * 拉取并规范化用户资料。
     */
    abstract protected function fetchProfile(string $accessToken): array;
}
