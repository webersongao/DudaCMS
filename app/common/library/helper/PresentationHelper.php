<?php
// +----------------------------------------------------------------------
// | DudaCMS 统一展示层：头像、时间、语言、主题、转义与富文本输出。
// +----------------------------------------------------------------------

namespace app\common\library\helper;

/**
 * 全站展示统一入口。
 * 模板禁止直接拼接 URL/头像/时间/富文本；一律经此助手。
 */
class PresentationHelper
{
    public const DEFAULT_AVATAR = '/static/common/image/default-avatar.svg';

    private const HTML_LANG = [
        'zh-cn' => 'zh-CN', 'zh-tw' => 'zh-Hant', 'en-us' => 'en', 'es-es' => 'es',
    ];

    private const TIME_FORMATS = [
        'default'  => 'Y-m-d H:i',
        'datetime' => 'Y-m-d H:i:s',
        'date'     => 'Y-m-d',
        'compact'  => 'n-j H:i',
        'time'     => 'H:i',
        'year'     => 'Y',
    ];

    /** 统一头像地址；$absolute=true 时输出带域名的绝对地址（API 用）。 */
    public static function avatar($avatar, bool $absolute = false): string
    {
        $value = trim((string) $avatar);
        if ($value === '') {
            $value = self::DEFAULT_AVATAR;
        } elseif (!str_starts_with($value, '/') && preg_match('#^[a-z][a-z0-9+.-]*://#i', $value) !== 1) {
            $value = '/' . ltrim($value, '/');
        }
        if (!$absolute) {
            return $value;
        }
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $value) === 1) {
            return $value;
        }
        $domain = rtrim((string) request()->domain(), '/');
        if (preg_match('#^https?://#i', $domain) !== 1) {
            $domain = 'http://localhost';
        }
        return $domain . $value;
    }

    /** 统一时间展示：context 支持 default/datetime/date/compact/time/year，或自定义 date() 格式。 */
    public static function time($timestamp, string $context = 'default'): string
    {
        $timestamp = self::timestamp($timestamp);
        if ($timestamp <= 0) {
            return '';
        }
        $format = self::TIME_FORMATS[$context] ?? $context;
        if ($context === 'default' && get_setting('date_friendly_enable') !== 'normal') {
            $friendly = date_friendly($timestamp);
            return $friendly !== false ? (string) $friendly : date($format, $timestamp);
        }
        return date($format, $timestamp);
    }

    /** 可被 date() 接受的时间戳；空值/非法日期返回 0。 */
    public static function timestamp($value): int
    {
        if ($value === null || $value === '' || is_bool($value) || is_array($value)) {
            return 0;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        $time = strtotime((string) $value);
        return $time === false ? 0 : $time;
    }

    /** 当前语言代码（白名单内）。 */
    public static function lang(): string
    {
        $lang = strtolower(trim((string) (cookie('dask_lang') ?: config('lang.default_lang') ?: 'zh-cn')));
        return array_key_exists($lang, self::HTML_LANG) ? $lang : 'zh-cn';
    }

    /** <html lang> 属性值。 */
    public static function htmlLang(): string
    {
        return self::HTML_LANG[self::lang()] ?? 'zh-CN';
    }

    /** 主题偏好：light/dark/auto，统一从 cookie/localStorage 回退到站点配置。 */
    public static function themeMode(): string
    {
        $mode = strtolower(trim((string) (cookie('dask_theme') ?: get_setting('theme_mode', 'auto'))));
        return in_array($mode, ['light', 'dark', 'auto'], true) ? $mode : 'auto';
    }

    /** 输出 HTML 文本（自动转义）。 */
    public static function escape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    /** 输出 HTML 属性值。 */
    public static function attr($value): string
    {
        return self::escape($value);
    }

    /** 输出 JSON 属性值（自动 HTML 转义）。 */
    public static function jsonAttr($value): string
    {
        return self::escape(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    /** 安全 URL：只允许 http/https/mailto/tel/站内相对路径。 */
    public static function safeUrl($url, string $fallback = '#'): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return $fallback;
        }
        if (preg_match('#^(https?://|mailto:|tel:|/)#i', $url) === 1 && !str_starts_with($url, '//')) {
            return $url;
        }
        return $fallback;
    }

    /** 富文本展示：任何富文本输出都必须经此净化。 */
    public static function richText($html): string
    {
        return HtmlHelper::purify(htmlspecialchars_decode((string) $html));
    }

    /** 文本展示：先净化再转义，防止存储型 XSS。 */
    public static function text($value): string
    {
        return self::escape(strip_tags((string) $value));
    }
}
