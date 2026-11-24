<?php
namespace app\common\middleware;

use app\service\api\RateLimitService;
use Closure;
use think\Request;
use think\Response;

/**
 * API 写操作统一限流：按令牌或 IP 进行每分钟配额控制。
 */
class ApiRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!defined('ENTRANCE') || ENTRANCE !== 'api') {
            return $next($request);
        }
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $auth = trim((string) $request->header('Authorization', ''));
        $identity = $auth !== '' ? 'token:' . sha1($auth) : 'ip:' . $request->ip();
        $key = $identity . ':' . strtolower($request->method()) . ':' . $request->pathinfo();
        RateLimitService::hit($key, 60, 60);

        return $next($request);
    }
}
