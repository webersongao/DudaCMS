<?php
// +----------------------------------------------------------------------
// | DudaCMS
// | Copyright (c) 2026 https://ask.btbk.org
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------

namespace app\common\library\helper;

/**
 * 附件物理路径解析助手
 *
 * attach.path 使用相对路径（storage/...），不绑定服务器目录。
 * 本助手统一解析各类输入（相对路径、绝对路径、URL）为当前服务器上的真实文件。
 */
class AttachPathHelper
{
    /**
     * 把 attach.path 解析为服务器上的绝对文件路径。
     * 支持三种路径格式：
     *   1) 相对路径：storage/avatar/x.png 或 /storage/avatar/x.png
     *   2) 绝对 URL：https://old.domain/storage/avatar/x.png
     *   3) 旧绝对路径：/www/wwwroot/xxx/public/storage/avatar/x.png
     */
    public static function resolve(string $path): string
    {
        $path = trim(htmlspecialchars_decode($path));
        if ($path === '') {
            return '';
        }
        $path = str_replace('\\/', '/', $path);
        $path = str_replace('\\', '/', $path);

        $relative = self::relative($path);
        if ($relative === '') {
            return '';
        }

        return rtrim(public_path(), '/\\') . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    /**
     * 归一化为以 storage/ 开头的相对路径；非本站 storage 资源返回空串。
     */
    public static function relative(string $path): string
    {
        $path = trim($path);
        // 去掉协议+域名
        $path = preg_replace('#^https?://[^/]+#i', '', $path) ?: $path;
        // 去掉 query / fragment
        $path = preg_replace('~[?#].*$~', '', $path) ?: $path;
        $path = ltrim($path, '/');
        if ($path === '') {
            return '';
        }

        // 旧绝对路径：.../public/storage/xxx
        if (preg_match('#(?:^|/)public/storage/(.+)$#i', $path, $m) === 1) {
            $path = 'storage/' . $m[1];
        } elseif (stripos($path, 'storage/') !== 0) {
            // 处理以 uploads/ 开头的路径写法
            if (stripos($path, 'uploads/') === 0) {
                $path = 'storage/' . substr($path, strlen('uploads/'));
            } else {
                return '';
            }
        }

        // 归一化 . / .. 与多余分隔符，避免越出 storage 根目录
        $parts = [];
        foreach (explode('/', $path) as $seg) {
            if ($seg === '' || $seg === '.') {
                continue;
            }
            if ($seg === '..') {
                array_pop($parts);
                continue;
            }
            $parts[] = $seg;
        }
        if (!$parts || strtolower($parts[0]) !== 'storage') {
            return '';
        }
        return implode('/', $parts);
    }

    /**
     * 把物理绝对路径转成可入库的相对路径；不在 storage 目录内则返回空串。
     */
    public static function toRelative(string $absolute): string
    {
        return self::relative($absolute);
    }
}
