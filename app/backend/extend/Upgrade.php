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
 * 本地版本升级：不访问任何官方服务器。
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
        $this->assign([
            'info'     => $this->upgradeHelper->getLocalInfo(),
            'packages' => $this->upgradeHelper->listLocalPackages(),
        ]);
        return $this->fetch();
    }

    /**
     * 本地升级包检测。
     */
    public function check($file = '')
    {
        $path = $this->resolvePackage((string) $file);
        if ($path === '') {
            $this->error('升级包不存在或路径非法', (string) url('index'));
        }
        $inspect = $this->upgradeHelper->inspectPackage($path);
        if (!$inspect['code']) {
            $this->error($inspect['msg'], (string) url('index'));
        }
        $package = $inspect['data'];
        $package['file'] = basename($path);
        $this->assign([
            'info'    => $this->upgradeHelper->getLocalInfo(),
            'package' => $package,
        ]);
        return $this->fetch();
    }

    /**
     * 上传本地升级包。
     */
    public function upload()
    {
        if (!$this->request->isPost()) {
            $this->error('请通过表单上传升级包', (string) url('index'));
        }
        $upload = $this->request->file('package');
        if (!$upload) {
            $this->error('请选择升级包文件', (string) url('index'));
        }
        if (strtolower((string) $upload->getOriginalExtension()) !== 'zip') {
            $this->error('升级包必须是 ZIP 格式', (string) url('index'));
        }
        if (filesize($upload->getPathname()) > 50 * 1024 * 1024) {
            $this->error('升级包不能超过 50MB', (string) url('index'));
        }

        try {
            $dir = $this->upgradeHelper->packageDir();
            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                $this->error('升级包目录不可写', (string) url('index'));
            }
            $safeName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $upload->getOriginalName()) ?: 'upgrade.zip';
            $saved = $upload->move($dir, date('YmdHis') . '_' . $safeName);
            $savedPath = $saved->getPathname();

            $inspect = $this->upgradeHelper->inspectPackage($savedPath);
            if (!$inspect['code']) {
                @unlink($savedPath);
                $this->error($inspect['msg'], (string) url('index'));
            }
            $this->success('升级包上传成功', (string) url('check', ['file' => $saved->getFilename()]));
        } catch (\Throwable $e) {
            $this->error('上传失败：' . $e->getMessage(), (string) url('index'));
        }
    }

    /**
     * 应用本地升级包。
     */
    public function apply()
    {
        if (!$this->request->isPost()) {
            $this->error('请从升级包详情页执行升级', (string) url('index'));
        }
        $path = $this->resolvePackage((string) $this->request->post('file', ''));
        if ($path === '') {
            $this->error('升级包不存在或路径非法', (string) url('index'));
        }
        $force = (bool) $this->request->post('force', 0);
        $result = $this->upgradeHelper->applyLocalPackage($path, $force);
        if (!$result['code']) {
            $this->error($result['msg'], (string) url('check', ['file' => basename($path)]));
        }
        $this->success($result['msg'], (string) url('index'));
    }

    /**
     * 只允许访问本地升级包目录内的 ZIP。
     */
    private function resolvePackage(string $name): string
    {
        $name = basename($name);
        if ($name === '' || strtolower((string) pathinfo($name, PATHINFO_EXTENSION)) !== 'zip') {
            return '';
        }
        $path = $this->upgradeHelper->packageDir() . $name;
        return is_file($path) ? $path : '';
    }
}
