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
 * GitHub OAuth2 登录。
 */
class Github extends OAuth2
{
    protected $platform = 'github';
    protected $authorizeUrl = 'https://github.com/login/oauth/authorize';
    protected $tokenUrl = 'https://github.com/login/oauth/access_token';
    protected $userInfoUrl = 'https://api.github.com/user';
    protected $defaultScope = 'read:user user:email';

    protected function fetchProfile(string $accessToken): array
    {
        $user = $this->fetchJson($this->userInfoUrl, $accessToken);
        $id = $user['id'] ?? '';
        if ($id === '') {
            return [];
        }
        return [
            'id'       => (string) $id,
            'nickname' => (string) ($user['login'] ?? $user['name'] ?? ''),
            'avatar'   => (string) ($user['avatar_url'] ?? ''),
            'sex'      => 0,
        ];
    }
}
