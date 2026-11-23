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

class Page extends Frontend
{
    public function index()
    {
        $id = $this->request->get('id', 0);
        $url_name = $this->request->get('url_name', '');
        $where[] = ['status', '=', 1];
        if ($id) {
            $where[] = ['id', '=', $id];
        }

        if ($url_name) {
            $where[] = ['url_name', '=', $url_name];
        }

        $info = db('page')->where($where)->find();
        if ($info) {
            $info['contents'] = htmlspecialchars_decode($info['contents']);
            $this->assign(['info' => $info]);
            $this->TDK($info['title'], $info['keywords'], $info['description']);
            return $this->fetch();
        }
        $this->error('页面不存在', '/');
    }

    /**
     * 积分规则页面
     */
    public function credit()
    {
        $list = db('credit_rule')->where([['credit', '<>', '0']])->select()->toArray();

        $this->assign([
            'list' => $list
        ]);
        return $this->fetch();
    }
}
