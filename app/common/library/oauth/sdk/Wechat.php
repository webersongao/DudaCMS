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

use app\common\library\helper\HttpHelper as Http;
use think\facade\Request;

/**
 * 微信
 */
class Wechat
{
    const GET_AUTH_CODE_URL = "https://open.weixin.qq.com/connect/oauth2/authorize";
    const GET_ACCESS_TOKEN_URL = "https://api.weixin.qq.com/sns/oauth2/access_token";
    const GET_USERINFO_URL = "https://api.weixin.qq.com/sns/userinfo";

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
        $state = (string) session('third_state_wechat');
        if ($state === '') {
            $state = bin2hex(random_bytes(16));
            session('third_state_wechat', $state);
        }
        $queryArr = array(
            "appid"         => $this->config['wechat']['app_id'],
            "redirect_uri"  => $this->config['wechat']['callback'] ?? (string)get_url('ThirdAuth/callback', ['platform' => 'wechat'], false, true),
            "response_type" => "code",
            "scope"         => $this->config['wechat']['scope'],
            "state"         => $state,
        );
        Request::isMobile() && $queryArr['display'] = 'mobile';
        return self::GET_AUTH_CODE_URL . '?' . http_build_query($queryArr) . '#wechat_redirect';
    }

    /**
     * 获取用户信息
     * @param array $params
     */
    public function getUserInfo($params = [])
    {
        $params = $params ?: request()->param();
        $state = (string) ($params['state'] ?? '');
        $expected = (string) session('third_state_wechat');
        if ($state === '' || $expected === '' || !hash_equals($expected, $state) || empty($params['code'])) {
            return [];
        }
        session('third_state_wechat', null);

        $data = $this->getAccessToken((string) $params['code']);
        $access_token = (string) ($data['access_token'] ?? '');
        if ($access_token === '') {
            return [];
        }

        $userInfo = [];
        if (stripos((string) $this->config['wechat']['scope'], 'snsapi_userinfo') !== false) {
            $queryArr = [
                "access_token" => $access_token,
                "openid"       => (string) ($data['openid'] ?? ''),
                "lang"         => 'zh_CN'
            ];
            $ret = Http::get(self::GET_USERINFO_URL, $queryArr)['data'];
            $profile = is_array($ret) ? $ret : json_decode((string) $ret, true);
            if (!$profile || isset($profile['errcode'])) {
                return [];
            }
            $profile['avatar'] = $profile['headimgurl'] ?? '';
            $userInfo = $profile;
        }

        return [
            'access_token'  => $access_token,
            'refresh_token' => (string) ($data['refresh_token'] ?? ''),
            'expires_in'    => (int) ($data['expires_in'] ?? 0),
            'openid'        => (string) ($data['openid'] ?? ''),
            'unionid'       => (string) ($data['unionid'] ?? ''),
            'user_info'     => $userInfo
        ];
    }

    /**
     * 获取access_token
     * @param string code
     */
    public function getAccessToken($code = '')
    {
        if (!$code) {
            return [];
        }
        $queryArr = array(
            "appid"      => $this->config['wechat']['app_id'],
            "secret"     => $this->config['wechat']['app_secret'],
            "code"       => $code,
            "grant_type" => "authorization_code",
        );
        $response = Http::get(self::GET_ACCESS_TOKEN_URL, $queryArr);
        if (!$response['code']) {
            return [];
        }
        return json_decode($response['data'], true);
    }
}
