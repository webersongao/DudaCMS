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
namespace app\logic\common;

use app\common\library\helper\LogHelper;
use app\model\Column;
use app\model\Question;
use app\model\Topic;
use app\model\Users;

class FocusLogic
{
    public static $error;

    /**
     * 更新全局关注
     * @param $item_id
     * @param $item_type
     * @param $uid
     * @return array|false
     */
    public static function updateFocusAction($item_id, $item_type, $uid)
    {
        if (!$item_id || !$item_type || !$uid) {
            self::setError('请求参数不正确');
            return false;
        }

        if (!in_array($item_type, ['question', 'topic', 'user', 'column', 'favorite'])) {
            self::setError('请求参数不正确');
            return false;
        }

        $dbName = 'question_focus';
        $where = $insertData = [];
        $anonymous = 0;
        switch ($item_type) {
            case 'question':
                $where['uid'] = (int)$uid;
                $where['question_id'] = (int)$item_id;
                $insertData = [
                    'question_id' => (int)$item_id,
                    'uid' => (int)$uid,
                    'create_time' => time()
                ];
                $anonymous = db('question')->where(['id' => $item_id, 'status' => 1])->value('is_anonymous');
                break;
            case 'topic':
                $dbName = 'topic_focus';

                $where['uid'] = (int)$uid;
                $where['topic_id'] = (int)$item_id;
                $insertData = [
                    'topic_id' => (int)$item_id,
                    'uid' => (int)$uid,
                    'create_time' => time()
                ];
                break;
            case 'user':
                $dbName = 'users_follow';

                $where['fans_uid'] = (int)$uid;
                $where['friend_uid'] = (int)$item_id;
                $insertData = [
                    'friend_uid' => (int)$item_id,
                    'fans_uid' => (int)$uid,
                    'create_time' => time()
                ];
                break;
            case 'column':
                $dbName = 'column_focus';
                $where['uid'] = (int)$uid;
                $where['column_id'] = (int)$item_id;
                $insertData = [
                    'column_id' => (int)$item_id,
                    'uid' => (int)$uid,
                    'create_time' => time()
                ];
                break;
            case 'favorite':
                $dbName = 'favorite_focus';
                $where['uid'] = (int)$uid;
                $where['tag_id'] = (int)$item_id;
                $insertData = [
                    'tag_id' => (int)$item_id,
                    'uid' => (int)$uid,
                    'create_time' => time()
                ];
                break;
        }

        if (db($dbName)->where($where)->value('id')) {
            if (!db($dbName)->where($where)->delete()) {
                self::setError('取消关注失败');
                return false;
            }
            //删除行为日志
            LogHelper::removeActionLog($item_type, $item_id, 'focus_' . $item_type, true);

            $count = self::updateFocusCount($item_id, $item_type);
            return ['count' => $count, 'type' => 'un_focus'];
        }

        if (!db($dbName)->insertGetId($insertData)) {
            self::setError('关注失败');
            return false;
        }
        LogHelper::addActionLog('focus_' . $item_type, $item_type, $item_id, $uid, $anonymous);
        if ($item_type == 'user') {
            send_notify($uid, $item_id, 'TYPE_PEOPLE_FOCUS_ME', '', $item_id);
        }

        $count = self::updateFocusCount($item_id, $item_type);
        return ['count' => $count, 'type' => 'focus'];
    }

    /**
     * 检查用户是否关注过
     * @param mixed $uid
     * @param mixed $item_type
     * @param int $item_id
     * @return mixed
     */
    public static function checkUserIsFocus($uid = null, $item_type = null, int $item_id = 0)
    {
        if (!$item_id || !$item_type || !$uid) {
            self::setError('请求参数不正确');
            return false;
        }
        $target = self::focusTarget($item_type);
        if (!$target) {
            self::setError('请求参数不正确');
            return false;
        }
        [$dbName, $uid_column, $item_column] = $target;

        return db($dbName)->where([
            $uid_column => (int)$uid,
            $item_column => (int)$item_id,
        ])->value('id');
    }

    /**
     * 批量关注状态，替代列表里逐行 checkUserIsFocus()
     * @param int $uid
     * @param string $item_type
     * @param array $item_ids
     * @return array item_id => true
     */
    public static function getFocusMapByIds($uid, string $item_type, array $item_ids): array
    {
        $item_ids = array_values(array_unique(array_filter(array_map('intval', $item_ids))));
        $target = $uid ? self::focusTarget($item_type) : [];
        if (!$item_ids || !$target) {
            return [];
        }
        [$dbName, $uid_column, $item_column] = $target;

        $rows = db($dbName)->where([$uid_column => (int)$uid])
            ->whereIn($item_column, implode(',', $item_ids))
            ->column($item_column);

        return array_fill_keys(array_map('intval', $rows), true);
    }

    /**
     * 关注关系所在的表和字段：[表名, 发起人字段, 目标字段]
     */
    protected static function focusTarget(string $item_type): array
    {
        return match ($item_type) {
            'question' => ['question_focus', 'uid', 'question_id'],
            'topic' => ['topic_focus', 'uid', 'topic_id'],
            'fans' => ['users_follow', 'friend_uid', 'fans_uid'],
            'user', 'friend' => ['users_follow', 'fans_uid', 'friend_uid'],
            'column' => ['column_focus', 'uid', 'column_id'],
            'favorite' => ['users_favorite_focus', 'uid', 'tag_id'],
            default => [],
        };
    }

    /**
     * 更新关注数量
     * @param $item_id
     * @param $item_type
     * @return int
     */
    public static function updateFocusCount($item_id, $item_type): int
    {
        $count = 0;
        switch ($item_type) {
            case 'question':
                $dbName = 'question_focus';
                $countWhere = ['question_id' => (int)$item_id];
                $count = db($dbName)->where($countWhere)->count();
                Question::update(['focus_count' => $count], ['id' => $item_id]);
                break;
            case 'topic':
                $dbName = 'topic_focus';
                $countWhere = ['topic_id' => (int)$item_id];
                $count = db($dbName)->where($countWhere)->count();
                Topic::update(['focus' => $count], ['id' => $item_id]);
                break;
            case 'user':
                $dbName = 'users_follow';
                $countWhere = ['fans_uid' => (int)$item_id];
                $count = db($dbName)->where($countWhere)->count();
                $fans_count = db($dbName)->where(['friend_uid' => (int)$item_id])->count();
                Users::updateUserFiled($item_id, ['friend_count' => $count, 'fans_count' => $fans_count]);
                break;
            case 'column':
                $dbName = 'column_focus';
                $countWhere = ['column_id' => (int)$item_id];
                $count = db($dbName)->where($countWhere)->count();
                Column::update(['focus_count' => $count], ['id' => (int)$item_id]);
                break;
            case 'favorite':
                $dbName = 'users_favorite_focus';
                $countWhere = ['tag_id' => (int)$item_id];
                $count = db($dbName)->where($countWhere)->count();
                db('favorite_tag')->where(['id' => $item_id])->update(['focus_count' => $count]);
                break;
        }

        return $count;
    }

    /**
     * 设置错误信息
     * @param $error
     * @return mixed
     */
    public static function setError($error)
    {
        return self::$error = $error;
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public static function getError()
    {
        return self::$error;
    }
}
