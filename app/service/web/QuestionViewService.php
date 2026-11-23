<?php
namespace app\service\web;

use app\model\Users;
use app\model\Vote;
use think\facade\Db;

/**
 * 问答展示层共享查询：收敛 Web 页面与 Ajax 局部刷新中重复的状态查询。
 * 控制器不应再直接拼装这些 SQL，避免两处逻辑不一致。
 */
class QuestionViewService
{
    public static function bestAnswerCount(int $questionId): int
    {
        if ($questionId <= 0) {
            return 0;
        }
        return (int) Db::name('answer')->where(['question_id' => $questionId, 'is_best' => 1])->count() > 0 ? 1 : 0;
    }

    public static function hasThanks(int $answerId, int $uid): int
    {
        if ($answerId <= 0 || $uid <= 0) {
            return 0;
        }
        return (int) Db::name('answer_thanks')->where(['answer_id' => $answerId, 'uid' => $uid])->value('id') > 0 ? 1 : 0;
    }

    public static function hasUninterested(string $itemType, int $itemId, int $uid): int
    {
        if ($itemId <= 0 || $uid <= 0 || $itemType === '') {
            return 0;
        }
        return (int) Db::name('uninterested')->where([
            'item_id'   => $itemId,
            'item_type' => $itemType,
            'uid'       => $uid,
        ])->value('id') > 0 ? 1 : 0;
    }

    public static function forceFoldCount(int $questionId): int
    {
        if ($questionId <= 0) {
            return 0;
        }
        return (int) Db::name('answer')->where(['question_id' => $questionId, 'force_fold' => 1])->count();
    }

    /**
     * 给单条回答补齐与当前用户相关的展示标记。
     */
    public static function decorateAnswerFlags(array $answer, int $uid): array
    {
        $answerId = (int) ($answer['id'] ?? 0);
        $answer['has_thanks'] = self::hasThanks($answerId, $uid);
        $answer['has_uninterested'] = self::hasUninterested('answer', $answerId, $uid);
        return $answer;
    }

    /**
     * 批量补齐一页回答中与查看者相关的展示标记，替代逐行
     * getUserInfo / checkFavorite / getReportInfo / checkFocus /
     * getVoteByType / hasThanks / hasUninterested 造成的 N+1。
     * 字段口径与原逐行实现一致；游客(uid=0)时所有标记为假值且零查询。
     *
     * @param array $answers 每条至少含 id、uid
     * @param int   $uid     查看者 uid
     * @return array
     */
    public static function decorateAnswers(array $answers, int $uid): array
    {
        if (!$answers) {
            return $answers;
        }

        $answer_ids = [];
        $author_uids = [];
        foreach ($answers as $answer) {
            $answer_id = (int) ($answer['id'] ?? 0);
            if ($answer_id > 0) {
                $answer_ids[$answer_id] = true;
            }
            // 保留 uid=0：匿名/系统回答仍要走 getUserInfo(0) 返回游客资料，与逐行实现一致
            $author_uids[(int) ($answer['uid'] ?? 0)] = true;
        }
        $answer_ids = array_keys($answer_ids);
        $author_uids = array_keys($author_uids);

        // 按去重后的作者取资料：同一作者的多个回答只取一次，仍是 getUserInfo 的原始结构
        $user_map = [];
        foreach ($author_uids as $author_uid) {
            $user_map[$author_uid] = Users::getUserInfo($author_uid);
        }

        $favorite_ids = $report_ids = $vote_map = $followed_uids = $thanked_ids = $uninterested_ids = [];

        if ($uid && $answer_ids) {
            $favorite_ids = array_flip(array_map('intval',
                Db::name('users_favorite')->where(['uid' => $uid, 'item_type' => 'answer'])
                    ->whereIn('item_id', $answer_ids)->column('item_id')));

            $report_ids = array_flip(array_map('intval',
                Db::name('report')->where(['uid' => $uid, 'item_type' => 'answer'])
                    ->whereIn('item_id', $answer_ids)->column('item_id')));

            $vote_map = Vote::getVoteMapByIds($answer_ids, 'answer', $uid);

            $thanked_ids = array_flip(array_map('intval',
                Db::name('answer_thanks')->where(['uid' => $uid])
                    ->whereIn('answer_id', $answer_ids)->column('answer_id')));

            $uninterested_ids = array_flip(array_map('intval',
                Db::name('uninterested')->where(['uid' => $uid, 'item_type' => 'answer'])
                    ->whereIn('item_id', $answer_ids)->column('item_id')));

            $followed_uids = array_flip(array_map('intval',
                Db::name('users_follow')->where(['fans_uid' => $uid])
                    ->whereIn('friend_uid', $author_uids)->column('friend_uid')));
        }

        foreach ($answers as $key => $answer) {
            $answer_id = (int) ($answer['id'] ?? 0);
            $author_uid = (int) ($answer['uid'] ?? 0);
            $answers[$key]['user_info'] = $user_map[$author_uid] ?? null;
            $answers[$key]['checkFavorite'] = isset($favorite_ids[$answer_id]) ? 1 : 0;
            $answers[$key]['checkReport'] = isset($report_ids[$answer_id]) ? 1 : 0;
            $answers[$key]['vote_value'] = (int) ($vote_map[$answer_id] ?? 0);
            $answers[$key]['user_focus'] = isset($followed_uids[$author_uid]);
            $answers[$key]['has_thanks'] = isset($thanked_ids[$answer_id]) ? 1 : 0;
            $answers[$key]['has_uninterested'] = isset($uninterested_ids[$answer_id]) ? 1 : 0;
        }

        return $answers;
    }
}
