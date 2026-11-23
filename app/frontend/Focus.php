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
use app\logic\common\FocusLogic;
use app\model\Common;

/**
 * 我的关注
 * Class Focus
 * @package app\ask\controller
 */
class Focus extends Frontend
{
    public function index()
    {
        if (!$this->user_id) {
            $this->redirect('/');
        }
        $this->assign('user', $this->user_info);
        return $this->fetch();
    }

    public function focus_list()
    {
        $types = ['fans', 'friend', 'column', 'topic', 'question'];
        $type = $this->request->post('type', 'question');
        $uid = $this->request->post('uid', 0);
        $page = $this->request->param('page', 1);
        $data = Common::getUserFocus($uid, $type, $page);

        if (!empty($data['data'])) {
            // 整页关注状态一次批量取，避免逐行 checkUserIsFocus 造成 N+1。
            $focus_type = $type == 'fans' ? 'friend' : $type;
            $focus_map = in_array($type, $types)
                ? FocusLogic::getFocusMapByIds($this->user_id, $focus_type, array_column($data['data'], 'item_id'))
                : [];
            foreach ($data['data'] as $key => $val) {
                if (in_array($type, $types)) {
                    if ($type == 'fans') $val['uid'] = $val['fans_uid'];
                    if ($type == 'friend') $val['uid'] = $val['friend_uid'];
                    $data['data'][$key]['has_focus'] = isset($focus_map[$val['item_id']]);
                }
            }
        }

        $this->assign(['list' => $data['data'], 'type' => $type]);
        $this->result(['last_page' => $data['last_page'], 'total' => $data['total'], 'html' => $this->fetch()]);
    }
}
