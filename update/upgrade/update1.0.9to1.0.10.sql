-- ============================================================================
-- @version    1.0.10
-- @from       1.0.9
-- @summary   移除失效配置项并对配置分组做功能分类
-- @verify     config:cdn_url=absent, config:watermark_max_lines=absent
-- @note       幂等，可安全重复执行
-- ============================================================================

-- 1. 移除失效配置：资源URL（统一走当前域名）与水印最大行数（水印按图片宽度自适应折行）。
DELETE FROM `dask_config` WHERE `name` IN ('cdn_url', 'watermark_max_lines');
