<?php
// +----------------------------------------------------------------------
// | Dask
// +----------------------------------------------------------------------
// | 自定义命令基类：T1 起移除已废弃的任务队列/进程服务，仅保留进度输出能力。
// +----------------------------------------------------------------------
namespace app\common\command;

use think\console\Command as ThinkCommand;
use think\console\Input;
use think\console\Output;

abstract class Command extends ThinkCommand
{
    protected function initialize(Input $input, Output $output): Command
    {
        return $this;
    }

    protected function setTaskProgress(?string $message = null, $progress = null): Command
    {
        if (is_string($message) && $message !== '') {
            $this->output->writeln($message . ($progress !== null ? ' ' . $progress . '%' : ''));
        }
        return $this;
    }

    protected function setTaskError($message): Command
    {
        if (is_string($message) && $message !== '') {
            $this->output->writeln('[ERROR] ' . $message);
        }
        return $this;
    }

    protected function setTaskSuccess($message): Command
    {
        if (is_string($message) && $message !== '') {
            $this->output->writeln('[OK] ' . $message);
        }
        return $this;
    }
}
