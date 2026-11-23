<?php
namespace app\frontend;

use app\common\controller\Frontend;

/**
 * 站点公告
 */
class Announce extends Frontend
{
    public function index()
    {
        $list = db('announce')
            ->where(['status' => 1])
            ->order('set_top', 'desc')
            ->order('sort', 'desc')
            ->order('id', 'desc')
            ->select();
        $this->assign(['list' => $list]);
        return $this->fetch();
    }

    public function detail()
    {
        $id = $this->request->get('id',0,'intval');
        $info = db('announce')->where(['status'=>1,'id'=>$id])->find();
        if(!$info)
        {
            $this->error('公告不存在');
        }
        $info['message'] = htmlspecialchars_decode($info['message']);
        $this->assign([
            'info'=>$info
        ]);
        return $this->fetch();
    }
}