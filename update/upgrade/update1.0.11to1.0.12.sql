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

-- 话题最多选择数量：补充 0-5 的范围说明（校验在 Config 控制器服务端执行）。
UPDATE `dask_config` SET `tips`='话题最多选择数量，范围 0 - 5；0 表示不允许选择话题' WHERE `name`='max_topic_select';
