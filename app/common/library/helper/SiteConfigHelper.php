<?php

namespace app\common\library\helper;

/**
 * 站点内置配置读取入口。
 *
 * 这批配置不再由后台 dask_config 表维护，统一在 config/dask.php 中集中管理：
 * 站点展示资源、水印字体与账号安全策略。所有读取必须走本类，
 * 避免各处再回退到 get_setting 读到已删除的旧键。
 */
class SiteConfigHelper
{
    /**
     * 读取账号策略项。
     */
    public static function account(string $key, $default = null)
    {
        return config('dask.account_policy.' . $key, $default);
    }

    /** 读取全部账号策略，供模板等批量场景使用。 */
    public static function accountAll(): array
    {
        $policy = config('dask.account_policy', []);
        return is_array($policy) ? $policy : [];
    }

    /** 读取内置功能配置（草稿、多语言等）。 */
    public static function feature(string $key, $default = null)
    {
        return config('dask.feature.' . $key, $default);
    }

    /** 草稿自动保存间隔（毫秒），供前端定时器直接使用。 */
    public static function draftIntervalMs(): int
    {
        return max(1000, (int) self::feature('auto_save_interval_ms', 30000));
    }

    /**
     * 站点资源绝对 URL（基于站内静态目录）。
     */
    public static function assetUrl(string $key): string
    {
        $path = trim((string) config('dask.site.' . $key, ''));
        return $path !== '' ? asset($path, false) : '';
    }

    /**
     * BUG 反馈邮箱：优先内置配置，留空回退站点联系邮箱。
     */
    public static function bugEmail(): string
    {
        $email = trim((string) config('dask.site.bug_email', ''));
        if ($email !== '') {
            return $email;
        }
        return trim((string) get_setting('contact_email', ''));
    }

    /**
     * 水印字体绝对路径。
     */
    public static function watermarkFont(): string
    {
        $path = trim((string) config('dask.watermark.font', ''));
        if ($path === '') {
            return '';
        }
        if ($path[0] === '/' || preg_match('#^[A-Za-z]:[\\\\/]#', $path)) {
            return $path;
        }
        return public_path('static') . $path;
    }
}
