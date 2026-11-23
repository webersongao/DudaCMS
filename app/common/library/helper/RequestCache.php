<?php
// +----------------------------------------------------------------------
// | DudaCMS
// +----------------------------------------------------------------------
// | 请求级只读缓存：在同一请求内复用重复的等值查询结果（如同一问题被
// | 控制器、热度计算、侧栏各读一次）。与 PHP `static` 的关键区别是它在每次
// | 请求开始时由全局中间件 AppInit 调用 reset() 清空，绝不跨请求驻留，
// | 因此不会读到上一次请求的脏数据。
// +----------------------------------------------------------------------
namespace app\common\library\helper;

class RequestCache
{
    /** @var array<string,mixed> */
    protected static array $store = [];

    /**
     * 命中直接返回缓存值；未命中执行 $producer 并缓存其结果。
     * 返回值是数组副本（PHP 按值返回），调用方对结果的修改不会污染缓存。
     */
    public static function remember(string $key, callable $producer)
    {
        if (array_key_exists($key, self::$store)) {
            return self::$store[$key];
        }
        self::$store[$key] = $producer();
        return self::$store[$key];
    }

    public static function forget(string $key): void
    {
        unset(self::$store[$key]);
    }

    /** 按前缀失效，用于内容写入后清掉同一行的不同字段组合缓存 */
    public static function forgetPrefix(string $prefix): void
    {
        $len = strlen($prefix);
        foreach (array_keys(self::$store) as $key) {
            if (strncmp($key, $prefix, $len) === 0) {
                unset(self::$store[$key]);
            }
        }
    }

    public static function reset(): void
    {
        self::$store = [];
    }
}
