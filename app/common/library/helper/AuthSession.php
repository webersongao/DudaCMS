<?php
// +----------------------------------------------------------------------
// | DudaCMS 统一认证会话：前台/后台身份键隔离，兼容旧 session 与 remember cookie。
// +----------------------------------------------------------------------

namespace app\common\library\helper;

use think\facade\Session;

class AuthSession
{
    public const FRONT_UID = 'auth.front.uid';
    public const FRONT_INFO = 'auth.front.info';
    public const FRONT_TOKEN = 'auth.front.token';
    public const ADMIN_UID = 'auth.admin.uid';
    public const ADMIN_INFO = 'auth.admin.info';

    /** 清理前台身份，不影响后台登录态。 */
    public static function clearFront(): void
    {
        $token = (string) session(self::FRONT_TOKEN ?: '');
        $legacyToken = (string) session('login_token');
        foreach ([$token, $legacyToken] as $item) {
            if ($item !== '') {
                TokenHelper::delete($item);
            }
        }
        foreach ([self::FRONT_UID, self::FRONT_INFO, self::FRONT_TOKEN,
            'login_uid', 'login_user_info', 'login_token'] as $key) {
            session($key, null);
        }
        $cookieKey = self::rememberCookieKey();
        cookie($cookieKey, null);
    }

    /** 清理后台身份，不影响前台登录态。 */
    public static function clearAdmin(): void
    {
        foreach ([self::ADMIN_UID, self::ADMIN_INFO, 'admin_login_uid', 'admin_user_info', 'admin_login_user_info'] as $key) {
            session($key, null);
        }
    }

    /** 写入前台身份；调用方需在权限提升前执行 Session::regenerate(true)。 */
    public static function writeFront(int $uid, array $user = [], string $token = ''): void
    {
        session(self::FRONT_UID, $uid);
        session(self::FRONT_INFO, $user);
        session('login_uid', $uid);
        session('login_user_info', $user);
        if ($token !== '') {
            session(self::FRONT_TOKEN, $token);
            session('login_token', $token);
        }
    }

    /** 写入后台身份。 */
    public static function writeAdmin(int $uid, array $user = []): void
    {
        session(self::ADMIN_UID, $uid);
        session(self::ADMIN_INFO, $user);
        session('admin_login_uid', $uid);
        session('admin_user_info', $user);
    }

    public static function frontUid(bool $restoreRemember = true): int
    {
        $uid = (int) (session(self::FRONT_UID) ?: session('login_uid') ?: 0);
        if ($uid > 0) return $uid;
        if (!$restoreRemember || !SiteConfigHelper::account('remember_login_enable', false)) return 0;

        $cookieUid = (string) cookie(self::rememberCookieKey());
        if ($cookieUid === '') return 0;
        $payload = SignedValueHelper::verify('remember', $cookieUid);
        if ($payload === null || !str_contains($payload, '|')) {
            cookie(self::rememberCookieKey(), null);
            return 0;
        }
        [$rawUid, $fingerprint] = array_pad(explode('|', $payload, 2), 2, '');
        $uid = (int) $rawUid;
        $user = db('users')->where('uid', $uid)->field('uid,password,salt,status')->find();
        $expected = $user ? substr(hash('sha256', (string) $user['password'] . (string) $user['salt']), 0, 16) : '';
        if (!$user || (int) $user['status'] !== 1 || !hash_equals($expected, (string) $fingerprint)) {
            cookie(self::rememberCookieKey(), null);
            return 0;
        }
        session(self::FRONT_UID, $uid);
        session('login_uid', $uid);
        return $uid;
    }

    public static function adminUid(): int
    {
        return (int) (session(self::ADMIN_UID) ?: session('admin_login_uid') ?: 0);
    }

    public static function adminInfo(): array
    {
        $info = session(self::ADMIN_INFO) ?: session('admin_user_info') ?: session('admin_login_user_info');
        return is_array($info) ? $info : [];
    }

    public static function frontInfo(): array
    {
        $info = session(self::FRONT_INFO) ?: session('login_user_info');
        return is_array($info) ? $info : [];
    }

    /** 权限提升前统一刷新 session id。 */
    public static function regenerate(): void
    {
        Session::regenerate(true);
    }

    public static function rememberCookieKey(): string
    {
        return config('app.token')['key'] . '_' . md5('login_uid');
    }

    /** 写入签名 remember cookie，绑定密码指纹。 */
    public static function writeRemember(int $uid, array $user, int $ttl): void
    {
        if ($ttl <= 0 || !SiteConfigHelper::account('remember_login_enable', false)) return;
        $fingerprint = substr(hash('sha256', (string) ($user['password'] ?? '') . (string) ($user['salt'] ?? '')), 0, 16);
        $value = SignedValueHelper::sign('remember', $uid . '|' . $fingerprint, $ttl);
        cookie(self::rememberCookieKey(), $value, ['expire' => $ttl]);
    }
}
