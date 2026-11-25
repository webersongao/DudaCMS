-- ============================================================================
-- @version    1.0.12
-- @from       1.0.11
-- @summary   草稿自动保存与多语言内置化，移除失效配置项
-- @verify     config:auto_save_draft=absent, config:enable_multilingual=absent
-- @note       幂等，可安全重复执行
-- ============================================================================

-- 草稿默认开启、间隔 30 秒；默认启用多语言、默认语言 zh-cn，均改为 config/dask.php 内置。
-- enable_column / remove_xss 无任何代码读取点（XSS 过滤始终强制开启），一并删除。
DELETE FROM `dask_config` WHERE `name` IN (
  'auto_save_draft', 'auto_save_draft_time',
  'enable_multilingual', 'default_language',
  'enable_column', 'remove_xss'
);
