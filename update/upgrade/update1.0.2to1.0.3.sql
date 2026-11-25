-- ============================================================================
-- DudaCMS 数据库升级脚本
-- @version   1.0.3
-- @requires  1.0.2
-- @title     第三方登录 Token 字段扩容
-- @summary   将 dask_oauth.access_token / refresh_token 从 varchar(255) 扩为 TEXT，避免 Google 等平台令牌过长导致登录失败
-- @summary   所有 DDL 幂等，可安全重复执行
-- @verify    column:oauth.access_token, column:oauth.refresh_token
-- ============================================================================

-- Google OAuth access_token 常超过 255 字符，varchar(255) 会触发 1406 Data too long。
SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_oauth` MODIFY COLUMN `access_token` TEXT NOT NULL COMMENT ''AccessToken''',
  'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_oauth' AND COLUMN_NAME='access_token' AND DATA_TYPE='text');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- refresh_token 同步扩容，避免部分平台返回较长刷新令牌。
SET @s = (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `dask_oauth` MODIFY COLUMN `refresh_token` TEXT NOT NULL COMMENT ''RefreshToken''',
  'SELECT 1')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dask_oauth' AND COLUMN_NAME='refresh_token' AND DATA_TYPE='text');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
