<?php
namespace app\service\api;

use app\api\v1\ApiException;

class RateLimitService
{
    public static function hit(string $key, int $max, int $windowSeconds): void
    {
        $cacheKey = 'api_rl_' . sha1($key);
        $count = (int) cache($cacheKey);
        if ($count >= $max) {
            throw new ApiException(1006, '请求过于频繁，请稍后再试', 429);
        }
        cache($cacheKey, $count + 1, $windowSeconds);
    }

    public static function byIp(string $action, int $max, int $windowSeconds): void
    {
        self::hit($action . ':' . request()->ip(), $max, $windowSeconds);
    }
}
