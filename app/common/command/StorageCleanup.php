<?php

namespace app\common\command;

use app\service\StorageCleanupService;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * 上传文件游离回收命令。
 *
 * php think storage-clean
 * php think storage-clean --dry-run
 * php think storage-clean --hours=6
 */
class StorageCleanup extends Command
{
    protected function configure()
    {
        $this->setName('storage-clean')
            ->setDescription('清理未绑定附件与无引用的 storage 物理文件')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不删除')
            ->addOption('hours', null, Option::VALUE_REQUIRED, '文件保留小时数，默认 24', 24);
    }

    protected function execute(Input $input, Output $output): int
    {
        $hours = max(1, (int) $input->getOption('hours'));
        $dryRun = (bool) $input->getOption('dry-run');
        $result = StorageCleanupService::cleanupOrphans($hours * 3600, $dryRun);

        $output->writeln(sprintf(
            'orphan attach: %d, files deleted: %d, files scanned: %d%s',
            $result['attach_deleted'],
            $result['files_deleted'],
            $result['scanned_files'],
            $dryRun ? ' (dry-run)' : ''
        ));
        return 0;
    }
}
