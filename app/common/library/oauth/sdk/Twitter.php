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
 * Twitter / X OAuth2 登录（授权码 + PKCE）。
 */
class Twitter extends OAuth2
{
    protected $platform = 'twitter';
    protected $authorizeUrl = 'https://x.com/i/oauth2/authorize';
    protected $tokenUrl = 'https://api.x.com/2/oauth2/token';
    protected $userInfoUrl = 'https://api.x.com/2/users/me?user.fields=profile_image_url';
    protected $defaultScope = 'tweet.read users.read offline.access';
    protected $usePkce = true;

    /**
     * X 的 token 端点要求 Basic Auth。
     */
    protected function tokenRequestOptions(array &$params): array
    {
        $basic = base64_encode(
            rawurlencode((string) $this->setting('app_id')) . ':' . rawurlencode((string) $this->setting('app_secret'))
        );
        unset($params['client_secret']);
        return [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . $basic,
            ],
        ];
    }

    protected function fetchProfile(string $accessToken): array
    {
        $response = $this->fetchJson($this->userInfoUrl, $accessToken);
        $user = $response['data'] ?? [];
        if (!is_array($user) || ($user['id'] ?? '') === '') {
            return [];
        }
        return [
            'id'        => (string) $user['id'],
            'nickname'  => (string) ($user['name'] ?? $user['username'] ?? ''),
            'avatar'    => (string) ($user['profile_image_url'] ?? ''),
            'sex'       => 0,
            'signature' => (string) ($user['description'] ?? ''),
        ];
    }
}
