<?php

namespace app\model;

class QuestionInvite extends BaseModel
{
    /**
     * 获取推荐邀请用户列表
     * @param $uid
     * @param int $question_id
     * @param int $page
     * @return array
     */
    public static function getRecommendInviteUsers($uid, int $question_id = 0, int $page = 1): array
    {
        $page = max(1, intval($page));
        //获取问题关联的话题
        $question_topic = db('topic_relation')
            ->where(['item_id' => $question_id, 'item_type' => 'question', 'status' => 1])
            ->column('topic_id');
        if (!$question_topic) {
            return [];
        }

        //获取在该问题话题中的相关问题
        $relation_rows = db('topic_relation')
            ->whereIn('topic_id', $question_topic)
            ->where([['item_id', '<>', $question_id], ['item_type', '=', 'question'], ['status', '=', 1]])
            ->column('item_id,uid');
        $relation_question_ids = array_values(array_unique(array_column($relation_rows, 'item_id')));
        $relation_question_uid = array_values(array_unique(array_column($relation_rows, 'uid')));
        if (!$relation_question_ids) {
            return [];
        }

        //获取在这些问题回答中的用户
        $answer_user_ids = db('answer')
            ->whereIn('question_id', $relation_question_ids)
            ->where('status', 1)
            ->group('uid')
            ->orderRaw('MAX(id) DESC')
            ->limit(100)
            ->column('uid');

        if ($answer_user_ids) {
            $user_ids = $answer_user_ids;
            $remark = '最近回答过该领域问题';
        } else {
            $user_ids = $relation_question_uid;
            $remark = '可能对该问题感兴趣';
        }
        if (!$user_ids) {
            return [];
        }

        $data = db('users')
            ->whereIn('uid', $user_ids)
            ->where([['status', '=', 1], ['uid', '<>', $uid]])
            ->order(['credit' => 'DESC'])
            ->field('uid,nick_name,user_name,avatar')
            ->paginate([
                'list_rows' => 10,
                'page' => $page,
            ])->toArray();
        if (!$data['data']) {
            return $data;
        }

        self::attachHasInvite($data['data'], $uid, $question_id);
        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['remark'] = $remark;
        }

        return $data;
    }

    /**
     * 批量标记用户对某问题的邀请状态
     */
    public static function attachHasInvite(array &$users, $sender_uid, int $question_id): void
    {
        if (!$users) {
            return;
        }
        $invited_uids = db('question_invite')
            ->where(['sender_uid' => intval($sender_uid), 'question_id' => intval($question_id)])
            ->whereIn('recipient_uid', array_column($users, 'uid'))
            ->column('recipient_uid');
        $invited_uids = array_flip(array_map('intval', $invited_uids));
        foreach ($users as $key => $val) {
            $users[$key]['has_invite'] = isset($invited_uids[intval($val['uid'])]) ? 1 : 0;
        }
    }

    /**
     * 获取已邀请用户
     */
    public static function getInvitedUsers($question_id = 0)
    {
        $uid_list = db('question_invite')->where(['question_id' => intval($question_id)])->column('recipient_uid');
        if (!$uid_list) return false;
        $users = Users::getUserInfoByIds($uid_list);
        foreach ($users as $key => $val) {
            $users[$key]['has_invite'] = 1;
        }
        return  $users;
    }
}
