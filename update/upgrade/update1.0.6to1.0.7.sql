-- ============================================================================
-- DudaCMS 数据库升级脚本
-- @version   1.0.7
-- @requires  1.0.6
-- @title     配置类型兼容修复
-- @summary   将历史配置 type 映射为表单构造器支持的类型，修复后台配置页 500
-- @summary   所有语句幂等，可安全重复执行
-- @verify    table:config
-- ============================================================================

-- 1. 历史类型映射
UPDATE `dask_config` SET `type`='text' WHERE `type` IN ('string','date','datetime','time','');
UPDATE `dask_config` SET `type`='number' WHERE `type` IN ('int','integer');
UPDATE `dask_config` SET `type`='radio' WHERE `type` IN ('bool','boolean','switch');
UPDATE `dask_config` SET `type`='image' WHERE `type` IN ('file','upload');

-- 2. 兜底：所有未知类型降级为 text，避免 add{Type} 方法不存在导致后台 500
UPDATE `dask_config` SET `type`='text'
WHERE `type` NOT IN ('text','textarea','number','password','radio','checkbox','select','select2','array','image','images','files','editor','code','html','button');

-- 3. 空 option 统一为空 JSON，避免 json_decode(null) 触发异常
UPDATE `dask_config` SET `option`='[]' WHERE `option` IS NULL OR `option`='';
