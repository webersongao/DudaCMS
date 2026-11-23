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
use think\facade\Request;

/**
 * 在线版本升级：扫描随代码同步的 SQL 升级文件，检测相邻版本并执行。
 * 不访问任何外部服务器，无需上传 ZIP 升级包。
 */
class Upgrade extends Backend
{
    protected $upgradeHelper;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->upgradeHelper = UpgradeHelper::instance();
    }

    public function index()
    {
        $info = $this->upgradeHelper->getLocalInfo();
        $pending = $this->upgradeHelper->getPendingUpgrade();

        // 为每个升级文件预处理状态标签，避免模板内调用函数
        $files = $this->upgradeHelper->listUpgradeFiles();
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
            'logs'          => $this->upgradeHelper->listUpgradeLogs(),
            'needs_upgrade' => UpgradeHelper::compareVersion($info['version'], $info['db_version']) > 0,
            'preflight'     => $this->upgradeHelper->preflight(),
            'lock'          => $this->upgradeHelper->lockStatus(),
        ]);
        return $this->fetch();
    }

    /**
     * 执行相邻版本升级。
     *
     * 始终返回 JSON：前端用 fetch 请求，可能被当成普通请求而拿到 HTML 跳转页，
     * 导致前端 r.json() 报 Unexpected token '<'。这里不依赖 getResponseType()，
     * 强制以 JSON 回包，前端据 code 字段判断结果。
     */
    public function run()
    {
        if (!$this->request->isPost()) {
            $this->ajaxResult(['code' => 0, 'msg' => '请从升级页面执行升级']);
        }
        $version = (string) $this->request->post('version', '');
        $result = $this->upgradeHelper->runUpgrade($version);
        $this->ajaxResult([
            'code' => !empty($result['code']) ? 1 : 0,
            'msg'  => (string) ($result['msg'] ?? ''),
            'url'  => (string) url('index'),
        ]);
    }
}
