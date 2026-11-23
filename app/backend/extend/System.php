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

namespace app\backend\extend;

use app\common\controller\Backend;
use app\common\library\helper\UpgradeHelper;
use think\App;

/**
 * 系统运维：把「本地升级 / 数据库备份 / 数据库还原」聚合到同一页面。
 *
 * 三个功能仍由各自的 Upgrade / Database 控制器实现，本页只提供统一的
 * Tab 外壳，iframe 以 _ajax_open=1 加载各功能主体（去掉侧栏与顶栏，
 * 保留完整 head 资源），避免功能逻辑重复与回归风险。
 */
class System extends Backend
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index()
    {
        $this->assign([
            'info' => UpgradeHelper::instance()->getLocalInfo(),
            'tabs' => [
                [
                    'key'   => 'upgrade',
                    'title' => '本地升级',
                    'icon'  => 'fa fa-download',
                    'url'   => (string) url('extend.Upgrade/index', ['_ajax_open' => 1]),
                ],
                [
                    'key'   => 'backup',
                    'title' => '数据库备份',
                    'icon'  => 'fa fa-database',
                    'url'   => (string) url('extend.Database/database', ['_ajax_open' => 1]),
                ],
                [
                    'key'   => 'restore',
                    'title' => '数据库还原',
                    'icon'  => 'fa fa-undo',
                    'url'   => (string) url('extend.Database/restore', ['_ajax_open' => 1]),
                ],
            ],
        ]);
        return $this->fetch();
    }
}
