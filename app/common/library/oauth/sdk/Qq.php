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

class Qq
{
    const GET_AUTH_CODE_URL = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
    const GET_USERINFO_URL = "https://graph.qq.com/user/get_user_info";
    const GET_OPENID_URL = "https://graph.qq.com/oauth2.0/me";

    /**
     * 配置信息
     * @var array
     */
    private $config = [];

    public function __construct($options = [])
    {
        $thirdConfig = \app\common\library\helper\ThirdAuthHelper::config();
        $this->config = array_merge($this->config, $thirdConfig);
        $this->config = array_merge($this->config, is_array($options) ? $options : []);
    }

    /**
     * 登陆
     */
    public function login()
    {
        header("Location:" . $this->getAuthorizeUrl());
    }

    /**
     * 获取authorize_url
     */
    public function getAuthorizeUrl(): string
    {
        $state = bin2hex(random_bytes(16));
        session('third_state_qq', $state);
        $queryArr = array(
            "response_type" => "code",
            "client_id"     => $this->config['qq']['app_id'],
            "redirect_uri"  => $this->config['qq']['callback'] ?? (string)get_url('ThirdAuth/callback', ['platform' => 'qq'], false, true),
            "scope"         => $this->config['qq']['scope'] ?: 'get_user_info',
            "state"         => $state,
        );
        request()->isMobile() && $queryArr['display'] = 'mobile';
        return self::GET_AUTH_CODE_URL . '?' . http_build_query($queryArr);
    }

    /**
     * 获取用户信息
     * @param array $params
     * @return array
     */
    public function getUserInfo($params = []): array
    {
        $params = $params ?: request()->param();
        $state = (string) ($params['state'] ?? '');
        $expected = (string) session('third_state_qq');
        if ($state === '' || $expected === '' || !hash_equals($expected, $state) || empty($params['code'])) {
            return [];
        }
        session('third_state_qq', null);

        //获取access_token
        $data = $this->getAccessToken((string) $params['code']);
        $access_token = $data['access_token'] ?? '';
        if ($access_token === '') {
            return [];
        }
        $openid = $this->getOpenId($access_token);
        $queryArr = [
            "access_token"       => $access_token,
            "oauth_consumer_key" => $this->config['qq']['app_id'],
            "openid"             => $openid,
        ];
        $userInfo = HttpHelper::get(self::GET_USERINFO_URL, $queryArr)['data'];
        $userInfo = json_decode($userInfo, true);
        if (!$userInfo || !isset($userInfo['ret']) || $userInfo['ret'] !== 0) {
            return [];
        }
        $userInfo['avatar'] = $userInfo['figureurl_qq_2'] ?? '';
        $userInfo['sex'] = $userInfo['gender'] == '男' ? 1 : ($userInfo['gender'] == '女' ? 2 : 0);
        return [
            'access_token'  => $access_token,
            'refresh_token' => $data['refresh_token'] ?? '',
            'expires_in'    => (int) ($data['expires_in'] ?? 0),
            'openid'        => $openid,
            'user_info'     => $userInfo
        ];
    }

    /**
     * 获取access_token
     * @param string $code
     */
    public function getAccessToken($code = ''): array
    {
        if (!$code) {
            return [];
        }
        $queryArr = array(
            "grant_type"    => "authorization_code",
            "client_id"     => $this->config['qq']['app_id'],
            "client_secret" => $this->config['qq']['app_secret'],
            "redirect_uri"  => $this->config['qq']['callback'] ?? (string)get_url('ThirdAuth/callback', ['platform' => 'qq'], false, true),
            "code"          => $code,
        );

        $ret = HttpHelper::get(self::GET_ACCESS_TOKEN_URL, $queryArr);
        if ($ret['code'] == 0) {
            return [];
        }
        $params = [];
        parse_str($ret['data'], $params);
        return $params ?: [];
    }

    /**
     * 获取open_id
     * @param string $access_token
     * @return string
     */
    private function getOpenId($access_token = ''): string
    {
        $response = HttpHelper::get(self::GET_OPENID_URL, ['access_token' => $access_token]);

        if (!$response['code']) {
            return '';
        }
        if (strpos($response['data'], "callback") !== false) {
            $left_pos = strpos($response['data'], "(");
            $right_pos = strrpos($response['data'], ")");
            $response['data'] = substr($response['data'], $left_pos + 1, $right_pos - $left_pos - 1);
        }
        $user = (array)json_decode($response['data'], true);
        return $user['openid'] ?? '';
    }
}
