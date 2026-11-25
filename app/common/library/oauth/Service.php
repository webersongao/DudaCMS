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
namespace app\common\library\oauth;

use app\common\library\helper\MailHelper;
use app\common\library\helper\PresentationHelper;
use app\common\library\helper\SecretHelper;
use app\model\Users;
use app\model\Third;
use think\facade\Db;

/**
 * 第三方登录服务类
 */
class Service
{
    private static function debugLog(string $event, array $context = []): void
    {
        \think\facade\Log::write(
            '[oauth_connect][' . $event . '] ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'error'
        );
    }

    /** 归一化第三方邮箱：只保留合法地址并统一小写。 */
    private static function normalizeEmail($email): string
    {
        $email = strtolower(trim((string) $email));
        return MailHelper::isEmail($email) ? mb_substr($email, 0, 255) : '';
    }

    /** 邮箱是否已被其它用户占用。 */
    private static function emailOwner(string $email, int $uid = 0)
    {
        if ($email === '') {
            return null;
        }
        $query = db('users')->where('email', $email);
        if ($uid > 0) {
            $query->where('uid', '<>', $uid);
        }
        return $query->find();
    }

    /** 第三方平台返回的邮箱是否已验证，只有明确验证通过才视为已验证。 */
    private static function isEmailVerified($verified): bool
    {
        if (is_bool($verified)) {
            return $verified;
        }
        if (is_numeric($verified)) {
            return (int) $verified === 1;
        }
        return in_array(strtolower(trim((string) $verified)), ['1', 'true', 'yes', 'verified'], true);
    }

    /** 不同平台的性别字段统一为 users.sex：1男 2女 0保密。 */
    private static function normalizeSex($sex): int
    {
        if (is_numeric($sex)) {
            $sex = (int) $sex;
            return $sex === 1 || $sex === 2 ? $sex : 0;
        }
        $sex = strtolower(trim((string) $sex));
        if (in_array($sex, ['1', 'male', 'm', '男'], true)) {
            return 1;
        }
        if (in_array($sex, ['2', 'female', 'f', '女'], true)) {
            return 2;
        }
        return 0;
    }

    /**
     * 第三方登录
     * @param string $platform 平台
     * @param array $params 参数
     * @param array $extend 会员扩展信息
     */
    public static function connect(string $platform, array $params = [], array $extend = [])
    {
        $openid = (string) ($params['openid'] ?? '');
        if ($openid === '') {
            self::debugLog('openid_missing', ['platform' => $platform]);
            return false;
        }

        $userInfo = is_array($params['user_info'] ?? null) ? $params['user_info'] : [];
        $email = self::normalizeEmail($userInfo['email'] ?? '');
        $emailVerified = $email !== '' && self::isEmailVerified($userInfo['email_verified'] ?? false);

        self::debugLog('connect_start', [
            'platform'      => $platform,
            'openid_hash'   => substr(md5($openid), 0, 8),
            'has_nickname'  => !empty($userInfo['nickname'] ?? ''),
            'has_email'     => $email !== '',
            'email_verified'=> $emailVerified,
        ]);

        $time = time();
        $expiresIn = (int) ($params['expires_in'] ?? 0);
        $values = [
            'platform'      => $platform,
            'openid'        => $openid,
            'unionid'       => (string) ($params['unionid'] ?? ''),
            'open_username' => mb_substr((string) ($userInfo['nickname'] ?? ''), 0, 50),
            'access_token'  => SecretHelper::encrypt($params['access_token'] ?? ''),
            'refresh_token' => SecretHelper::encrypt($params['refresh_token'] ?? ''),
            'expires_in'    => $expiresIn,
            'login_time'    => $time,
            'expire_time'   => $time + $expiresIn,
            'token'         => SecretHelper::encrypt($params['token'] ?? ''),
        ];

        $third = db('oauth')->where(['platform' => $platform, 'openid' => $openid])->find();

        if ($third) {
            $user = $third['uid'] ? Users::getUserInfo($third['uid']) : [];
            if (!$user) {
                self::debugLog('bound_user_missing', [
                    'platform'    => $platform,
                    'openid_hash' => substr(md5($openid), 0, 8),
                    'uid'         => (int) ($third['uid'] ?? 0),
                ]);
                return false;
            }

            Db::startTrans();
            try {
                db('oauth')->where(['id' => $third['id']])->update($values);

                // 老绑定用户此前没有邮箱：本次平台若能返回已验证邮箱，直接补全为已验证绑定邮箱。
                if ($email !== '' && $emailVerified && (string) ($user['email'] ?? '') === '') {
                    if (self::emailOwner($email, (int) ($third['uid'] ?? 0))) {
                        self::debugLog('existing_email_conflict', [
                            'platform'    => $platform,
                            'uid'         => (int) ($third['uid'] ?? 0),
                        ]);
                    } else {
                        db('users')->where('uid', (int) $third['uid'])->update([
                            'email'          => $email,
                            'is_valid_email' => 1,
                            'group_id'       => 4,
                            'update_time'    => $time,
                        ]);
                        $user['email'] = $email;
                        self::debugLog('existing_email_bound', [
                            'platform'    => $platform,
                            'uid'         => (int) ($third['uid'] ?? 0),
                        ]);
                    }
                }
                Db::commit();
            } catch (\Throwable $e) {
                Db::rollback();
                self::debugLog('existing_update_exception', [
                    'platform' => $platform,
                    'message'  => $e->getMessage(),
                ]);
                return false;
            }

            if (!Users::extracted($user)) {
                self::debugLog('existing_login_failed', [
                    'platform'    => $platform,
                    'openid_hash' => substr(md5($openid), 0, 8),
                    'uid'         => (int) ($third['uid'] ?? 0),
                ]);
                return false;
            }

            self::debugLog('connect_existing_ok', [
                'platform'    => $platform,
                'openid_hash' => substr(md5($openid), 0, 8),
                'uid'         => (int) ($third['uid'] ?? 0),
            ]);
            return true;
        }

        // 邮箱已属于其它账号时不能自动绑定，否则会绕过邮箱唯一语义并造成账号混淆。
        if ($email !== '' && self::emailOwner($email)) {
            self::debugLog('new_email_conflict', [
                'platform'    => $platform,
                'openid_hash' => substr(md5($openid), 0, 8),
            ]);
            return false;
        }

        $nickname = trim(strip_tags((string) ($userInfo['nickname'] ?? '')));
        $username = self::buildUsername($nickname);
        $password = bin2hex(random_bytes(max(6, (int) get_setting('password_min_length'))));

        // 只使用平台已授权并返回的数据作为新用户默认值，不覆盖管理员显式传入的 extend。
        if ($nickname !== '' && empty($extend['nick_name'])) {
            $extend['nick_name'] = mb_substr($nickname, 0, 50);
        }
        if (empty($extend['sex'])) {
            $extend['sex'] = self::normalizeSex($userInfo['sex'] ?? 0);
        }
        if (empty($extend['avatar']) && !empty($userInfo['avatar'])) {
            $extend['avatar'] = mb_substr(PresentationHelper::safeUrl($userInfo['avatar'], ''), 0, 255);
        }
        if (empty($extend['signature']) && !empty($userInfo['signature'])) {
            $extend['signature'] = mb_substr(trim(strip_tags((string) $userInfo['signature'])), 0, 255);
        }
        if (!empty($userInfo['birthday'])) {
            $birthday = $userInfo['birthday'];
            if (!is_numeric($birthday)) {
                $birthday = strtotime((string) $birthday);
            }
            if ($birthday && empty($extend['birthday'])) {
                $extend['birthday'] = (int) $birthday;
            }
        }
        if ($email !== '') {
            $extend['email'] = $email;
            $extend['is_valid_email'] = $emailVerified ? 1 : 0;
            if ($emailVerified) {
                $extend['group_id'] = 4;
            }
        }

        Db::startTrans();
        try {
            $uid = Users::registerUser($username, $password, $extend, false);
            if (!$uid) {
                self::debugLog('register_failed', [
                    'platform'     => $platform,
                    'openid_hash'  => substr(md5($openid), 0, 8),
                    'username_hash'=> substr(md5($username), 0, 8),
                    'error'        => Users::getError(),
                ]);
                Db::rollback();
                return false;
            }

            $user = Users::getUserInfo($uid);
            if (!$user) {
                throw new \RuntimeException('新注册用户读取失败：' . $uid);
            }

            $values['uid'] = (int) $uid;
            $existId = db('oauth')->where(['openid' => $openid, 'platform' => $platform])->value('id');
            if ($existId) {
                db('oauth')->where(['id' => $existId])->update($values);
            } else {
                Third::create($values);
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            self::debugLog('connect_exception', [
                'platform'    => $platform,
                'openid_hash' => substr(md5($openid), 0, 8),
                'message'     => $e->getMessage(),
                'file'        => basename($e->getFile()),
                'line'        => $e->getLine(),
            ]);
            return false;
        }

        if (!Users::extracted($user)) {
            self::debugLog('new_login_failed', [
                'platform'    => $platform,
                'openid_hash' => substr(md5($openid), 0, 8),
                'uid'         => (int) $uid,
            ]);
            return false;
        }

        self::debugLog('connect_new_ok', [
            'platform'    => $platform,
            'openid_hash' => substr(md5($openid), 0, 8),
            'uid'         => (int) $uid,
        ]);
        return true;
    }

    private static function buildUsername(string $nickname): string
    {
        $nickname = trim($nickname);
        if ($nickname !== '') {
            $candidate = mb_substr($nickname, 0, 50);
            if (!Users::checkUserExist($candidate)) {
                return $candidate;
            }
        }

        do {
            $candidate = 'U' . bin2hex(random_bytes(4));
        } while (Users::checkUserExist($candidate));

        return $candidate;
    }
}
