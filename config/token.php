<?php
return [
    // 驱动方式
    'type'     => 'Mysql',
    // 摘要密钥：来自安装期生成的 app.token.key，不入库
    'key'      => config('app.token')['key'],
    // 缓存有效期 0表示永久缓存
    'expire'   => 1400000,
];
