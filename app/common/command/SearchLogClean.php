<?php

namespace app\common\command;

use app\model\Common;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * 搜索日志治理命令。
 *
 * php think search-log-clean --dry-run
 * php think search-log-clean
 */
class SearchLogClean extends Command
{
    protected function configure()
    {
        $this->setName('search-log-clean')
            ->setDescription('清理 SQL 探测串与异常长搜索记录')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不删除');
    }

    protected function execute(Input $input, Output $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $result = Common::cleanDirtySearchLogs($dryRun);

        $output->writeln(sprintf(
            'dirty search logs: %d, deleted: %d%s',
            $result['matched'],
            $result['deleted'],
            $dryRun ? ' (dry-run)' : ''
        ));

        return 0;
    }
}
