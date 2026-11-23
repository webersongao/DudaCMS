<?php
// +----------------------------------------------------------------------
// | 多语言设置
// +----------------------------------------------------------------------

return [
    // 默认语言
    'default_lang'    => 'zh-cn',
    // 允许的语言列表
    'allow_lang_list' => [
        'zh-cn',
        'zh-tw',
        'en-us',
        'es-es'
    ],
    // 多语言自动侦测变量名
    'detect_var'      => 'lang',
    // 是否使用Cookie记录
    'use_cookie'      => true,
    // 多语言cookie变量
    'cookie_var'      => 'dask_lang',
    // 多语言header变量
    'header_var'      => 'dask_lang',
    // 扩展语言包
    'extend_list'     => [],
    // Accept-Language转义为对应语言包名称
    'accept_language' => [
        'zh-hans-cn' => 'zh-cn',
        'zh-cn'      => 'zh-cn',
        'zh-hant'    => 'zh-tw',
        'zh-tw'      => 'zh-tw',
        'zh-hk'      => 'zh-tw',
        'zh'         => 'zh-cn',
        'en-us'      => 'en-us',
        'en'         => 'en-us',
        'es-es'      => 'es-es',
        'es'         => 'es-es',
    ],
    // 语言切换器展示名（用各自文字书写，不做翻译）
    'lang_labels'     => [
        'zh-cn' => '简体中文',
        'zh-tw' => '繁體中文',
        'en-us' => 'English',
        'es-es' => 'Español',
    ],
    // 是否支持语言分组
    'allow_group'     => true,
];
