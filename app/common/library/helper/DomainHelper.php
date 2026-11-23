<?php
// +----------------------------------------------------------------------
// | DudaCMS
// | Copyright (c) 2026 https://ask.btbk.org
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------

namespace app\common\library\helper;

use think\facade\Config;
use think\Request;

/**
 * 域名解耦助手
 *
 * 数据库里只保存相对路径或站外绝对链接；站内绝对链接统一在展示时
 * 按当前请求域名组装，从而做到换域名不改库。
 */
class DomainHelper
{
    /** @var array 规范化后的本站域名（小写、无端口） */
    private static $siteHosts = null;

    /** @var array 站点本地路径前缀 */
    private static $relativePrefixes = null;

    /**
     * 本站域名列表（小写）
     */
    public static function siteHosts(): array
    {
        if (self::$siteHosts === null) {
            $hosts = (array) Config::get('domain.site_hosts', []);
            self::$siteHosts = array_values(array_unique(array_filter(array_map(function ($h) {
                return strtolower(trim(explode(':', (string) $h)[0]));
            }, $hosts))));
        }
        return self::$siteHosts;
    }

    /**
     * 需要相对化的站内路径前缀
     */
    public static function relativePrefixes(): array
    {
        if (self::$relativePrefixes === null) {
            $prefixes = (array) Config::get('domain.relative_prefixes', []);
            self::$relativePrefixes = array_values(array_filter(array_map(function ($p) {
                $p = '/' . trim((string) $p, '/');
                return $p === '/' ? '' : $p . '/';
            }, $prefixes)));
        }
        return self::$relativePrefixes;
    }

    /**
     * 当前请求域名（含协议），如 https://example.com
     */
    public static function currentDomain(): string
    {
        $request = app('request');
        if ($request instanceof Request) {
            return rtrim($request->domain(), '/');
        }
        return '';
    }

    /**
     * 当前请求 host（小写、无端口）
     */
    public static function currentHost(): string
    {
        $request = app('request');
        if ($request instanceof Request) {
            return strtolower(trim(explode(':', $request->host(true))[0]));
        }
        return '';
    }

    /**
     * 判断 URL 是否指向本站
     */
    public static function isLocalUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || $url[0] === '/' || stripos($url, 'data:') === 0) {
            return false;
        }
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }
        return $host === self::currentHost() || in_array($host, self::siteHosts(), true);
    }

    /**
     * 入库前：把指向本站的绝对 URL 转成相对路径。
     *
     * 只处理"站内域名 + 已知本地路径前缀"的 URL，例如：
     *   https://www.example.com/storage/article/x.webp -> /storage/article/x.webp
     *   https://www.example.com/article/detail.html?id=1 -> /article/detail.html?id=1
     * 站外链接（优酷/B站/图床等）原样保留。
     */
    public static function toRelativeHtml(string $html): string
    {
        if ($html === '' || !self::hasLocalAbsoluteUrl($html)) {
            return $html;
        }

        $pattern = '~https?://[^\s"\'<>()]+~i';
        return preg_replace_callback($pattern, function ($m) {
            $url = $m[0];
            if (!self::isLocalUrl($url)) {
                return $url;
            }
            $path = parse_url($url, PHP_URL_PATH) ?: '';
            if ($path === '' || !self::isLocalPath($path)) {
                return $url;
            }
            $query = parse_url($url, PHP_URL_QUERY);
            return $path . ($query ? '?' . $query : '');
        }, $html) ?? $html;
    }

    /**
     * 展示时：把本站域名的绝对 URL 重写为当前访问域名，
     * 同时把库的相对路径（/storage/...）补成当前域名的完整 URL。
     *
     * 站外链接保持不动；已使用当前域名的链接也不动。
     */
    public static function rewriteHtml(string $html, string $domain = ''): string
    {
        if ($html === '') {
            return $html;
        }
        $domain = $domain ?: self::currentDomain();
        if ($domain === '') {
            return $html;
        }

        // 1) 本站域名 -> 当前域名（仅站内域名，站外资源不动）
        $hosts = self::siteHosts();
        if ($hosts) {
            $html = preg_replace_callback('~(https?:)//([^\s"\'<>()]+)~i', function ($m) use ($hosts, $domain) {
                $host = strtolower(trim(explode('/', $m[2])[0]));
                $host = trim(explode(':', $host)[0]);
                if (!in_array($host, $hosts, true)) {
                    return $m[0];
                }
                // 取 path+query，避免端口/host 混淆
                $path = parse_url($m[0], PHP_URL_PATH) ?: '';
                $query = parse_url($m[0], PHP_URL_QUERY);
                $fragment = parse_url($m[0], PHP_URL_FRAGMENT);
                return $domain . $path . ($query ? '?' . $query : '') . ($fragment ? '#' . $fragment : '');
            }, $html) ?? $html;
        }

        // 2) 正文里的站内相对路径（src="/storage/..." / href="/article/..."）补当前域名。
        //    仅处理 HTML 属性中的路径，避免误伤普通文本。
        $prefixRegex = self::localPathRegex();
        if ($prefixRegex !== '') {
            $html = preg_replace_callback(
                '~\b(src|href)=("|\')(/' . $prefixRegex . '[^"\']*)\2~i',
                function ($m) use ($domain) {
                    return $m[1] . '=' . $m[2] . $domain . $m[3] . $m[2];
                },
                $html
            ) ?? $html;
        }

        return $html;
    }

    /**
     * 判断路径是否属于本站本地资源前缀
     */
    public static function isLocalPath(string $path): bool
    {
        $path = '/' . ltrim($path, '/');
        foreach (self::relativePrefixes() as $prefix) {
            if ($prefix !== '' && strpos($path, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 快速判断 HTML 里是否含有本站绝对 URL（用于跳过无谓的正则）
     */
    private static function hasLocalAbsoluteUrl(string $html): bool
    {
        if (stripos($html, 'http://') === false && stripos($html, 'https://') === false) {
            return false;
        }
        foreach (self::siteHosts() as $host) {
            if (stripos($html, $host) !== false) {
                return true;
            }
        }
        return stripos($html, self::currentHost()) !== false;
    }

    /**
     * 供属性重写使用的路径前缀正则片段
     */
    private static function localPathRegex(): string
    {
        $parts = array_map(function ($p) {
            return preg_quote(trim($p, '/'), '~');
        }, self::relativePrefixes());
        return $parts ? '(?:' . implode('|', $parts) . ')' : '';
    }
}
