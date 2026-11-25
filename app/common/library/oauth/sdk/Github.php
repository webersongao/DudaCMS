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
        $email = (string) ($user['email'] ?? '');
        $emailVerified = false;
        // 现有 scope 已包含 user:email：公开邮箱为空时再读取已验证的主邮箱。
        if ($email === '') {
            $emails = $this->fetchJson('https://api.github.com/user/emails', $accessToken);
            foreach ($emails as $item) {
                if (!is_array($item) || empty($item['primary']) || empty($item['verified'])) {
                    continue;
                }
                $email = (string) ($item['email'] ?? '');
                $emailVerified = $email !== '';
                break;
            }
        } else {
            // /user 返回的 email 由 GitHub 账号配置确认，仍以邮箱列表中的 verified 状态为准。
            $emails = $this->fetchJson('https://api.github.com/user/emails', $accessToken);
            foreach ($emails as $item) {
                if (is_array($item) && strcasecmp((string) ($item['email'] ?? ''), $email) === 0) {
                    $emailVerified = !empty($item['verified']);
                    break;
                }
            }
        }

        return [
            'id'            => (string) $id,
            'nickname'      => (string) ($user['login'] ?? $user['name'] ?? ''),
            'avatar'        => (string) ($user['avatar_url'] ?? ''),
            'sex'           => 0,
            'email'         => $email,
            'email_verified'=> $emailVerified,
            'signature'     => (string) ($user['bio'] ?? ''),
        ];
    }
}
