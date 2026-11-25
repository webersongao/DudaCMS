-- ============================================================================
-- DudaCMS 数据库升级脚本
-- @version   1.0.4
-- @requires  1.0.3
-- @title     第三方登录规范统一与数据库结构审计优化
-- @summary   统一第三方回调文案为不带 .html；补充审计索引、扩容字段、修正注释与密钥输入类型
-- @summary   所有 DDL 幂等，可安全重复执行
-- @verify    index:users_token.idx_ut_uid_expire, index:attach.idx_attach_item, column:users_online.last_url
-- ============================================================================

-- 1. 第三方登录配置文案：统一为不带 .html 的绝对地址格式
UPDATE `dask_config` SET `tips`='勾选后前台登录页显示对应入口；回调地址由系统按当前域名自动组装为 https://当前域名/ThirdAuth/callback?platform=平台' WHERE `name`='third_enable';
UPDATE `dask_config` SET `tips`='回调地址：https://当前域名/ThirdAuth/callback?platform=qq（按实际域名补全）' WHERE `name`='third_qq_scope';
UPDATE `dask_config` SET `tips`='回调地址：https://当前域名/ThirdAuth/callback?platform=wechat（按实际域名补全）' WHERE `name`='third_wechat_scope';
UPDATE `dask_config` SET `tips`='回调地址：https://当前域名/ThirdAuth/callback?platform=weibo（按实际域名补全）' WHERE `name`='third_weibo_scope';
UPDATE `dask_config` SET `tips`='回调地址：https://当前域名/ThirdAuth/callback?platform=github（按实际域名补全）' WHERE `name`='third_github_scope';
UPDATE `dask_config` SET `tips`='回调地址：https://当前域名/ThirdAuth/callback?platform=google（按实际域名补全）' WHERE `name`='third_google_scope';
UPDATE `dask_config` SET `tips`='回调地址：https://当前域名/ThirdAuth/callback?platform=twitter（按实际域名补全）' WHERE `name`='third_twitter_scope';

-- 2. 密钥类配置统一使用 password 输入框，避免明文展示
UPDATE `dask_config` SET `type`='password' WHERE `name` IN ('search_engine_password','third_qq_app_secret','third_wechat_app_secret','third_weibo_app_secret','third_github_app_secret','third_google_app_secret','third_twitter_app_secret','sms_tencent_secret_id') AND `type`<>'password';

-- 3. 字段类型与长度优化（幂等）
SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_attach` MODIFY COLUMN `ext` varchar(20) DEFAULT NULL COMMENT ''后缀''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_attach' AND COLUMN_NAME='ext' AND DATA_TYPE='varchar' AND CHARACTER_MAXIMUM_LENGTH=20);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_attach` MODIFY COLUMN `size` bigint unsigned DEFAULT 0 COMMENT ''大小（字节）''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_attach' AND COLUMN_NAME='size' AND DATA_TYPE='bigint' AND LOCATE('unsigned', COLUMN_TYPE)>0);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_attach` MODIFY COLUMN `sort` int unsigned NOT NULL DEFAULT 50 COMMENT ''排序值''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_attach' AND COLUMN_NAME='sort' AND DATA_TYPE='int' AND LOCATE('unsigned', COLUMN_TYPE)>0);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_links` MODIFY COLUMN `logo` varchar(255) NOT NULL DEFAULT '''' COMMENT ''网站logo''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_links' AND COLUMN_NAME='logo' AND DATA_TYPE='varchar' AND CHARACTER_MAXIMUM_LENGTH=255);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_users_online` MODIFY COLUMN `last_url` varchar(512) DEFAULT NULL COMMENT ''停留页面（不含 query）''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_users_online' AND COLUMN_NAME='last_url' AND DATA_TYPE='varchar' AND CHARACTER_MAXIMUM_LENGTH=512);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_oauth` MODIFY COLUMN `open_username` varchar(100) NOT NULL DEFAULT '''' COMMENT ''第三方会员昵称''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_oauth' AND COLUMN_NAME='open_username' AND DATA_TYPE='varchar' AND CHARACTER_MAXIMUM_LENGTH=100);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_sms_log` MODIFY COLUMN `mobile` varchar(20) NOT NULL COMMENT ''手机号''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_sms_log' AND COLUMN_NAME='mobile' AND DATA_TYPE='varchar' AND CHARACTER_MAXIMUM_LENGTH=20);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_sms_log` MODIFY COLUMN `ip` varchar(45) NOT NULL COMMENT ''ip''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_sms_log' AND COLUMN_NAME='ip' AND DATA_TYPE='varchar' AND CHARACTER_MAXIMUM_LENGTH=45);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. 默认值与注释修正（幂等）
SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_users_verify` MODIFY COLUMN `reason` varchar(255) NOT NULL DEFAULT '''' COMMENT ''审核理由''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_users_verify' AND COLUMN_NAME='reason' AND COLUMN_DEFAULT IS NOT NULL);
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_users_verify_type` MODIFY COLUMN `remark` text NULL COMMENT ''备注''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_users_verify_type' AND COLUMN_NAME='remark' AND IS_NULLABLE='YES');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)>0,
  'ALTER TABLE `dask_credit_group` MODIFY COLUMN `remark` text NULL COMMENT ''备注''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_credit_group' AND COLUMN_NAME='remark' AND IS_NULLABLE='NO');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)>0,
  'ALTER TABLE `dask_credit_group` MODIFY COLUMN `system` int(10) unsigned NULL DEFAULT 0 COMMENT ''是否系统内置：0否，1是''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_credit_group' AND COLUMN_NAME='system' AND COLUMN_COMMENT='修改时间');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)>0,
  'ALTER TABLE `dask_approval` MODIFY COLUMN `status` int(11) NOT NULL DEFAULT 0 COMMENT ''审核状态：0待审核，1已审核，2已拒绝''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_approval' AND COLUMN_NAME='status' AND COLUMN_COMMENT NOT LIKE '%1已审核%');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*)>0,
  'ALTER TABLE `dask_column` MODIFY COLUMN `verify` tinyint(1) DEFAULT 0 COMMENT ''审核状态：0审核中，1已通过''', 'SELECT 1')
  FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_column' AND COLUMN_NAME='verify' AND COLUMN_COMMENT LIKE '%-1%');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5. 审计补充索引（幂等）
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_users_token` ADD INDEX `idx_ut_uid_expire` (`uid`,`expire_time`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_users_token' AND INDEX_NAME='idx_ut_uid_expire'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_attach` ADD INDEX `idx_attach_item` (`item_type`,`item_id`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_attach' AND INDEX_NAME='idx_attach_item'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_attach` ADD INDEX `idx_attach_access_key` (`access_key`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_attach' AND INDEX_NAME='idx_attach_access_key'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_report` ADD INDEX `idx_report_item` (`item_id`,`item_type`,`uid`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_report' AND INDEX_NAME='idx_report_item'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_report` ADD INDEX `idx_report_uid_item` (`uid`,`item_type`,`item_id`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_report' AND INDEX_NAME='idx_report_uid_item'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_column_recommend_article` ADD INDEX `idx_cra_column_status` (`column_id`,`status`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_column_recommend_article' AND INDEX_NAME='idx_cra_column_status'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_users_favorite` ADD INDEX `idx_fav_tag` (`tag_id`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_users_favorite' AND INDEX_NAME='idx_fav_tag'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_answer_thanks` ADD INDEX `idx_at_answer_uid` (`answer_id`,`uid`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_answer_thanks' AND INDEX_NAME='idx_at_answer_uid'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_uninterested` ADD INDEX `idx_un_item` (`item_id`,`item_type`,`uid`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_uninterested' AND INDEX_NAME='idx_un_item'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_browse_records` ADD INDEX `idx_br_uid_item` (`uid`,`item_type`,`item_id`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_browse_records' AND INDEX_NAME='idx_br_uid_item'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s = (SELECT IF(COUNT(*)=0,'ALTER TABLE `dask_browse_records` ADD INDEX `idx_br_uid_status_time` (`uid`,`status`,`create_time`)','SELECT 1') FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_browse_records' AND INDEX_NAME='idx_br_uid_status_time'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
