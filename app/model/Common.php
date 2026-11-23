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

use think\facade\Db;
use think\Model;

class Common extends BaseModel
{
    /**
     * 检查收藏
     * @param $where
     * @return mixed
     */
    public static function checkFavorite($where)
    {
        return db('users_favorite')->where($where)->find();
    }

    /**
     * 检查赞踩
     * @param $where
     * @param $type
     * @return array|Model|null
     */
    public static function checkVote($where, $type)
    {
        switch ($type) {
            case 'question_comment':
                $info = db('question_vote')->where($where)->find();
                break;

            default:

                break;
        }
        return $info;
    }

    /**
     * 获取用户关注
     * @param $uid
     * @param $type
     * @param $page
     * @param $per_page
     * @param $pjax
     * @return false
     */
    public static function getUserFocus($uid, $type, $page = 1, $per_page = 10, $pjax = '')
    {
        if (!$uid || !$type) return false;
        $dbName = 'question_focus';
        $where = [];
        switch ($type) {
            case 'question':
                $where['uid'] = $uid;
                break;

            case 'friend':
                $dbName = 'users_follow';
                $where = ['fans_uid' => $uid];
                break;

            case 'fans':
                $dbName = 'users_follow';
                $where = ['friend_uid' => $uid];
                break;

            case 'column':
                $dbName = 'column_focus';
                $where = ['uid' => $uid];
                break;

            case 'topic':
                $dbName = 'topic_focus';
                $where = ['uid' => $uid];
                break;

            case 'favorite':
                $dbName = 'favorite_focus';
                $where = ['uid' => $uid];
                break;
        }
        $paginate = [
            'list_rows' => $per_page,
            'page' => $page,
            'query' => request()->param()
        ];
        if ($pjax) $paginate['pjax'] = $pjax;
        $result = db($dbName)
            ->where($where)
            ->paginate($paginate);
        $page_render = $result->render();
        $result = $result->toArray();
        $result['page'] = $page_render;

        if ($result['data']) {
            foreach ($result['data'] as $key => $val) {
                switch ($type) {
                    case 'question':
                        $question_ids = array_column($result['data'], 'question_id');
                        $question_infos = Question::getQuestionByIds($question_ids);
                        if (!empty($question_infos) && isset($question_infos[$val['question_id']])) {
                            $result['data'][$key] = $question_infos[$val['question_id']];
                            $result['data'][$key]['user_info'] = Users::getUserInfo($question_infos[$val['question_id']]['uid']);
                            $result['data'][$key]['detail'] = str_cut(strip_tags(htmlspecialchars_decode($question_infos[$val['question_id']]['detail'])), 0, 150);
                            $result['data'][$key]['topics'] = Topic::getTopicByItemType('question', $val['question_id']);
                            $result['data'][$key]['vote_value'] =  Vote::getVoteByType($val['question_id'], 'question', $uid);
                            $result['data'][$key]['item_id'] =  $val['question_id'];
                        } else {
                            unset($result['data'][$key]);
                        }
                        break;

                    case 'friend':
                        $uid_s = array_column($result['data'], 'friend_uid');
                        $user_infos = Users::getUserInfoByIds($uid_s);
                        if (!empty($user_infos)) {
                            $result['data'][$key]['user_info'] = $user_infos[$val['friend_uid']];
                            $result['data'][$key]['item_id'] =  $val['friend_uid'];
                        }
                        break;

                    case 'fans':
                        $uid_s = array_column($result['data'], 'fans_uid');
                        $user_infos = Users::getUserInfoByIds($uid_s);
                        $result['data'][$key]['user_info'] = $user_infos[$val['fans_uid']] ?? [];
                        $result['data'][$key]['item_id'] =  $val['fans_uid'];
                        break;

                    case 'column':
                        $column_ids = array_column($result['data'], 'column_id');
                        $column_infos = Column::getColumnByIds($column_ids);
                        $result['data'][$key] = $column_infos[$val['column_id']];
                        $result['data'][$key]['item_id'] =  $val['column_id'];
                        break;

                    case 'topic':
                        $topic_ids = array_column($result['data'], 'topic_id');
                        $topic_infos = Topic::getTopicByIds($topic_ids);
                        $result['data'][$key] = $topic_infos[$val['topic_id']];
                        $result['data'][$key]['uid'] = $val['uid'];
                        $result['data'][$key]['item_id'] = $val['topic_id'];
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * 判断搜索词是否为注入/探测样本，不应进入热门搜索。
     */
    public static function isDirtySearchKeyword(string $keyword): bool
    {
        $keyword = trim($keyword);
        if ($keyword === '' || mb_strlen($keyword) > 60) {
            return true;
        }

        $patterns = [
            '/\b(?:union\s+select|select\s+.{0,80}\bfrom\b|insert\s+into|delete\s+from|drop\s+table|update\s+\w+\s+set)\b/i',
            '/\border\s+by\s+\d+\b/i',
            '/\b(?:and|or)\s+\d+\s*=\s*\d+\b/i',
            '/\bnull(?:\s*null){1,}\b/i',
            '/\b(?:all|any|some)\s+null\b/i',
            '/\bcase\s+when\b/i',
            '/\binformation_schema\b/i',
            '/\b(?:concat|elt|floor|extractvalue|updatexml)\s*\(/i',
            '/0x[0-9a-f]{6,}/i',
            '/quot/i',
            '/(?:--|#)\s*$|\/\*.*?\*\//',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $keyword) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取热搜记录：参数化分页，过滤注入样本与异常长词。
     */
    public static function getHotSearchList($page = 1, $per_page = 10): array
    {
        $page = max(1, intval($page));
        $per_page = min(50, max(1, intval($per_page)));
        $offset = ($page - 1) * $per_page;

        $query = Db::table(get_table('search_log'))
            ->field('keyword, COUNT(*) AS search_count')
            ->whereNotNull('keyword')
            ->where('keyword', '<>', '')
            ->where('create_time', '>=', time() - 30 * 86400)
            ->whereRaw('CHAR_LENGTH(keyword) BETWEEN 2 AND 180')
            ->group('keyword')
            ->having('search_count >= 2')
            ->order('search_count', 'desc')
            ->order('keyword', 'asc');

        $list = $query->limit($offset, $per_page)->select()->toArray();

        return array_values(array_filter($list, function ($item) {
            return !self::isDirtySearchKeyword((string) ($item['keyword'] ?? ''));
        }));
    }

    /**
     * 清理无效搜索记录。
     * 返回 ['matched' => n, 'deleted' => n]，dryRun=true 时只统计不删除。
     */
    public static function cleanDirtySearchLogs(bool $dryRun = false): array
    {
        $rows = Db::table(get_table('search_log'))
            ->field('id, keyword')
            ->select()
            ->toArray();

        $ids = [];
        foreach ($rows as $row) {
            if (self::isDirtySearchKeyword((string) ($row['keyword'] ?? ''))) {
                $ids[] = intval($row['id']);
            }
        }

        $deleted = 0;
        if (!$dryRun && $ids) {
            foreach (array_chunk($ids, 500) as $chunk) {
                $deleted += Db::table(get_table('search_log'))->whereIn('id', $chunk)->delete();
            }
        }

        return ['matched' => count($ids), 'deleted' => $deleted];
    }
}
