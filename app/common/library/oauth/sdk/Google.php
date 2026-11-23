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

/**
 * Google OAuth2 登录（OpenID Connect userinfo）。
 */
class Google extends OAuth2
{
    protected $platform = 'google';
    protected $authorizeUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected $tokenUrl = 'https://oauth2.googleapis.com/token';
    protected $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
    protected $defaultScope = 'openid email profile';

    protected function tokenRequestOptions(array &$params): array
    {
        // Google 允许在请求体传 client_secret，保持统一实现即可。
        return parent::tokenRequestOptions($params);
    }

    protected function fetchProfile(string $accessToken): array
    {
        $user = $this->fetchJson($this->userInfoUrl, $accessToken);
        $id = $user['sub'] ?? '';
        if ($id === '') {
            return [];
        }
        $gender = $user['gender'] ?? '';
        $sex = $gender === 'male' ? 1 : ($gender === 'female' ? 2 : 0);
        return [
            'id'       => (string) $id,
            'nickname' => (string) ($user['name'] ?? $user['email'] ?? ''),
            'avatar'   => (string) ($user['picture'] ?? ''),
            'sex'      => $sex,
        ];
    }
}
