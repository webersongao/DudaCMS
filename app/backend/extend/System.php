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
 * 系统运维：仅保留在线数据库升级。
 *
 * 保留 extend.System/index 作为后台菜单入口（url 不变，避免旧链接失效），
 * 本页直接渲染升级页内容，不再使用 Tab 外壳与 iframe。
 * 升级执行仍由 extend.Upgrade/run 处理；升级前自动备份由底层 BackupHelper 提供。
 */
class System extends Backend
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index()
    {
        $helper = UpgradeHelper::instance();
        $info   = $helper->getLocalInfo();
        $pending = $helper->getPendingUpgrade();

        $files = $helper->listUpgradeFiles();
        foreach ($files as $version => &$file) {
            if ($pending && $pending['version'] === (string) $version) {
                $file['status_label'] = '待升级';
                $file['status_class'] = 'text-bg-warning';
            } elseif (UpgradeHelper::compareVersion((string) $version, $info['db_version']) <= 0) {
                $file['status_label'] = '已应用';
                $file['status_class'] = 'text-bg-success';
            } else {
                $file['status_label'] = '等待前置版本';
                $file['status_class'] = 'text-bg-secondary';
            }
        }
        unset($file);

        $this->assign([
            'info'          => $info,
            'pending'       => $pending,
            'files'         => $files,
            'logs'          => $helper->listUpgradeLogs(),
            'needs_upgrade' => UpgradeHelper::compareVersion($info['version'], $info['db_version']) > 0,
            'preflight'     => $helper->preflight(),
            'lock'          => $helper->lockStatus(),
        ]);
        // 直接渲染升级页模板，不再套 Tab/iframe
        return $this->fetch('/extend/upgrade/index');
    }
}
