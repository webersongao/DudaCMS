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

use app\common\library\helper\HtmlHelper;
use app\common\library\helper\ImageHelper;
use app\common\library\helper\IpHelper;
use app\common\library\helper\LogHelper;
use app\common\library\helper\RequestCache;
use app\common\library\helper\TextDiffHelper;
use app\logic\common\FocusLogic;
use app\logic\search\libs\ElasticSearch;
use app\service\StorageCleanupService;
use Overtrue\Pinyin\Pinyin;
use Pay\Exceptions\Exception;
use think\facade\Db;
use WordAnalysis\Analysis;

/**
 * 问题模型类
 * Class Question
 * @package app\model
 */
class Question extends BaseModel
{
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $autoWriteTimestamp = true;

    //根据问题id获取问题详情
    public static function getQuestionInfo($question_id, $field = "*", $status = 1)
    {
        // 同一请求内按 (id, 字段集, status) 复用，消除详情页对同一条问题的重复读取；
        // 结果由 AppInit 中间件在每次请求开始时清空，绝不跨请求。
        $cache_key = 'question:' . intval($question_id) . ':' . md5(serialize($field)) . ':' . intval($status);
        return RequestCache::remember($cache_key, function () use ($question_id, $field, $status) {
            $where['status'] = $status;
            $where['id'] = intval($question_id);
            $question_info = db('question')->field($field)->where($where)->find();
            if (isset($question_info['detail'])) {
                $question_info['detail'] = \app\common\library\helper\HtmlHelper::purify(htmlspecialchars_decode($question_info['detail']));
            }
            if (isset($question_info['title'])) {
                $question_info['title'] = strip_tags(htmlspecialchars_decode($question_info['title']));
            }
            return $question_info;
        });
    }

    /**
     * 内容/状态写入后失效该问题在本请求内的只读缓存。
     */
    public static function clearRequestCache($question_id): void
    {
        RequestCache::forgetPrefix('question:' . intval($question_id) . ':');
    }

    //根据问题ids获取问题列表
    public static function getQuestionByIds($question_ids)
    {
        if (!$question_ids) return false;
        $question_ids = is_array($question_ids) ? $question_ids : explode(',', $question_ids);
        $questions_list = db('question')->where(['status' => 1, 'question_type' => 'normal'])->whereIn('id', $question_ids)->select()->toArray();
        $result = array();
        if ($questions_list) {
            foreach ($questions_list as $key => $val) {
                $val['detail'] = \app\common\library\helper\HtmlHelper::purify(htmlspecialchars_decode($val['detail']));
                $val['title'] = strip_tags(htmlspecialchars_decode($val['title']));
                $result[$val['id']] = $val;
            }
        }
        return $result;
    }

    /**
     * 新增保存问题
     * @param $uid
     * @param array $postData 保存数据
     * @param string $access_key
     * @return mixed
     */
    public static function saveQuestion($uid, array $postData, string $access_key = '')
    {
        $insertData = array(
            'title' => strip_tags($postData['title']),
            'detail' => HtmlHelper::fetchContentImagesToLocal($postData['detail'], 'question', $uid, true, $access_key),
            'search_text' => strip_tags(htmlspecialchars_decode($postData['detail'])),
            'uid' => (int)$uid,
            'is_anonymous' => (int)$postData['is_anonymous'],
            'user_ip' => IpHelper::getRealIp(),
            'category_id' => isset($postData['category_id']) ? intval($postData['category_id']) : 0,
            'question_type' => $postData['question_type'] ?? 'normal',
            'create_time' => time(),
            'update_time' => time(),
            'status' => db_input_int($postData['status'] ?? 1, 1)
        );
        $pinyin = new Pinyin();
        $token = $pinyin->permalink($postData['title'], '');
        $url_token = md5($token);
        $url_token = db('question')
            ->where([
                ['url_token', '=', $url_token],
                ['id', '<>', intval($postData['id'])]
            ])->value('id') ? md5($token . uniqueDate(2)) : $url_token;
        $insertData['url_token'] = $url_token;

        Db::startTrans();
        try {
            if (isset($postData['id']) && intval($postData['id'])) {
                $question_id = intval($postData['id']);
                unset($insertData['uid'], $insertData['create_time']);
                try {
                    $question_info = db('question')->where('id', $question_id)->find();
                    if (!$question_info) throw new \RuntimeException('问题不存在');
                    $insertData['modify_count'] = intval($question_info['modify_count']) + 1;
                    $result = db('question')->where('id', $question_id)->update($insertData);
                    self::clearRequestCache($question_id);
                    if ($result) {
                        //添加行为日志 标题有变化
                        $diffTitle = TextDiffHelper::compare($question_info['title'], $insertData['title']);
                        if (!empty($diffTitle)) {
                            LogHelper::addActionLog('modify_question_title', 'question', $question_id, $uid, intval($postData['is_anonymous']), 0, null, 0, ['old_content' => $question_info['title'], 'new_content' => $insertData['title']]);
                        }
                        //添加行为日志 描述有变化
                        $old_detail = strip_tags(htmlspecialchars_decode($question_info['detail']));
                        $new_detail = strip_tags(htmlspecialchars_decode($insertData['detail']));

                        $diffText = TextDiffHelper::compare($old_detail, $new_detail);
                        if (!empty($diffText)) {
                            LogHelper::addActionLog('modify_question_detail', 'question', $question_id, $uid, intval($postData['is_anonymous']), 0, null, 0, ['old_content' => $question_info['detail'], 'content' => $insertData['detail']]);
                        }

                        //内容修改记录
                        $old_topics = Topic::getTopicByItemType('question', $question_info['id']);
                        $old_info = [
                            'detail' => $question_info['detail'],
                            'title' => $question_info['title'],
                            'category_id' => (isset($question_info['category_id']) && $question_info['category_id']) ? $question_info['category_id'] : 0,
                            'topics' => $old_topics ? array_column($old_topics, 'id') : []
                        ];
                        $new_info = [
                            'detail' => $insertData['detail'],
                            'title' => $insertData['title'],
                            'category_id' => (isset($insertData['category_id']) && $insertData['category_id']) ? $insertData['category_id'] : 0,
                            'topics' => (isset($postData['topics']) && $postData['topics']) ? $postData['topics'] : []
                        ];
                        $extends = [
                            'old_info' => $old_info,
                            'new_info' => $new_info
                        ];

                        LogHelper::addActionLog('modify_log', 'question', $question_id, $uid, intval($postData['is_anonymous']), 0, null, 0, $extends);

                        //删除草稿
                        Draft::deleteDraftByItemID($uid, 'question', $question_id);
                    }
                    //ES索引
                    ElasticSearch::instance()->update('question', $question_info);
                } catch (Exception $e) {
                    throw $e;
                }
            } else {
                try {
                    if ($question_id = db('question')->insertGetId($insertData)) {
                        //更新用户问题数量
                        $question_count = db('question')->where(['uid' => $uid, 'status' => 1])->count();
                        Users::updateUserFiled($uid, ['question_count' => $question_count], null, true);

                        //添加行为日志
                        LogHelper::addActionLog('publish_question', 'question', $question_id, $uid, $insertData['is_anonymous'], 0, '', 0, $insertData);
                        //添加积分日志
                        LogHelper::addCreditLog('NEW_QUESTION', $question_id, 'question', $uid);
                        //删除草稿
                        Draft::deleteDraftByItemID($uid, 'question');
                        //添加问题关注
                        FocusLogic::updateFocusAction($question_id, 'question', $uid);
                        //ES索引
                        ElasticSearch::instance()->create('question', self::getQuestionInfo($question_id));
                    }
                } catch (Exception $e) {
                    throw $e;
                }
            }

            //存储话题问题关联
            if (isset($postData['topics']) && $postData['topics']) {
                $topics = is_array($postData['topics']) ? array_filter($postData['topics']) : explode(',', trim($postData['topics']));
                if (!empty($topics)) {
                    foreach ($topics as $key => $title) {
                        if ($title) {
                            $topic_id = isset($postData['from']) ? $title : Topic::saveTopic($title, $uid);
                            Topic::saveTopicRelation($uid, $topic_id, $question_id, 'question', $postData['status'] ?? 1);
                        }
                    }
                }
            }

            if ($access_key) {
                //更新编辑器附件
                Attach::updateAttach('question', $question_id, $access_key);

                //更新附件
                Attach::updateAttach('question_attach', $question_id, $access_key);
            }
            //加入内容聚合表
            PostRelation::savePostRelation($question_id, 'question');

            /**
             * 内容存储后操作
             */
            // 提交事务
            Db::commit();
            StorageCleanupService::flush();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            StorageCleanupService::clearPending();
            self::setError($e->getMessage());
            return false;
        }
        return $question_id;
    }

    /**
     * 更新问题浏览量
     * @param $question_id
     * @param int $uid
     * @return bool
     */
    public static function updateQuestionViews($question_id, int $uid = 0): bool
    {
        $cache_key = md5('cache_question_' . $question_id . '_' . $uid);
        $cache_result = cache($cache_key);
        if ($cache_result) {
            return true;
        }
        cache($cache_key, $cache_key, ['expire' => 60]);
        return db('question')->where(['id' => $question_id])->inc('view_count')->update();
    }

    //获取问题评论列表
    public static function getQuestionComments($question_id, $page, $sort = ['create_time' => 'desc'], $per_page = 10): array
    {
        $list = db('question_comment')
            ->where([['question_id', '=', (int)$question_id], ['status', '=', 1]])
            ->order($sort)
            ->paginate([
                'list_rows' => $per_page,
                'page' => $page,
                'query' => request()->param()
            ])->toArray();

        // 整页批量取用户/我的赞/我的举报/赞同数，替代循环内逐行查询的 N+1。
        $view_uid = intval(session('login_uid'));
        $comment_ids = array_column($list['data'], 'id');
        $comment_uids = array_column($list['data'], 'uid');
        $user_infos = $comment_uids ? Users::getUserInfoByIds($comment_uids) : [];
        $my_vote_ids = $my_report_ids = $vote_count_map = [];
        if ($comment_ids) {
            $id_list = implode(',', $comment_ids);
            if ($view_uid) {
                $my_vote_ids = array_flip(array_map('intval', db('question_vote')
                    ->where(['uid' => $view_uid, 'item_type' => 'question_comment', 'vote_value' => 1])
                    ->whereIn('item_id', $id_list)->column('item_id')));
                // 保留真实举报记录 id（item_id => report.id），与原 getReportInfo() 结构一致，
                // 不能伪造 id=评论 id，否则未来按该 id 删除举报会误删。
                $my_report_ids = db('report')
                    ->where(['uid' => $view_uid, 'item_type' => 'question_comment'])
                    ->whereIn('item_id', $id_list)->column('id', 'item_id');
            }
            $vote_count_rows = db('question_vote')
                ->field('item_id, count(*) AS cnt')
                ->where(['item_type' => 'question_comment', 'vote_value' => 1])
                ->whereIn('item_id', $id_list)
                ->group('item_id')->select()->toArray();
            foreach ($vote_count_rows as $row) {
                $vote_count_map[$row['item_id']] = (int) $row['cnt'];
            }
        }
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['user_info'] = $user_infos[$value['uid']] ?? Users::getUserInfo(0);
            $list['data'][$key]['check'] = isset($my_vote_ids[$value['id']]) ? 1 : 0;
            $list['data'][$key]['report'] = isset($my_report_ids[$value['id']])
                ? ['id' => $my_report_ids[$value['id']], 'uid' => $view_uid, 'item_type' => 'question_comment'] : null;
            $list['data'][$key]['vote_count'] = (int) ($vote_count_map[$value['id']] ?? 0);
            $list['data'][$key]['create_time_label'] = date_friendly($value['create_time']);
        }

        return $list;
    }

    /**
     * 获取问题评论列表
     * @param $item_id
     * @return mixed
     */
    public static function getQuestionCommentVoteCount($item_id)
    {
        return db('question_vote')
            ->where([['item_id', '=', (int)$item_id], ['item_type', '=', 'question_comment'], ['vote_value', '=', 1]])
            ->count();
    }

    /**
     * 获取问题评论列表
     * @param $item_id
     * @param $uid
     * @param $comment
     * @return bool
     */
    public static function comment_vote($item_id, $uid, $comment): bool
    {
        $vote = db('question_vote')
            ->where([['item_id', '=', (int)$item_id], ['item_type', '=', 'question_comment'], ['uid', '=', $uid]])
            ->find();

        if ($vote) {
            db('question_vote')
                ->where([['item_id', '=', (int)$item_id], ['item_type', '=', 'question_comment'], ['uid', '=', $uid]])
                ->update(['vote_value' => $vote['vote_value'] == 1 ? 0 : 1]);
        } else {
            db('question_vote')
                ->save(['create_time' => time(), 'vote_value' => 1, 'item_id' => $item_id, 'item_type' => 'question_comment', 'item_uid' => $comment['uid'], 'uid' => $uid]);
        }
        return true;
    }

    /**
     * 获取问题评论列表
     * @param $item_id
     * @return mixed
     */
    public static function comment($item_id)
    {
        return db('question_comment')->find($item_id);
    }

    //保存问题评论
    public static function saveComments($uid, $question_id, $message)
    {
        $at_info = Users::parseAtUser($message, true, true);
        $arr['question_id'] = intval($question_id);
        $arr['uid'] = intval($uid);
        $arr['message'] = $at_info[0];
        $arr['update_time'] = time();
        $arr['create_time'] = time();
        $arr['at_uid'] = $at_info[1] ? implode(',', array_values($at_info[1])) : '';

        $result = db('question_comment')->insertGetId($arr);
        if (!$result) {
            self::setError('评论失败');
            return false;
        }
        if ($arr['at_uid']) {
            foreach (array_values($at_info[1]) as $k => $v) {
                //自己@自己不发送通知
                if ($v != $uid) {
                    send_notify($uid, $v, 'QUESTION_COMMENT_AT_ME', 'question', $question_id);
                }
            }
        }

        $pinto = db('question')->field('uid')->find($question_id);

        //自己评论自己不发送通知
        if ($uid != $pinto['uid']) {
            send_notify($uid, $pinto['uid'], 'NEW_QUESTION_COMMENT', 'question', $question_id);
        }

        //更新问题评论数量
        self::updateCommentCount($question_id);

        $arr['id'] = $result;
        $arr['create_time_label'] = date_friendly($arr['create_time']);
        return $arr;
    }

    /**
     * 删除问题评论
     * @param $comment_id
     * @param $user
     * @return Question|false
     */
    public static function deleteComment($comment_id, $user)
    {
        if (!$info = db('question_comment')->field('uid,question_id')->find($comment_id)) {
            self::setError('问题评论不存在');
            return false;
        }

        if (!($info['uid'] == $user['uid'] || $user['group_id'] <= 2)) {
            self::setError('您没有删除评论的权限');
            return false;
        }

        if (!db('question_comment')->where(['id' => $comment_id])->update(['status' => 0])) {
            self::setError('问题评论删除失败');
            return false;
        }

        $comment_count = db('question_comment')->where(['question_id' => $info['question_id'], 'status' => 1])->count();
        return self::update(['comment_count' => $comment_count], ['id' => $info['question_id']]);
    }

    /**
     * 更新问题评论数
     * @param $question_id
     * @return Question
     */
    public static function updateCommentCount($question_id)
    {
        $count = db('question_comment')->where(['question_id' => intval($question_id), 'status' => 1])->count();
        return self::update(['comment_count' => $count], ['id' => intval($question_id)]);
    }

    /**
     * 保存更新邀请用户
     * @param $question_info
     * @param $sender_uid
     * @param $recipient_uid
     * @return false
     */
    public static function saveQuestionInvite($question_info, $sender_uid, $recipient_uid)
    {
        $question_id = $question_info['id'];
        if (!$question_id || !$sender_uid || !$recipient_uid) return false;
        $where[] = ['question_id', '=', (int)$question_id];
        $where[] = ['sender_uid', '=', (int)$sender_uid];
        $where[] = ['recipient_uid', '=', (int)$recipient_uid];

        $invite_id = db('question_invite')->where($where)->value('id');

        if ($invite_id) {
            return false;
        }

        $insert_data = array(
            'question_id' => (int)$question_id,
            'sender_uid' => (int)$sender_uid,
            'recipient_uid' => (int)$recipient_uid,
            'create_time' => time()
        );
        $result = db('question_invite')->insert($insert_data);
        if ($result) {
            LogHelper::addCreditLog('INVITE_ANSWER', $question_id, 'question', $sender_uid);
        }
        send_notify($sender_uid, $recipient_uid, 'INVITE_ANSWER', 'question', $question_id);
        return $result;
    }

    /**
     * 获取相关问题
     * @param $question_id
     * @param int $limit
     * @return array|false
     */
    public static function getRelationQuestion($question_id, $limit = 10)
    {
        if (!$question_id) {
            return false;
        }

        $cache_key = 'cache_relation_question_list_time_' . md5($question_id);

        $list = cache($cache_key) ?: [];
        $cache_relation_list_time = get_setting('cache_relation_list_time');
        if ($cache_relation_list_time && $list) {
            return $list;
        }

        $question_info = self::getQuestionInfo($question_id);
        $keywords = Analysis::getKeywords($question_info['title']);
        $keywords = $keywords ? explode(',', $keywords) : [];

        if ($keywords) {
            foreach ($keywords as $key => $keyword) {
                $keywords[$key] = preg_replace('/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/', '', trim($keyword));
            }
        }

        $where = $keywords ? "status=1 AND (`title` regexp '" . implode('|', $keywords) . "')" : 'status=1';
        $list = db('question')
            ->whereRaw($where)
            ->where([['id', '<>', $question_id]])
            ->orderRaw('RAND()')
            ->order('view_count', 'DESC')
            ->limit($limit)
            ->select()
            ->toArray();

        if ($list) {
            foreach ($list as $val) {
                $val['detail'] = \app\common\library\helper\HtmlHelper::purify(htmlspecialchars_decode($val['detail']));
                $val['title'] = strip_tags(htmlspecialchars_decode($val['title']));
            }
            cache($cache_key, $list, $cache_relation_list_time * 60);
        }

        return $list;
    }


    /**
     * 获取相关问题
     * @param $question_id
     * @param int $page
     * @param int $per_page
     * @return array|false
     */
    public static function getRelationQuestionByMobile($question_id, $page = 1, $per_page = 10)
    {
        if (!$question_id) {
            return false;
        }

        $cache_key = 'cache_relation_question_list_time_' . md5($question_id);

        $list = cache($cache_key) ?: [];
        $cache_relation_list_time = get_setting('cache_relation_list_time');
        if ($cache_relation_list_time && $list) {
            return $list;
        }

        $question_info = self::getQuestionInfo($question_id);
        $keywords = Analysis::getKeywords($question_info['title']);
        $keywords = $keywords ? explode(',', $keywords) : [];
        $where = $keywords ? "status=1 AND (`title` regexp '" . implode('|', $keywords) . "' OR `detail` regexp '" . implode('|', $keywords) . "')" : 'status=1';
        $list = db('question')
            ->whereRaw($where)
            ->where([['id', '<>', $question_id]])
            ->orderRaw('RAND()')
            ->order('view_count', 'DESC')
            ->page($page, $per_page)
            ->paginate(
                [
                    'list_rows' => $per_page,
                    'page' => $page,
                    'query' => request()->param(),
                ]
            )->toArray();

        if ($list) {
            cache($cache_key, $list, $cache_relation_list_time * 60);
        }

        return $list;
    }

    //问题操作管理
    public static function manger($question_id, $type)
    {
        if (!$question_info = self::getQuestionInfo($question_id)) {
            self::setError('问题不存在');
            return false;
        }

        if (!$question_info['status']) {
            self::setError('问题已被删除');
            return false;
        }

        switch ($type) {
            case 'recommend':
                self::update(['is_recommend' => 1], ['id' => $question_id]);
                PostRelation::updatePostRelation($question_id, 'question', ['is_recommend' => 1]);
                break;
            case 'un_recommend':
                self::update(['is_recommend' => 0], ['id' => $question_id]);
                PostRelation::updatePostRelation($question_id, 'question', ['is_recommend' => 0]);
                break;
            case 'set_top':
                self::update(['set_top' => 1, 'set_top_time' => time()], ['id' => $question_id]);
                PostRelation::updatePostRelation($question_id, 'question', ['set_top' => 1, 'set_top_time' => time()]);
                break;
            case 'unset_top':
                self::update(['set_top' => 0, 'set_top_time' => 0], ['id' => $question_id]);
                PostRelation::updatePostRelation($question_id, 'question', ['set_top' => 0, 'set_top_time' => 0]);
                break;
        }

        return true;
    }

    /**
     * 删除问题
     * @param $id
     * @param bool $realMove
     * @return bool
     */
    public static function removeQuestion($id, $realMove = false): bool
    {
        $id = is_array($id) ? $id : explode(',', $id);
        $question_infos = db('question')->whereIn('id', $id)->column('id,status,uid');
        if (!$question_infos) {
            return false;
        }

        Db::startTrans();
        if ($realMove) {
            try {
                $answerIds = db('answer')->whereIn('question_id', $id)->column('id');
                db('question_comment')->whereIn('question_id', $id)->delete();
                db('answer')->whereIn('question_id', $id)->delete();
                db('question')->whereIn('id', $id)->delete();
                db('browse_records')->whereIn('item_id', $id)->where(['item_type' => 'question'])->delete();
                StorageCleanupService::markByItems('question', $id);
                StorageCleanupService::markByItems('question_attach', $id);
                StorageCleanupService::markByItems('answer', $answerIds);
                StorageCleanupService::markByItems('answer_attach', $answerIds);
                foreach ($question_infos as $question_info) {
                    PostRelation::updatePostRelation($question_info['id'], 'question', ['status' => 0]);
                    LogHelper::removeActionLog('question', $question_info['id'], null, true);
                    db('question_focus')->where(['question_id' => $question_info['id']])->delete();
                }
                // 提交事务后再删除物理文件，避免回滚导致文件先丢失。
                Db::commit();
                StorageCleanupService::flush();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                StorageCleanupService::clearPending();
                self::setError($e->getMessage());
                return false;
            }
        } else {
            try {
                if (!db('question')->whereIn('id', $id)->update(['status' => 0])) {
                    throw new \RuntimeException('删除失败');
                }
                //更新首页表
                foreach ($question_infos as $question_info) {
                    //逻辑删除行为记录
                    LogHelper::removeActionLog('question', $question_info['id']);
                    PostRelation::updatePostRelation($question_info['id'], 'question', ['status' => 0]);
                    db('question_focus')->where(['question_id' => $question_info['id']])->update(['status' => 0]);
                    db('browse_records')->whereIn('item_id', $id)->where(['item_type' => 'question'])->update(['status' => 0]);
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                self::setError($e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * 恢复问题
     * @param $id
     * @return bool
     */
    public static function recordQuestion($id): bool
    {
        $id = is_array($id) ? $id : explode(',', $id);
        $question_infos = db('question')->whereIn('id', $id)->column('id,status,uid');
        Db::startTrans();
        try {
            if (!$question_infos) {
                throw new \RuntimeException('问题不存在');
            }

            if (!db('question')->whereIn('id', $id)->update(['status' => 1])) {
                throw new \RuntimeException('恢复失败');
            }
            db('question_focus')->whereIn('question_id', $id)->update(['status' => 1]);
            db('browse_records')->whereIn('item_id', $id)->where(['item_type' => 'question'])->update(['status' => 1]);
            //更新首页表
            foreach ($question_infos as $question_info) {
                //逻辑删除行为记录
                LogHelper::recordActionLog('question', $question_info['id']);
                PostRelation::updatePostRelation($question_info['id'], 'question', ['status' => 1]);
            }
            Db::commit();
            return true;
        } catch (\Exception $exception) {
            Db::rollback();
            self::setError($exception->getMessage());
            return false;
        }
    }

    /**
     * 获取问题的最佳回答信息
     * @param $question_id
     * @return false|array
     */
    public static function getQuestionBestAnswerById($question_id)
    {
        if (!$question_info = self::getQuestionInfo($question_id)) {
            self::setError('问题不存在');
            return false;
        }
        $best_info = db('answer')->where(['is_best' => 1, 'status' => 1, 'question_id' => $question_id])->find();
        $question_info['best_info'] = $best_info ?: [];
        return $question_info;
    }

    /**
     * 获取问题的最佳回答信息
     * @param $question_ids
     * @param string $array_key
     * @return array|false
     */
    public static function getQuestionBestAnswerByIds($question_ids, string $array_key = 'id')
    {
        if (!$question_ids) {
            return false;
        }
        $best_infos = db('answer')->where(['is_best' => 1, 'status' => 1])->whereIn('question_id', $question_ids)->select()->toArray();
        $infos = [];
        foreach ($best_infos as $key => $val) {
            $infos[$val['question_id']] = $val;
        }

        $question_infos = self::getQuestionByIds($question_ids);

        $return = [];
        foreach ($question_infos as $key => $val) {
            $val['best_info'] = $infos[$val['id']] ?? [];
            $return[$val[$array_key]][] = $val;
        }

        return $return;
    }

    /**
     * 获取问题列表
     * @param null $uid
     * @param null $sort
     * @param null $topic_ids
     * @param null $category_id
     * @param int $page
     * @param int $per_page
     * @param int $relation_uid
     * @param string $pjax
     * @return array
     */
    public static function getQuestionList($uid = null, $sort = null, $topic_ids = null, $category_id = null, $page = 1, $per_page = 0, $relation_uid = 0, $pjax = 'tabMain')
    {
        //推荐内容
        if ($sort == 'recommend') {
            return PostRelation::getRecommendPost($uid, 'question', $topic_ids, $category_id, $page, $per_page, $relation_uid, $pjax);
        }

        $data_list = [];
        $key = md5($sort . '-' . $category_id . '-' . ($topic_ids && is_array($topic_ids) ? implode(',', $topic_ids) : $topic_ids) . '-' . $page . '-' . $per_page . '-' . $relation_uid);
        $cache_key = 'cache_list_question_data_' . $key;

        if ($cache_list_time = get_setting('cache_list_time')) {
            $data_list = cache($cache_key);
        }
        if ($data_list) return $data_list;

        $order = $where = array();
        $order['set_top_time'] = 'DESC';
        $where[] = ['status', '=', 1];
        $where[] = ['question_type', '=', 'normal'];

        if ($relation_uid) {
            $where[] = ['uid', '=', $relation_uid];
        }

        if ($sort == 'unresponsive') {
            $where[] = ['answer_count', '=', 0];
        }

        if ($sort == 'hot') {
            $order['popular_value'] = 'desc';
            $where[] = ['popular_value', '>', 1];
        }

        if ($sort == 'new') {
            $order['set_top_time'] = 'DESC';
            $order['update_time'] = 'DESC';
        }

        $order['create_time'] = 'DESC';

        if ($topic_ids) {
            $topic_ids = is_array($topic_ids) ? $topic_ids : explode(',', $topic_ids);
            $topic_where =  'item_type="question" AND status=1';
            $topicIdsWhere = ' AND topic_id IN(' . implode(',', array_unique($topic_ids)) . ')';
            $relationInfo = db('topic_relation')
                ->whereRaw($topic_where . $topicIdsWhere)
                ->column('item_id,item_type');
            $item_ids = array_column($relationInfo, 'item_id');
            $where[] = ['id', 'in', implode(',', $item_ids)];
        }

        if ($category_id) {
            $category_ids = Category::getCategoryIds($category_id);
            if ($category_ids) {
                $where[] = ['category_id', 'in', implode(',', $category_ids)];
            } else {
                $where[] = ['category_id', '=', $category_id];
            }
        }

        $list = db('question')->where($where)->order($order)->paginate(
            [
                'list_rows' => $per_page ?: get_setting('contents_per_page'),
                'page' => $page,
                'pjax' => $pjax
            ]
        );

        $pageVar = $list->render();
        $allList = $list->toArray();
        $question_ids = array_column($allList['data'], 'id');
        $users_info = Users::getUserInfoByIds(array_column($allList['data'], 'uid'), 'user_name,avatar,nick_name,uid');
        $last_answers = Answer::getLastAnswerByIds($question_ids);
        $topic_infos = Topic::getTopicByItemIds($question_ids, 'question');
        // 整页批量取关注/赞踩状态，避免循环内逐行查询造成 N+1。
        $focus_map = FocusLogic::getFocusMapByIds($uid, 'question', $question_ids);
        $question_vote_map = Vote::getVoteMapByIds($question_ids, 'question', $uid);
        $answer_vote_map = Vote::getVoteMapByIds(
            $last_answers ? array_column($last_answers, 'id') : [], 'answer', $uid
        );
        $result_list = [];

        foreach ($allList['data'] as $key => $data) {
            $result_list[$key] = $data;
            $result_list[$key]['title'] = htmlspecialchars_decode($result_list[$key]['title']);
            $result_list[$key]['answer_info'] = $last_answers[$data['id']] ?? false;
            if ($result_list[$key]['answer_info']) {
                $result_list[$key]['answer_info']['user_info'] = $users_info[$last_answers[$data['id']]['uid']] ?? '';
                $result_list[$key]['answer_info']['vote_value'] = $answer_vote_map[$last_answers[$data['id']]['id']] ?? 0;
            } else {
                $result_list[$key]['has_focus'] = isset($focus_map[$data['id']]);
            }

            $detail = $result_list[$key]['answer_info'] ? $result_list[$key]['answer_info']['content'] : $result_list[$key]['detail'];
            $result_list[$key]['vote_value'] = $question_vote_map[$data['id']] ?? 0;
            $result_list[$key]['detail'] = $result_list[$key]['answer_info'] && isset($users_info[$last_answers[$data['id']]['uid']]) ?  ($result_list[$key]['answer_info']['is_anonymous'] ? '<a href="javascript:;" class="dask-username" >匿名用户</a> :' : '<a href="' . $result_list[$key]['answer_info']['user_info']['url'] . '" class="dask-username" >' . $result_list[$key]['answer_info']['user_info']['nick_name'] . '</a> :') . str_cut(strip_tags($result_list[$key]['answer_info']['content']), 0, 150) : str_cut(strip_tags(htmlspecialchars_decode($result_list[$key]['detail'])), 0, 150);

            if ($result_list[$key]['answer_info'] && !isset($users_info[$last_answers[$data['id']]['uid']])) {
                $result_list[$key]['answer_info'] = [];
            }

            $cover  = ImageHelper::srcList(htmlspecialchars_decode($detail));
            $result_list[$key]['img_list'] = $cover;
            $result_list[$key]['topics'] = $topic_infos[$data['id']] ?? [];
            $result_list[$key]['user_info'] = $users_info[$data['uid']] ?? [
                'uid' => (int) $data['uid'],
                'user_name' => '',
                'nick_name' => L('匿名用户'),
                'avatar' => '/static/common/image/default-avatar.svg',
            ];
        }
        $data_list['list'] = $result_list;
        $data_list['page'] = $pageVar;
        $data_list['total'] = $allList['last_page'];

        if ($cache_list_time) {
            cache($cache_key, $data_list, ['expire' => $cache_list_time * 60]);
        }
        return $data_list;
    }

    /**
     * 获取关注用户列表（最多 10 个）
     * @param $question_id
     * @return array|false|false[]
     */
    public static function getQuestionFocusUsers($question_id)
    {
        if (!$question_id) return false;
        $user_ids = db('question_focus')
            ->where(['question_id' => $question_id, 'status' => 1])
            ->order('id', 'DESC')
            ->limit(10)
            ->column('uid');
        if (!$user_ids) return [];
        return Users::getUserInfoByIds($user_ids, '', 99);
    }

    /**
     * 自动锁定问题
     * @return false
     */
    public static function autoLockQuestion(): bool
    {
        if (!get_setting('auto_question_lock_day')) {
            return false;
        }
        return db('question')->whereRaw('`is_lock` = 0 AND `update_time` < ' . (time() - ONE_DAY * get_setting('auto_question_lock_day')))->update(['is_lock' => 1]);
    }
}
