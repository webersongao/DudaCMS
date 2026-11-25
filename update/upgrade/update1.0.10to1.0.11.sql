-- ============================================================================
-- @version    1.0.11
-- @from       1.0.10
-- @summary   站点资源与账号策略内置化，移除对应后台配置项
-- @verify     config:all-builtin=absent
-- @note       幂等，可安全重复执行
-- ============================================================================

-- 站点展示资源与联系方式：logo/favicon 固定用站内资源，描述/关键词复用 seo_*，BUG 邮箱复用 contact_email。
-- 水印字体固定使用内置思源黑体；账号策略集中到 config/dask.php，不再后台开放。
DELETE FROM `dask_config` WHERE `name` IN (
  'site_logo', 'site_description', 'site_keywords', 'site_favicon', 'report_bug_email',
  'watermark_font',
  'password_min_length', 'password_max_length', 'password_type',
  'username_min_length', 'username_max_length',
  'password_max_retries', 'password_lock_minutes',
  'unique_login', 'remember_login_enable', 'remember_login_time'
);
