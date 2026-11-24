<?php

// +----------------------------------------------------------------------
// | 缓存设置（T1 起统一使用文件缓存；原 memcached/redis 后台配置链从未生效）
// +----------------------------------------------------------------------

return [
    // 默认缓存驱动
    'default' => env('cache.driver', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => config('app.cache')['prefix'],
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            'serialize'  => ['dask_serialize', 'dask_unserialize'],
        ],
        // 生产环境可在 .env 设置 cache.driver=redis 启用 Redis
        'redis' => [
            'type'       => 'Redis',
            'host'       => env('cache.redis_host', '127.0.0.1'),
            'port'       => (int) env('cache.redis_port', 6379),
            'password'   => env('cache.redis_password', ''),
            'select'     => (int) env('cache.redis_select', 0),
            'timeout'    => 0,
            'prefix'     => config('app.cache')['prefix'],
            'expire'     => 0,
            'tag_prefix' => 'tag:',
            'serialize'  => ['dask_serialize', 'dask_unserialize'],
        ],
    ],
];
