<?php
namespace app\common\middleware;

use Closure;
use think\exception\HttpException;
use think\facade\Session;
use think\Request;
use think\Response;

/**
 * 会话级 CSRF 校验。
 *
 * API 独立入口使用 Bearer Token，不走 Session CSRF；
 * GET/HEAD/OPTIONS 为安全方法，页面通过 csrf_token() 输出同一会话令牌。
 */
class Csrf
{
    public function handle(Request $request, Closure $next): Response
    {
        if (defined('ENTRANCE') && ENTRANCE === 'api') {
            return $next($request);
        }

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $sessionToken = (string) Session::get('__token__', '');
        $token = (string) ($request->header('X-CSRF-TOKEN', '')
            ?: $request->post('__token__', '')
            ?: $request->post('token', ''));

        if ($sessionToken === '' || $token === '' || !hash_equals($sessionToken, $token)) {
            // CSRF 失败属于客户端请求问题，不应被计成 500 服务端错误。
            throw new HttpException(403, 'invalid token');
        }

        return $next($request);
    }
}
