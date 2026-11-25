<?php

return array (
  'tabs' => 
  array (
  ),
  'publish' => 
  array (
  ),
  'category' => 
  array (
  ),
  'relation' => 
  array (
  ),
  // 系统配置分组（内置常量）。
  // dask_config.group 字段仍存分组 id 字符串，作为这里的 key。
  // 11=第三方登录、12=主题风格、13=编辑器、14=短信配置已提升为【系统管理】下的一级入口，
  // 由 Config::third()/theme()/editor()/sms() 固定渲染，不再作为 config 页的并列 tab。
  'config_group' => 
  array (
    1 => '站点配置',
    3 => '邮箱配置',
    4 => '上传配置',
    5 => '注册访问',
    6 => '成长值规则',
    7 => '功能配置',
    8 => '优化配置',
    9 => '内容配置',
    10 => '其他配置',
  ),

  // ==========================================================================
  // 以下原本是后台可编辑项，改为代码内置常量统一维护：
  // - 站点展示资源固定使用项目内已有文件，避免后台误填导致图片/字体丢失；
  // - 账号策略属于安全基线，不应由后台随意放宽，集中在此便于审计；
  // - 站点描述/关键词统一复用 seo_description/seo_keywords；联系邮箱统一用 contact_email。
  // ==========================================================================
  'site' => [
    // 站点图标：使用项目内置图标；favicon 留空时模板回退到 brand/favicon.ico。
    'logo'         => 'brand/logo-bihu.svg',
    'favicon'      => 'brand/favicon.ico',
    // BUG 反馈接收邮箱留空时回退到 contact_email。
    'bug_email'    => '',
  ],

  // 水印字体：项目内置思源黑体（中英文及 @、©、®、™ 等符号均支持）。
  'watermark' => [
    'font' => 'vendor/captcha/font/SourceHanSansCN-Normal.ttf',
  ],

  // 身份验证门槛：二选一（邮箱或手机号）验证通过后才允许修改活动。
  // 未启用短信时仅邮箱路径可用；白名单内的 action 不拦截，避免用户无法去验证/退出。
  'verification' => [
    'enabled' => true,
    // 允许未验证用户访问的操作（小写 控制器/方法）。
    'allow_unverified' => [
      'account/login',
      'account/register',
      'account/send_valid_mail',
      'account/valid_email_verify',
      'account/modify_email',
      'account/modify_mobile',
      'account/modify_password',
      'account/modify_urltoken',
      'account/save_profile',
      'account/logout',
      'account/welcome_first_login',
      'ajax/sms',
      'ajax/check_mobile',
      'ajax/check_email',
    ],
  ],

  // 功能配置：草稿自动保存与多语言为固定基线，后台不再自定义。
  'feature' => [
    // 默认开启草稿自动保存，间隔 30 秒（毫秒值供前端定时器直接使用）。
    'auto_save_draft'      => true,
    'auto_save_interval'   => 30,        // 秒
    'auto_save_interval_ms' => 30000,    // 毫秒 = 秒 * 1000
    // 默认启用前台多语言，站点默认语言 zh-cn。
    'multilingual_enable'  => true,
    'default_language'     => 'zh-cn',
  ],

  // 账号策略（注册 / 登录 / 找回密码统一读取，后台不再自定义）。
  'account_policy' => [
    'username_min_length'   => 2,
    'username_max_length'   => 15,
    'password_min_length'   => 8,
    'password_max_length'   => 30,
    // 密码必须包含的类型：number / letter / special；留空不限制。
    'password_types'        => ['number', 'letter'],
    'password_max_retries'  => 5,
    'password_lock_minutes' => 10,
    // 记住登录：默认关闭，避免长期 cookie 自动登录；有效期 7 天。
    'remember_login_enable' => false,
    'remember_login_days'   => 7,
    // 单一登录：默认关闭，允许多端同时在线。
    'unique_login'          => false,
  ],
);
