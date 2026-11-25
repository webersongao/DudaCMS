-- ============================================================================
-- DudaCMS 数据库升级脚本
-- @version   1.0.5
-- @requires  1.0.4
-- @title     配置体系精简与现代化
-- @summary   删除失效配置；补齐被代码读取但缺失的键；修正类型、命名与文案；密钥不再明文回显
-- @summary   所有语句幂等，可安全重复执行
-- @verify    table:config
-- ============================================================================

-- 1. 删除从未生效或从未被读取的配置项
DELETE FROM `dask_config` WHERE `name` IN (
  'cache_type', 'cache_host', 'cache_port', 'cache_password',
  'email_test', 'notify_type',
  'popular_gravity', 'popular_agree_ratio', 'popular_against_ratio',
  'popular_view_ratio', 'popular_comment_ratio', 'popular_quality_init_value'
);

-- 2. 统一命名（仅在新键不存在时迁移，避免覆盖管理员已修改的值）
UPDATE `dask_config` SET `name`='password_max_retries'
WHERE `name`='errors_exceeds_limit_password'
  AND NOT EXISTS (SELECT 1 FROM (SELECT `name` FROM `dask_config`) AS c WHERE c.`name`='password_max_retries');
UPDATE `dask_config` SET `name`='password_lock_minutes'
WHERE `name`='password_error_limit_time'
  AND NOT EXISTS (SELECT 1 FROM (SELECT `name` FROM `dask_config`) AS c WHERE c.`name`='password_lock_minutes');
DELETE FROM `dask_config` WHERE `name` IN ('errors_exceeds_limit_password', 'password_error_limit_time');

-- 3. 数值型配置改为 number 类型，并统一 option 为空数组
UPDATE `dask_config` SET `type`='number', `option`='[]'
WHERE `name` IN ('upload_file_size', 'upload_image_size', 'email_port', 'upload_image_thumb_percent');
UPDATE `dask_config` SET `option`='[]' WHERE `option`='null';

-- 4. 清理重复扩展名与错误文案
UPDATE `dask_config` SET `value`='rar,zip,avi,rmvb,3gp,flv,mp3,mp4,txt,doc,xls,ppt,pdf,docx,xlsx'
WHERE `name`='upload_file_ext' AND `value`='rar,zip,avi,rmvb,3gp,flv,mp3,mp4,txt,doc,xls,ppt,pdf,xls,docx,xlsx,doc';
UPDATE `dask_config` SET `tips`='首页热门话题列表链接'
WHERE `name`='t_mb_index_hot_topic_url' AND `tips`='首页热门用户URL';

-- 5. 补齐代码已读取但种子缺失的配置项（INSERT IGNORE 保证可重复执行）
INSERT IGNORE INTO `dask_config` (`id`,`name`,`group`,`title`,`tips`,`type`,`value`,`option`,`sort`,`settings`,`system`,`source`) VALUES
(512,'site_description','1','站点描述','用于页面 meta description 与侧边栏站点简介','textarea','','[]',4,NULL,1,0),
(513,'site_keywords','1','站点关键词','用于页面 meta keywords 与 API 输出','textarea','','[]',5,NULL,1,0),
(514,'upload_max_size','4','API 上传上限','供前端 API 与编辑器读取，单位：KB','number','20480','[]',1,NULL,1,0),
(515,'t_hm_search_enable','12','发现页搜索框','关闭后隐藏发现页顶部搜索框','radio','Y','{"Y":"显示","N":"隐藏"}',1,NULL,1,0),
(516,'contact_email','1','联系邮箱','用于站点页脚与错误提示中的联系地址','text','','null',6,NULL,1,0),
(517,'site_favicon','1','站点图标','填写完整 URL 或以 / 开头的站内路径，留空使用默认图标','text','','null',7,NULL,1,0);
