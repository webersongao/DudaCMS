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

use app\common\library\helper\RandomHelper;
use app\model\Users;
use app\model\Third;
use think\facade\Db;

/**
 * 第三方登录服务类
 */
class Service
{
    /**
     * 第三方登录
     * @param string $platform 平台
     * @param array $params 参数
     * @param array $extend 会员扩展信息
     */
    public static function connect(string $platform, array $params = [], array $extend = [])
    {
        $openid = (string) ($params['openid'] ?? '');
        $expiresIn = (int) ($params['expires_in'] ?? 0);
        if ($openid === '') {
            return false;
        }
        $time = time();
        $values = [
            'platform'      => $platform,
            'openid'        => $openid,
            'open_username' => $params['user_info']['nickname'] ?? '',
            'access_token'  => $params['access_token'] ?? '',
            'refresh_token' => $params['refresh_token'] ?? '',
            'expires_in'    => $expiresIn,
            'login_time'    => $time,
            'expire_time'   => $time + $expiresIn,
            'token'         => $params['token'] ?? '',
        ];

        $third = db('oauth')->where(['platform' => $platform, 'openid' => $params['openid']])->find();

        if ($third) {
            $user = $third['uid'] ? Users::getUserInfo($third['uid']) : [];
            if (!$user) {
                return false;
            }
            Users::extracted($user);
            return true;
        } else {
            $username = 'U' . RandomHelper::alnum(intval(get_setting('username_min_length')));
            if (isset($params['user_info']['nickname'])) {
                $username = Users::checkUserExist($params['user_info']['nickname']) ? 'U' . RandomHelper::alnum(intval(get_setting('username_min_length'))) : $params['user_info']['nickname'];
            }
            $password = RandomHelper::alnum(get_setting('password_min_length'));

            \think\facade\Db::startTrans();
            try {
                $extend['sex'] = [
                    $params['user_info']['sex'] ?? 0
                ];

                if (isset($params['user_info']['avatar'])) {
                    $extend['avatar'] = $params['user_info']['avatar'];
                }

                // 默认注册一个会员
                $uid = Users::registerUser($username, $password, $extend);

                if (!$uid) {
                    return false;
                }

                $user = Users::getUserInfo($uid);
                // 保存第三方信息
                $values['uid'] = $uid;
                if ($id = db('oauth')->where(['openid' => $values['openid'], 'platform' => $values['platform']])->value('id')) {
                    db('oauth')->where(['id' => $id])->update($values);
                } else {
                    Third::create($values);
                }

                if ($user) {
                    // 直接登录
                    Users::extracted($user);
                }

                \think\facade\Db::commit();
                return true;
            } catch (\Exception $e) {
                \think\facade\Db::rollback();
                return false;
            }
        }
    }
}
