<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DS);
// 前台入口标识在入口文件静态定义，避免中间件运行时改写路由配置。
define('ENTRANCE', 'frontend');

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
// 判断是否安装程序
if (!is_file(ROOT_PATH . 'install' . DS . 'lock' . DS . 'install.lock')) {
    header("location:./install.php");exit;
}

// 单一 PHP 响应式 SSR：所有前台页面走同一套模板，PWA 只是安装外壳。
// clean URL 由 route/app.php 的静态路由负责，入口不再改写请求。

// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
