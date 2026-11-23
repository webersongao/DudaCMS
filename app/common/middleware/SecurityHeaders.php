<?php
// +----------------------------------------------------------------------
// | 全局安全响应头
// +----------------------------------------------------------------------

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // PHP-FPM 可能在响应发出阶段补上 X-Powered-By，显式移除以免暴露运行环境版本。
        header_remove('X-Powered-By');

        $response->header([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options'        => 'SAMEORIGIN',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=(self)',
            'X-XSS-Protection'       => '0',
            // 只声明不限制脚本/样式的指令，避免破坏现有 SSR 内联脚本；先封住高危能力。
            'Content-Security-Policy' => "base-uri 'self'; object-src 'none'; frame-ancestors 'self'; upgrade-insecure-requests",
        ]);

        if ($request->isSsl()) {
            $response->header([
                'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            ]);
        }

        return $response;
    }
}
