<?php
namespace app\logic\common;

use app\common\library\helper\ImageHelper;
use app\model\Answer;
use app\model\Article;
use app\model\Category;
use app\model\Question;
use app\model\Topic;
use app\model\Users;
use app\model\Vote;

class TemplateTag
{
    /**
     * 获取问题列表
     */
    public static function getQuestionList($uid=null, $sort = null, $topic_ids = null, $category_id = null, $limit=0,  $per_page=0,$cache=0, $pjax='tabMain',$question_type='normal'): array
    {
        $sort = safe_content_sort($sort);
        $uid=$uid?:getLoginUid();
        $page = request()->param('page',1,'intval');
        $key = $sort.'-'.$category_id.'-'.($topic_ids && is_array($topic_ids) ? implode(',',$topic_ids) :$topic_ids).'-'.$page.'-'.$per_page;
        $cache_key = 'cache_tag_template_question_data_'.$key;
        $result_list = [];
        if($cache_explore_time = get_setting('cache_explore_time'))
        {
            $result_list = cache($cache_key);
        }
        if($result_list && $cache) return $result_list;

        $order = $where = array();
        $order['set_top_time'] = 'DESC';
        $where[] = ['status','=',1];
        if($question_type) $where[] = ['question_type','=',$question_type];
        if($sort=='unresponsive')
        {
            $where[] = ['answer_count','=',0];
        }

        if($sort=='hot')
        {
            $order['popular_value'] = 'desc';
            $where[] = ['popular_value','>',get_setting('content_popular_value_show',0)];
        }

        if($sort=='new'){
            $order['set_top_time'] = 'DESC';
            $order['update_time'] = 'DESC';
        }

        //推荐内容
        if($sort=='recommend')
        {
            $order['is_recommend'] = 'DESC';
            $where[] = ['is_recommend','=',1];
        }

        $order['create_time'] = 'DESC';

        if ($topic_ids)
        {
            $topic_ids = is_array($topic_ids) ? $topic_ids : explode(',',$topic_ids);
            $topic_where =  'item_type="question" AND status=1' ;
            $topicIdsWhere = ' AND topic_id IN('.implode(',',array_unique($topic_ids)).')';
            $relationInfo = db('topic_relation')
                ->whereRaw($topic_where.$topicIdsWhere)
                ->column('item_id,item_type');
            $item_ids = array_column($relationInfo,'item_id');
            $where[] = ['id','in',implode(',', $item_ids)];
        }

        if ($category_id)
        {
            $category_ids = Category::getCategoryIds($category_id);
            if($category_ids)
            {
                $where[] = ['category_id','in', implode(',',$category_ids )];
            }else{
                $where[] = ['category_id','=', $category_id];
            }
        }

        $result_list = [];

        if(!$limit)
        {
            $list = db('question')->where($where)->order($order)->paginate(
                [
                    'list_rows'=> $per_page ? : get_setting('contents_per_page'),
                    'query' => request()->param(),
                    'pjax'=>$pjax
                ]
            );

            $pageVar = $list->render();
            $allList = $list->toArray();

            $data_list['page'] = $pageVar;
            $data_list['total'] = $allList['last_page'];
            $allList = $allList['data'];
        }else{
            $allList = db('question')->where($where)->order($order)->limit($limit)->select()->toArray();
        }

        $uid_lists = array_column($allList,'uid','uid');
        $last_answers = $sort=='hot'?Answer::getHotAnswerByIds(array_column($allList,'id')):Answer::getLastAnswerByIds(array_column($allList,'id'));
        if ($last_answers)
        {
            foreach ($last_answers as $key => $val)
            {
                $uid_lists[$val['uid']] = $val['uid'];
            }
        }

        $topic_infos = Topic::getTopicByItemIds(array_column($allList,'id'), 'question');
        $answerUidList = $last_answers ? array_column($last_answers,'uid') : [];
        $uid_lists = $answerUidList ? array_merge($uid_lists,$answerUidList) : $uid_lists;

        $users_info = Users::getUserInfoByIds($uid_lists,'user_name,avatar,nick_name,uid',99);

        //是否已回答、赞踩、关注按整页批量取，避免在下面循环里逐行查库
        $question_ids = array_column($allList, 'id');
        $question_vote_map = Vote::getVoteMapByIds($question_ids, 'question', $uid);
        $question_focus_map = FocusLogic::getFocusMapByIds($uid, 'question', $question_ids);
        $answer_vote_map = Vote::getVoteMapByIds($last_answers ? array_column($last_answers, 'id') : [], 'answer', $uid);
        $answered_map = $uid && $question_ids ? db('answer')->where([['uid', '=', intval($uid)], ['status', '=', 1]])
            ->whereIn('question_id', implode(',', $question_ids))->column('id', 'question_id') : [];
        //「回答用户」列表只有 API 侧输出，Web 模板无消费方，这里不再逐行查 answer.uid

        foreach ($allList as $key=>$data)
        {
            $result_list[$key] = $data;
            $result_list[$key]['title'] = htmlspecialchars_decode($result_list[$key]['title']);

            //是否已回答
            $result_list[$key]['is_answer'] = intval($answered_map[intval($data['id'])] ?? 0);

            //最后回答
            $result_list[$key]['answer_info'] = $last_answers[$data['id']] ?? false;

            if($result_list[$key]['answer_info']){
                $result_list[$key]['answer_info']['user_info'] = $users_info[$last_answers[$data['id']]['uid']]??['url'=>'javascript:;','uid'=>0,'name'=>'未知用户','avatar'=>'static/common/image/default-avatar.svg'];
                $result_list[$key]['answer_info']['vote_value'] = intval($answer_vote_map[intval($last_answers[$data['id']]['id'])] ?? 0);
                $result_list[$key]['answer_info']['content'] = str_cut(strip_tags($result_list[$key]['answer_info']['content']),0,150);
                $result_list[$key]['answer_info']['img_list'] = ImageHelper::srcList($result_list[$key]['answer_info']['content']);
            }

            $result_list[$key]['has_focus'] = isset($question_focus_map[intval($data['id'])]);

            $result_list[$key]['vote_value'] = intval($question_vote_map[intval($data['id'])] ?? 0);

            $result_list[$key]['img_list'] = ImageHelper::srcList($result_list[$key]['detail']);

            $result_list[$key]['detail'] = str_cut(strip_tags(htmlspecialchars_decode($result_list[$key]['detail'])),0,150);


            if($result_list[$key]['answer_info'] && !isset($users_info[$last_answers[$data['id']]['uid']]))
            {
                $result_list[$key]['answer_info'] = [];
            }

            //问题话题
            $result_list[$key]['topics'] = $topic_infos[$data['id']] ?? [];

            if(!isset($users_info[$data['uid']]))
            {
                unset($result_list[$key]);
                continue;
            }
            $result_list[$key]['user_info'] = $users_info[$data['uid']];
        }

        $data_list['list'] = $result_list;

        if($cache_explore_time && $cache)
        {
            cache($cache_key,$data_list,['expire'=>$cache_explore_time*60]);
        }
        return $data_list;
    }

    /**
     * 获取文章列表
     */
    public static function getArticleList($uid=null,$sort = null, $topic_ids = null, $category_id = null, $limit=0, $per_page=0,$cache=0, $pjax='tabMain',$article_type='normal'): array
    {
        $sort = safe_content_sort($sort);
        $uid=$uid?:getLoginUid();
        $page = request()->param('page',1,'intval');
        $key = $sort.'-'.$category_id.'-'.($topic_ids && is_array($topic_ids) ? implode(',',$topic_ids) :$topic_ids).'-'.$page.'-'.$per_page;
        $cache_key = 'cache_tag_template_article_data_'.$key;
        $data_list = [];
        if($cache_explore_time = get_setting('cache_explore_time'))
        {
            $data_list = cache($cache_key);
        }
        if($data_list && $cache) return $data_list;

        $order = $where = array();
        //$order['set_top_time'] = 'DESC';
        $where[] = ['status','=',1];
        if($article_type) $where[] = ['article_type','=',$article_type];
        $orderRaw = '';
        if($sort=='hot')
        {
            $order['popular_value'] = 'desc';
            $where[] = ['popular_value','>',get_setting('content_popular_value_show',0)];
        }elseif($sort=='new'){
            $order['set_top_time'] = 'DESC';
        }elseif($sort=='recommend')
        {
            $order['is_recommend'] = 'DESC';
            $where[] = ['is_recommend','=',1];
        }else{
            $orderRaw = $sort;
        }

        $order['create_time'] = 'DESC';

        if ($topic_ids)
        {
            $topic_ids = is_array($topic_ids) ? $topic_ids : explode(',',$topic_ids);
            $topic_where =  'item_type="article" AND status=1' ;
            $topicIdsWhere = ' AND topic_id IN('.implode(',',array_unique($topic_ids)).')';
            $relationInfo = db('topic_relation')
                ->whereRaw($topic_where.$topicIdsWhere)
                ->column('item_id,item_type');
            $item_ids = array_column($relationInfo,'item_id');
            $where[] = ['id','in',implode(',', $item_ids)];
        }

        if ($category_id)
        {
            $category_ids = Category::getCategoryIds($category_id);
            if($category_ids)
            {
                $where[] = ['category_id','in', implode(',',$category_ids )];
            }else{
                $where[] = ['category_id','=', $category_id];
            }
        }

        if(!$limit)
        {
            $list = db('article')
                ->where($where)
                ->order($orderRaw?:$order)
                ->paginate(
                    [
                        'list_rows'=> $per_page ? : get_setting('contents_per_page'),
                        'query'=>request()->param(),
                        'pjax'=>$pjax
                    ]
                );

            $pageVar = $list->render();
            $allList = $list->toArray();
            $data_list['page'] = $pageVar;
            $data_list['total'] = $allList['last_page'];
            $allList = $allList['data'];
        }else{
            $allList = db('article')
                ->where($where)
                ->order($orderRaw?:$order)
                ->limit($limit)
                ->select()
                ->toArray();
        }

        $users_info = Users::getUserInfoByIds(array_column($allList,'uid'));
        $topic_infos = Topic::getTopicByItemIds(array_column($allList,'id'), 'article');
        $result_list = [];
        foreach ($allList as $key=>$data)
        {
            $result_list[$key] = $data;
            $result_list[$key]['title'] = htmlspecialchars_decode($data['title']);
            $result_list[$key]['message'] = str_cut(strip_tags(htmlspecialchars_decode($data['message'])),0,120);
            $cover  = ImageHelper::srcList(htmlspecialchars_decode($data['message']));
            $result_list[$key]['img_list'] = $cover?:[];
            $result_list[$key]['topics'] = $topic_infos[$data['id']] ?? [];
            $result_list[$key]['user_info'] = $users_info[$data['uid']] ?? ['url'=>'javascript:;','uid'=>0,'name'=>'未知用户'];
            $result_list[$key]['vote_value'] = Vote::getVoteByType($data['id'],'article',$uid);
        }
        $data_list['list'] = $result_list;
        if($cache_explore_time && $cache)
        {
            cache($cache_key,$data_list,['expire'=>$cache_explore_time*60]);
        }
        return $data_list;
    }

    /**
     * 通用解析聚合数据列表
     */
    public static function processPostList($contents,$uid,$sort='')
    {
        if (!$contents) {
            return [];
        }
        $result_list = [];
        $last_answers = $topic_infos = $question_ids = $article_ids = $data_list_uid = $question_infos = $article_infos =  array();
        foreach ($contents as $data)
        {
            if($data['item_type']=='question')
            {
                $question_ids[] = $data['item_id'];
            }elseif($data['item_type']=='article')
            {
                $article_ids[] = $data['item_id'];
            }

            $data_list_uid[$data['uid']] = $data['uid'];
        }

        if ($question_ids)
        {
            $last_answers = $sort=='hot'?Answer::getHotAnswerByIds($question_ids):Answer::getLastAnswerByIds($question_ids);
            if ($last_answers)
            {
                foreach ($last_answers as $key => $val)
                {
                    $data_list_uid[$val['uid']] = $val['uid'];
                }
            }
            $topic_infos['question'] = Topic::getTopicByItemIds($question_ids, 'question');
            $question_infos = Question::getQuestionByIds($question_ids);
        }

        if ($article_ids)
        {
            $topic_infos['article'] = Topic::getTopicByItemIds($article_ids, 'article');
            $article_infos = Article::getArticleByIds($article_ids);
        }
        $users_info = Users::getUserInfoByIds($data_list_uid,'user_name,avatar,nick_name,uid',99);

        //赞踩、关注、是否已回答按整页批量取，避免在下面的循环里逐行查库
        $question_ids = array_values(array_unique($question_ids));
        $article_ids = array_values(array_unique($article_ids));
        $question_vote_map = Vote::getVoteMapByIds($question_ids, 'question', $uid);
        $article_vote_map = Vote::getVoteMapByIds($article_ids, 'article', $uid);
        $answer_vote_map = $last_answers ? Vote::getVoteMapByIds(array_column($last_answers, 'id'), 'answer', $uid) : [];
        $question_focus_map = FocusLogic::getFocusMapByIds($uid, 'question', $question_ids);
        $answered_map = $uid && $question_ids ? db('answer')->where([['uid', '=', intval($uid)], ['status', '=', 1]])
            ->whereIn('question_id', implode(',', $question_ids))->column('id', 'question_id') : [];

        foreach ($contents as $key => $data)
        {
            if($data['item_type']=='question')
            {
                if($question_infos && isset($question_infos[$data['item_id']]))
                {
                    $result_list[$key] = $question_infos[$data['item_id']];
                    $result_list[$key]['answer_info'] = $last_answers[$data['item_id']] ?? false;

                    //是否已回答
                    $result_list[$key]['is_answer'] = $answered_map[$data['item_id']] ?? 0;

                    if($result_list[$key]['answer_info']){
                        $result_list[$key]['answer_info']['user_info'] = $users_info[$last_answers[$data['item_id']]['uid']]??['url'=>'javascript:;','uid'=>0,'name'=>'未知用户','avatar'=>'static/common/image/default-avatar.svg'];
                        $result_list[$key]['answer_info']['vote_value'] = intval($answer_vote_map[$last_answers[$data['item_id']]['id']] ?? 0);
                        $result_list[$key]['answer_info']['content'] = str_cut(strip_tags($result_list[$key]['answer_info']['content']),0,150);
                        $result_list[$key]['answer_info']['img_list'] = ImageHelper::srcList($result_list[$key]['answer_info']['content']);
                    }

                    $result_list[$key]['has_focus'] = isset($question_focus_map[$data['item_id']]);

                    //$detail = $result_list[$key]['answer_info'] ? $result_list[$key]['answer_info']['content'] : $result_list[$key]['detail'];
                    $result_list[$key]['vote_value'] = intval($question_vote_map[$data['item_id']] ?? 0);
                    $result_list[$key]['img_list'] = ImageHelper::srcList($result_list[$key]['detail']);

                    $result_list[$key]['detail'] = str_cut(strip_tags($result_list[$key]['detail']),0,150);

                    $result_list[$key]['item_type'] = 'question';
                    $result_list[$key]['topics'] = $topic_infos['question'][$data['item_id']] ?? [];
                    $result_list[$key]['user_info'] = $users_info[$data['uid']] ?? ['url'=>'javascript:;','uid'=>0,'name'=>'未知用户','avatar'=>'static/common/image/default-avatar.svg'];
                    $result_list[$key]['item_id'] = intval($data['item_id']);
                    $result_list[$key]['item_type'] = $data['item_type'];
                    $result_list[$key]['post_id'] = intval($data['id']);
                }
            }

            if($data['item_type']=='article')
            {
                if($article_infos && isset($article_infos[$data['item_id']]))
                {
                    $result_list[$key] = $article_infos[$data['item_id']];
                    if(isset($article_infos[$data['item_id']]['paid_read']) && $article_infos[$data['item_id']]['paid_read'])
                    {
                        $result_list[$key]['message'] = str_cut(strip_tags($result_list[$key]['message']),0,$article_infos[$data['item_id']]['free_words']);
                    }else{
                        $result_list[$key]['message'] = str_cut(strip_tags($result_list[$key]['message']),0,120);
                    }

                    $result_list[$key]['item_type'] = 'article';
                    $cover  = ImageHelper::srcList($article_infos[$data['item_id']]['message']);
                    $result_list[$key]['img_list'] = $cover;
                    $result_list[$key]['topics'] = $topic_infos['article'][$data['item_id']] ?? [];
                    $result_list[$key]['user_info'] = $users_info[$data['uid']] ?? ['url'=>'javascript:;','uid'=>0,'name'=>'未知用户'];
                    $result_list[$key]['vote_value'] = intval($article_vote_map[$data['item_id']] ?? 0);
                    $result_list[$key]['item_id'] = intval($data['item_id']);
                    $result_list[$key]['item_type'] = $data['item_type'];
                    $result_list[$key]['post_id'] = intval($data['id']);
                }
            }

            $data['key'] = $key;
            $result = '';
            if($result)
            {
                $result = json_decode($result,true);
                $result_list = array_merge($result_list,$result);
            }
        }
        return $result_list;
    }


}