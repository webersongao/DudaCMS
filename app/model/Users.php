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

namespace app\model;

use app\common\library\helper\ImageHelper;
use app\common\library\helper\IpHelper;
use app\common\library\helper\LogHelper;
use app\common\library\helper\MailHelper;
use app\common\library\helper\NotifyHelper;
use app\common\library\helper\RandomHelper;
use app\common\library\helper\SignedValueHelper;
use app\common\library\helper\StringHelper;
use app\common\library\helper\TokenHelper;
use app\common\service\SmsService;
use app\logic\search\libs\ElasticSearch;
use app\service\StorageCleanupService;
use Overtrue\Pinyin\Pinyin;
use Pay\Exceptions\Exception;

/**
 * 公用用户模型
 * Class Users
 * @package app\model
 */
class Users extends BaseModel
{
    protected $pk = 'uid';
    protected $name = 'users';
    public static $_token;
    public static $_user;
    public static $_is_login;

    /** 前台资料可更新字段白名单 */
    public const PROFILE_FIELDS = [
        'nick_name', 'avatar', 'signature', 'birthday', 'url_token', 'sex', 'user_banner', 'is_first_login',
    ];

    /** 任何入口都禁止通过通用更新接口改写的字段 */
    public const IMMUTABLE_FIELDS = [
        'uid', 'group_id', 'status', 'forbidden_ip', 'client', 'credit_group_id',
    ];

    /**
     * 删除用户
     * @param $uid
     * @param int $realMove 1真实删除
     * @return bool
     */
    public static function removeUser($uid, int $realMove = 0): bool
    {
        $uid = is_array($uid) ? $uid : explode(',', $uid);
        if (empty($uid = array_diff($uid, [1, getLoginUid()]))) {
            self::setError('不可删创世管理员或自己');
            return false;
        }

        \think\facade\Db::startTrans();
        try {
            if ($realMove) {
                // 先收集待清理的上传资源，避免记录删除后无法反查。
                $avatars = db('users')->whereIn('uid', $uid)->column('avatar');
                $question_ids = db('question')->whereIn('uid', $uid)->column('id');
                $answer_ids = array_values(array_unique(array_merge(
                    db('answer')->whereIn('question_id', $question_ids)->column('id'),
                    db('answer')->whereIn('uid', $uid)->column('id')
                )));
                $article_ids = db('article')->whereIn('uid', $uid)->column('id');
                $article_covers = db('article')->whereIn('uid', $uid)->column('cover');

                //真实删除用户
                db('users')->whereIn('uid', $uid)->delete();
                //删除问题
                db('question')->whereIn('id', $question_ids)->delete();
                //删除问题下的回答及该用户的回答
                if ($answer_ids) {
                    db('answer')->whereIn('id', $answer_ids)->delete();
                }
                //删除操作记录
                db('action_log')->whereIn('uid', $uid)->delete();
                db('action_log_all')->whereIn('uid', $uid)->delete();
                //删除关注好友记录
                db('users_follow')->whereRaw('fans_uid IN(' . implode(',', $uid) . ') OR friend_uid IN(' . implode(',', $uid) . ')')->delete();
                //删除用户的文章
                db('article')->whereIn('id', $article_ids)->delete();
                db('article_comment')->whereIn('article_id', $article_ids)->delete();
                db('article_comment')->whereIn('uid', $uid)->delete();
                //删除通知和私信数据
                db('users_inbox')->whereIn('uid', $uid)->delete();
                db('users_inbox_dialog')->whereRaw('`sender_uid` IN(' . implode(',', $uid) . ') OR `recipient_uid` IN(' . implode(',', $uid) . ')')->delete();

                db('users_notify')->whereRaw('`sender_uid` IN(' . implode(',', $uid) . ') OR `recipient_uid` IN(' . implode(',', $uid) . ')')->delete();

                //删除首页数据
                db('post_relation')->whereIn('uid', $uid)->delete();

                //清理头像与内容附件/封面（两阶段，事务提交后物理删除）
                foreach (array_merge($avatars, $article_covers) as $url) {
                    if ($url !== null && $url !== '') {
                        StorageCleanupService::markByUrl((string) $url);
                    }
                }
                StorageCleanupService::markByItems('question', $question_ids);
                StorageCleanupService::markByItems('question_attach', $question_ids);
                StorageCleanupService::markByItems('answer', $answer_ids);
                StorageCleanupService::markByItems('answer_attach', $answer_ids);
                StorageCleanupService::markByItems('article', $article_ids);
                StorageCleanupService::markByItems('article_attach', $article_ids);
            } else {
                db('users')->whereIn('uid', $uid)->update(['status' => 0]);
                //问题
                $question_ids = db('question')->whereIn('uid', $uid)->column('id');
                //删除问题
                db('question')->whereIn('id', $question_ids)->update(['status' => 0]);
                //删除问题下的回答
                db('answer')->whereIn('question_id', $question_ids)->update(['status' => 0]);
                //删除该用户的回答
                db('answer')->whereIn('uid', $uid)->update(['status' => 0]);
                //删除操作记录
                db('action_log')->whereIn('uid', $uid)->update(['status' => 0]);
                db('action_log_all')->whereIn('uid', $uid)->update(['status' => 0]);

                //删除用户的文章
                $article_ids = db('article')->whereIn('uid', $uid)->column('id');
                db('article')->whereIn('id', $article_ids)->update(['status' => 0]);
                db('article_comment')->whereIn('article_id', $article_ids)->update(['status' => 0]);
                db('article_comment')->whereIn('uid', $uid)->update(['status' => 0]);

                //删除通知和私信数据
                db('users_inbox')->whereIn('uid', $uid)->update(['status' => 0]);
                db('users_inbox_dialog')->whereRaw('`sender_uid` IN(' . implode(',', $uid) . ') OR `recipient_uid` IN(' . implode(',', $uid) . ')')->update(['status' => 0]);

                db('users_notify')->whereRaw('`sender_uid` IN(' . implode(',', $uid) . ') OR `recipient_uid` IN(' . implode(',', $uid) . ')')->update(['status' => 0]);

                //删除首页数据
                db('post_relation')->whereIn('uid', $uid)->update(['status' => 0]);
            }
            \think\facade\Db::commit();
            StorageCleanupService::flush();
            return true;
        } catch (\Exception $e) {
            StorageCleanupService::clearPending();
            self::setError($e->getMessage());
            \think\facade\Db::rollback();
            return false;
        }
    }

    /**
     * 恢复用户
     * @param $uid
     * @return bool
     */
    public static function recoverUsers($uid): bool
    {
        $uid = is_array($uid) ? $uid : explode(',', $uid);
        \think\facade\Db::startTrans();
        try {
            db('users')->whereIn('uid', $uid)->update(['status' => 1]);
            //问题
            $question_ids = db('question')->whereIn('uid', $uid)->column('id');
            //删除问题
            db('question')->whereIn('id', $question_ids)->update(['status' => 1]);
            //删除问题下的回答
            db('answer')->whereIn('question_id', $question_ids)->update(['status' => 1]);
            //删除该用户的回答
            db('answer')->whereIn('uid', $uid)->update(['status' => 1]);
            //删除操作记录
            db('action_log')->whereIn('uid', $uid)->update(['status' => 1]);
            db('action_log_all')->whereIn('uid', $uid)->update(['status' => 1]);

            //删除用户的文章
            $article_ids = db('article')->whereIn('uid', $uid)->column('id');
            db('article')->whereIn('id', $article_ids)->update(['status' => 1]);
            db('article_comment')->whereIn('article_id', $article_ids)->update(['status' => 1]);
            db('article_comment')->whereIn('uid', $uid)->update(['status' => 1]);

            //删除通知和私信数据
            db('users_inbox')->whereIn('uid', $uid)->update(['status' => 1]);
            db('users_inbox_dialog')->whereRaw('`sender_uid` IN(' . implode(',', $uid) . ') OR `recipient_uid` IN(' . implode(',', $uid) . ')')->update(['status' => 1]);

            db('users_notify')->whereRaw('`sender_uid` IN(' . implode(',', $uid) . ') OR `recipient_uid` IN(' . implode(',', $uid) . ')')->update(['status' => 1]);

            //删除首页数据
            db('post_relation')->whereIn('uid', $uid)->update(['status' => 1]);
            \think\facade\Db::commit();
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            \think\facade\Db::rollback();
            return false;
        }
    }

    /**
     * 判断用户登录，并返回所有信息，写入session
     * @param $account
     * @param $password
     * @param int $remember
     * @param string $client
     * @return bool|mixed
     */
    public static function getLogin($account, $password, int $remember = 0, string $client = 'pc')
    {
        $user = self::checkUserExist($account);
        if (!$user) {
            self::setError('用户不存在');
            return false;
        }

        if ($user['status'] == 0 || $user['status'] == 2) {
            self::setError('用户不存在或未审核通过');
            return false;
        }

        $errorCount = cache($user['uid'] . '_error_count') ?: 0;
        $passwordOk = verify_password($password, (string) $user['password'], (string) $user['salt']);
        if ($passwordOk && password_needs_upgrade((string) $user['password'])) {
            // MD5 摘要密码在登录成功后自动升级为 bcrypt
            db('users')->where('uid', $user['uid'])->update(['password' => hash_password($password)]);
        }
        $password_error_limit_time = intval(get_setting('password_lock_minutes'));
        if (!$passwordOk || ($password_error_limit_time > 0 && cache($user['uid'] . '_error_time'))) {
            $errors_exceeds_limit_password = intval(get_setting('password_max_retries'));

            //限制时长
            if (cache($user['uid'] . '_error_time') && $password_error_limit_time > 0) {
                $time = $password_error_limit_time - round((time() - (int)cache($user['uid'] . '_error_time')) / 60);
                self::setError('请等待' . $time . '分钟后重试');
                cache($user['uid'] . '_error_count', null);
            } else {
                ++$errorCount;
                if ($errors_exceeds_limit_password && $errors_exceeds_limit_password <= $errorCount && $password_error_limit_time > 0) {
                    cache($user['uid'] . '_error_time', time(), ['expire' => $password_error_limit_time * 60]);
                    self::setError('您已达到最大重试次数,请等待' . $password_error_limit_time . '分钟后重试');
                } elseif ($errors_exceeds_limit_password && $errors_exceeds_limit_password <= $errorCount) {
                    self::setError('账号已锁定，请联系管理员');
                } else {
                    if ($errors_exceeds_limit_password) {
                        self::setError('用户密码不正确,您还可继续重试' . (int)($errors_exceeds_limit_password - $errorCount) . '次');
                        cache($user['uid'] . '_error_count', $errorCount);
                    } else {
                        self::setError('用户密码不正确');
                    }
                }
            }
            return false;
        }
        $user['client'] = $client;
        return self::extracted($user, $remember);
    }

    /**
     * 随机默认头像（新用户注册时写入）
     * 16 个几何符号头像，Modern Zhihu × Dark Humor 风格，12px 亦可辨识。
     * 游客/匿名/空头像统一走 default-avatar.svg 兜底。
     */
    public static function randomAvatar(): string
    {
        static $avatars = null;
        if ($avatars === null) {
            $avatars = [];
            for ($i = 1; $i <= 16; $i++) {
                $avatars[] = '/static/common/image/avatars/avatar-' . str_pad((string)$i, 2, '0', STR_PAD_LEFT) . '.svg';
            }
        }
        return $avatars[array_rand($avatars)];
    }

    /**
     * 新用户注册
     * @param string $account
     * @param string $password
     * @param array $extend
     * @param bool $login
     * @param bool $admin_register
     * @param string $client
     * @return mixed
     */
    public static function registerUser(string $account, string $password, array $extend = [], bool $login = true, $admin_register = false, string $client = 'pc')
    {
        if (!$account || !$password) {
            self::setError('账号或密码必填');
            return false;
        }

        if (self::checkUserExist($account) || (isset($extend['nick_name']) && self::checkUserExist($extend['nick_name']))) {
            self::setError('用户已存在');
            return false;
        }

        $data = array();
        $salt = RandomHelper::alnum();
        $data['create_time'] = time();
        $data['update_time'] = time();

        $data = $extend ? array_merge($data, $extend) : $data;
        $data['user_name'] = $account;
        $data['password'] = hash_password($password);
        $data['salt'] = $salt;
        $data['reg_ip'] = request()->ip();
        $data['is_first_login'] = 1;
        $data['nick_name'] = $data['nick_name'] ?? $account;

        $pinyin = new Pinyin();
        $url_token = $pinyin->permalink($account, '');
        $token = db('users')->where('url_token', $url_token)->value('url_token');
        $url_token = $token ? $url_token . '-' . time() : $url_token;
        if (!$url_token) {
            $url_token = StringHelper::uuid('uniqid');
        }

        $data['url_token'] = $url_token;
        $data['credit_group_id'] = $extend['credit_group_id'] ?? ($data['credit_group_id'] ?? 1);
        $data['group_id'] = $extend['group_id'] ?? ($data['group_id'] ?? 4);
        $data['client'] = $client;
        if (empty($data['avatar'])) {
            $data['avatar'] = self::randomAvatar();
        }

        if (!$data['group_id'] && get_setting('register_valid_type') == 'email') {
            $data['group_id'] = 3; //未验证系统组
        }

        if (get_setting('register_valid_type') == 'admin') {
            $data['status'] = 2;
        }
        //$data['group_id'] = $data['group_id']?:4;
        try {
            $uid = db('users')->strict(false)->insertGetId($data);
            //用户通知配置
            db('users_extends')->insert([
                'uid' => $uid,
                'inbox_setting' => 'all',
                'notify_setting' => json_encode(NotifyHelper::getDefaultNotifyConfig(), JSON_UNESCAPED_UNICODE),
            ]);

            //添加积分记录
            LogHelper::addCreditLog('REGISTER', $uid, 'users', $uid);
            ElasticSearch::instance()->create('users', db('users')->find($uid));

            if ($admin_register) {
                return $uid;
            }

            if (get_setting('register_valid_type') == 'admin') {
                self::setError('注册成功，等待管理员审核');
                return false;
            } else {
                if ($login) {
                    Users::directLogin($uid);
                }
                return $uid;
            }
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * 获取用户信息
     * @param $uid
     * @param bool $extend
     * @return array|false
     */
    public static function getUserInfo($uid, bool $extend = true)
    {
        if (!$uid) {
            $user_info['avatar'] = '/static/common/image/default-avatar.svg';
            $user_info['url'] = 'javascript:;';
            $user_info['name'] = '游客';
            $user_info['group_id'] = 5;
            $user_info['status'] = 1;
            $user_info['group_name'] = '游客组';
            $user_info['verified'] = '';
            $user_info['credit_group_id'] = 1;
            $user_group_info = self::getUserGroupInfo();
            if ($user_group_info) {
                $user_info = array_merge($user_info, $user_group_info);
            }
            return $user_info;
        }

        $user_info = db('users')->find($uid);
        $user_extend_info = $extend ? db('users_extends')->withoutField('id')->where('uid', $uid)->find() : [];

        if (!$user_info) {
            return false;
        }
        $user_info['is_online'] = self::isOnline($uid);
        $user_info['avatar'] = $user_info['avatar'] ?: '/static/common/image/default-avatar.svg';
        $user_info['url'] = get_user_url($uid);
        $user_info['name'] = $user_info['nick_name'];
        $verify_info = $user_info['verified'] ? db('users_verify_type')->where(['name' => $user_info['verified'], 'status' => 1])->field('icon,title')->find() : [];
        $user_info['verified_icon'] = $verify_info ? $verify_info['icon'] : '';
        $user_info['verified_name'] = $verify_info ? $verify_info['title'] : '';

        $user_group_info = self::getUserGroupInfo($uid);
        if ($user_group_info) {
            $user_info = array_merge($user_info, $user_group_info);
        }

        if ($user_extend_info && $extend) {
            $user_info = array_merge($user_info, $user_extend_info);
        }
        return $user_info;
    }

    /**
     * 获取用户信息，根据字段
     * @param $uid
     * @param mixed $field
     * @param bool $extend
     * @param mixed $extend_field
     * @return false|array
     */
    public static function getUserInfoByUid($uid, $field = '*', bool $extend = false, $extend_field = '*')
    {
        $user_info = db('users')->where(['uid' => $uid])->field($field)->find();
        if (!$user_info) {
            return false;
        }

        if (isset($user_info['avatar']) && $user_info['avatar']) {
            $user_info['avatar'] = ImageHelper::replaceImageUrl($user_info['avatar']);
        } else {
            $user_info['avatar'] = request()->domain() . '/static/common/image/default-avatar.svg';
        }

        if (!isset($user_info['signature']) || !$user_info['signature']) $user_info['signature'] = '暂无个人签名...';

        $user_info['url'] = get_user_url($uid);
        $user_info['name'] = $user_info['nick_name'] ?? '';
        $verify_info = $user_info['verified'] ? db('users_verify_type')->where(['name' => $user_info['verified'], 'status' => 1])->field('icon,title')->find() : [];
        $user_info['verified_icon'] = $verify_info ? $verify_info['icon'] : '';
        $user_info['verified_name'] = $verify_info ? $verify_info['title'] : '';
        $user_extend_info = $extend ? db('users_extends')->withoutField('id')->where('uid', $uid)->field($extend_field)->find() : [];

        if ($user_extend_info) {
            $user_info = array_merge($user_info, $user_extend_info);
        }

        return $user_info;
    }

    /**
     * 批量获取用户展示信息，替代列表里逐行 getUserInfoByUid()
     */
    public static function getUserInfoMapByUids(array $uids): array
    {
        $uids = array_values(array_unique(array_filter(array_map('intval', $uids))));
        if (!$uids) {
            return [];
        }
        $rows = db('users')->whereIn('uid', $uids)->select()->toArray();
        $verifyRows = db('users_verify_type')->where(['status' => 1])->field('name,icon,title')->select()->toArray();
        $verifyTypes = [];
        foreach ($verifyRows as $verifyRow) {
            $verifyTypes[$verifyRow['name']] = $verifyRow;
        }
        $domain = request()->domain();
        $map = [];
        foreach ($rows as $row) {
            $uid = (int) $row['uid'];
            $row['avatar'] = $row['avatar']
                ? ImageHelper::replaceImageUrl($row['avatar'])
                : $domain . '/static/common/image/default-avatar.svg';
            if (!isset($row['signature']) || !$row['signature']) {
                $row['signature'] = '暂无个人签名...';
            }
            $row['url'] = get_user_url($uid);
            $row['name'] = $row['nick_name'] ?? '';
            $verifyInfo = $row['verified'] ? ($verifyTypes[$row['verified']] ?? []) : [];
            $row['verified_icon'] = $verifyInfo['icon'] ?? '';
            $row['verified_name'] = $verifyInfo['title'] ?? '';
            $map[$uid] = $row;
        }
        return $map;
    }

    /**
     * 获取用户组信息
     * @param int $uid
     * @return array|mixed
     */
    public static function getUserGroupInfo(int $uid = 0)
    {
        //游客组
        if (!$uid) {
            return [
                'permission' => json_decode(db('admin_group')->where('id', 5)->value('permission'), true)
            ];
        }

        static $groups = [];
        if (isset($groups[$uid])) {
            return $groups[$uid];
        }
        // 执行查询
        //$map[] = ['status', '=', 1];
        $map[] = ['uid', '=', $uid];

        $user_info = db('users')->where($map)->field('credit_group_id,group_id')->find();

        $user_groups = db('credit_group')->where('id', intval($user_info['credit_group_id']))->field('title as group_name,permission,group_icon')->find();
        //非普通用户使用系统组权限
        if (intval($user_info['group_id']) != 4) {
            $permission = db('admin_group')->where("id", intval($user_info['group_id']))->field('permission,title')->find();
            $user_groups['permission'] = $permission['permission'];
            $user_groups['group_name'] = $permission['title'];
            $user_groups['permission'] = json_decode($user_groups['permission'], true);
            $list = db('users_permission')->whereRaw("`group`='common' OR `group`='system'")->column('value', 'name');
        } else {
            $user_groups['permission'] = isset($user_groups['permission']) ? json_decode($user_groups['permission'], true) : [];
            $list = db('users_permission')->whereRaw("`group` = 'common' OR `group` = 'credit'")->column('value', 'name');
        }
        $user_groups['group_name'] = $user_groups['group_name'] ?? '未知组';
        $user_groups['permission'] = array_merge($list, $user_groups['permission']);

        $groups[$uid] = $user_groups ?: [];
        return $user_groups;
    }

    /**
     * 根据用户ids获取用户信息
     * @param $ids
     * @param string $field
     * @param int $status
     * @return array|false|false[]
     */
    public static function getUserInfoByIds($ids, string $field = '', int $status = 1)
    {
        if (!is_array($ids) || count($ids) == 0) {
            return false;
        }
        $ids = array_unique($ids);

        $where = [];

        if ($status != 99) {
            $where['status'] = $status;
        }

        $query = db('users')->where($where)->withoutField('password')->whereIn('uid', implode(',', $ids));
        if ($field !== '') {
            // 头像、昵称、主页地址是下面每条都要用到的，随主查询一次带出，避免逐行回查 dask_users
            $select = array_values(array_unique(array_merge(
                array_map('trim', explode(',', $field)),
                ['uid', 'nick_name', 'avatar', 'user_name', 'url_token']
            )));
            $query->field(implode(',', $select));
        }
        $user_info = $query->select()->toArray();

        $data = array();
        if ($user_info) {
            $domain = request()->domain();
            $online_map = self::getOnlineMap(array_column($user_info, 'uid'));
            foreach ($user_info as $key => $val) {
                $data[$val['uid']] = $val;
                $data[$val['uid']]['is_online'] = $online_map[$val['uid']] ?? false;
                $data[$val['uid']]['avatar'] = $val['avatar'] ? ImageHelper::replaceImageUrl($val['avatar']) : $domain . '/static/common/image/default-avatar.svg';
                $data[$val['uid']]['name'] = $val['nick_name'];
                $data[$val['uid']]['url'] = build_user_url($val['url_token'] ?? '', $val['user_name'] ?? '');
            }
        }
        return $data;
    }

    /**
     * 更新用户字段
     * @param $uid
     * @param $data //主表用户信息
     * @param $extend //附加表用户信息
     * @param bool $trusted 是否来自内部可信业务（密码/积分/认证等）
     * @return mixed
     */
    public static function updateUserFiled($uid, $data = null, $extend = null, bool $trusted = false): bool
    {
        // 更新主表
        if ($data) {
            if (!$trusted) {
                $data = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));
            }
            if (!$data) {
                return false;
            }
            if (array_intersect_key($data, array_flip(self::IMMUTABLE_FIELDS))) {
                return false;
            }
            // 所有用户资料写库统一按表结构归一化，空字符串不再写入数值/日期列。
            $data = db_input_normalize('users', $data);
            if (!$data) {
                return false;
            }
            $oldAvatar = isset($data['avatar'])
                ? (string) db('users')->where(['uid' => $uid])->value('avatar')
                : '';
            if (isset($data['password']) && $data['password']) {
                $data['salt'] = $data['salt'] ?? RandomHelper::alnum();
                // 已是 bcrypt/argon2 哈希时不要重复 hash
                $data['password'] = strpos((string) $data['password'], '$') === 0
                    ? $data['password']
                    : hash_password((string) $data['password']);
            }

            $pinyin = new Pinyin();
            if (isset($data['url_token']) && $data['url_token']) $data['url_token'] = $pinyin->permalink($data['url_token'], '');

            if (!db('users')->where(['uid' => $uid])->update($data)) return false;

            if ($oldAvatar !== '' && isset($data['avatar']) && (string) $data['avatar'] !== $oldAvatar) {
                StorageCleanupService::deleteByUrlNow($oldAvatar);
            }
        }

        // 更新附属表用户信息
        if ($extend && !db('users_extends')->where(['uid' => $uid])->update($extend)) return false;

        //更新缓存信息
        if ($uid === getLoginUid()) {
            $user = self::getUserInfo($uid);
            /*$cookieKey = config('app.token')['key'].'_'.md5('login_uid');
            $cookieUid = cookie($cookieKey,$user['uid']);*/
            session('login_user_info', $user);
        }

        return true;
    }

    /**
     * 仅更新前台资料白名单字段，禁止批量赋值敏感列
     */
    public static function updateProfileFields($uid, array $data, $extend = null): bool
    {
        $clean = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));
        if (!$clean) {
            return false;
        }
        return self::updateUserFiled($uid, $clean, $extend, false);
    }

    /**
     * 检查用户名是否已存在
     * @param $account
     * @param string $field
     * @return mixed
     */
    public static function checkUserExist($account, string $field = '*')
    {
        $where = $whereOr = array();
        if (MailHelper::isEmail($account)) {
            $where['email'] = $account;
        } else if (preg_match("/^1[3-9]\d{9}$/", $account)) {
            $where['mobile'] = $account;
        } else {
            $where['user_name'] = $account;
            $whereOr['nick_name'] = $account;
        }
        return db('users')->where($where)->whereOr($whereOr)->field($field)->find();
    }

    /**
     * 解析内容中的用户名
     * @param $content
     * @param bool $with_user 返回用户信息
     * @param bool $to_uid 转换用户名为uid
     * @return array
     */
    public static function parseAtUser($content, bool $with_user = false, bool $to_uid = false): array
    {
        $result = $all_users = array();
        $content_uid = '';
        preg_match_all('/@([^@,:\s,]+)/i', strip_tags($content), $result);
        if (is_array($result[1])) {
            $match_name = array();
            foreach ($result[1] as $user_name) {
                if (in_array($user_name, $match_name, true)) {
                    continue;
                }
                $match_name[] = $user_name;
            }

            $match_name = array_unique($match_name);

            arsort($match_name);

            $content_uid = $content;

            $userMap = [];
            if ($match_name) {
                $rows = db('users')->whereIn('user_name', $match_name)
                    ->whereOr('nick_name', 'in', $match_name)
                    ->whereOr('email', 'in', $match_name)
                    ->whereOr('mobile', 'in', $match_name)
                    ->select()->toArray();
                foreach ($rows as $row) {
                    foreach (['user_name', 'nick_name', 'email', 'mobile'] as $field) {
                        if (!empty($row[$field])) {
                            $userMap[$row[$field]] = $row;
                        }
                    }
                }
            }

            foreach ($match_name as $user_name) {
                $user_info = $userMap[$user_name] ?? null;
                if ($user_info) {
                    $content = str_replace('@' . $user_name, '<a href="' . get_user_url($user_info['uid'])  . '" class="dask-username text-primary me-1" data-id="' . $user_info['uid'] . '" target="_blank">@' . $user_info['nick_name'] . '</a>', $content);
                    if ($to_uid) {
                        $content_uid = str_replace('@' . $user_name, '@' . $user_info['uid'], $content_uid);
                    }
                    if ($with_user) {
                        $all_users[$user_info['uid']] = $user_info;
                    }
                }
            }
        }
        return [$content, $all_users, $content_uid];
    }

    /**
     * 获取用户列表
     * @param array $where
     * @param array $order
     * @param int $current_page
     * @param int $per_page
     * @param int $uid
     * @param string $pjax
     * @return array
     */
    public static function getUserList(array $where = [], array $order = [], int $current_page = 1, int $per_page = 10, int $uid = 0, string $pjax = 'mainTab'): array
    {
        $order = $order ?: $order = ['create_time' => 'desc'];

        $list = db('users')->where($where)->withoutField('password')->order($order)->paginate([
            'list_rows' => $per_page ?: 10, //每页数量
            'page' => $current_page ?: 1, //当前页面
            'query' => request()->param(),
            'pjax' => $pjax
        ]);
        $page = $list->render();
        $user = $list->toArray();
        $result = array();
        // 整页批量取在线/关注/认证/用户组，替代循环内逐行查询的 N+1。
        $uids = array_column($user['data'], 'uid');
        $id_list = implode(',', array_map('intval', $uids));
        $online_map = $uids ? self::getOnlineMap($uids) : [];
        $follow_map = ($uid && $id_list)
            ? db('users_follow')->where(['fans_uid' => $uid])->whereIn('friend_uid', $id_list)->column('id', 'friend_uid')
            : [];
        $verify_names = [];
        foreach ($user['data'] as $value) {
            if ($value['verified']) $verify_names[$value['verified']] = $value['verified'];
        }
        $verify_map = $verify_names
            ? db('users_verify_type')->where(['status' => 1])->whereIn('name', array_values($verify_names))
                ->field('name,icon,title')->select()->toArray()
            : [];
        $verify_map = array_column($verify_map, null, 'name');
        $admin_group_ids = $credit_group_ids = [];
        foreach ($user['data'] as $value) {
            if (intval($value['group_id']) == 4) {
                if (intval($value['credit_group_id'])) $credit_group_ids[] = intval($value['credit_group_id']);
            } elseif (intval($value['group_id'])) {
                $admin_group_ids[] = intval($value['group_id']);
            }
        }
        $admin_group_map = $admin_group_ids
            ? db('admin_group')->whereIn('id', implode(',', array_unique($admin_group_ids)))->column('title', 'id') : [];
        $credit_group_map = $credit_group_ids
            ? db('credit_group')->whereIn('id', implode(',', array_unique($credit_group_ids)))->column('title', 'id') : [];

        foreach ($user['data'] as $value) {
            $result[$value['uid']] = $value;
            $result[$value['uid']]['is_online'] = (bool) ($online_map[$value['uid']] ?? false);
            $result[$value['uid']]['has_focus'] = $follow_map[$value['uid']] ?? null;
            $result[$value['uid']]['avatar'] = $value['avatar'] ?: '/static/common/image/default-avatar.svg';
            $result[$value['uid']]['url'] = get_user_url($value['uid']);
            $result[$value['uid']]['name'] = $value['nick_name'];
            $verify_info = $value['verified'] && isset($verify_map[$value['verified']]) ? $verify_map[$value['verified']] : [];
            $result[$value['uid']]['verified_icon'] = $verify_info ? $verify_info['icon'] : '';
            $result[$value['uid']]['verified_name'] = $verify_info ? $verify_info['title'] : '';
            if (intval($value['group_id']) != 4) {
                $result[$value['uid']]['group_name'] = $admin_group_map[$value['group_id']] ?? '';
            } else {
                $result[$value['uid']]['group_name'] = $credit_group_map[$value['credit_group_id']] ?? '';
            }
        }
        return ['list' => $result, 'page' => $page, 'total' => $user['last_page']];
    }

    /**
     * 是否关注
     * @param $uid
     * @param $target_uid
     * @return mixed
     */
    public static function checkFocus($uid, $target_uid)
    {
        return db('users_follow')->where(['fans_uid' => $uid, 'friend_uid' => $target_uid])->value('id');
    }

    //检测用户是否在线
    public static function isOnline($uid): bool
    {
        if (get_setting('online_check') != 'Y') {
            return false;
        }

        //1分钟内不再检测
        $last_login_time = cache('last_login_time_' . $uid);

        if ($last_login_time && time() < $last_login_time + (get_setting('online_check_time') * 60)) {
            return true;
        }

        $map[] = array('uid', '=', $uid);
        $last_login_time = db('users_online')->where($map)->value('last_login_time');

        if (!$last_login_time) {
            return false;
        }

        if ($last_login_time + ((int)get_setting('online_check_time') * 60) > time()) {
            cache('last_login_time_' . $uid, $last_login_time, 60);
            return true;
        }
        return false;
    }

    /**
     * 批量在线状态，替代列表里逐行 isOnline() 查询
     * @param array $uids
     * @return array uid => bool
     */
    public static function getOnlineMap(array $uids): array
    {
        $uids = array_values(array_unique(array_filter(array_map('intval', $uids))));
        if (!$uids || get_setting('online_check') != 'Y') {
            return [];
        }

        $deadline = time() - ((int)get_setting('online_check_time') * 60);
        $last_login_times = db('users_online')->whereIn('uid', implode(',', $uids))->column('last_login_time', 'uid');

        $map = [];
        foreach ($last_login_times as $uid => $last_login_time) {
            $map[$uid] = $last_login_time > $deadline;
        }
        return $map;
    }

    /**
     * 更新用户通知未读数
     * @param $recipient_uid
     * @return bool
     */
    public static function updateNotifyUnread($recipient_uid)
    {
        $unread_num = db('users_notify')->where(['recipient_uid' => (int)$recipient_uid, 'read_flag' => 0])->count();
        return self::updateUserFiled($recipient_uid, ['notify_unread' => $unread_num], null, true);
    }

    /**
     * 更新私信数
     * @param $uid
     * @return mixed
     */
    public static function updateInboxUnread($uid)
    {
        $sender_unread_num = db('users_inbox_dialog')->where(['sender_uid' => (int)$uid])->sum('sender_unread');
        $recipient_unread_num = db('users_inbox_dialog')->where(['recipient_uid' => (int)$uid])->sum('recipient_unread');
        $unread_num = (int)($sender_unread_num + $recipient_unread_num);
        return self::updateUserFiled($uid, ['inbox_unread' => $unread_num], null, true);
    }

    /**
     * 获取热门用户
     * @param int $uid
     * @param array $where
     * @param array $order
     * @param int $per_page
     * @param int $page
     * @return mixed
     */
    public static function getHotUsers(int $uid = 0, array $where = [], array $order = [], int $per_page = 5, int $page = 1, $is_api = false)
    {
        $where = !empty($where) ? $where : [['status', '=', 1], ['credit', '>', 0]];
        $order = !empty($order) ? $order : ['credit' => 'DESC', 'answer_count' => 'DESC'];
        $cacheKey = 'hot_users_' . $uid . '_' . $per_page . '_' . $page . '_' . md5(json_encode([$where, $order]));
        $list = cache($cacheKey);
        if (!$list) {
            $list = db('users')
                ->where([['uid', '<>', $uid]])
                ->where($where)
                ->orderRaw('RAND()')
                ->order($order)
                ->limit($per_page)
                ->select()
                ->toArray();

            $avatar = request()->domain() . '/static/common/image/default-avatar.svg';
            // 批量查询“当前用户是否关注这些热门用户”，避免 N+1 且纠正方向
            $uids = array_map('intval', array_column($list, 'uid'));
            $focusUids = ($uid && $uids)
                ? db('users_follow')->where(['fans_uid' => (int) $uid, 'status' => 1])
                    ->whereIn('friend_uid', $uids)->column('friend_uid')
                : [];
            $focusUids = array_map('intval', $focusUids);

            foreach ($list as $key => $val) {
                if ($is_api) {
                    $list[$key]['avatar'] = $val['avatar'] ? ImageHelper::replaceImageUrl($val['avatar']) : $avatar;
                }
                $list[$key]['url'] = get_user_url($val['uid']);
                $list[$key]['is_focus'] = in_array((int) $val['uid'], $focusUids, true) ? 1 : 0;
            }
            cache($cacheKey, $list, 120);
        }
        return $list;
    }

    /**
     * 更新主页访问量
     * @param $user_id
     * @param int $uid
     * @return bool
     */
    public static function updateUsersViews($user_id, int $uid = 0): bool
    {
        $cache_key = md5('cache_user_' . $user_id . '_' . $uid);
        $cache_result = cache($cache_key);
        if ($cache_result) {
            return true;
        }
        cache($cache_key, $cache_key, ['expire' => 60]);
        return db('users')->where(['uid' => $user_id])->inc('views_count')->update();
    }

    /**
     * 退出登录
     * @return bool
     */
    public static function logout(): bool
    {
        $token = session('login_token');
        if ($token) {
            TokenHelper::delete($token);
        }
        session('admin_login_uid', null);
        session('admin_login_user_info', null);
        session('login_uid', null);
        session('login_user_info', null);
        $cookieKey = config('app.token')['key'] . '_' . md5('login_uid');
        cookie($cookieKey, null);
        self::$_is_login = false;
        self::$_user = null;
        return true;
    }

    /**
     * 手机号登录注册
     * @param $mobile
     * @param $code
     * @param array $extend
     * @param int $remember
     * @param bool $ignoreApproval
     * @param string $client
     * @return mixed
     */
    public static function loginByMobile($mobile, $code, array $extend = [], int $remember = 0, $ignoreApproval = false, $client = 'pc')
    {
        $user = self::checkUserExist($mobile);
        if (!$code) {
            self::setError('验证码不可为空');
            return false;
        }
        if (!SmsService::verify($mobile, $code)) {
            self::setError('验证码不正确');
            return false;
        }

        if ($user) {
            if ($user['status'] == 0 || $user['status'] == 2) {
                self::setError('用户不存在或未审核通过');
                return false;
            }
            self::directLogin($user['uid']);
            return $user['uid'];
        }

        $data = array();
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['user_name'] = $extend['username'] ?? 'm' . $mobile;
        $data['mobile'] = $mobile;
        $data['group_id'] = $extend['group_id'] ?? ($data['group_id'] ?? 4);
        $data['salt'] = RandomHelper::alnum();
        $data['password'] = isset($extend['password']) ? hash_password($extend['password']) : hash_password(RandomHelper::alnum(16));
        $data['is_first_login'] = 1;
        $data['reg_ip'] = request()->ip();
        $data['nick_name'] = $extend['username'] ?? 'm_' . md5($mobile);
        $pinyin = new Pinyin();
        $url_token = $pinyin->permalink($data['user_name'], '');
        $data['url_token'] = $url_token;
        $data['is_valid_mobile'] = 1;
        $data['credit_group_id'] = $data['credit_group_id'] ?? 1;
        $data['client'] = $client;
        if (empty($data['avatar'])) {
            $data['avatar'] = self::randomAvatar();
        }
        if (!$data['group_id'] && get_setting('register_valid_type') == 'email' && get_setting('register_type') == 'all') {
            $data['group_id'] = 3; //未验证系统组
        }
        //$data['group_id'] = $data['group_id'] ?? 4;
        if (get_setting('register_valid_type') == 'admin' && !$ignoreApproval)  $data['status'] = 2;

        $uid = db('users')->insertGetId($data);

        //用户通知配置
        db('users_extends')->insert([
            'uid' => $uid,
            'inbox_setting' => 'all',
            'notify_setting' => json_encode(NotifyHelper::getDefaultNotifyConfig(), JSON_UNESCAPED_UNICODE),
        ]);

        //添加积分记录
        LogHelper::addCreditLog('REGISTER', $uid, 'users', $uid);

        ElasticSearch::instance()->create('users', db('users')->find($uid));

        if (get_setting('register_valid_type') == 'admin' && !$ignoreApproval) {
            self::setError('注册成功，等待管理员审核');
            return false;
        } else {
            self::directLogin($uid, $remember);
            return $uid;
        }
    }

    /**
     * 直接登录
     * @param $uid
     * @param int $remember
     * @return mixed
     */
    public static function directLogin($uid, int $remember = 0)
    {
        self::logout();
        $user = self::getUserInfo($uid);
        if (!$user) {
            self::setError('用户不存在');
            return false;
        }
        return self::extracted($user, $remember);
    }

    /**
     * @param $user
     * @param int $remember
     * @return false|mixed
     */
    public static function extracted($user, int $remember = 0)
    {
        //登录成功后钩子

        $ip = IpHelper::getRealIp();
        if ($user['status'] == 1) {
            // 登录成功后重新生成 Session ID，防止会话固定攻击
            \think\facade\Session::regenerate(true);

            if ($remember && get_setting('remember_login_enable') == 'Y') {
                config('session.expire', $remember);
                $cookieKey = config('app.token')['key'] . '_' . md5('login_uid');
                // 记住登录 cookie 使用 HMAC 签名，并绑定当前密码指纹；改密后旧 cookie 自动失效。
                $fingerprint = substr(hash('sha256', (string) ($user['password'] ?? '') . (string) ($user['salt'] ?? '')), 0, 16);
                $rememberValue = SignedValueHelper::sign('remember', (int) $user['uid'] . '|' . $fingerprint, $remember);
                cookie($cookieKey, $rememberValue, ['expire' => $remember]);
            };

            $data['last_login_time'] = time();
            $data['last_login_ip'] = $ip;
            $data['client'] = $user['client'] ?? 'pc';
            self::update($data, ['uid' => $user['uid']]);

            session('last_login_time', $data['last_login_time']);
            if ($data['last_login_ip']) {
                session('last_login_ip', $data['last_login_ip']);
            } else {
                session('last_login_ip', $ip);
            }

            session('access_time', time());

            // 检测是否有同一IP的记录，有更新，否则 添加
            $map = array();
            //$map[] = ['last_login_ip', '=', $ip];
            $map[] = ['uid', '=', $user['uid']];
            $online_id = db('users_online')->where($map)->value('id');
            $last_url = safe_last_url();
            $user_agent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'CLI');

            $data = array();
            $data['uid'] = $user['uid'];
            $data['last_login_time'] = time();
            if (!$online_id) {
                // 插入在线用户表
                $data['last_url'] = $last_url;
                $data['user_agent'] = $user_agent;
                $data['last_login_ip'] = $ip;
                db('users_online')->insert($data);
            } else {
                // 更新在线用户表
                $data['last_login_ip'] = $ip;
                $data['last_url'] = $last_url;
                $data['user_agent'] = $user_agent;
                db('users_online')->where($map)->save($data);
            }
            unset($user['password']);

            session('login_uid', $user['uid']);
            session('login_user_info', $user);

            self::$_user = $user;
            session('login_token', RandomHelper::uuid());
            $token = session('login_token');

            self::$_token = $token;
            self::$_is_login = true;

            //token 过期时间
            $token_expire_time = $remember ?: config('token.expire');
            TokenHelper::set($token, $user['uid'], $token_expire_time);

            //添加行为记录
            LogHelper::addActionLog('user_login', 'users', $user['uid'], $user['uid']);
            //添加积分记录
            LogHelper::addCreditLog('LOGIN', $user['uid'], 'users', $user['uid']);
            return $user;
        }

        if ($user['status'] == 0) {
            self::setError('该账号已被管理员删除！');
        }

        if ($user['status'] == 3) {
            $users_forbidden = db('users_forbidden')->where(['uid' => $user['uid'], 'status' => 1])->find();
            if ($users_forbidden && $users_forbidden['forbidden_time'] > time()) {
                self::setError('该账号已被管理员封禁！封禁原因：' . $users_forbidden['forbidden_reason'] . ';解封时间：' . date('Y-m-d H:i:s', $users_forbidden['forbidden_time']));
            } else {
                db('users')->where(['uid' => $user['uid']])->update(['status' => 1]);
            }
        }
        self::setError('账号异常');
        return false;
    }

    /**
     * 按统一成长值重新定级，并把邀请数量同步为等级组权限中的唯一来源
     * @param int $uid
     * @return bool
     */
    public static function updateUserCreditGroup(int $uid): bool
    {
        if ($uid <= 0) {
            return false;
        }
        $credit = (int) db('users')->where('uid', $uid)->value('credit');
        $group = db('credit_group')->where([
            ['min_credit', '<=', $credit],
            ['max_credit', '>', $credit],
            ['status', '=', 1],
        ])->field('id,permission')->find();
        if (!$group) {
            return false;
        }
        $update = ['credit_group_id' => $group['id']];
        $permission = json_decode((string) $group['permission'], true);
        if (is_array($permission) && isset($permission['available_invite_count'])) {
            $update['available_invite_count'] = (int) $permission['available_invite_count'];
        }
        return (bool) db('users')->where('uid', $uid)->update($update);
    }

    /**
     * 获取用户当天发布文章或问题的数量
     */
    public static function getUserPublishNum($uid, $table)
    {
        if (!in_array($table, array('question', 'article'))) {
            return false;
        }
        // 获取当天0秒unix时间戳
        $today_first_seconds = strtotime(date("Y-m-d 00:00:00"));
        // 获取当天最后一秒unix时间戳
        $today_last_seconds = strtotime(date("Y-m-d 23:59:59"));

        return db($table)->where(['uid' => $uid, 'status' => 1])->whereBetweenTime('create_time', $today_first_seconds, $today_last_seconds)->count();
    }

    /**
     * 验证用户是否需要提交验证码才可发文
     */
    public static function checkUserPublishTimeAndCount($uid, $table): bool
    {
        if (!in_array($table, array('question', 'article', 'answer'))) {
            return false;
        }
        // 获取时间戳
        $beginTime = time() - (intval(get_setting('publish_content_verify_time') * 60));
        $count = db($table)->where([['uid', '=', $uid], ['status', '=', 1], ['create_time', '>=', $beginTime]])->count();
        if ($count >= intval(get_setting('publish_content_verify_num'))) {
            return false;
        }
        return true;
    }

    // 重置密码
    public static function resetPassword($uid, $password)
    {
        $salt = RandomHelper::alnum();
        return self::update([
            'salt' => $salt,
            'password' => hash_password($password)
        ], ['uid' => $uid]);
    }

    // 批量封禁IP
    public static function batchForbiddenIp($uid)
    {
        $uid = is_array($uid) ? $uid : explode(',', $uid);
        if (empty($uid = array_diff($uid, [1, getLoginUid()]))) {
            self::setError('不可封创世管理员或自己的IP');
            return false;
        }

        if (self::whereIn('uid', $uid)->save(['forbidden_ip' => 1])) {
            $ip = self::whereIn('uid', $uid)->column('last_login_ip', 'uid');
            $arr = [];
            $time = time();
            foreach ($ip as $key => $val) {
                $arr[] = [
                    'uid' => $key,
                    'ip' => $val,
                    'time' => $time
                ];
            }

            return db('forbidden_ip')->insertAll($arr);
        }

        return false;
    }

    // 批量解除封禁Ip
    public static function batchLiftIp($uid)
    {
        $uid = is_array($uid) ? $uid : explode(',', $uid);
        if (self::whereIn('uid', $uid)->save(['forbidden_ip' => 0])) {
            return db('forbidden_ip')->whereIn('uid', $uid)->delete();
        }

        return false;
    }

    // 封禁用户IP
    public static function forbiddenIp($data)
    {
        $uids = is_array($data['uid']) ? $data['uid'] : explode(',', $data['uid']);
        if (empty($uids = array_diff($uids, [1, getLoginUid()]))) {
            self::setError('不可封创世管理员或自己的IP');
            return false;
        }

        if (self::where('uid', $data['uid'])->save(['forbidden_ip' => 1])) {
            return db('forbidden_ip')->strict(false)->insert($data);
        }

        return false;
    }

    // 解封用户IP
    public static function liftIp($uid)
    {
        if (self::where('uid', $uid)->save(['forbidden_ip' => 0])) {
            return db('forbidden_ip')->where('uid', $uid)->delete();
        }

        return false;
    }
}
