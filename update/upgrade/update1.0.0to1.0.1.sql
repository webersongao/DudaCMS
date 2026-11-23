-- ============================================================================
-- DudaCMS 数据库升级脚本
-- @version   1.0.1
-- @requires  1.0.0
-- @title     系统运维聚合与第三方登录菜单升级
-- @summary   统一版本源；系统运维菜单合并；第三方登录提升一级入口；权限与图标修复
-- @summary   新增在线升级记录表；全部 SQL 幂等，可安全重复执行
-- @verify    table:upgrade_log, column:admin_auth.icon, column:config.tips
-- ============================================================================
--
--
-- 【幂等规范·必须遵守】所有升级 SQL 必须可安全重复执行：
--   1) 新增字段：用 PREPARE + information_schema 判断列不存在再 ALTER，例如给用户表加 col_x：
--        SET @s = (SELECT IF(COUNT(*)=0,
--          'ALTER TABLE `dask_users` ADD COLUMN `col_x` varchar(50) NOT NULL DEFAULT ""',
--          'SELECT 1') FROM information_schema.COLUMNS
--          WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_users' AND COLUMN_NAME='col_x');
--        PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
--      注：若线上账号无 information_schema 读权限，可省略判断直接 ALTER——
--          升级框架以版本号保证同一脚本仅执行一次（执行后数据库版本推进，待升级项消失），
--          SQL 幂等作为手动重复执行时的第二重保护。
--   2) 新增数据：一律 INSERT IGNORE ...（依赖主键/唯一键）
--   3) 删除数据：DELETE ... WHERE 明确条件
--   4) 更新数据：UPDATE ... WHERE 明确条件，值为固定结果
--   5) 建表：CREATE TABLE IF NOT EXISTS
--   6) 数组型字段追加：CONCAT + FIND_IN_SET(x)=0 防重复
--

-- 1. 清理已废弃的配置项
DELETE FROM `dask_config` WHERE `name` IN (
  'admin_menu_type', 'db_version', 'sub_dir', 'system_version',
  'site_logo_text', 'site_slogan', 'site_tagline', 'third_bind_account'
);

-- 2. 题图副标题提示文案更新
UPDATE `dask_config` SET `tips` = '留空时使用默认文案' WHERE `name` = 't_hm_search_min_text';

-- 3. 新增「系统运维」与「第三方登录」一级菜单
INSERT IGNORE INTO `dask_admin_auth`
  (`id`, `pid`, `name`, `title`, `type`, `status`, `condition`, `sort`, `auth_open`, `icon`, `create_time`, `update_time`, `param`, `group`)
VALUES
  (389, 4, 'extend.System/index', '系统运维', 1, 1, '', 30, 1, 'fa fa-tools', 0, 0, '', 'system'),
  (415, 2, 'admin.Config/third', '第三方登录', 1, 1, '', 3, 1, 'fab fa-weixin', 0, 0, '', NULL);

-- 4. 旧的三项功能降级为系统运维下的无菜单权限项
UPDATE `dask_admin_auth` SET `pid` = 389, `status` = 0 WHERE `id` IN (77, 81, 118);

-- 5. 修复「操作-下载」权限规则大小写（Backend 权限校验为小写 snake_case）
UPDATE `dask_admin_auth` SET `name` = 'extend.Database/downfile' WHERE `id` = 83;

-- 6. 修复短信配置图标（fa-comment-sms 在 FontAwesome 6 已移除）
UPDATE `dask_admin_auth` SET `icon` = 'fas fa-sms' WHERE `id` = 412;

-- 7. 前台管理员权限规则补齐新增菜单 ID（389 系统运维）
UPDATE `dask_admin_group` SET `rules` = CONCAT(`rules`, ',389')
  WHERE `id` = 2 AND FIND_IN_SET('389', `rules`) = 0;

-- 8. 建立在线升级记录表（升级助手会在执行前后写入记录；此处建表供后续版本追踪）
CREATE TABLE IF NOT EXISTS `dask_upgrade_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(20) NOT NULL DEFAULT '' COMMENT '升级到的版本',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=进行中 1=成功 2=失败',
  `sql_file` varchar(100) NOT NULL DEFAULT '' COMMENT 'SQL文件',
  `sql_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行SQL条数',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '说明',
  `backup_path` varchar(255) NOT NULL DEFAULT '' COMMENT '升级前备份路径',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据库升级记录';
