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
use app\model\Score as ScoreModel;

class Credit extends Frontend
{
    protected $needLogin=['index'];

	public function index()
	{
		$type = $this->request->param('type','log');
		$page = $this->request->param('page',1,'intval');
		$where=['uid'=>$this->user_id];
		$data = ScoreModel::getScoreList($where,$page,10,'tabMain');
		$this->assign($data);
		$this->assign('type',$type);
		return $this->fetch();
	}
}