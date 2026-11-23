<?php
// +----------------------------------------------------------------------
// | 图形化安装统一入口：/install/
// | 实际安装逻辑集中在 public/install.php，避免两套实现漂移。
// +----------------------------------------------------------------------

$script = (string) ($_SERVER['SCRIPT_NAME'] ?? '/install/');
$_SERVER['SCRIPT_NAME'] = str_replace('install/index.php', 'install.php', $script);
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];

require dirname(__DIR__) . '/install.php';
