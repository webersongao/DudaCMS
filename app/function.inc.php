<?php
// 应用公共文件
use app\common\library\helper\ImageHelper;
use app\model\Notify;
use app\model\Config;
use app\model\Users;
use Overtrue\Pinyin\Pinyin;
use think\Container;

/**
 * 会话级 CSRF 令牌。
 *
 * TP8 内置 token() 每次调用都会按 REQUEST_TIME_FLOAT 重新生成并覆盖 Session，
 * 多标签页 / 后台多开时会让先打开页面的表单令牌失效。这里改为「每个会话一个稳定令牌」，
 * 登录成功后由业务侧清空令牌实现轮换，兼顾多页面并发与重放防护。
 */
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $token = (string) session('__token__', '');
        if ($token === '') {
            $token = bin2hex(random_bytes(16));
            session('__token__', $token);
        }
        return $token;
    }
}

/**
 * 输出 CSRF 隐藏字段。
 */
if (!function_exists('csrf_field')) {
    function csrf_field(string $name = '__token__'): string
    {
        return '<input type="hidden" name="' . $name . '" value="' . csrf_token() . '">';
    }
}

/**
 * 数字转换为字符串单位
 * @param $num
 * @return string
 */
if (! function_exists('num2string')) {
    function num2string($num): string
    {
        if ($num >= 10000) {
            $num = round($num / 10000 * 100) / 100 . 'W+';
        } elseif ($num >= 1000) {
            $num = round($num / 1000 * 100) / 100 . 'K';
        }
        return $num;
    }
}

//后台使用前台地址替换
if(!function_exists('get_url'))
{
    function get_url(string $url = '', array $vars = [], $suffix = true, $domain = false)
    {
        $url = (string)url($url,$vars,$suffix,$domain);
        $admin_url = config('app.admin');
        return str_replace($admin_url.'/','',$url);
    }
}

//前台使用后台地址替换
if(!function_exists('backend_url'))
{
    function backend_url(string $url = '', array $vars = [], $suffix = true, $domain = true): string
    {
        $url = (string)url($url,$vars,$suffix,$domain);
        $admin_url = config('app.admin');
        return strstr($url,$admin_url.'/') ? $url : '/'.$admin_url.'/'.$url;
    }
}

/**
 * 统一静态资源 URL。
 *
 * 目标结构全部位于 public/static 下：
 *   - vendor/ 第三方库
 *   - common/ 跨端 CSS/JS/字体/设计令牌
 *   - admin/  后台私有资源
 *   - brand/  品牌资源（logo/favicon/二维码/题图/品牌配图）
 *
 * 助手统一归一化传入路径，调用方无需自行拼接 cdnUrl、staticUrl 或版本参数。
 */
/**
 * 归一化静态资源相对路径，asset() 与 asset_version() 共用同一口径。
 */
if (!function_exists('asset_normalize_path')) {
    function asset_normalize_path(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $path = preg_replace('#^static/#i', '', $path) ?: $path;

        $aliases = [
            'libs/' => 'vendor/',
            'design/' => 'common/design/',
        ];
        foreach ($aliases as $old => $new) {
            if (strpos($path, $old) === 0) {
                return $new . substr($path, strlen($old));
            }
        }
        return $path;
    }
}

/**
 * 逐文件资源版本号。
 *
 * 构建脚本 npm run assets:hash 扫描 public/static 生成 build-manifest.json，
 * 每个文件以 sha256 前 12 位作为版本。模板里的 ?v= 统一走这里，
 * 不再使用全站共享的 config('version.version')。
 */
if (!function_exists('asset_version')) {
    function asset_version(string $path): string
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            $manifest = root_path() . 'public' . DIRECTORY_SEPARATOR
                . 'static' . DIRECTORY_SEPARATOR . 'build-manifest.json';
            if (is_file($manifest)) {
                $data = json_decode((string) file_get_contents($manifest), true);
                if (is_array($data) && isset($data['files']) && is_array($data['files'])) {
                    $map = $data['files'];
                }
            }
        }

        $path = asset_normalize_path($path);
        $version = $map[$path] ?? (string) config('version.version', '1');
        if (function_exists('env') && env('APP_DEBUG')) {
            // 开发态保留强制刷新能力，但前缀仍是该文件自己的 hash，便于断言。
            $version .= '-dev' . time();
        }
        return $version;
    }
}

/**
 * 给前端按需加载器提供逐文件版本表（避免它复用 dask-util.js 自身的 hash）。
 */
if (!function_exists('asset_versions_json')) {
    function asset_versions_json(array $paths): string
    {
        $map = [];
        foreach ($paths as $path) {
            $path = (string) $path;
            $map[$path] = asset_version($path);
        }
        return (string) json_encode($map, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}

if (!function_exists('asset')) {
    function asset(string $path, bool $versioned = true): string
    {
        $path = asset_normalize_path($path);

        $base = trim((string) get_setting('cdn_url', ''));
        if ($base === '' && function_exists('request')) {
            try {
                $base = (string) request()->domain();
            } catch (Throwable $e) {
                $base = '';
            }
        }
        $base = rtrim($base, '/');
        $url = $base . '/static/' . ltrim($path, '/');

        if ($versioned) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'v=' . rawurlencode(asset_version($path));
        }
        return $url;
    }
}

/**
 * @param $q
 * @param string $string 要分割的字符串
 * @param int $length 指定的长度
 * @param string $class
 * @param String $end 在分割后的字符串块追加的内容
 * @param bool $once
 * @return string
 */
if(!function_exists('mb_chunk_split'))
{
    function mb_chunk_split($q, string $string, int $length,string $class='text-danger', string $end='\r\n', bool $once = false): string
    {
        $array = array();
        $str_len = mb_strlen($string);
        $b=0;
        while($str_len){
            $array[] = mb_substr($string, 0, $length, "utf-8");
            if($once)
                return htmlspecialchars($array[0] . $end, ENT_QUOTES, 'UTF-8');
            $string = mb_substr($string, $length, $str_len, "utf-8");
            $str_len = mb_strlen($string);
        }
        $q = (string)(is_array($q) ? end($q) : $q);
        foreach ($array as $key => $value) {
            if(stripos($value,$q)){
                $b=$key;
                break;
            }
        }
        $data=array_slice($array,$b,3);
        $result = count($data)>1 ? implode(" ", $data).'...':implode(" ", $data);
        $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
        if ($q === '') {
            return $result;
        }
        $class = preg_replace('/[^A-Za-z0-9_-]/', '', $class);
        $qSafe = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');
        return str_ireplace($qSafe, '<b class="'.$class.'">'.$qSafe.'</b>', $result);
    }
}

/**
 * 数据库实例
 * @param string $name 类名或标识 默认获取当前应用实例
 * @param bool $newInstance 是否每次创建新的实例
 * @return mixed
 */
if (! function_exists('db')) {
    function db(string $name='', bool $newInstance = false)
    {
        if($name)
        {
            return Container::getInstance()->make('db', [], $newInstance)->name($name);
        }
        return Container::getInstance()->make('db', [], $newInstance);
    }
}

//实例化小部件函数
if (! function_exists('widget')) {
    /**
     * 小部件函数
     * @param mixed $name 调用小部件控制器
     * @param array $param 小部件请求参数
     * @param string $layer 小部件模型层
     * @return mixed
     */
    function widget($name, array $param = [],string $layer='widget')
    {
        $name  = str_replace('/', '\\', $name);
        $module_exp = explode('@',$name);
        $array = explode('\\', end($module_exp));
        if(count($module_exp)==2)
        {
            $model = $module_exp[0];
            $action = $array[1];
            $class = app()->make('\\app\\'.$model .'\\'.$layer.'\\' . ucfirst($array[0]));
        }else {
            $action = $array[1];
            $class = app()->make(app()->getNamespace() .'\\'.$layer.'\\' . ucfirst($array[0]));
        }
        $call = [$class, $action];
        return app()->invoke($call, $param);
    }
}

/**
 * 字符串截取
 * @param $string
 * @param $start
 * @param $length
 * @param string $charset
 * @param string $dot
 * @return string
 */
if (! function_exists('str_cut')) {
    function str_cut($string, $start, $length, $charset = 'UTF-8', $dot = '...'): string
    {
        if (mb_strlen($string, $charset) <= $length) {
            return $string;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($string, $start, $length, $charset) . $dot;
        }

        return iconv_substr($string, $start, $length, $charset) . $dot;
    }
}

/**
 * 获取系统配置
 * @param string $name
 * @param null $default
 * @return mixed
 */
if (! function_exists('get_setting')) {
    function get_setting(string $name = '', $default = null)
    {
        return Config::getConfigs($name, $default);
    }
}

if (! function_exists('set_setting')) {
    function set_setting(string $name, $value): bool
    {
        return Config::setConfig($name, $value);
    }
}

if (! function_exists('config_bool')) {
    /** 统一布尔配置解析，兼容历史 Y/N 与 1/0 存储 */
    function config_bool(string $name, bool $default = false): bool
    {
        $value = get_setting($name, null);
        if ($value === null) {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower((string) $value), ['y', '1', 'true', 'on', 'yes'], true);
    }
}

if (! function_exists('config_int')) {
    function config_int(string $name, int $default = 0): int
    {
        $value = get_setting($name, null);
        return is_numeric($value) ? (int) $value : $default;
    }
}

if (! function_exists('editor_render')) {
    /**
     * 渲染系统内置 Vditor 编辑器。
     * 模板中用法：{:editor_render(['name'=>'detail','value'=>$value,'cat'=>'question','access_key'=>$key])}
     */
    function editor_render(array $params = []): string
    {
        return \app\common\library\helper\EditorHelper::render($params);
    }
}

if (! function_exists('lang_active_class')) {
    /**
     * 当前语言与 $code 一致时返回 ' is-active' 片段，供语言切换器高亮当前项。
     * 命名空间与三元逻辑封装在 PHP 侧，避免模板 {:} 解析器无法编译。
     */
    function lang_active_class(string $code): string
    {
        return \think\facade\Lang::getLangSet() === $code ? ' is-active' : '';
    }
}

if (! function_exists('available_langs')) {
    /**
     * 语言切换器数据源：按 config('lang.allow_lang_list') 顺序返回每项的
     * code/label/url/cls。url 与高亮类名预先算好，避免模板 {:} 解析器处理
     * 数组取值/命名空间表达式。
     * @return array<int, array{code:string,label:string,url:string,cls:string}>
     */
    function available_langs(): array
    {
        $codes  = (array) config('lang.allow_lang_list');
        $labels = (array) config('lang.lang_labels');
        $now    = \think\facade\Lang::getLangSet();
        $out    = [];
        foreach ($codes as $code) {
            $out[] = [
                'code'  => $code,
                'label' => $labels[$code] ?? $code,
                'url'   => url('ajax/change_lang', ['lang' => $code]),
                'cls'   => $now === $code ? ' is-active' : '',
            ];
        }
        return $out;
    }
}

/**
 * 获取内置字典数据（代码内置常量）。
 * 仅保留通知分组 / 通知类型 / 语言选择三组运行期必需的映射。
 */
if (! function_exists('get_dict')) {
    function get_dict(string $name = ''): array
    {
        $dicts = [
            'notify_group' => [
                'email' => '邮件通知', 'site' => '站内通知',
            ],
            'notify_type' => [
                'TYPE_PEOPLE_FOCUS_ME' => '关注我的', 'TYPE_PEOPLE_AT_ME' => '提到我的',
                'TYPE_AGREE' => '赞同喜欢', 'TYPE_ANSWER_COMMENT' => '评论回复',
                'TYPE_INVITE' => '邀请我的', 'TYPE_APPROVAL' => '站务通知',
                'TYPE_SYSTEM_NOTIFY' => '系统通知',
            ],
            'language_select' => [
                'zh-cn' => '中文', 'en-us' => '英文', 'zh-tw' => '繁體中文', 'es-es' => '西班牙语',
            ],
        ];
        return $dicts[$name] ?? [];
    }
}

/**
 * 获取主题风格配置（数据源为 dask_config group 12，后台「系统配置 → 主题风格」维护）。
 *
 * 支持两种调用形态：
 *   - get_theme_setting()                      返回 [分组][键] 二维数组（供 $theme_config 消费）
 *   - get_theme_setting('home.search_enable')  点分路径读取单个值
 * checkbox/files/images/select 由 Config::getConfigs 已 explode 为数组，in_array 用法保持兼容。
 *
 * @param string $name    点分路径，如 home.search_enable；留空返回全部分组
 * @param mixed  $default 键不存在时的默认值
 * @return mixed
 */
if (! function_exists('get_theme_setting')) {
    function get_theme_setting(string $name = '', $default = '')
    {
        // 旧分组名 => dask_config 键前缀（短前缀，name 长度 <= 50）
        static $groupPrefix = [
            'home'            => 't_hm_',
            'question'        => 't_qs_',
            'article'         => 't_art_',
            'column'          => 't_col_',
            'common'          => 't_cm_',
            'question_detail' => 't_qd_',
            'article_detail'  => 't_ad_',
            'mobile'          => 't_mb_',
        ];

        $all = get_setting(); // 扁平 name=>value，checkbox 等已 explode 为数组

        if ($name === '') {
            // 返回二维数组结构 [group][key] => value
            $config = [];
            foreach ($all as $key => $value) {
                if (strpos($key, 't_') !== 0) {
                    continue;
                }
                foreach ($groupPrefix as $group => $prefix) {
                    if (strpos($key, $prefix) === 0) {
                        $config[$group][substr($key, strlen($prefix))] = $value;
                        break;
                    }
                }
            }
            return $config;
        }

        $parts = explode('.', $name);
        $group = strtolower(array_shift($parts));
        if (! isset($groupPrefix[$group]) || empty($parts)) {
            return $default;
        }
        // 键名 common.filter-grey 归一为 t_cm_filter_grey（连字符转下划线）
        $configKey = $groupPrefix[$group] . str_replace('-', '_', implode('.', $parts));
        return array_key_exists($configKey, $all) ? $all[$configKey] : $default;
    }
}


/**
 * 格式化Bytes字符
 */
if(!function_exists('formatBytes'))
{
    function formatBytes($size, $delimiter = ''): string
    {
        // $size = $size*1024;
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
        return round($size, 2) . $delimiter . $units[$i];
    }
}

/**
 * 格式化时间格式
 */
if(!function_exists('date_friendly'))
{
    function date_friendly($timestamp, $time_limit = 604800, $out_format = 'Y-m-d H:i', $formats = null, $time_now = null)
    {
        if (!$timestamp)
        {
            return false;
        }

        if(get_setting('date_friendly_enable')=='normal')
        {
            return date($out_format,$timestamp);
        }

        if ($formats == null)
        {
            $formats = array(
                'YEAR' => L('%s 年前'),
                'MONTH' => L('%s 月前'),
                'DAY' => L('%s 天前'),
                'HOUR' => L('%s 小时前'),
                'MINUTE' => L('%s 分钟前'),
                'SECOND' => L('%s 秒前'),
                'YEARS' => L('%ss 年前'),
                'MONTHS' => L('%ss 月前'),
                'DAYS' => L('%ss 天前'),
                'HOURS' => L('%ss 小时前'),
                'MINUTES' => L('%ss 分钟前'),
                'SECONDS' => L('%ss 秒前')
            );
        }
        $time_now = $time_now == null ? time() : $time_now;
        $seconds = $time_now - $timestamp;

        if ($seconds == 0)
        {
            $seconds = 1;
        }

        if (!$time_limit OR $seconds > $time_limit)
        {
            return date($out_format, $timestamp);
        }
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $days = floor($hours / 24);
        $months = floor($days / 30);
        $years = floor($months / 12);
        $flag = '';
        if ($years > 0)
        {
            $diffFormat = 'YEAR';
            if($years > 1){
                $flag = 's';
            }
        }
        else
        {
            if ($months > 0)
            {
                $diffFormat = 'MONTH';
                if($months > 1){
                    $flag = 's';
                }
            }
            else
            {
                if ($days > 0)
                {
                    $diffFormat = 'DAY';
                    if($days > 1){
                        $flag = 's';
                    }
                }
                else
                {
                    if ($hours > 0)
                    {
                        $diffFormat = 'HOUR';
                        if($hours > 1){
                            $flag = 's';
                        }
                    }
                    else
                    {
                        if($minutes > 0){
                            $diffFormat = 'MINUTE';
                            if($minutes > 1){
                                $flag = 's';
                            }
                        }else{
                            $diffFormat = 'SECOND';
                            if($seconds > 1){
                                $flag = 's';
                            }
                        }
                    }
                }
            }
        }

        $dateDiff = null;
        switch ($diffFormat)
        {
            case 'YEAR' :
                $dateDiff = sprintf($formats[$diffFormat], $years, $flag);
                break;
            case 'MONTH' :
                $dateDiff = sprintf($formats[$diffFormat], $months, $flag);
                break;
            case 'DAY' :
                $dateDiff = sprintf($formats[$diffFormat], $days, $flag);
                break;
            case 'HOUR' :
                $dateDiff = sprintf($formats[$diffFormat], $hours, $flag);
                break;
            case 'MINUTE' :
                $dateDiff = sprintf($formats[$diffFormat], $minutes, $flag);
                break;
            case 'SECOND' :
                $dateDiff = sprintf($formats[$diffFormat], $seconds, $flag);
                break;
        }
        return $dateDiff;
    }
}

/**
 * 窄容器时间：折叠年份前缀，保留月日与时分，避免发布时间/更新时间折行。
 */
if(!function_exists('date_compact'))
{
    function date_compact($timestamp)
    {
        if (!$timestamp)
        {
            return '';
        }
        return '***' . date('n-j H:i', $timestamp);
    }
}

/**
 * 时间戳格式化时间
 * @param $time
 * @param string $format
 * @return false|string
 */
if(!function_exists('formatTime'))
{
    function formatTime($time, $format = 'Y-m-d H:i:s')
    {
        return date($format, intval($time));
    }
}

/**
 * 发送通知，系统内置通知，只发送用户允许接收的类型
 * @param int $sender_uid 发送用户id
 * @param int $recipient_uid 接收用户id
 * @param int $action_type 通知类型
 * @param string $item_type 通知内容类型
 * @param int $item_id 通知内容id
 * @param array $content 通知详细数据
 * @return bool
 */
if(!function_exists('send_notify'))
{
    function send_notify($sender_uid=0, $recipient_uid=0, $action_type='', $item_type='', $item_id = 0, $content = array(),$anonymous=0): bool
    {
        return \app\common\library\helper\NotifyHelper::sendNotify(intval($sender_uid),intval($recipient_uid),$action_type,intval($item_id),$item_type,$content);
    }
}

/**
 * 发送系统自定义通知
 * @param int $sender_uid 发送人
 * @param int $recipient_uid 接受用户
 * @param string $subject 通知标题
 * @param string $content 通知内容
 * @param int $anonymous 是否匿名发送
 * @return false|mixed
 */
if(!function_exists('send_site_diy_notify')){
    function send_site_diy_notify($sender_uid = 0, $recipient_uid = 0,$subject = '',$content='',$anonymous=0)
    {
        return Notify::sendNotify($sender_uid, $recipient_uid,$subject,$content,$anonymous);
    }
}

/**
 * 发送自定义邮件
 * @param mixed $email 邮件地址
 * @param $subject 邮件主题
 * @param $message 邮件内容
 * @return array
 */
if(!function_exists('send_email')) {
    function send_email( $email, $subject = '', $message = '')
    {
        return \app\common\library\helper\MailHelper::sendEmail($email, $subject, $message);
    }
}

// 用户相关
/**
 * 由用户名字段直接拼主页地址，供批量取用户信息时复用，避免逐行回查 dask_users
 * @param $url_token
 * @param $user_name
 * @param array $param
 * @param bool $domain
 * @return string
 */
if(!function_exists('build_user_url'))
{
    function build_user_url($url_token, $user_name, array $param = [], $domain = false): string
    {
        if ($url_token) {
            $name = $url_token;
        } else {
            static $pinyin;
            $pinyin = $pinyin ?: new Pinyin();
            $name = $pinyin->permalink((string) $user_name, '');
        }
        // 用户主页统一为 /people/{url_token}，地址里不再出现 index/index.php。
        $path = '/people/' . rawurlencode($name);
        if (!empty($param['type'])) {
            $path .= '/' . rawurlencode((string) $param['type']);
        }
        $query = array_diff_key($param, ['name' => 1, 'type' => 1]);
        if ($query) {
            $path .= '?' . http_build_query($query);
        }
        return $domain ? (string) \think\facade\Request::domain() . $path : $path;
    }
}

/**
 * 获取用户链接地址
 * @param $uid
 * @param array $param
 * @return string
 */
if(!function_exists('get_user_url'))
{
    function get_user_url($uid, array $param=[],$domain=false): string
    {
        static $userInfo = [];
        if(!array_key_exists($uid,$userInfo))
        {
            $userInfo[$uid] = db('users')->where('uid',$uid)->field('user_name,url_token')->find();
        }

        if(!$userInfo[$uid])
        {
            $name = (string) ($param['name'] ?? '');
            unset($param['name']);
            return $name !== '' ? build_user_url($name, $name, $param, $domain) : '/people/';
        }

        return build_user_url($userInfo[$uid]['url_token'], $userInfo[$uid]['user_name'], $param, $domain);
    }
}

if (!function_exists('get_username')) {
    function get_username($uid = 0): string
    {
        if (!($uid && is_numeric($uid))) {
            // 获取当前登录用户名；前台会话缺失时回退到 login_uid，避免 :string 返回 null 触发 500
            $name = session('login_user_info.nick_name');
            if ($name === null || $name === '') {
                $loginUid = session('login_uid');
                if ($loginUid) {
                    $name = db('users')->where('uid', $loginUid)->value('nick_name');
                }
            }
            return (string) $name;
        }

        static $list;
        // 获取缓存数据
        if (empty($list)) {
            $list = cache('sys_user_username_list');
        }

        // 查找用户信息
        $key = "u{$uid}";
        if (isset($list[$key])) {
            // 已缓存，直接使用
            return $list[$key];
        } else {
            // 调用接口获取用户信息
            $info = db('users')->field('nick_name')->find($uid);
            if ($info && $info['nick_name']) {
                $nickname = $info['nick_name'];
                $list[$key] = $nickname;
                // 缓存用户
                $count = count($list);
                // Gao:用户量超过5000，则半年失效，否则24小时失效，保证首页日志显示更友好
                $expire_time = $count > 5000 ? 15552000 : 86400; 
                $max = max(3000, get_setting('user_max_cache'));
                while ($count-- > $max) {
                    array_shift($list);
                }
                cache('sys_user_username_list', $list, $expire_time);
                return $nickname;
            } else {
                return '';
            }
        }
    }
}

if (!function_exists('get_link_username')) {
    function get_link_username($uid = 0): string
    {
        if (!($uid && is_numeric($uid))) {
            // 获取当前登录用户名；前台会话缺失时回退到 login_uid，避免 :string 返回 null 触发 500
            $name = session('login_user_info.nick_name');
            if ($name === null || $name === '') {
                $loginUid = session('login_uid');
                if ($loginUid) {
                    $name = db('users')->where('uid', $loginUid)->value('nick_name');
                }
            }
            return (string) $name;
        }
        
        // 获取用户名
        $name = get_username($uid);
        if (strlen($name) > 0) {
            return '<a href="' . get_user_url($uid) . '" class="dask-username" data-id="' . $uid . '" target="_blank">' . $name . '</a>';
        } else {
            return '';
        }
    }
}

if(!function_exists('get_user_info')) {
    function get_user_info($uid = 0, $field = '')
    {
        if (!$uid && !$uid = getLoginUid()) {
            return false;
        }

        $user_info = cache('user_info_' . $uid);
        if (!$user_info) {
            $user_info = Users::getUserInfo($uid);
            cache('user_info_' . $uid, $user_info,300);
        }
        return $field ? $user_info[$field] : $user_info;
    }
}

// 过滤危险标签：防SS攻击
if (!function_exists('remove_xss')) {
    function remove_xss($string): string
    {
        if(get_setting('remove_xss')!='Y')
        {
            // 即使后台关闭 HTMLPurifier，也做一次轻量 DOM 清洗，避免存储型 XSS
            return \app\common\library\helper\HtmlHelper::purify((string) $string);
        }

        // 生成配置对象
        $config = HTMLPurifier_Config::createDefault();
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('HTML.Trusted',true);
        $config->set('HTML.SafeEmbed',true);
        $config->set('HTML.SafeObject',true);
        $config->set('Output.FlashCompat',true);
        // 以下就是配置：
        $config->set('Core.Encoding', 'UTF-8');
        // 设置允许使用的HTML标签
        $config->set('HTML.Allowed', 'div,b,strong,i,em,a[href|title],ul,ol,li,br,p[style],span[style],img[width|height|alt|src]');
        // 设置允许出现的CSS样式属性
        $config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
        // 设置a标签上是否允许使用target="_blank"
        $config->set('HTML.TargetBlank', TRUE);
        // 使用配置生成过滤用的对象
        $obj = new HTMLPurifier($config);
        // 过滤字符串
        return $obj->purify($string);
    }
}

if(!function_exists('isSuperAdmin'))
{
    /**
     * 是否是超级管理员
     * @return bool
     */
    function isSuperAdmin(): bool
    {
        if(!getLoginUid()) return false;
        $uid = getLoginUid();
        $userInfo = Users::getUserInfo($uid);
        return $userInfo['group_id']==1;
    }
}

if(!function_exists('isNormalAdmin'))
{
    /**
     * 是否是普通管理员
     * @return bool
     */
    function isNormalAdmin(): bool
    {
        if(!getLoginUid()) return false;
        $uid = getLoginUid();
        $userInfo = Users::getUserInfo($uid);
        return $userInfo['group_id']==2;
    }
}


if(!function_exists('get_user_permission'))
{
    /**
     * 获取用户权限
     * @param mixed $permission
     * @param int $uid
     * @return mixed
     */
    function get_user_permission($permission='',int $uid=0)
    {
        if(!$uid && !$uid = getLoginUid()) return false;
        $permission_list = Users::getUserGroupInfo($uid);
        return $permission ? ($permission_list['permission'][$permission] ?? false) : $permission_list['permission'];
    }
}
/**
 * 唯一日期编码
 * @param integer $size 编码长度
 * @param string $prefix 编码前缀
 * @return string
 */
if(!function_exists('uniqueDate')) {
    function uniqueDate(int $size = 16, string $prefix = ''): string
    {
        if ($size < 14) $size = 14;
        $code = $prefix . date('Ymd') . (date('H') + date('i')) . date('s');
        while (strlen($code) < $size) $code .= rand(0, 9);
        return $code;
    }
}

/**
 * 获取数据表全名
 * @param string $table 数据表名
 * @return string
 */
if (! function_exists('get_table')) {
    function get_table($table): string
    {
        return config('database.connections.mysql.prefix').$table;
    }
}

if (!function_exists('get_month_list')) {
    /**
     * 生成一段时间的月份列表
     */
    function get_month_list($timestamp1, $timestamp2, $year_format = 'Y', $month_format = 'm'): array
    {
        $years = date($year_format, $timestamp1);
        $months = date($month_format, $timestamp1);
        $days = date('d', $timestamp1);

        $yearn = date($year_format, $timestamp2);
        $month = date($month_format, $timestamp2);
        $day = date('d', $timestamp2);
        $monthInterval = 0;
        if ($years == $yearn)
        {
            $monthInterval = $month - $months;
        }
        else if ($years < $yearn)
        {
            $yearInterval = $yearn - $years -1;
            $monthInterval = (12 - $months + $month) + 12 * $yearInterval;
        }

        $timeData = array();
        for ($i = 0; $i <= $monthInterval; $i++)
        {
            $tmpTime = mktime(0, 0, 0, $months + $i, 1, $years);
            $timeData[$i]['year'] = date($year_format, $tmpTime);
            $timeData[$i]['month'] = date($month_format, $tmpTime);
            $timeData[$i]['beginday'] = '01';
            $timeData[$i]['endday'] = date('t', $tmpTime);
        }
        $timeData[0]['beginday'] = $days;
        $timeData[$monthInterval]['endday'] = $day;
        unset($tmpTime);
        return $timeData;
    }
}

if (!function_exists('cjk_strlen')) {
    function cjk_strlen($string, $charset = 'UTF-8')
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, $charset);
        } else {
            return iconv_strlen($string, $charset);
        }
    }
}

if (!function_exists('parseSql')) {
    function parseSql($sql, $to='dask_', $from='dask_')
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
}

if (!function_exists('compile_password')) {
    /**
     * 生成密码摘要（配套 verify_password 使用；新密码请使用 hash_password）
     */
    function compile_password($password, $salt): string
    {
        return md5(md5($password) . 'salt' . $salt);
    }
}

if (!function_exists('hash_password')) {
    /**
     * 生成安全的密码哈希（bcrypt）
     */
    function hash_password(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}

if (!function_exists('safe_order_column')) {
    /**
     * 校验排序字段名，防止 ORDER BY 注入（只允许字母/数字/下划线）
     */
    function safe_order_column($column, string $default = 'id'): string
    {
        $column = (string) $column;
        return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column) ? $column : $default;
    }
}

if (!function_exists('safe_content_sort')) {
    /**
     * 内容列表排序白名单，防止模板标签把用户输入直接拼进 ORDER BY
     */
    function safe_content_sort($sort, string $default = 'new'): string
    {
        $sort = strtolower(trim((string) $sort));
        return in_array($sort, ['new', 'hot', 'recommend', 'unresponsive', 'focus', 'default'], true)
            ? $sort
            : $default;
    }
}

if (!function_exists('safe_order_direction')) {
    /**
     * 校验排序方向
     */
    function safe_order_direction($direction): string
    {
        return strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';
    }
}

if (!function_exists('verify_password')) {
    /**
     * 校验密码：支持 bcrypt/argon2 与 md5(md5(pwd)+salt) 两种摘要
     */
    function verify_password(string $password, string $hash, string $salt = ''): bool
    {
        if ($hash === '') {
            return false;
        }

        // 现代哈希（$2y$、$argon2...）
        if (password_get_info($hash)['algo'] !== null && strpos($hash, '$') === 0) {
            return password_verify($password, $hash);
        }

        // MD5 摘要
        return hash_equals((string) $hash, compile_password($password, $salt));
    }
}

if (!function_exists('password_needs_upgrade')) {
    /**
     * 是否需要把 MD5 摘要升级为 bcrypt
     */
    function password_needs_upgrade(string $hash): bool
    {
        return strpos($hash, '$2y$') !== 0 || password_needs_rehash($hash, PASSWORD_BCRYPT);
    }
}

//系统自定义序列化
if (!function_exists('dask_serialize')) {
    function dask_serialize( $obj ): string
    {
        return base64_encode(gzcompress(serialize($obj)));
    }
}

//系统自定义反序列化
if (!function_exists('dask_unserialize')) {
    function dask_unserialize($txt)
    {
        $raw = @gzuncompress(base64_decode((string) $txt));
        if ($raw === false) {
            return false;
        }
        // 禁止反序列化任意对象，避免 PHP 对象注入
        return @unserialize($raw, ['allowed_classes' => false]);
    }
}

if (!function_exists('L')) {
    /**
     * 全局多语言函数
     */
    function L($str, $vars = [], $lang = '')
    {
        if (is_numeric($str) || empty($str)) {
            return $str;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return lang($str, $vars, $lang);
    }
}

if (!function_exists('copyDir')) {
    // 拷贝目录
    function copyDir($dir, $toDir)
    {
        if (!is_dir($toDir)) {
            mkdir($toDir); // 如果$toDir没有创建则创建目录
        }

        if ($handle = @opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue; // 避免删除当前目录和上级目录
                }
                if (is_dir($dir . '/' . $file)) {
                    // 如果是目录就递归
                    copyDir($dir . '/' . $file,$toDir . '/' . $file);
                } else {
                    // 如果是文件直接复制过去
                    copy($dir . '/' . $file,$toDir . '/' . $file);
                }
            }
            closedir($handle);
        }
    }
}

if(!function_exists('getServerIp'))
{
    function getServerIp()
    {
        if (!empty($_SERVER['SERVER_ADDR'])) {
            $ip = $_SERVER['SERVER_ADDR'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $ip = gethostbyname($_SERVER['SERVER_NAME']);
        } else {
            // CLI 环境无 SERVER_ADDR/SERVER_NAME 时的回退
            $ip = gethostbyname(gethostname());
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '127.0.0.1';
    }
}

if(!function_exists('isWx')) {
    function isWx()
    {
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (preg_match('/micromessenger/i', $user_agent)) {
            return true;
        }
        return false;
    }
}

/**
 * 去除空格和换行
 * @param $str
 * @return array|string|string[]|null
 */
if(!function_exists('removeEmpty')) {
    function removeEmpty($str): string
    {
        $str = str_replace(array("\r\n", "\r", "\n"), "", $str);
        $str = htmlspecialchars_decode($str);
        $str = str_replace('&nbsp;','',$str);
        $str = preg_replace('# #', '', $str);
        if($img = ImageHelper::src($str))
        {
            return strip_tags($img);
        }
        return strip_tags($str);
    }
}

if(!function_exists('array_key_sort_asc_callback')) {
    function array_key_sort_asc_callback($a, $b): int
    {
        if ($a['sort'] == $b['sort']) {
            return 0;
        }
        return ($a['sort'] < $b['sort']) ? -1 : 1;
    }
}

// 密码检查
if (!function_exists('daskCheckPassword')) {
    function daskCheckPassword($value)
    {
        // 验证密码长度
        $len = mb_strlen($value);
        if ($len < get_setting('password_min_length') || $len > get_setting('password_max_length')) {
            return '请输入'.get_setting('password_min_length').' - '.get_setting('password_max_length').' 位的密码';
        }

        $types = get_setting('password_type');
        if (empty($types)) return true;
        if (in_array('number', $types) && !preg_match("/[0-9]+/", $value)) return '密码需包含数字';

        if (in_array('special', $types) && !preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/", $value)) return '密码需包含特殊字符';

        if (in_array('letter', $types) && !preg_match("/[a-zA-Z]+/", $value)) return '密码需包含大小写字母';

        return true;
    }
}

//图片地址替换
if(!function_exists('replacePic'))
{
    function replacePic($pic='',$uploadUrl='')
    {
        $uploadUrl = $uploadUrl ?: request()->domain();
        if(!$pic) return $pic;

        if(!strstr($pic,'http') && !strstr($pic,'https'))
        {
            $pic = $uploadUrl.$pic;
        }
        return $pic;
    }
}

/**
 * 注册异步处理任务
 * @param string $title 任务名称
 * @param string $command 执行脚本
 * @param mixed $later 首次执行延时时间
 * @param array $data 任务附加数据
 * @param int $rscript 任务类型(0单例,1多例)
 * @param int $loops 循环等待时间
 **/
//获取用户UID
if(!function_exists('getLoginUid'))
{
    function getLoginUid()
    {
        $sessionUid = session('login_uid');
        if ($sessionUid) {
            return $sessionUid;
        }
        if (get_setting('remember_login_enable') != 'Y') {
            return 0;
        }
        $cookieKey = config('app.token')['key'] . '_' . md5('login_uid');
        $cookieUid = (string) cookie($cookieKey);
        if ($cookieUid === '') {
            return 0;
        }
        $payload = \app\common\library\helper\SignedValueHelper::verify('remember', $cookieUid);
        if ($payload === null || !str_contains($payload, '|')) {
            cookie($cookieKey, null);
            return 0;
        }
        [$uid, $fingerprint] = array_pad(explode('|', $payload, 2), 2, '');
        $uid = (int) $uid;
        $user = db('users')->where('uid', $uid)->field('uid,password,salt,status')->find();
        $expected = $user ? substr(hash('sha256', (string) $user['password'] . (string) $user['salt']), 0, 16) : '';
        if (!$user || $user['status'] != 1 || !hash_equals($expected, (string) $fingerprint)) {
            cookie($cookieKey, null);
            return 0;
        }
        session('login_uid', $uid);
        return $uid;
    }
}

function checkTableExist($table)
{
    $prefix = app()->db->getConfig('connections.mysql.prefix');
    $table = strstr($table,$prefix) ? $table : $prefix.$table;
    $row = \think\facade\Db::query(
        "SELECT COUNT(*) AS num FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = ?",
        [$table]
    );
    return (int) ($row[0]['num'] ?? 0);
}


function online_decrypt($password)
{
    $key = (string) config('app.crypto.key', '');
    $iv  = (string) config('app.crypto.iv', '');
    if ($key === '' || $iv === '') {
        return false;
    }
    return openssl_decrypt(base64_decode((string) $password), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
}

// Gao：操作频次限制
if (!function_exists('checkValidLimit')) {
    function checkValidLimit($uid = '', $cachekey = '', $expire_minutes = 1)
    {
        if (empty($uid) || empty($cachekey) || !is_numeric($expire_minutes) || $expire_minutes <= 0) {
            return false;
        }
        $cacheKey = $cachekey . '_' . $uid;
        $lastCallTime = cache($cacheKey);
        $currentTime = microtime(true); // 获取当前的浮点数时间戳

        if ($lastCallTime !== false && ($currentTime - $lastCallTime) < ($expire_minutes * 60)) {
            return false;
        }
        cache($cacheKey, $currentTime);
        return true;
    }

}


// 私信消息渲染：QQ 表情 token 与图片 token 统一在此转换为安全 HTML。
if (!function_exists('render_inbox_message')) {
    function render_inbox_message($message): string
    {
        $text = strip_tags(htmlspecialchars_decode((string) $message, ENT_QUOTES));
        $images = [];
        $text = preg_replace_callback('/\[img\]([^\[\]\s]+)\[\/img\]/i', function ($m) use (&$images) {
            $url = htmlspecialchars_decode($m[1], ENT_QUOTES);
            $parts = parse_url($url);
            $host = strtolower((string) ($parts['host'] ?? ''));
            $currentHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
            $safe = ($host === '' && strpos($url, '//') !== 0 && !preg_match('#^[a-z][a-z0-9+.-]*:#i', $url))
                || ($host !== '' && $currentHost !== '' && $host === $currentHost);
            if (!$safe || !preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $parts['path'] ?? '')) {
                return '[图片]';
            }
            $src = ($host === '' && strpos($url, '/') !== 0) ? '/' . $url : $url;
            $index = count($images);
            $images[$index] = '<img class="dask-im-image" src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="图片" loading="lazy">';
            return "\x00IMGToken" . $index . "\x00";
        }, $text);

        $manifest = root_path() . 'public/static/common/image/emotion/emotion.json';
        $tokenMap = [];
        if (is_file($manifest)) {
            $items = json_decode((string) file_get_contents($manifest), true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (!empty($item['text']) && !empty($item['name'])) {
                        $tokenMap[trim((string) $item['text'], '[]')] = (string) $item['name'];
                    }
                }
            }
        }
        $text = preg_replace_callback('/\[([^\[\]]{1,20})\]/u', function ($m) use ($tokenMap) {
            $token = $m[1];
            if (isset($tokenMap[$token]) && preg_match('/^expression_\d+$/i', $tokenMap[$token])) {
                $src = '/static/common/image/emotion/' . $tokenMap[$token] . '.png';
                return '<img class="dask-im-emoji" src="' . $src . '" alt="' . htmlspecialchars($m[0], ENT_QUOTES, 'UTF-8') . '">';
            }
            return htmlspecialchars($m[0], ENT_QUOTES, 'UTF-8');
        }, $text);

        foreach ($images as $index => $html) {
            $text = str_replace("\x00IMGToken" . $index . "\x00", $html, $text);
        }
        return nl2br($text);
    }
}

// 对话列表预览：去掉图片 token，保留表情文字，输出已转义的纯文本。
if (!function_exists('inbox_message_text')) {
    function inbox_message_text($message): string
    {
        $text = strip_tags(htmlspecialchars_decode((string) $message, ENT_QUOTES));
        $text = preg_replace('/\[img\][^\[\]]*\[\/img\]/i', '[图片]', $text);
        $text = preg_replace('/\[([^\[\]]{1,20})\]/u', '[$1]', $text);
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

// Lucide 图标静态字符串（按需使用，不引入完整图标 JS）。
if (!function_exists('dask_icon')) {
    function dask_icon(string $name, string $class = '', int $size = 16): string
    {
        $paths = [
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
            'circle-arrow-up' => '<circle cx="12" cy="12" r="10"/><path d="M12 16V8"/><path d="m8 12 4-4 4 4"/>',
        ];
        if (!isset($paths[$name])) return '';
        $cls = trim('dask-icon ' . $class);
        return '<svg class="' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') . '" width="' . $size
            . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
            . ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . $paths[$name] . '</svg>';
    }
}

// 短信能力开关：模板与业务层共用的只读入口（内置 SmsService，不再读插件表）。
if (!function_exists('sms_enabled')) {
    function sms_enabled(): bool
    {
        return \app\common\service\SmsService::isEnabled();
    }
}

// 在线用户停留页面：去掉 query/fragment 并限制长度，避免写入 users_online.last_url 时截断。
if (!function_exists('safe_last_url')) {
    function safe_last_url(string $fallback = '/'): string
    {
        try {
            $url = (string) request()->url();
        } catch (\Throwable $e) {
            $url = $fallback;
        }
        $url = explode('?', $url, 2)[0];
        $url = explode('#', $url, 2)[0];
        if ($url === '') {
            $url = $fallback;
        }
        return mb_substr($url, 0, 255);
    }
}

// 回跳地址：只接受站内同源地址，杜绝 HTTP_REFERER 造成的开放重定向。
if (!function_exists('safe_return_url')) {
    function safe_return_url(string $fallback = '/'): string
    {
        $referer = (string) request()->server('HTTP_REFERER', '');
        if ($referer === '') {
            return $fallback;
        }
        $parts = parse_url($referer);
        if (!is_array($parts)) {
            return $fallback;
        }
        $host = strtolower((string) request()->server('HTTP_HOST', ''));
        if (isset($parts['host']) && strtolower((string) $parts['host']) !== $host) {
            return $fallback;
        }
        $path = (string) ($parts['path'] ?? '');
        if ($path === '' || str_starts_with($path, '//')) {
            return $fallback;
        }
        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}

/**
 * 读取表结构用于写库前的类型归一化（按请求缓存，失败时返回空数组以保持原行为）。
 */
if (!function_exists('db_table_columns')) {
    function db_table_columns(string $table): array
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }
        try {
            $prefix = (string) config('database.connections.mysql.prefix');
            $fullName = ($prefix !== '' && !str_starts_with($table, $prefix)) ? $prefix . $table : $table;
            $rows = \think\facade\Db::query('SHOW COLUMNS FROM `' . str_replace('`', '', $fullName) . '`');
            $columns = [];
            foreach ($rows as $row) {
                $field = (string) ($row['Field'] ?? '');
                if ($field !== '') {
                    $columns[$field] = $row;
                }
            }
        } catch (\Throwable $e) {
            $columns = [];
        }
        return $cache[$table] = $columns;
    }
}

/** 表单数值安全转换：非数字/空值统一回退默认值，避免 MySQL 1366。 */
if (!function_exists('db_input_int')) {
    function db_input_int($value, int $default = 0): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        if (is_string($value) && preg_match('/^-?\d+/', trim($value), $match) === 1) {
            return (int) $match[0];
        }
        return $default;
    }
}

/** 表单日期安全转换：支持 Y-m-d / Y-m-d H:i:s / 时间戳，空值与非法值回退默认值。 */
if (!function_exists('db_input_timestamp')) {
    function db_input_timestamp($value, int $default = 0): int
    {
        if ($value === null || $value === '' || is_bool($value) || is_array($value)) {
            return $default;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        $time = strtotime((string) $value);
        return $time === false ? $default : $time;
    }
}

/**
 * 按表结构把单个字段值归一化为数据库可接受的类型。
 * 空字符串写入整数/日期列时：可空列写 NULL，否则写列默认值或类型零值。
 */
if (!function_exists('db_input_field_value')) {
    function db_input_field_value(string $table, string $field, $value)
    {
        $columns = db_table_columns($table);
        if (!isset($columns[$field])) {
            return $value;
        }
        $meta = $columns[$field];
        $type = strtolower(trim((string) ($meta['Type'] ?? '')));
        $base = strtolower(trim(explode('(', $type)[0]));
        $nullable = strtoupper((string) ($meta['Null'] ?? '')) === 'YES';
        $default = $meta['Default'] ?? null;
        if (is_string($default) && strtolower($default) === 'null') {
            $default = null;
        }
        $isDateName = str_contains($field, 'time') || str_contains($field, 'birthday') || str_contains($field, 'date');

        if (in_array($base, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'], true)) {
            $intDefault = $default !== null && is_numeric($default) ? (int) $default : 0;
            if ($value === '' || $value === null || is_array($value) || is_bool($value)) {
                if (is_bool($value)) {
                    return (int) $value;
                }
                return ($nullable && $isDateName) ? null : $intDefault;
            }
            if (is_numeric($value)) {
                return (int) $value;
            }
            if ($isDateName) {
                $time = strtotime((string) $value);
                if ($time !== false) {
                    return $time;
                }
            }
            return ($nullable && $isDateName) ? null : $intDefault;
        }

        if (in_array($base, ['decimal', 'float', 'double'], true)) {
            if ($value === '' || $value === null || is_array($value) || !is_numeric($value)) {
                return $nullable ? null : ($default !== null && is_numeric($default) ? $default + 0 : 0);
            }
            return $value + 0;
        }

        if (in_array($base, ['date', 'datetime', 'timestamp'], true)) {
            if ($value === '' || $value === null || is_array($value)) {
                return $nullable ? null : ($default ?: date('Y-m-d H:i:s', 0));
            }
            $time = is_numeric($value) ? (int) $value : strtotime((string) $value);
            if ($time === false) {
                return $nullable ? null : ($default ?: date('Y-m-d H:i:s', 0));
            }
            return $base === 'date' ? date('Y-m-d', $time) : date('Y-m-d H:i:s', $time);
        }

        return $value;
    }
}

/**
 * 批量归一化写库数据：丢弃表中不存在的字段，并按列类型修正空值/字符串。
 * 仅用于写路径；表结构探测失败时保持原数据，避免影响无权限环境。
 */
if (!function_exists('db_input_normalize')) {
    function db_input_normalize(string $table, array $data): array
    {
        $columns = db_table_columns($table);
        if (!$columns) {
            return $data;
        }
        foreach ($data as $field => $value) {
            if (!array_key_exists($field, $columns)) {
                unset($data[$field]);
                continue;
            }
            $data[$field] = db_input_field_value($table, $field, $value);
        }
        return $data;
    }
}

/**
 * 请求级兜底：把常见数字/时间字段的空字符串转为安全默认值。
 * 写库前仍应按表结构归一化；这里用于拦截尚未显式处理的遗留入口。
 */
if (!function_exists('normalize_numeric_post')) {
    function normalize_numeric_post(array $post): array
    {
        $intNames = [
            'uid', 'id', 'pid', 'sort', 'status', 'sex', 'verify', 'recommend',
            'group_id', 'credit_group_id', 'category_id', 'column_id', 'topic_id',
            'question_id', 'answer_id', 'article_id', 'item_id', 'tag_id', 'attach_id',
        ];
        $timeNames = ['birthday', 'forbidden_time', 'create_time', 'update_time', 'expire_time', 'wait_time'];
        foreach ($post as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            if ($key === 'id' && is_string($value) && str_contains($value, ',')) {
                $post[$key] = implode(',', array_map('db_input_int', explode(',', $value)));
                continue;
            }
            $isInt = in_array($key, $intNames, true)
                || str_starts_with((string) $key, 'is_')
                || str_ends_with((string) $key, '_id')
                || str_ends_with((string) $key, '_count')
                || str_ends_with((string) $key, '_num');
            if ($isInt) {
                $post[$key] = db_input_int($value);
                continue;
            }
            if (in_array($key, $timeNames, true) || str_ends_with((string) $key, '_time')) {
                $post[$key] = db_input_timestamp($value);
            }
        }
        return $post;
    }
}
