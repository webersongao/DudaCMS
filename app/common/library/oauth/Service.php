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

        self::debugLog('connect_start', [
            'platform'     => $platform,
            'openid_hash'  => substr(md5($openid), 0, 8),
            'has_nickname' => !empty($params['user_info']['nickname'] ?? ''),
        ]);

        $time = time();
        $expiresIn = (int) ($params['expires_in'] ?? 0);
        $values = [
            'platform'      => $platform,
            'openid'        => $openid,
            'unionid'       => (string) ($params['unionid'] ?? ''),
            'open_username' => mb_substr((string) ($params['user_info']['nickname'] ?? ''), 0, 50),
            'access_token'  => (string) ($params['access_token'] ?? ''),
            'refresh_token' => (string) ($params['refresh_token'] ?? ''),
            'expires_in'    => $expiresIn,
            'login_time'    => $time,
            'expire_time'   => $time + $expiresIn,
            'token'         => (string) ($params['token'] ?? ''),
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

            db('oauth')->where(['id' => $third['id']])->update($values);
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

        $username = self::buildUsername((string) ($params['user_info']['nickname'] ?? ''));
        $password = bin2hex(random_bytes(max(6, (int) get_setting('password_min_length'))));
        $extend['sex'] = (int) ($params['user_info']['sex'] ?? 0);
        if (isset($params['user_info']['avatar'])) {
            $extend['avatar'] = mb_substr((string) $params['user_info']['avatar'], 0, 255);
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
