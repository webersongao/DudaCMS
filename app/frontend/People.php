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
use app\logic\common\FocusLogic;
use app\model\Users as UsersModel;

/**
 * 用户模块
 * Class People
 */
class People extends Frontend
{
    protected $needLogin = [];
    public function index()
    {
        $url = $this->request->param('name');
        $url = is_array($url) ? end($url) : $url;
        $url = is_string($url) ? trim($url) : '';

        $url = $url ?: ($this->user_info['url_token'] ?? '');
        $uid = (int) $this->request->param('uid', 0);
        if (!$url && !$uid) {
            $this->error('访问页面不存在');
        }

        // 参数绑定，避免 whereRaw 拼接造成 SQL 注入
        $uid = $uid ?: (int) db('users')
            ->where(function ($query) use ($url) {
                $query->where('url_token', $url)->whereOr('user_name', $url);
            })
            ->value('uid');
        if (!$uid) {
            $this->error('用户不存在', '/');
        }

        $user = UsersModel::getUserInfo((int)$uid);

        if (!$user || $user['status'] === 2) {
            $this->error('当前用户不存在', '/');
        }

        UsersModel::updateUsersViews($uid, $this->user_id);
        $user['draft_count'] = db('draft')->where(['uid' => (int)$uid])->count();
        $user['favorite_count'] = db('users_favorite')->where(['uid' => (int)$uid])->count();
        $user['has_focus'] = FocusLogic::checkUserIsFocus($this->user_id, 'user', $user['uid']);

        $type = $this->request->param('type', 'dynamic');
        $this->assign('type', $type);

        $this->assign('question_count', LogHelper::getActionLogCount('publish_question', $user['uid'], $this->user_id));
        $this->assign('answer_count', LogHelper::getActionLogCount('publish_answer', $user['uid'], $this->user_id));
        $this->assign('article_count', LogHelper::getActionLogCount('publish_article', $user['uid'], $this->user_id));
        $this->assign('user', $user);

        $this->TDK($user['nick_name'] . '的主页');
        return $this->fetch();
    }

    /**
     * 用户列表
     */
    public function lists()
    {
        $page = $this->request->param('page', 1);
        $sort = $this->request->param('sort', 'credit');
        if ($page > 5) {
            $page = 5;
        }
        $order = [];
        $where[] = ['status', '=', 1];
        switch ($sort) {
            case 'answer':
                $order['answer_count'] = 'DESC';
                $where[] = ['answer_count', '>', 0];
                break;
            //活跃榜
            default:
                $order['credit'] = 'DESC';
                $where[] = ['credit', '>', 0];
                break;
        }
        $data = UsersModel::getUserList($where, $order, $page, 20, $this->user_id);
        $this->assign($data);
        $this->assign('sort', $sort);
        $this->TDK('大咖');
        return $this->fetch();
    }
}
