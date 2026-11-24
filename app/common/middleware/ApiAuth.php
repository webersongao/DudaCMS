<?php
namespace app\common\middleware;

use app\api\v1\ApiException;
use app\api\v1\TokenService;
use Closure;
use think\Request;
use think\Response;

/**
 * API v1 强制鉴权中间件。
 *
 * 挂在需要登录的路由分组上，在路由层统一拦截未登录请求，
 * 避免逐个控制器方法调用 requireLogin() 时遗漏。
 *
 * 解析逻辑与 app\api\v1\Base::parseToken() 保持一致（同一个 TokenService），
 * 校验失败抛 ApiException(1001, '请先登录', 401)，由 ExceptionHandle 渲染为
 * 与控制器内 requireLogin() 完全相同的 JSON 响应。
 */
class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->header('Authorization', '');
        $uid = 0;
        if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $m)) {
            $payload = TokenService::parse(trim($m[1]));
            if ($payload) {
                $uid = (int) $payload['sub'];
                $status = (int) \think\facade\Db::name('users')->where('uid', $uid)->value('status');
                if ($status !== 1) {
                    $uid = 0;
                }
            }
        }

        if (!$uid) {
            throw new ApiException(1001, '请先登录', 401);
        }

        return $next($request);
    }
}
