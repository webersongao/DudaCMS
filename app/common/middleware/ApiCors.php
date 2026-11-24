<?php
namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;

class ApiCors
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!defined('ENTRANCE') || ENTRANCE !== 'api') {
            return $next($request);
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allow = array_filter(array_map('trim', explode(',', (string) env('api.allow_origins', ''))));
        $originHost = $origin ? (parse_url($origin, PHP_URL_HOST) ?: '') : '';

        //通配符不得与凭证请求共存：只接受白名单中的精确 Origin 或同源 Origin
        $allowed = $origin && (
            in_array($origin, $allow, true)
            || ($originHost && $originHost === $request->host(true))
        );

        if ($allowed) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
            header('Access-Control-Allow-Credentials: true');
        }

        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type,Authorization,AccessToken,UserToken,version,ClientType,Origin,X-Requested-With,X-Request-Id');
        header('Access-Control-Max-Age: 86400');

        if ($request->method() === 'OPTIONS') {
            return response('', 204);
        }

        return $next($request);
    }
}
