<?php
namespace app\model;

use app\common\library\helper\ImageHelper;
use app\logic\common\FocusLogic;

class Feature extends BaseModel
{
    /**
     * 获取专题列表
     * @param $where
     * @param $order
     * @param $page
     * @param $per_page
     * @param $pjax
     * @return array
     */
    public static function getFeatureList($where=[],$order=[],$page=1,$per_page=10,$pjax='tabMain')
    {
        $list = db('feature')
            ->where($where)
            ->order($order)
            ->paginate(
                [
                    'list_rows' => $per_page,
                    'page' => $page,
                    'query' => request()->param(),
                    'pjax' => $pjax
                ]
            );
        $pageVar = $list->render();
        $data = $list->toArray();
        foreach ($data['data'] as $k=>$v)
        {
            $data['data'][$k]['topics'] = self::getRelationTopicByFeatureId($v['id'],4);
        }
        return ['list' => $data['data'], 'page' => $pageVar, 'total' => $data['last_page']];
    }

    private static function getRelationTopicByFeatureId($feature_id,$limit)
    {
        $topic_ids = db('feature_topic')
            ->where(['feature_id'=>$feature_id])
            ->limit($limit)
            ->column('topic_id');

        return Topic::getTopicByIds($topic_ids);
    }

    /**
     * 获取关联话题内容
     * @param $uid
     * @param $feature_id
     * @param $sort
     * @param $page
     * @param $per_page
     * @param $pjax
     * @return array|false|mixed|object|null
     */
    public static function getRelationFeatureList($uid,$feature_id,$sort='hot',$page=1,$per_page=10,$pjax='tabMain')
    {
        $topic_ids = db('feature_topic')
            ->where(['feature_id'=>$feature_id])
            ->column('topic_id');
        if(!$topic_ids) return [];

        if($sort=='best')
        {
            $topic_ids = is_array($topic_ids) ? $topic_ids : explode(',',$topic_ids);
            $topic_where ='item_type="question" AND status=1';
            $topicIdsWhere = ' AND topic_id IN('.implode(',',array_unique($topic_ids)).')';
            $questionIds = db('topic_relation')
                ->whereRaw($topic_where.$topicIdsWhere)
                ->column('item_id');
            $questions_list = db('question')
                ->where(['status'=>1,'question_type'=>'normal'])
                ->where('best_answer','>',0)
                ->whereIn('id',$questionIds)
                ->paginate(
                    [
                        'list_rows' => $per_page,
                        'page' => $page,
                        'query' => request()->param(),
                        'pjax' => $pjax
                    ]
                );
            $pageVar = $questions_list->render();
            $allList = $questions_list->toArray();
            $result = $question_ids = $data_list_uid = $topic_infos = array();

            if ($allList['data'])
            {
                foreach ($allList['data'] AS $key => $val)
                {
                    $val['detail'] = htmlspecialchars_decode($val['detail']);
                    $val['title'] = htmlspecialchars_decode($val['title']);
                    $result[$val['id']] = $val;
                    $question_ids[]=$val['id'];
                    $data_list_uid[$val['uid']] = $val['uid'];
                }
            }

            $last_answers = Answer::getBestAnswerByQuestionIds($question_ids)?:[];
            if ($last_answers)
            {
                foreach ($last_answers as $key => $val)
                {
                    $data_list_uid[$val['uid']] = $val['uid'];
                }
            }

            $topic_infos['question'] = Topic::getTopicByItemIds($question_ids, 'question');
            $users_info = Users::getUserInfoByIds($data_list_uid,'user_name,avatar,nick_name,uid',99);
            // 整页批量取关注/赞踩/已回答状态，避免循环内逐行查询造成 N+1。
            $focus_map = FocusLogic::getFocusMapByIds($uid, 'question', $question_ids);
            $question_vote_map = Vote::getVoteMapByIds($question_ids, 'question', $uid);
            $answer_vote_map = Vote::getVoteMapByIds(
                $last_answers ? array_column($last_answers, 'id') : [], 'answer', $uid
            );
            // 语义与原逐行 value('id') 一致：question_id => 该用户回答的 id
            $answered_map = $uid && $question_ids
                ? db('answer')->where(['uid' => $uid, 'status' => 1])
                    ->whereIn('question_id', implode(',', $question_ids))->column('id', 'question_id')
                : [];

            $result_list = [];
            foreach ($result as $key=>$data) {
                $result_list[$key] = $data;
                $result_list[$key]['answer_info'] = $last_answers[$data['id']] ?? false;
                //是否已回答（批量 map，返回回答 id，与原 value('id') 一致）
                $result_list[$key]['is_answer'] = $answered_map[$data['id']] ?? 0;

                if($result_list[$key]['answer_info']){
                    $result_list[$key]['answer_info']['user_info'] = $users_info[$last_answers[$data['id']]['uid']]??['url'=>'javascript:;','uid'=>0,'name'=>'未知用户','avatar'=>'/static/common/image/default-avatar.svg'];
                    $result_list[$key]['answer_info']['vote_value'] = $answer_vote_map[$last_answers[$data['id']]['id']] ?? 0;
                }
                $result_list[$key]['has_focus'] = isset($focus_map[$data['id']]);
                $detail = $result_list[$key]['answer_info'] ? $result_list[$key]['answer_info']['content'] : $result_list[$key]['detail'];
                $result_list[$key]['vote_value'] = $question_vote_map[$data['id']] ?? 0;
                $result_list[$key]['detail'] = $result_list[$key]['answer_info'] ?  ($result_list[$key]['answer_info']['is_anonymous'] ? '<a href="javascript:;" class="dask-username" >匿名用户</a> :' : '<a href="'.$result_list[$key]['answer_info']['user_info']['url'].'" class="dask-username" >'.$result_list[$key]['answer_info']['user_info']['name'].'</a> :').str_cut(strip_tags($result_list[$key]['answer_info']['content']),0,150) : str_cut(strip_tags($result_list[$key]['detail']),0,150);
                $cover = ImageHelper::srcList($detail);
                $result_list[$key]['img_list'] = $cover;
                $result_list[$key]['item_type'] = 'question';
                $result_list[$key]['topics'] = $topic_infos['question'][$data['id']] ?? [];
                $result_list[$key]['user_info'] = $users_info[$data['uid']] ?? ['url'=>'javascript:;','uid'=>0,'name'=>'未知用户','avatar'=>'/static/common/image/default-avatar.svg'];
                $result_list[$key]['item_id'] = intval($data['id']);
            }

            return ['list'=>$result_list,'page'=>$pageVar,'total'=>$allList['last_page']];
        }

        return PostRelation::getPostRelationList($uid,null,$sort,$topic_ids,null,$page,$per_page,0,$pjax);
    }

}