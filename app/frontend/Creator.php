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

namespace app\frontend;

use app\common\controller\Frontend;
use app\common\library\helper\LogHelper;

/**
 * 创作者中心
 * Class Creator
 * @package app\member\frontend
 */
class Creator extends Frontend
{
    protected $needLogin = ['index'];

    public function index()
    {
        $this->assign('question_count', LogHelper::getActionLogCount('publish_question', $this->user_id, $this->user_id));
        $this->assign('answer_count', LogHelper::getActionLogCount('publish_answer', $this->user_id, $this->user_id));
        $this->assign('article_count', LogHelper::getActionLogCount('publish_article', $this->user_id, $this->user_id));
        return $this->fetch();
    }
}
