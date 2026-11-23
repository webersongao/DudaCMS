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

namespace think;

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DS);
define('ENTRANCE', 'api');

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

if (!is_file(ROOT_PATH . 'install' . DS . 'lock' . DS . 'install.lock')) {
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['code' => 5001, 'message' => '系统尚未安装', 'data' => null], JSON_UNESCAPED_UNICODE);
    exit;
}

$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
