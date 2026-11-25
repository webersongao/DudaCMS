<?php
namespace app\common\middleware;

use app\common\library\helper\RequestCache;
use think\Request;
class AppInit
{
    public function handle(Request $request, \Closure $next)
    {
        // 清空请求级只读缓存：确保本请求不复用上一次（同一 php-fpm 进程）请求的查询结果
        RequestCache::reset();

        // 框架 initializer 把 error_reporting 重置为 E_ALL，并将所有错误（含
        // E_DEPRECATED）通过 set_error_handler 转成 ErrorException。PHP 8.2 下
        // htmlspecialchars_decode(null) 这类 deprecated 调用会被误判为致命错误，
        // 导致通知、浏览记录等页面打不开；此类调用按空字符串处理，不影响运行。
        // 这里显式排除 deprecated
        // 级别，保留 E_ALL 的其余错误继续被捕获，从根源消除这类系统性误报。
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        // 入口标识与 controller_layer 已在入口文件 / config/route.php 静态确定，
        // 中间件不再运行时改写路由配置；首页固定为 Index/index。

        //定义一天时间戳
        define('ONE_DAY',86400);

        // 统一全站时区，避免模板与服务端时间不一致。
        $timezone = trim((string) get_setting('site_timezone', 'Asia/Shanghai'));
        if ($timezone !== '' && function_exists('date_default_timezone_set')) {
            @date_default_timezone_set($timezone);
        }

        // 设置默认语言：多语言开关与默认语言改为内置配置。
        $defaultLang = cookie('dask_lang');
        $builtinLang = (string) \app\common\library\helper\SiteConfigHelper::feature('default_language', 'zh-cn');
        $multilingual = \app\common\library\helper\SiteConfigHelper::feature('multilingual_enable', true);
        if ($builtinLang !== '' && (!$defaultLang || $defaultLang !== $builtinLang)) {
            if (!$multilingual) {
                cookie('dask_lang', $builtinLang);
            }
        }
        return $next($request);
    }
}