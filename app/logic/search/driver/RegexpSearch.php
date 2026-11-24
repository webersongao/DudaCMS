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

namespace app\logic\search\driver;

use app\common\library\helper\ImageHelper;
use app\logic\common\FocusLogic;
use app\model\UsersFavorite;
use app\model\Users;
use app\model\Answer;
use app\model\Article;
use app\model\Column;
use app\model\Question;
use app\model\Report;
use app\model\Topic;
use app\model\Vote;
use think\facade\Db;

class RegexpSearch
{
    /**
     * 聚合搜索
     * @param $keywords
     * @param string $type
     * @param int $uid
     * @param array $sort
     * @param int $page
     * @param int $per_page
     * @return array|false
     */
    public function search($keywords, string $type = '', int $uid = 0, array $sort = [], $page = 1, $per_page = 10)
    {
        $page = max(1, intval($page));
        $per_page = max(1, intval($per_page));
        $offset = ($page - 1) * $per_page;

        $keywords = is_array($keywords) ? $keywords : explode(' ', (string) $keywords);
        $keywords = array_values(array_filter(array_map(static function ($item) {
            return self::normalizeKeyword((string) $item);
        }, $keywords)));
        if (!$keywords) {
            return false;
        }

        $where = [
            ['status', '=', 1],
            ['search_engine', '=', get_setting('search_handle', 'regexp')],
        ];
        if ($type !== '') {
            if (preg_match('/^[A-Za-z0-9_]+$/', $type) !== 1) {
                return false;
            }
            $where[] = ['name', '=', trim($type)];
        }
        $types = db('search_engine')->where($where)->field('name,search_field,pk,union_sql')->select()->toArray();
        if (!$types) {
            return false;
        }

        $prefix = (string) config('database.connections.mysql.prefix');
        $mainSelects = [];
        $countSelects = [];
        $bindings = [];

        foreach ($types as $typeInfo) {
            $table = self::safeIdentifier((string) $typeInfo['name']);
            $pk = self::safeIdentifier((string) $typeInfo['pk']);
            $unionSql = self::safeUnionSql((string) $typeInfo['union_sql']);

            $fields = [];
            foreach (explode('|', (string) $typeInfo['search_field']) as $field) {
                $field = self::safeIdentifier(trim($field));
                if ($field !== '') {
                    $fields[] = $field;
                }
            }
            if ($table === '' || $pk === '' || $unionSql === '' || !$fields) {
                continue;
            }

            $conditions = [];
            foreach ($fields as $field) {
                $parts = [];
                foreach ($keywords as $keyword) {
                    $parts[] = $field . ' REGEXP ?';
                    $bindings[] = $keyword;
                }
                $conditions[] = '(' . implode(' AND ', $parts) . ')';
            }
            $conditionSql = 'status=1 AND (' . implode(' OR ', $conditions) . ')';
            $fieldList = $pk . ' AS id,' . $unionSql . ',create_time AS sort_time,\'' . $table . '\' AS search_type';
            $mainSelects[] = 'SELECT ' . $fieldList . ' FROM `' . $prefix . $table . '` WHERE ' . $conditionSql;
            $countSelects[] = 'SELECT ' . $pk . ' AS id,\'' . $table . '\' AS search_type FROM `' . $prefix . $table . '` WHERE ' . $conditionSql;
        }

        if (!$mainSelects) {
            return false;
        }

        $unionMain = '((' . implode(') UNION (', $mainSelects) . '))';
        $unionCount = '((' . implode(') UNION (', $countSelects) . '))';
        $searchResult = Db::query('SELECT * FROM ' . $unionMain . ' AS __search_result ORDER BY sort_time DESC LIMIT ' . $offset . ',' . $per_page, $bindings);
        $countRow = Db::query('SELECT COUNT(*) AS total FROM ' . $unionCount . ' AS __search_count', $bindings);
        $totalRow = isset($countRow[0]['total']) ? (int) $countRow[0]['total'] : 0;

        return $this->parseMixResult($keywords, $searchResult, $uid, $totalRow);
    }

    /**
     * 聚合解析
     * @param $keyword
     * @param $contents
     * @param int $uid
     * @param int $totalRow
     * @return array|false
     */
    public function parseMixResult($keyword, $contents, $uid = 0, $totalRow = 0)
    {
        if (empty($contents)) {
            return false;
        }
        $uid = (int) $uid;
        $questionIds = $articleIds = $answerIds = $topicResultIds = [];

        foreach ($contents as $data) {
            $id = (int) $data['id'];
            switch ($data['search_type']) {
                case 'question':
                    $questionIds[] = $id;
                    break;
                case 'article':
                    $articleIds[] = $id;
                    break;
                case 'answer':
                    $answerIds[] = $id;
                    break;
                case 'topic':
                    $topicResultIds[] = $id;
                    break;
            }
        }
        $questionIds = array_values(array_unique(array_filter($questionIds)));
        $articleIds = array_values(array_unique(array_filter($articleIds)));
        $answerIds = array_values(array_unique(array_filter($answerIds)));
        $topicResultIds = array_values(array_unique(array_filter($topicResultIds)));

        $articleInfos = $answerInfos = [];
        if ($articleIds) {
            $articleInfos = Article::getArticleByIds($articleIds);
        }
        if ($answerIds) {
            $answerInfos = Answer::getAnswerInfoByIds($answerIds);
        }

        $answerQuestionIds = [];
        foreach ($answerInfos as $info) {
            if (!empty($info['question_id'])) {
                $answerQuestionIds[] = (int) $info['question_id'];
            }
        }
        $allQuestionIds = array_values(array_unique(array_filter(array_merge($questionIds, $answerQuestionIds))));

        $questionInfos = [];
        $topicInfos = [];
        if ($allQuestionIds) {
            $questionInfos = Question::getQuestionByIds($allQuestionIds);
            $topicInfos['question'] = Topic::getTopicByItemIds($allQuestionIds, 'question');
        }
        if ($articleIds) {
            $topicInfos['article'] = Topic::getTopicByItemIds($articleIds, 'article');
        }

        $userIds = [];
        foreach ([$questionInfos, $articleInfos, $answerInfos] as $infos) {
            foreach ($infos as $info) {
                if (!empty($info['uid'])) {
                    $userIds[] = (int) $info['uid'];
                }
            }
        }
        $userIds = array_values(array_unique(array_filter($userIds)));
        $userMap = Users::getUserInfoMapByUids($userIds);

        $questionVoteMap = Vote::getVoteMapByIds($questionIds, 'question', $uid);
        $articleVoteMap = Vote::getVoteMapByIds($articleIds, 'article', $uid);
        $answerVoteMap = Vote::getVoteMapByIds($answerIds, 'answer', $uid);

        $favoriteMap = ['question' => [], 'article' => []];
        if ($uid && ($questionIds || $articleIds)) {
            $favTypes = [];
            if ($questionIds) {
                $favTypes[] = 'question';
            }
            if ($articleIds) {
                $favTypes[] = 'article';
            }
            $rows = db('users_favorite')->where(['uid' => $uid])
                ->whereIn('item_type', $favTypes)
                ->whereIn('item_id', array_merge($questionIds, $articleIds))
                ->field('item_type,item_id,id')->select();
            foreach ($rows as $row) {
                $favoriteMap[$row['item_type']][(int) $row['item_id']] = $row['id'];
            }
        }

        $reportMap = [];
        if ($uid && $articleIds) {
            $rows = db('report')->where(['uid' => $uid, 'item_type' => 'article'])->whereIn('item_id', $articleIds)->select();
            foreach ($rows as $row) {
                $reportMap[(int) $row['item_id']] = $row;
            }
        }

        $answeredMap = [];
        if ($uid && $allQuestionIds) {
            $rows = db('answer')->where(['uid' => $uid, 'status' => 1])
                ->whereIn('question_id', $allQuestionIds)
                ->field('question_id,id')->select();
            foreach ($rows as $row) {
                $answeredMap[(int) $row['question_id']] = $row['id'];
            }
        }

        $topicIds = $topicResultIds;
        foreach ($topicInfos as $byItem) {
            foreach ($byItem as $topics) {
                foreach ((array) $topics as $topic) {
                    if (!empty($topic['id'])) {
                        $topicIds[] = (int) $topic['id'];
                    }
                }
            }
        }
        $topicIds = array_values(array_unique(array_filter($topicIds)));
        $topicRowMap = [];
        if ($topicIds) {
            $rows = db('topic')->whereIn('id', $topicIds)->select();
            foreach ($rows as $row) {
                $topicRowMap[(int) $row['id']] = $row;
            }
        }

        $focusMaps = [
            'question' => FocusLogic::getFocusMapByIds($uid, 'question', $allQuestionIds),
            'user'     => FocusLogic::getFocusMapByIds($uid, 'user', $userIds),
            'topic'    => FocusLogic::getFocusMapByIds($uid, 'topic', $topicIds),
        ];

        $columnIds = [];
        foreach ($articleInfos as $info) {
            if (!empty($info['column_id'])) {
                $columnIds[] = (int) $info['column_id'];
            }
        }
        $columnIds = array_values(array_unique(array_filter($columnIds)));
        $columnMap = [];
        if ($columnIds) {
            $rows = db('column')->whereIn('id', $columnIds)->where('verify', 1)
                ->field('id,name,cover,uid,post_count,join_count')->select();
            foreach ($rows as $row) {
                $columnMap[(int) $row['id']] = $row;
            }
        }

        $keywords = is_array($keyword) ? $keyword : [$keyword];

        $resultList = [];
        foreach ($contents as $key => $data) {
            $type = $data['search_type'];
            $id = (int) $data['id'];

            if ($type === 'question' && isset($questionInfos[$id])) {
                $row = $questionInfos[$id];
                $detailText = strip_tags(htmlspecialchars_decode((string) ($row['detail'] ?? ''), ENT_QUOTES));
                $titleText = strip_tags(htmlspecialchars_decode((string) ($row['title'] ?? ''), ENT_QUOTES));
                foreach ($keywords as $q) {
                    $row['detail'] = mb_chunk_split($q, $detailText, 200);
                    $row['title'] = mb_chunk_split($q, $titleText, 100);
                }
                $row['vote_value'] = $questionVoteMap[$id] ?? 0;
                $row['is_answer'] = $answeredMap[$id] ?? 0;
                $row['is_favorite'] = $favoriteMap['question'][$id] ?? 0;
                $row['has_focus'] = isset($focusMaps['question'][$id]) ? 1 : 0;
                $row['search_type'] = 'question';
                $row['topics'] = $topicInfos['question'][$id] ?? [];
                $row['user_info'] = $userMap[(int) ($row['uid'] ?? 0)] ?? [];
                $row['url'] = (string) url('question/detail', ['id' => $id]);
                $row['img_list'] = ImageHelper::srcList($row['detail']);
                $resultList[$key] = $row;
            } elseif ($type === 'article' && isset($articleInfos[$id])) {
                $row = $articleInfos[$id];
                $messageText = strip_tags(htmlspecialchars_decode((string) ($row['message'] ?? ''), ENT_QUOTES));
                $titleText = strip_tags(htmlspecialchars_decode((string) ($row['title'] ?? ''), ENT_QUOTES));
                foreach ($keywords as $q) {
                    $row['message'] = mb_chunk_split($q, $messageText, 100);
                    $row['title'] = mb_chunk_split($q, $titleText, 100);
                }
                $row['img_list'] = ImageHelper::srcList($row['message']);
                $row['is_favorite'] = $favoriteMap['article'][$id] ?? 0;
                $row['is_report'] = $reportMap[$id] ?? null;
                $row['topics'] = $topicInfos['article'][$id] ?? [];
                $row['user_info'] = $userMap[(int) ($row['uid'] ?? 0)] ?? [];
                $row['column_info'] = !empty($row['column_id']) && isset($columnMap[(int) $row['column_id']])
                    ? $columnMap[(int) $row['column_id']] : false;
                $row['vote_value'] = $articleVoteMap[$id] ?? 0;
                $row['search_type'] = 'article';
                $row['url'] = (string) url('article/detail', ['id' => $id]);
                $resultList[$key] = $row;
            } elseif ($type === 'users' && isset($userMap[$id])) {
                $row = $userMap[$id];
                $nameText = strip_tags(htmlspecialchars_decode((string) ($row['name'] ?? ''), ENT_QUOTES));
                foreach ($keywords as $q) {
                    $row['name'] = mb_chunk_split($q, $nameText, 100);
                }
                $row['url'] = get_user_url($id);
                $row['has_focus'] = isset($focusMaps['user'][$id]) ? 1 : 0;
                $row['search_type'] = 'users';
                $resultList[$key] = $row;
            } elseif ($type === 'topic' && isset($topicRowMap[$id])) {
                $row = $topicRowMap[$id];
                $titleText = strip_tags(htmlspecialchars_decode((string) ($row['title'] ?? ''), ENT_QUOTES));
                $descText = strip_tags(htmlspecialchars_decode((string) ($row['description'] ?? ''), ENT_QUOTES));
                foreach ($keywords as $q) {
                    $row['title'] = mb_chunk_split($q, $titleText, 100);
                    $row['description'] = mb_chunk_split($q, $descText, 100);
                }
                $row['url'] = (string) url('topic/detail', ['id' => $id]);
                $row['has_focus'] = isset($focusMaps['topic'][$id]) ? 1 : 0;
                $row['search_type'] = 'topic';
                $resultList[$key] = $row;
            } elseif ($type === 'answer' && isset($answerInfos[$id])) {
                $answerInfo = $answerInfos[$id];
                $questionId = (int) $answerInfo['question_id'];
                if (!isset($questionInfos[$questionId])) {
                    continue;
                }
                $userInfo = $userMap[(int) ($answerInfo['uid'] ?? 0)] ?? [];
                $row = $questionInfos[$questionId];
                $row['is_answer'] = $answeredMap[$questionId] ?? 0;
                $row['answer_info'] = $answerInfo;
                $row['answer_info']['user_info'] = $userInfo;
                $row['answer_info']['vote_value'] = $answerVoteMap[$id] ?? 0;
                $row['url'] = (string) url('question/detail', ['id' => $questionId, 'answer' => $id]);
                $row['topics'] = $topicInfos['question'][$questionId] ?? [];

                $contentText = strip_tags(htmlspecialchars_decode((string) ($answerInfo['content'] ?? ''), ENT_QUOTES));
                $highlight = '';
                foreach ($keywords as $q) {
                    $highlight = mb_chunk_split($q, $contentText, 150);
                }
                $nick = htmlspecialchars((string) ($userInfo['nick_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $userUrl = htmlspecialchars((string) ($userInfo['url'] ?? ''), ENT_QUOTES, 'UTF-8');
                $row['detail'] = '<a href="' . $userUrl . '" class="dask-username" >' . $nick . '</a> :' . $highlight;
                $row['search_type'] = 'answer';
                $row['img_list'] = ImageHelper::srcList(htmlspecialchars_decode((string) ($answerInfo['content'] ?? ''), ENT_QUOTES));
                $resultList[$key] = $row;
            }
        }

        return ['list' => $resultList, 'total' => $totalRow];
    }

    /**
     * 安全字段名/表名：仅允许字母数字下划线
     */
    private static function safeIdentifier(string $value): string
    {
        $value = trim($value);
        return preg_match('/^[A-Za-z0-9_]+$/', $value) === 1 ? $value : '';
    }

    /**
     * 校验搜索配置中的字段列表，例如 title,search_text as detail
     */
    private static function safeUnionSql(string $sql): string
    {
        $sql = trim($sql);
        if ($sql === '' || preg_match('/^[A-Za-z0-9_.,\s]+$/', $sql) !== 1) {
            return '';
        }
        foreach (explode(',', $sql) as $part) {
            $part = trim($part);
            if ($part === '') {
                return '';
            }
            $segments = preg_split('/\s+as\s+/i', $part);
            if (count($segments) > 2 || self::safeIdentifier($segments[0]) === '') {
                return '';
            }
            if (isset($segments[1]) && self::safeIdentifier($segments[1]) === '') {
                return '';
            }
        }
        return $sql;
    }

    /**
     * 只保留可读字符，转义正则元字符，保留 | 作为或语义
     */
    private static function normalizeKeyword(string $keyword): string
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return '';
        }
        $keyword = preg_replace('/[^\p{L}\p{N}\s|]/u', '', $keyword) ?: '';
        if (mb_substr_count($keyword, '|') > 4) {
            $keyword = str_replace('|', '', $keyword);
        }
        $parts = array_filter(array_map('trim', explode('|', $keyword)), static function ($part) {
            return $part !== '';
        });
        $parts = array_map(static function ($part) {
            return preg_quote($part, '/');
        }, $parts);
        return implode('|', $parts);
    }
}
