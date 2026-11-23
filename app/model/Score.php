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

class Score extends BaseModel
{
	protected $name = 'credit_rule';

	//获取积分明细列表
	public static function getScoreList($where = [], $page = 1, $per_page = 10, $pjax = ''): array
	{
		$list = db('credit_log')->where($where)->order('create_time', 'DESC')->paginate(
			[
				'list_rows' => $per_page,
				'page' => $page,
				'query' => request()->param(),
				'pjax' => $pjax
			]
		);
		$pageVar = $list->render();
		$list = $list->all();

		foreach ($list as $key => $val) {
			switch ($val['action_type']) {
				case 'LOGIN':
					$list[$key]['extend'] = '';
					break;
			}
		}
		return ['list' => $list, 'page' => $pageVar];
	}
}
