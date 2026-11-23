<?php
// 全局中间件定义文件
return [
    \app\common\middleware\SecurityHeaders::class,
    // 全局请求缓存
     //\think\middleware\CheckRequestCache::class,
    // 多语言加载
     \think\middleware\LoadLangPack::class,
    // Session初始化
     \think\middleware\SessionInit::class,
    // CSRF 表单令牌（API 入口跳过）
    \app\common\middleware\Csrf::class,
    //多入口服务
    \app\common\middleware\AppInit::class,
    \app\common\middleware\ApiCors::class,
    // 域名处理：展示时把站内域名/相对路径按当前域名动态组装
    \app\common\middleware\DomainRewrite::class,
];
