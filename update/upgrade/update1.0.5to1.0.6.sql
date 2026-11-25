-- ============================================================================
-- DudaCMS 数据库升级脚本
-- @version   1.0.6
-- @requires  1.0.5
-- @title     字段类型与注释规范化
-- @summary   修正布尔/计数字段的显示宽度，搜索日志 IP 改为 varchar(45) 支持 IPv6，补齐状态注释
-- @summary   MODIFY COLUMN 天然幂等，可安全重复执行
-- @verify    column:search_log.ip, column:links.status, index:users_favorite.uk_uf_uid_item, index:answer_thanks.uk_at_uid_answer
-- ============================================================================

-- 1. 计数字段显示宽度规范化
ALTER TABLE `dask_users` MODIFY COLUMN `views_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '个人主页浏览量';
ALTER TABLE `dask_users` MODIFY COLUMN `question_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题数量';
ALTER TABLE `dask_users` MODIFY COLUMN `answer_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回答数量';

-- 2. 布尔状态字段统一 tinyint(1)
ALTER TABLE `dask_links` MODIFY COLUMN `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1正常，0禁用';
ALTER TABLE `dask_topic` MODIFY COLUMN `lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否锁定';
ALTER TABLE `dask_topic` MODIFY COLUMN `top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐';
ALTER TABLE `dask_browse_records` MODIFY COLUMN `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1正常0删除';
ALTER TABLE `dask_column_recommend_article` MODIFY COLUMN `status` tinyint(1) unsigned DEFAULT '0' COMMENT '审核状态：0待审核，1已审核，2已拒绝';

-- 3. IP 字段支持 IPv6
ALTER TABLE `dask_search_log` MODIFY COLUMN `ip` varchar(45) DEFAULT NULL COMMENT '搜索IP地址（支持 IPv6）';

-- 4. 关系表唯一约束：仅当目标索引不存在且存量数据无重复时添加，绝不删除用户数据。
SET @s = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_users_favorite' AND INDEX_NAME='uk_uf_uid_item') > 0,
  'SELECT 1',
  IF((SELECT COUNT(*) FROM (SELECT uid,item_type,item_id,COUNT(*) c FROM dask_users_favorite GROUP BY uid,item_type,item_id HAVING c>1) d) = 0,
    'ALTER TABLE `dask_users_favorite` ADD UNIQUE KEY `uk_uf_uid_item` (`uid`,`item_type`,`item_id`)',
    'SELECT 1')));
PREPARE dask_stmt FROM @s;
EXECUTE dask_stmt;
DEALLOCATE PREPARE dask_stmt;

SET @s = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_answer_thanks' AND INDEX_NAME='uk_at_uid_answer') > 0,
  'SELECT 1',
  IF((SELECT COUNT(*) FROM (SELECT uid,answer_id,COUNT(*) c FROM dask_answer_thanks GROUP BY uid,answer_id HAVING c>1) d) = 0,
    'ALTER TABLE `dask_answer_thanks` ADD UNIQUE KEY `uk_at_uid_answer` (`uid`,`answer_id`)',
    'SELECT 1')));
PREPARE dask_stmt FROM @s;
EXECUTE dask_stmt;
DEALLOCATE PREPARE dask_stmt;
