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
  // 12=主题风格、13=编辑器、14=短信配置已提升为【系统管理】下的一级入口，
  // 由 Config::theme()/editor()/sms() 固定渲染，不再作为 config 页的并列 tab。
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
    11 => '第三方登录',
  ),
);
