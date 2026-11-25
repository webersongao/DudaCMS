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

/**
 * 微博
 */
class Weibo
{
    const GET_AUTH_CODE_URL = "https://api.weibo.com/oauth2/authorize";
    const GET_ACCESS_TOKEN_URL = "https://api.weibo.com/oauth2/access_token";
    const GET_USERINFO_URL = "https://api.weibo.com/2/users/show.json";

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
        session('third_state_weibo', $state);
        $queryArr = array(
            "response_type" => "code",
            "client_id"     => $this->config['weibo']['app_id'],
            "redirect_uri"  => $this->config['weibo']['callback'] ?? (string)get_url('ThirdAuth/callback', ['platform' => 'weibo'], false, true),
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
        $expected = (string) session('third_state_weibo');
        if ($state === '' || $expected === '' || !hash_equals($expected, $state) || empty($params['code'])) {
            return [];
        }
        session('third_state_weibo', null);

        //获取access_token
        $data = $this->getAccessToken((string) $params['code']);
        $access_token = is_array($data) ? ($data['access_token'] ?? '') : '';
        if ($access_token === '') {
            return [];
        }
        $uid = (string) ($data['uid'] ?? '');
        $queryArr = [
            "access_token" => $access_token,
            "uid"          => $uid,
        ];
        $ret = Http::get(self::GET_USERINFO_URL, $queryArr);
        $userInfo = (array) json_decode((string) ($ret['data'] ?? ''), true);
        if (!$userInfo || isset($userInfo['error_code'])) {
            return [];
        }
        $userInfo['nickname'] = $userInfo['screen_name'] ?? '';
        $userInfo['avatar'] = $userInfo['profile_image_url'] ?? '';
        $userInfo['signature'] = $userInfo['description'] ?? '';
        return [
            'access_token'  => $access_token,
            'refresh_token' => (string) ($data['refresh_token'] ?? ''),
            'expires_in'    => (int) ($data['expires_in'] ?? 0),
            'openid'        => $uid,
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
            return '';
        }
        $queryArr = array(
            "grant_type"    => "authorization_code",
            "client_id"     => $this->config['weibo']['app_id'],
            "client_secret" => $this->config['weibo']['app_secret'],
            "redirect_uri"  => $this->config['weibo']['callback'] ?? (string)get_url('ThirdAuth/callback', ['platform' => 'weibo'], false, true),
            "code"          => $code,
        );
        $response = Http::post(self::GET_ACCESS_TOKEN_URL, $queryArr);
        if (!$response['code']) {
            return [];
        }
        $ret = json_decode($response['data'], true);
        return $ret ?: [];
    }
}
