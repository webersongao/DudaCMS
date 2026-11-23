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

ini_set('display_errors', 'Off');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// 安装器是独立入口，不经过应用全局中间件，需自行加固响应头。
// 只封禁高危能力，不限制 script/style，避免影响安装向导的内联脚本。
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: base-uri 'self'; object-src 'none'; frame-ancestors 'self'; upgrade-insecure-requests");

use Overtrue\Pinyin\Pinyin;
use think\App;
use think\facade\Db;

require __DIR__ . '/../vendor/autoload.php';
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DS);
define('INSTALL_PATH', ROOT_PATH . 'install' . DS);
define('CONFIG_PATH', ROOT_PATH . 'config' . DS);

// 安装器不经过应用助手，直接读取构建产物里的逐文件 hash。
function dask_install_asset_version(string $path): string
{
    static $map = null;
    if ($map === null) {
        $map = [];
        $manifest = __DIR__ . '/static/build-manifest.json';
        if (is_file($manifest)) {
            $data = json_decode((string) file_get_contents($manifest), true);
            if (is_array($data) && isset($data['files']) && is_array($data['files'])) {
                $map = $data['files'];
            }
        }
    }
    return $map[$path] ?? '1.0.0';
}

if (is_file(INSTALL_PATH . 'lock' . DS . 'install.lock') && ($_GET['step'] ?? '') != 4) {
    echo '
		<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        </head>
        <body>
        	你已经安装过该系统，如果想重新安装，请先删除站点install\lock目录下的 install.lock 文件，然后再安装。
        </body>
        </html>';
    exit;
}

if (!is_file(INSTALL_PATH . 'lock' . DS . 'install.lock') && ($_GET['step'] ?? '') == 4) {
    header('Location:install.php');
}

if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    die('本系统需要PHP版本 >= 8.2.0 环境（ThinkPHP 8），当前PHP版本为：' . PHP_VERSION);
}
$path = $_SERVER['SCRIPT_NAME'];
$path = str_replace('install.php', '', $path);
$currentHost = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $path;

$check_extension = check_extension([
    'fileinfo' => '获取文件的MIME信息',
    'PDO' => '必须拓展,否则无法正常安装系统',
    'pdo_mysql' => 'MySQL 数据库驱动',
    'mbstring' => '多字节字符串处理',
    'json' => 'JSON 序列化',
    'gd' => '图片处理',
    'zip' => '压缩包处理',
    'curl' => 'HTTP 请求',
    'openssl' => '用于支持SSL传输协议的软件包'
]);

$disable_functions_enable = !in_array('exec', explode(',', ini_get('disable_functions')));
$check_extension[] = [
    'extension_name' => 'exec',
    'extension_loaded_enable' => $disable_functions_enable,
    'remark' => '用于执行定时任务',
    'class' => $disable_functions_enable ? '' : 'dask-text-danger',
];

// 发布包不包含运行时目录，安装器先按需创建再检查写权限。
foreach ([ROOT_PATH . 'runtime' . DS, ROOT_PATH . 'backup' . DS, ROOT_PATH . 'public' . DS . 'storage' . DS] as $runtimeDir) {
    if (!is_dir($runtimeDir)) {
        @mkdir($runtimeDir, 0775, true);
    }
}

$file_write_enable = file_write_enable(array(
    ROOT_PATH . 'config' . DS,
    ROOT_PATH . 'runtime' . DS,
    ROOT_PATH . 'public' . DS,
    ROOT_PATH . 'backup' . DS,
    ROOT_PATH . 'public' . DS . 'storage' . DS
));

//检查拓展函数
function check_extension($extensions = [])
{
    if (empty($extensions)) {
        return false;
    }
    $return = array();
    foreach ($extensions as $key => $val) {
        $extension_loaded_enable = extension_loaded($key) ? 1 : 0;
        $return[$key] = array(
            'extension_name' => $key,
            'extension_loaded_enable' => $extension_loaded_enable,
            'remark' => $val,
            'class' => $extension_loaded_enable ? '' : 'dask-text-danger',
        );
    }
    return $return;
}

//检查目录是否可读写
function file_write_enable($file_write_enable): array
{
    $return = array();
    foreach ($file_write_enable as $val) {
        $return[$val] = array(
            'dir' => $val,
            'enable' => isReadWrite($val) ? 1 : 0,
            'error' => !isReadWrite($val) ? '读写权限不足' : '正常'
        );
    }
    return  $return;
}

function isReadWrite($file): bool
{
    if (DIRECTORY_SEPARATOR === '\\') {
        return true;
    }
    if (DIRECTORY_SEPARATOR === '/' && @ini_get("safe_mode") === false) {
        return is_writable($file);
    }
    if (!is_file($file) || ($fp = @fopen($file, "r+")) === false) {
        return false;
    }
    fclose($fp);
    return true;
}

// POST请求
if (isPost()) {
    $post = $_POST;
    switch ($post['step']) {
        case 1:
            if (!isset($post['accept']) || !$post['accept']) {
                $data = [
                    'code' => 0,
                    'msg'  => '请先阅读并同意许可协议',
                ];
            } else {
                $data = [
                    'code' => 1,
                    'msg'  => '即将进入下一步安装',
                    'url'  => 'install.php?step=2',
                ];
            }
            die(json_encode($data));
            break;
        case 2:
            $errorInfo = null;

            if (is_file(INSTALL_PATH . 'lock' . DS . 'install.lock')) {
                $errorInfo = '已安装系统，如需重新安装请删除文件：/install/lock/install.lock';
            } elseif (!isReadWrite(ROOT_PATH . 'config' . DS)) {
                $errorInfo = ROOT_PATH . 'config' . DS . '：读写权限不足';
            } elseif (!isReadWrite(ROOT_PATH . 'runtime' . DS)) {
                $errorInfo = ROOT_PATH . 'runtime' . DS . '：读写权限不足';
            } elseif (!isReadWrite(ROOT_PATH . 'public' . DS)) {
                $errorInfo = ROOT_PATH . 'public' . DS . '：读写权限不足';
            } elseif (!checkPhpVersion('8.2.0')) {
                $errorInfo = 'PHP版本不能小于8.2.0（ThinkPHP 8 要求）';
            } elseif (!extension_loaded("PDO")) {
                $errorInfo = '当前未开启PDO，无法进行安装';
            }
            if (!empty($errorInfo)) {
                $data = [
                    'code' => 0,
                    'msg'  => $errorInfo,
                ];
                die(json_encode($data));
            }
            $data = [
                'code' => 1,
                'msg'  => '检测正常',
                'url'  => 'install.php?step=3',
            ];
            die(json_encode($data));
            break;
        case 3:
            if (!$post['database']) {
                $data = [
                    'code' => 0,
                    'msg'  => '请填写数据库名称',
                ];
                die(json_encode($data));
            }

            if (!$post['db_username']) {
                $data = [
                    'code' => 0,
                    'msg'  => '请填写数据库账号',
                ];
                die(json_encode($data));
            }

            if (!$post['db_password']) {
                $data = [
                    'code' => 0,
                    'msg'  => '请填写数据库密码',
                ];
                die(json_encode($data));
            }

            $cover = $post['cover'] == 1;
            $database = $post['database'];
            if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
                die(json_encode(['code' => 0, 'msg' => '数据库名称只能包含字母、数字和下划线']));
            }
            $hostname = $post['hostname'] ?: '127.0.0.1';
            $hostport = $post['hostport'] ?: '3306';
            $dbUsername = $post['db_username'];
            $dbPassword = $post['db_password'];
            $prefix = $post['prefix'];
            if (!preg_match('/^[A-Za-z0-9_]*$/', (string) $prefix)) {
                die(json_encode(['code' => 0, 'msg' => '数据表前缀只能包含字母、数字和下划线']));
            }
            $adminUrl = $post['admin_url'] ?? 'admin';
            $username = $post['username'];
            $password = $post['password'];
            // 参数验证
            $validateError = null;

            // 判断是否有特殊字符
            if ($adminUrl) {
                $check = preg_match('/[0-9a-zA-Z]+$/', $adminUrl, $matches);
                if (!$check) {
                    $validateError = '后台地址不能含有特殊字符, 只能包含字母或数字。';
                    $data = [
                        'code' => 0,
                        'msg'  => $validateError,
                    ];
                    die(json_encode($data));
                }
                if (strlen($adminUrl) < 2) {
                    $validateError = '后台的地址不能小于2位数';
                } elseif (strlen($password) < 5) {
                    $validateError = '管理员密码不能小于5位数';
                } elseif (strlen($username) < 4) {
                    $validateError = '管理员账号不能小于4位数';
                }
            } else {
                $adminUrl = 'admin';
            }

            if (!empty($validateError)) {
                $data = [
                    'code' => 0,
                    'msg'  => $validateError,
                ];
                die(json_encode($data));
            }

            // DB类初始化
            $config = [
                'type'     => 'mysql',
                'hostname' => $hostname,
                'username' => $dbUsername,
                'password' => $dbPassword,
                'hostport' => $hostport,
                'charset'  => 'utf8mb4',
                'prefix'   => $prefix,
                'debug'    => true,
                'collation' => 'utf8mb4_general_ci',
            ];

            Db::setConfig([
                'default'     => 'mysql',
                'connections' => [
                    'mysql'   => $config,
                    'install' => array_merge($config, ['database' => $database]),
                ],
            ]);

            // 检测数据库连接
            if (!checkConnect()) {
                $data = [
                    'code' => 0,
                    'msg'  => '数据库连接失败',
                ];
                die(json_encode($data));
            }
            // 检测数据库是否存在
            if (!$cover && checkDatabase($database)) {
                $data = [
                    'code' => 0,
                    'msg'  => '数据库已存在，请选择覆盖安装或者修改数据库名',
                ];
                die(json_encode($data));
            }
            // 创建数据库
            createDatabase($database);
            // 导入sql语句等等
            $adminUrl = strstr($adminUrl, '.php') === false ? $adminUrl . '.php' : $adminUrl;

            $install = install($username, $password, array_merge($config, ['database' => $database]), $adminUrl);
            if ($install !== true) {
                $data = [
                    'code' => 0,
                    'msg'  => '系统安装失败：' . $install,
                ];
                die(json_encode($data));
            }
            $data = [
                'code' => 1,
                'msg'  => '系统安装成功',
                'url'  => 'install.php?step=4&admin=' . $adminUrl,
            ];
            die(json_encode($data));
            break;
        case 4:
            break;
    }
}

function isPost()
{
    return ($_SERVER['REQUEST_METHOD'] === 'POST') ? 1 : 0;
}

function checkPhpVersion($version)
{
    $php_version = explode('-', PHP_VERSION);
    return strnatcasecmp($php_version[0], $version) >= 0;
}

function checkConnect()
{
    try {
        Db::query("select version()");
    } catch (\Exception $e) {
        return false;
    }
    return true;
}

function checkDatabase($database)
{
    $check = Db::query("SELECT * FROM information_schema.schemata WHERE schema_name='{$database}'");
    if (empty($check)) {
        return false;
    }
    return true;
}

function createDatabase($database)
{
    try {
        Db::execute("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8mb4");
    } catch (\Exception $e) {
        return false;
    }
    return true;
}

function parseSql($sql, $to, $from)
{
    [$pure_sql, $comment] = [[], false];
    $sql = explode("\n", trim(str_replace(["\r\n", "\r"], "\n", $sql)));
    foreach ($sql as $key => $line) {
        if ($line == '') {
            continue;
        }
        if (preg_match("/^(#|--)/", $line)) {
            continue;
        }
        if (preg_match("/^\/\*(.*?)\*\//", $line)) {
            continue;
        }
        if (substr($line, 0, 2) === '/*') {
            $comment = true;
            continue;
        }
        if (substr($line, -2) === '*/') {
            $comment = false;
            continue;
        }
        if ($comment) {
            continue;
        }
        if ($from != '') {
            $line = str_replace('`' . $from, '`' . $to, $line);
        }
        if ($line === 'BEGIN;' || $line === 'COMMIT;') {
            continue;
        }
        $pure_sql[] = $line;
    }
    $pure_sql = implode("\n", $pure_sql);
    return explode(";\n", $pure_sql);
}

function install($username, $password, $config, $adminUrl)
{
    $sqlPath = file_get_contents(INSTALL_PATH . 'sql' . DS . 'install.sql');
    $sqlArray = parseSql($sqlPath, $config['prefix'], 'dask_');
    Db::startTrans();
    try {
        foreach ($sqlArray as $vo) {
            Db::connect('install')->execute($vo);
        }

        $pinyin = new Pinyin();
        $url_token = $pinyin->permalink($username, '');
        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $salt = substr(str_shuffle(str_repeat($pool, (int)ceil(6 / strlen($pool)))), 0, 6);
        $uid = Db::connect('install')
            ->name('users')
            ->insertGetId([
                'nick_name' => $username,
                'user_name'    => $username,
                'avatar'    => '/static/common/image/default-avatar.svg',
                'password'    => password($password, $salt),
                'salt' => $salt,
                'create_time' => time(),
                'group_id' => 1,
                'credit_group_id' => 1,
                'url_token' => $url_token,
                'credit' => 1000,
                'status' => 1
            ]);
        if ($uid) {
            Db::connect('install')
                ->name('users_extends')
                ->insert([
                    'uid' => $uid,
                    'inbox_setting' => 'all',
                    'notify_setting' => '{"site":["BEST_ANSWER","TYPE_PEOPLE_FOCUS_ME","QUESTION_ANSWER","QUESTION_COMMENT_AT_ME","QUESTION_ANSWER_COMMENT_AT_ME","NEW_ANSWER_COMMENT","INVITE_ANSWER","NEW_ARTICLE_COMMENT","ARTICLE_COMMENT_AT_ME","NEW_QUESTION_COMMENT","AGREE_CONTENT"],"email":["BEST_ANSWER","TYPE_PEOPLE_FOCUS_ME","QUESTION_ANSWER","QUESTION_COMMENT_AT_ME","QUESTION_ANSWER_COMMENT_AT_ME","NEW_ANSWER_COMMENT","INVITE_ANSWER","NEW_ARTICLE_COMMENT","ARTICLE_COMMENT_AT_ME","NEW_QUESTION_COMMENT","AGREE_CONTENT"]}',
                ]);

            Db::connect('install')
                ->name('credit_log')
                ->insert([
                    'uid' => $uid,
                    'record_id' => $uid,
                    'action_type' => 'AWARD',
                    'credit' => 1000,
                    'remark' => '系统操作成长值',
                    'balance' => 1000,
                    'create_time' => time(),
                    'record_db' => 'users'
                ]);

        }
        // 处理安装文件
        !is_dir(INSTALL_PATH) && !mkdir($concurrentDirectory = INSTALL_PATH) && !is_dir($concurrentDirectory);
        !is_dir(INSTALL_PATH . 'lock' . DS) && !mkdir($concurrentDirectory = INSTALL_PATH . 'lock' . DS) && !is_dir($concurrentDirectory);
        @file_put_contents(INSTALL_PATH . 'lock' . DS . 'install.lock', date('Y-m-d H:i:s'));
        @file_put_contents(CONFIG_PATH . 'app.php', getAppConfig($adminUrl));
        @file_put_contents(ROOT_PATH . 'public' . DS . $adminUrl, getAdminUrl());
        @file_put_contents(CONFIG_PATH . 'database.php', getDatabaseConfig($config));
        Db::commit();
        return true;
    } catch (\Exception $e) {
        Db::rollback();
        return $e->getMessage();
    }
}

function password($value, $salt)
{
    return md5(md5($value) . 'salt' . $salt);
}

function getAdminUrl()
{
    $http = '$http';
    $response = '$response';
    return <<<EOT
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
// [ 应用入口文件 ]
namespace think;
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DS);
define('ENTRANCE', 'backend');

if (!is_file(ROOT_PATH . 'install' . DS . 'lock' . DS . 'install.lock')) {
header("location:/install.php");exit;
}

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
// 执行HTTP应用并响应
{$http} = (new App())->http;
{$response} = {$http}->run();
{$response}->send();
{$http}->end({$response});
EOT;
}
function rand_build($type = 'alnum', $len = 8)
{
    $pool = '';
    switch ($type) {
        case 'alpha':
        case 'alnum':
        case 'numeric':
        case 'noZero':
            switch ($type) {
                case 'alpha':
                    $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    break;
                case 'alnum':
                    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    break;
                case 'numeric':
                    $pool = '0123456789';
                    break;
                case 'noZero':
                    $pool = '123456789';
                    break;
            }
            return substr(str_shuffle(str_repeat($pool, (int)ceil($len / strlen($pool)))), 0, $len);
        case 'unique':
        case 'md5':
            return md5(uniqid((string)mt_rand()));
        case 'encrypt':
        case 'sha1':
            return sha1(uniqid((string)mt_rand(), true));
    }
}

function renderConfigTemplate(string $name, array $values): string
{
    $template = CONFIG_PATH . $name . '.php.example';
    if (!is_file($template) || !is_readable($template)) {
        throw new RuntimeException('配置模板缺失或不可读：' . $template);
    }

    $content = file_get_contents($template);
    $replacements = [];
    foreach ($values as $key => $value) {
        // 安装期值写入 PHP 单引号字符串，先转义反斜杠与单引号。
        $replacements['{{' . $key . '}}'] = addcslashes((string) $value, "\\'");
    }
    $content = strtr($content, $replacements);

    if (preg_match('/\{\{[A-Z_]+\}\}/', $content, $matches) === 1) {
        throw new RuntimeException('配置模板存在未替换占位符：' . $matches[0]);
    }
    return $content;
}

function getAppConfig($admin)
{
    return renderConfigTemplate('app', [
        'ADMIN_FILE'     => $admin,
        'SESSION_NAME'   => rand_build('alpha', 16),
        'SESSION_PREFIX' => rand_build('alpha', 10),
        'CACHE_PREFIX'   => rand_build('alpha', 10),
        'TOKEN_KEY'      => rand_build('alpha', 10),
    ]);
}

function getDatabaseConfig($data)
{
    return renderConfigTemplate('database', [
        'DB_HOSTNAME' => $data['hostname'],
        'DB_NAME'     => $data['database'],
        'DB_USERNAME' => $data['username'],
        'DB_PASSWORD' => $data['password'],
        'DB_HOSTPORT' => $data['hostport'],
        'DB_PREFIX'   => $data['prefix'],
    ]);
}

?>

<?php if (!isPost()) { ?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <base href="<?php echo $currentHost; ?>" /><!--[if IE]></base><![endif]-->
        <title>安装程序 - DudaCMS社交化问答程序</title>
        <link rel="stylesheet" href="static/common/fonts/fonts.min.css?v=<?php echo dask_install_asset_version('common/fonts/fonts.min.css'); ?>">
        <link rel="stylesheet" href="static/vendor/bootstrap/dist/css/bootstrap.min.css?v=<?php echo dask_install_asset_version('vendor/bootstrap/dist/css/bootstrap.min.css'); ?>">
        <link rel="stylesheet" href="static/common/design/tokens.css">
        <script src="static/common/js/standalone-theme.js"></script>
        <script src="static/vendor/jquery/dist/jquery.min.js?v=<?php echo dask_install_asset_version('vendor/jquery/dist/jquery.min.js'); ?>"></script>
        <script src="static/vendor/bootstrap/dist/js/bootstrap.bundle.min.js?v=<?php echo dask_install_asset_version('vendor/bootstrap/dist/js/bootstrap.bundle.min.js'); ?>"></script>
        <script src="static/common/js/dask-string.js"></script>
        <script src="static/vendor/layer/layer.js"></script>
    </head>
    <style>
        body {
            background-image: url('static/common/image/bg.jpg');
            background-size: cover
        }

        .install-left {
            background: none
        }

        .main {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            top: 3%
        }

        .nav-main-link,
        .nav-main-link .nav-main-link-icon {
            font-size: 1.2rem;
        }

        .nav-main-link.active,
        .nav-main-link.active .nav-main-link-icon {
            color: #46c37b !important;
            font-weight: bold;
        }

        .nav-main-link:hover {
            background: none
        }

        .nav-main {
            background: var(--dask-surface, #fff);
            border-bottom: 1px solid var(--dask-line, #eee);
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        .block-rounded {
            border-radius: 0 0 5px 5px !important;
        }

        /*通用滚动条样式，兼容性强*/
        .dask-overflow-auto {
            overflow-y: auto;
            overflow-x: hidden;
        }

        /*滚动条样式*/
        .dask-overflow-auto::-webkit-scrollbar {
            width: 6px;
        }

        .dask-overflow-auto::-webkit-scrollbar-thumb {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
            background: #D0D3D9;
        }

        .dask-overflow-auto::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.1);
        }

        li {
            list-style: none
        }

        /* 深色模式：背景图加暗色遮罩，表单卡片/文字/控件跟随令牌 */
        html[data-dark="1"] body::before {
            content: ""; position: fixed; inset: 0; z-index: -1;
            background: rgba(12, 14, 18, .72);
        }
        html[data-dark="1"] .bg-white,
        html[data-dark="1"] .card { background-color: var(--dask-surface) !important; color: var(--dask-text); }
        html[data-dark="1"] .install-left,
        html[data-dark="1"] .install-left .card-header { background-color: var(--dask-surface) !important; color: var(--dask-text); }
        html[data-dark="1"] .form-control,
        html[data-dark="1"] .table { background-color: var(--dask-surface) !important; color: var(--dask-text); border-color: var(--dask-line); }
        html[data-dark="1"] .nav-main { color: var(--dask-text); }


        .nav-main .nav-main-item {
            display: inline-block
        }
    </style>

    <body>
        <div class="main">
            <h2 class="text-center text-white mb-4 mt-3">
                DudaCMS问答系统安装程序
            </h2>
            <ul class="nav-main nav-main-horizontal nav-main-hover nav-main-horizontal-center py-2 mb-0 text-center">
                <li class="nav-main-item mx-2">
                    <a class="nav-main-link <?php echo !$_GET['step'] || $_GET['step'] == 1 ? 'active' : ''; ?>" href="javascript:;">
                        <i class="nav-main-link-icon fa fa-check"></i>
                        <span class="nav-main-link-name">许可协议</span>
                    </a>
                </li>
                <li class="nav-main-item mx-2">
                    <a class="nav-main-link <?php echo $_GET['step'] == 2 ? 'active' : ''; ?>" href="javascript:;">
                        <i class="nav-main-link-icon fa fa-check"></i>
                        <span class="nav-main-link-name">环境检测</span>
                    </a>
                </li>
                <li class="nav-main-item mx-2">
                    <a class="nav-main-link <?php echo $_GET['step'] == 3 ? 'active' : ''; ?>" href="javascript:;">
                        <i class="nav-main-link-icon fa fa-check"></i>
                        <span class="nav-main-link-name">参数配置</span>
                    </a>
                </li>
                <li class="nav-main-item mx-2">
                    <a class="nav-main-link <?php echo $_GET['step'] == 4 ? 'active' : ''; ?>" href="javascript:;">
                        <i class="nav-main-link-icon fa fa-check"></i>
                        <span class="nav-main-link-name">安装完成</span>
                    </a>
                </li>
            </ul>

            <div class="p-3 bg-white mb-1">
                <div class="alert alert-danger mt-3">
                    <p class="mb-0">为了保证您能更好地使用 DudaCMS，安装前请仔细阅读【 <a href="https://ask.btbk.org/" target="_blank">系统安装指南</a> 】</p>
                </div>
            </div>
            <?php if (!$_GET['step'] || $_GET['step'] == 1) { ?>
                <div class="card border-0">
                    <div class="card-header text-center bg-white">
                        <b>阅读许可协议</b>
                    </div>
                    <div class="card-body pt-0">
                        <div class="dask-overflow-auto mb-3" style="max-height: 400px;border:1px solid #eee;padding: 10px 15px">
                            <p>版权所有 ©2020-2026 DudaCMS团队保留所有权利。</p>
                            <p>感谢您选择 DudaCMS（英文品牌名 Dask）。DudaCMS 是 DudaCMS团队推出的一款基于 TP8 的社交化知识付费问答系统与企业内部知识库系统，用于打造私有社交化问答和内部知识存储。</p>
                            <p>DudaCMS is a TP8-powered social Q&A and internal knowledge base platform.</p>
                            <p>DudaCMS 的官方网址是： <a href="https://ask.btbk.org" target="_blank">https://ask.btbk.org</a></p>
                            <p>为了使您正确并合法使用本软件，请您在使用前务必阅读清楚下面的协议条款：</p>
                            <strong>一、本授权协议适用且仅适用于 DudaCMS 6.x 版本，DudaCMS团队对本授权协议拥有最终解释权。</strong><br>
                            <strong>二、协议许可的权利 </strong>
                            <p>1、您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。 </p>
                            <p>2、您可以在协议规定的约束和限制范围内修改，DudaCMS源代码或界面风格以适应您的网站要求。 </p>
                            <p>3、您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。 </p>
                            <p>4、获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持内容，自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见将被作为首要考虑，但没有一定被采纳的承诺或保证。 </p>
                            <strong>二、协议规定的约束和限制 </strong>
                            <p>1、未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目的或实现盈利的网站）。购买商业授权请登陆 <a href="https://ask.btbk.org" target="_blank">DudaCMS</a> 了解最新说明。</p>
                            <p>2、未经官方许可，不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。</p>
                            <p>3、不管你的网站是否整体使用 ，还是部份使用，在你使用了DudaCMS的网站主页上必须加上DudaCMS的官方网址(<a href="https://ask.btbk.org" target="_blank">DudaCMS</a>)的链接。</p>
                            <p>4、未经官方许可，禁止在DudaCMS的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。</p>
                            <p>5、如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。 </p>
                            <strong>三、有限担保和免责声明 </strong>
                            <p>1、本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。 </p>
                            <p>2、用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺对免费用户提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。 </p>
                            <p>3、电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始确认本协议并安装 DudaCMS 即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。</p>
                            <p>4、如果本软件带有其它软件的整合API示范例子包，这些文件版权不属于本软件官方，并且这些文件是没经过授权发布的，请参考相关软件的使用许可合法的使用。</p>
                            <p><b>协议发布时间：</b> 2026年11月18日</p>
                            <p><b>版本最新更新：</b> 2026年11月18日 </p>
                        </div>
                        <div class="clearfix mb-3">
                            <form method="post" action="install.php?step=1">
                                <label class="float-start">
                                    <input name="accept" type="checkbox" value="1" class="check_boxId dask-checkbox" /> 我已经阅读并同意此协议
                                </label>
                                <input type="hidden" name="step" value="1">
                                <button type="button" class="btn btn-primary float-end dask-ajax-form">下一步</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if ($_GET['step'] == 2) { ?>
                <div class="card border-0">
                    <div class="card-body">
                        <div class="mb-3 dask-overflow-auto" style="max-height: 400px;">
                            <div class="dask-form-group">
                                <h4>系统环境检测</h4>
                            </div>
                            <table class="table table-striped table-vcenter">
                                <thead>
                                    <tr>
                                        <th>需要开启的拓展或函数</th>
                                        <th>开启状态</th>
                                        <th>开启建议</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($check_extension as $key => $val) { ?>
                                        <tr class="<?php echo $val['class']; ?>">
                                            <td><?php echo $val['extension_name']; ?></td>
                                            <td><?php echo $val['extension_loaded_enable'] ? '<i class="fa fa-check text-success"></i>' : '<i class="text-danger fa fa-wrench"></i>'; ?></td>
                                            <td>(<?php echo $val['remark']; ?>)</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <div class="dask-form-group">
                                <h4>目录权限检测</h4>
                            </div>
                            <table class="table table-striped table-vcenter">
                                <thead>
                                    <tr>
                                        <th>目录名</th>
                                        <th>读写权限</th>
                                        <th>提示信息</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($file_write_enable as $key => $val) { ?>
                                        <tr>
                                            <td><?php echo $val['dir']; ?></td>
                                            <td><?php echo $val['enable'] ? '<i class="fa fa-check text-success"></i>' : '<i class="text-danger fa fa-wrench"></i>'; ?></td>
                                            <td><?php echo $val['error']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="clearfix mb-3">
                            <form method="post" action="install.php?step=2">
                                <input type="hidden" name="step" value="2">
                                <a class="btn btn-danger float-start" href="install.php?step=1">上一步</a>
                                <button type="button" class="btn btn-primary float-end dask-ajax-form">下一步</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if ($_GET['step'] == 3) { ?>
                <div class="card border-0">
                    <div class="card-body">
                        <form method="post" action="install.php?step=3">
                            <div class="dask-overflow-auto" style="max-height: 400px;">
                                <div class="mb-3">
                                    <label class="dask-form-label">数据库地址</label>
                                    <input class="form-control" name="hostname" placeholder="请输入数据库地址" value="127.0.0.1">
                                </div>
                                <div class="mb-3">
                                    <label class="dask-form-label">数据库端口</label>
                                    <input class="form-control" name="hostport" placeholder="请输入数据库端口" value="3306">
                                </div>
                                <div class="mb-3">
                                    <label class="dask-form-label">数据库名称</label>
                                    <input class="form-control" name="database" placeholder="请输入数据库名称" value="">
                                </div>
                                <div class="mb-3">
                                    <label class="dask-form-label">数据表前缀</label>
                                    <input class="form-control" name="prefix" placeholder="请输入数据表前缀" value="dask_">
                                </div>
                                <div class="mb-3">
                                    <label class="dask-form-label">数据库账号</label>
                                    <input class="form-control" name="db_username" placeholder="请输入数据库账号" value="root">
                                </div>
                                <div class="mb-3">
                                    <label class="dask-form-label">数据库密码</label>
                                    <input type="password" class="form-control" name="db_password" placeholder="请输入数据库密码">
                                </div>
                                <div class="mb-3">
                                    <label class="dask-form-label">覆盖数据库</label>
                                    <div class="form-control-block" style="text-align: left">
                                        <input type="radio" name="cover" value="1">覆盖
                                        <input type="radio" name="cover" value="0" checked>不覆盖
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="dask-form-label">后台地址</label>
                                    <input class="form-control" name="admin_url" placeholder="默认为admin" value="">
                                </div>

                                <div class="mb-3">
                                    <label class="dask-form-label">管理员账号</label>
                                    <input class="form-control" name="username" placeholder="请输入管理员账号" value="admin">
                                </div>
                                <div class="mb-3">
                                    <label class="dask-form-label">管理员密码</label>
                                    <input type="password" class="form-control" name="password" placeholder="请输入管理员密码">
                                </div>

                            </div>
                            <div class="clearfix mb-3 mt-3">
                                <input type="hidden" name="step" value="3">
                                <a class="btn btn-danger float-start" href="install.php?step=2">上一步</a>
                                <button type="button" class="btn btn-primary float-end dask-ajax-form">下一步</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>

            <?php if ($_GET['step'] == 4) { ?>
                <div class="card border-0">
                    <div class="card-body pb-4">
                        <div class="alert alert-success" role="alert" style="border-radius: 10px">
                            <h3 class="alert-heading font-w300 my-2"><i class="fa fa-check"></i>系统安装成功！</h3>
                            <p class="mb-0">恭喜！您已成功安装本系统！</p>
                        </div>
                        <p class="mb-1"><b>前台地址：</b> <a class="alert-link" href="<?php echo $currentHost; ?>"><?php echo $currentHost; ?></a></p>
                        <p class="mb-1"><b>后台地址：</b> <a class="alert-link" href="<?php echo $currentHost . $_GET['admin']; ?>"><?php echo $currentHost . $_GET['admin']; ?></a></p>
                        <p class="mb-1"><b>官方社区：</b> <a class="alert-link" href="https://ask.btbk.org">https://ask.btbk.org</a></p>
                        <p class="mb-1"><b>常见问题：</b> <a class="alert-link" href="https://ask.btbk.org/">常见问题解答</a></p>
                    </div>
                </div>
            <?php } ?>
        </div>
        <script>
            $(document).on('click', '.dask-ajax-form', function(e) {
                const that = this;
                const form = $($(that).parents('form')[0]);
                var loading = layer.msg('正在安装DudaCMS,请稍后...', {
                    icon: 16,
                    shade: 0.2,
                    time: false
                });
                $.ajax({
                    url: form.attr('action'),
                    dataType: 'json',
                    type: 'post',
                    data: form.serialize(),
                    success: function(result) {
                        layer.closeAll();
                        var msg = result.msg ? result.msg : '操作成功';
                        if (result.code > 0) {
                            window.location.href = result.url;
                        } else {
                            layer.alert(msg);
                        }
                    },
                });
            });
        </script>
    </body>

    </html>
<?php } ?>