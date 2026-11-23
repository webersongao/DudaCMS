<?php
namespace app\service;

use think\facade\Db;

/**
 * 本地上传文件生命周期管理。
 *
 * 安全边界：只允许删除 public/storage 下的上传文件，/static/ 内置资源永不入队。
 * 两阶段删除：
 * 1) mark* 在数据库事务内删除 attach 记录并登记待删文件；
 * 2) flush() 在事务提交后物理删除文件，clearPending() 在回滚时丢弃队列。
 */
class StorageCleanupService
{
    /** @var array<int, string> */
    private static array $pendingFiles = [];

    /**
     * 保存了上传 URL 的字段表。用于后台通用编辑/删除和游离回收时的引用判断。
     * 注意：这里只列真实可能落 storage/ 文件的字段。
     */
    public const UPLOAD_FIELDS = [
        'users'                  => ['avatar'],
        'question'               => ['detail'],
        'answer'                 => ['content'],
        'article'                => ['message', 'cover'],
        'column'                 => ['cover'],
        'topic'                  => ['pic'],
        'draft'                  => ['data'],
        'feature'                => ['image'],
        'category'               => ['icon'],
        'links'                  => ['logo'],
        'credit_group'           => ['group_icon'],
        'help_chapter'           => ['image'],
    ];

    public static function markAttach(int $attachId, bool $force = false): bool
    {
        $attachId = (int) $attachId;
        if ($attachId <= 0) {
            return false;
        }
        $attach = Db::name('attach')->where('id', $attachId)->find();
        if (!$attach) {
            return false;
        }
        if (!$force && self::isReferenced($attachId, $attach)) {
            return Db::name('attach')->where('id', $attachId)->delete() > 0;
        }
        Db::name('attach')->where('id', $attachId)->delete();
        self::queueAttach($attach);
        return true;
    }

    /**
     * @param array<int, mixed> $itemIds
     */
    public static function markByItems(string $itemType, array $itemIds): int
    {
        $itemIds = array_values(array_filter(array_map('intval', $itemIds)));
        if ($itemType === '' || !$itemIds) {
            return 0;
        }
        $rows = Db::name('attach')->where('item_type', $itemType)->whereIn('item_id', $itemIds)->select()->toArray();
        $deleted = 0;
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            if (!self::isReferenced($id, $row)) {
                self::queueAttach($row);
            }
            $deleted += Db::name('attach')->where('id', $id)->delete();
        }
        return $deleted;
    }

    /**
     * 按 URL 删除附件记录并登记文件删除，适用于封面替换、头像替换、编辑器图片删除。
     * 非 storage/ URL（如 /static/common/image/default-avatar.svg）一律忽略。
     */
    public static function markByUrl(string $url): bool
    {
        $relative = self::relativeFromAny($url);
        if ($relative === '') {
            return false;
        }

        $stem = self::urlStem($relative);
        $rows = Db::name('attach')
            ->where(function ($q) use ($stem) {
                $q->whereLike('url', '%' . $stem . '%')
                    ->whereOr('thumb', '%' . $stem . '%');
            })
            ->select()
            ->toArray();

        $deleted = false;
        $matched = false;
        foreach ($rows as $row) {
            $candidates = array_filter([
                self::relativeFromAny((string) ($row['url'] ?? '')),
                self::relativeFromAny((string) ($row['thumb'] ?? '')),
            ]);
            if (!in_array($relative, $candidates, true)) {
                continue;
            }
            $matched = true;
            $deleted = self::markAttach((int) ($row['id'] ?? 0)) || $deleted;
        }

        // 处理未写入 attach 表的头像/封面。
        if (!$matched) {
            self::queueFile($relative);
        }
        return $deleted || !$matched;
    }

    /**
     * @param array<int, mixed> $urls
     */
    public static function markByUrls(array $urls): int
    {
        $count = 0;
        foreach (self::extractUrls($urls) as $url) {
            $count += self::markByUrl($url) ? 1 : 0;
        }
        return $count;
    }

    public static function flush(): int
    {
        $files = array_values(array_unique(self::$pendingFiles));
        self::$pendingFiles = [];
        $count = 0;
        foreach ($files as $file) {
            if (self::unlinkFile($file)) {
                $count++;
            }
        }
        return $count;
    }

    public static function clearPending(): void
    {
        self::$pendingFiles = [];
    }

    public static function deleteAttachNow(int $attachId, bool $force = false): bool
    {
        $result = self::markAttach($attachId, $force);
        self::flush();
        return $result;
    }

    public static function deleteByItemsNow(string $itemType, array $itemIds): int
    {
        $count = self::markByItems($itemType, $itemIds);
        self::flush();
        return $count;
    }

    public static function deleteByUrlNow(string $url): bool
    {
        $result = self::markByUrl($url);
        self::flush();
        return $result;
    }

    /**
     * @param array<int, mixed> $urls
     */
    public static function deleteByUrlsNow(array $urls): int
    {
        $count = self::markByUrls($urls);
        self::flush();
        return $count;
    }

    /**
     * 字段替换清理：删除旧值中已不在新值里的 storage 文件。
     * 用于后台通用编辑，调用方应传成功写入后的新数据。
     *
     * @param array<string, mixed> $newData
     * @param array<string, mixed>|null $oldData 更新前快照；不传时会读取当前库（仅适合更新前调用）
     */
    public static function cleanupUpdatedRecord(string $table, array $newData, $id = null, string $pk = 'id', ?array $oldData = null): int
    {
        $fields = self::UPLOAD_FIELDS[$table] ?? [];
        if (!$fields) {
            return 0;
        }
        $id = $id !== null ? $id : ($newData[$pk] ?? null);
        if ($id === null || $id === '') {
            return 0;
        }
        $oldRow = $oldData ?? Db::name($table)->where($pk, $id)->find();
        if (!$oldRow) {
            return 0;
        }

        $oldUrls = [];
        $newUrls = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $newData)) {
                $oldUrls = array_merge($oldUrls, self::extractUrls($oldRow[$field] ?? ''));
                $newUrls = array_merge($newUrls, self::extractUrls($newData[$field]));
            }
        }
        $removed = array_values(array_diff(array_unique($oldUrls), array_unique($newUrls)));
        return self::deleteByUrlsNow($removed);
    }

    /**
     * 记录物理删除时的图片清理。
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public static function cleanupDeletedRows(string $table, array $rows): int
    {
        $fields = self::UPLOAD_FIELDS[$table] ?? [];
        if (!$fields || !$rows) {
            return 0;
        }
        $urls = [];
        foreach ($rows as $row) {
            foreach ($fields as $field) {
                $urls = array_merge($urls, self::extractUrls($row[$field] ?? ''));
            }
        }
        return self::deleteByUrlsNow($urls);
    }

    /**
     * @param array<int, mixed> $ids
     */
    public static function cleanupDeletedRecords(string $table, array $ids, string $pk = 'id'): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids || !isset(self::UPLOAD_FIELDS[$table])) {
            return 0;
        }
        $rows = Db::name($table)->whereIn($pk, $ids)->select()->toArray();
        return self::cleanupDeletedRows($table, $rows ?: []);
    }

    /**
     * 游离附件与物理文件回收。
     *
     * - attach.item_id=0 且文件已超过 $olderThanSeconds 未使用：删除记录和文件；
     * - public/storage 下无任何 attach 记录、且未被内容引用的旧文件：物理删除。
     *
     * @param string $directory 只清理 storage 下的指定子目录，留空表示全部
     * @return array{attach_deleted:int, files_deleted:int, scanned_files:int}
     */
    public static function cleanupOrphans(int $olderThanSeconds = 86400, bool $dryRun = false, string $directory = ''): array
    {
        $olderThanSeconds = max(3600, $olderThanSeconds);
        $directory = trim(str_replace('\\', '/', $directory), '/');
        if ($directory !== '' && preg_match('#(^|/)\.\.?(/|$)#', $directory) === 1) {
            $directory = '';
        }
        $prefix = $directory !== '' ? 'storage/' . $directory . '/' : 'storage/';
        $cutoff = time() - $olderThanSeconds;
        $result = [
            'attach_deleted' => 0,
            'files_deleted'  => 0,
            'scanned_files'  => 0,
        ];

        // 1. 未绑定附件记录。
        $rows = Db::name('attach')->where('item_id', 0)->select()->toArray();
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $inScope = false;
            foreach (['path', 'url', 'thumb'] as $field) {
                if (strpos(self::relativeFromAny((string) ($row[$field] ?? '')), $prefix) === 0) {
                    $inScope = true;
                    break;
                }
            }
            if (!$inScope || self::isReferenced($id, $row)) {
                continue;
            }
            $file = '';
            foreach (['path', 'url', 'thumb'] as $field) {
                $candidate = self::resolveStorageFile((string) ($row[$field] ?? ''));
                if ($candidate !== '' && is_file($candidate)) {
                    $file = $candidate;
                    break;
                }
            }
            $mtime = $file !== '' ? (int) filemtime($file) : 0;
            // create_time 为 NULL 的记录以文件修改时间为准；文件已丢失则直接清记录。
            $createTime = (int) ($row['create_time'] ?? 0);
            $agePoint = $createTime > 0 ? $createTime : $mtime;
            if ($agePoint > $cutoff) {
                continue;
            }
            if (!$dryRun) {
                Db::name('attach')->where('id', $id)->delete();
                $result['attach_deleted']++;
                if ($file !== '' && self::unlinkFile($file)) {
                    $result['files_deleted']++;
                }
            }
        }

        // 2. 物理文件反查：建立所有 attach 引用的相对路径集合（含缩略图配对）。
        $known = [];
        $allAttach = Db::name('attach')->field('path,url,thumb')->select()->toArray();
        foreach ($allAttach as $row) {
            foreach (['path', 'url', 'thumb'] as $field) {
                $relative = self::relativeFromAny((string) ($row[$field] ?? ''));
                if ($relative === '') {
                    continue;
                }
                $known[$relative] = true;
                $paired = self::pairedRelative($relative);
                if ($paired !== '') {
                    $known[$paired] = true;
                }
            }
        }

        $root = rtrim(public_path(), '/\\') . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, rtrim($prefix, '/'));
        if (!is_dir($root)) {
            return $result;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        $publicRoot = rtrim(public_path(), '/\\');
        foreach ($iterator as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            if (!$fileInfo->isFile()) {
                continue;
            }
            $filePath = $fileInfo->getPathname();
            $relative = ltrim(str_replace('\\', '/', substr($filePath, strlen($publicRoot))), '/');
            $relative = self::relativeFromAny($relative);
            if ($relative === '' || isset($known[$relative])) {
                continue;
            }
            $result['scanned_files']++;
            if ((int) $fileInfo->getMTime() > $cutoff) {
                continue;
            }
            if (self::isUrlReferenced($relative)) {
                continue;
            }
            if (!$dryRun && self::unlinkFile($filePath)) {
                $result['files_deleted']++;
            }
        }

        return $result;
    }

    /**
     * 从字符串/数组/JSON 中提取 storage URL，统一为相对路径。
     *
     * @param mixed $value
     * @return array<int, string>
     */
    public static function extractUrls($value): array
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $text = htmlspecialchars_decode((string) $value);
        $text = str_replace('\\/', '/', $text);
        if ($text === '') {
            return [];
        }
        preg_match_all('#(?:https?://[^\s"\'<>]+?)?(?:/)?storage/[^\s"\'<>,]+\.[A-Za-z0-9]{1,8}#i', $text, $matches);
        $urls = [];
        foreach ($matches[0] as $url) {
            $relative = self::relativeFromAny($url);
            if ($relative !== '') {
                $urls[$relative] = $relative;
            }
        }
        return array_values($urls);
    }

    private static function isReferenced(int $attachId, array $attach): bool
    {
        $candidates = [];
        foreach (['url', 'thumb', 'path'] as $field) {
            $relative = self::relativeFromAny((string) ($attach[$field] ?? ''));
            if ($relative !== '') {
                $candidates[$relative] = true;
            }
        }
        if (!$candidates) {
            return false;
        }

        $checked = [];
        foreach (array_keys($candidates) as $relative) {
            foreach (self::urlVariants($relative) as $variant) {
                $checked[$variant] = true;
            }
        }

        // 其他 attach 记录共享同一文件。
        $first = array_key_first($checked);
        $stem = $first !== null ? self::urlStem($first) : '';
        if ($stem !== '') {
            $rows = Db::name('attach')
                ->where('id', '<>', $attachId)
                ->where(function ($q) use ($stem) {
                    $q->whereLike('url', '%' . $stem . '%')
                        ->whereOr('thumb', '%' . $stem . '%')
                        ->whereOr('path', '%' . $stem . '%');
                })
                ->field('url,thumb,path')
                ->select()
                ->toArray();
            foreach ($rows as $row) {
                foreach (['url', 'thumb', 'path'] as $field) {
                    $otherRelative = self::relativeFromAny((string) ($row[$field] ?? ''));
                    foreach (self::urlVariants($otherRelative) as $variant) {
                        if (isset($checked[$variant])) {
                            return true;
                        }
                    }
                }
            }
        }

        // 内容中仍在使用（含草稿 JSON 中转义斜杠的情况）。
        foreach (array_keys($checked) as $variant) {
            if (self::isUrlReferenced($variant)) {
                return true;
            }
        }

        return false;
    }

    private static function isUrlReferenced(string $url): bool
    {
        $relative = self::relativeFromAny($url);
        if ($relative === '') {
            return false;
        }
        $variants = self::urlVariants($relative);
        foreach (self::UPLOAD_FIELDS as $table => $fields) {
            foreach ($fields as $field) {
                foreach ($variants as $variant) {
                    if (Db::name($table)->whereLike($field, '%' . $variant . '%')->count() > 0) {
                        return true;
                    }
                    $escaped = str_replace('/', '\\/', $variant);
                    if ($escaped !== $variant
                        && Db::name($table)->whereLike($field, '%' . $escaped . '%')->count() > 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private static function queueAttach(array $attach): void
    {
        foreach (['path', 'url', 'thumb'] as $field) {
            self::queueFile((string) ($attach[$field] ?? ''));
        }
    }

    private static function queueFile(string $path): void
    {
        $file = self::resolveStorageFile($path);
        if ($file !== '') {
            self::$pendingFiles[] = $file;
        }
    }

    private static function unlinkFile(string $path): bool
    {
        $file = self::resolveStorageFile($path);
        if ($file === '' || !is_file($file)) {
            return false;
        }
        $deleted = @unlink($file);
        $paired = self::pairedFile($file);
        if ($paired !== '' && is_file($paired)) {
            @unlink($paired);
        }
        return $deleted;
    }

    /**
     * 任意形式的路径/URL -> 规范化的 storage/ 相对路径。
     * 只接受 public/storage 下的上传文件，/static/、内置默认图、上级目录一律返回空。
     */
    private static function relativeFromAny(string $path): string
    {
        $path = trim(htmlspecialchars_decode($path));
        if ($path === '') {
            return '';
        }
        $path = str_replace('\\/', '/', $path);
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#^https?://[^/]+#i', '', $path) ?: $path;
        $path = preg_replace('~[?#].*$~', '', $path) ?: $path;
        $path = ltrim($path, '/');
        if ($path === '') {
            return '';
        }
        if (preg_match('#(?:^|/)public/storage/(.+)$#i', $path, $m) === 1) {
            $path = 'storage/' . $m[1];
        } elseif (stripos($path, 'storage/') !== 0) {
            return '';
        }
        return self::canonicalRelative($path);
    }

    private static function canonicalRelative(string $relative): string
    {
        $relative = str_replace('\\', '/', $relative);
        if (stripos($relative, 'storage/') !== 0) {
            return '';
        }
        $inner = substr($relative, strlen('storage/'));
        if ($inner === '' || preg_match('#(^|/)\.\.?(/|$)#', $inner) === 1) {
            return '';
        }
        if (preg_match('#^(.*)/([^/]*)\.thumb\.([^.]+)$#i', $inner, $m) === 1) {
            $inner = $m[1] . '/' . $m[2] . '.' . $m[3];
        }
        return 'storage/' . $inner;
    }

    private static function resolveStorageFile(string $path): string
    {
        $relative = self::relativeFromAny($path);
        if ($relative === '') {
            return '';
        }
        return rtrim(public_path(), '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, substr($relative, strlen('storage/')));
    }

    /**
     * @return array<int, string>
     */
    private static function urlVariants(string $relative): array
    {
        $relative = self::relativeFromAny($relative);
        if ($relative === '') {
            return [];
        }
        $variants = [$relative];
        if (preg_match('#^(.*)/([^/]*)\.thumb\.([^.]+)$#i', $relative, $m) === 1) {
            $variants[] = $m[1] . '/' . $m[2] . '.' . $m[3];
        } else {
            $paired = self::pairedRelative($relative);
            if ($paired !== '') {
                $variants[] = $paired;
            }
        }
        return array_values(array_unique($variants));
    }

    private static function urlStem(string $relative): string
    {
        $name = basename(str_replace('\\', '/', $relative));
        $name = preg_replace('#\.thumb\.#i', '.', $name) ?: $name;
        return preg_replace('#\.[^.]+$#', '', $name) ?: $name;
    }

    private static function pairedRelative(string $relative): string
    {
        $relative = self::relativeFromAny($relative);
        if ($relative === '') {
            return '';
        }
        $inner = substr($relative, strlen('storage/'));
        $dir = dirname($inner);
        $name = basename($inner);
        if ($dir === '.') {
            $dir = '';
        }
        $prefix = $dir !== '' ? $dir . '/' : '';
        if (preg_match('#^(.*)\.thumb\.([^.]+)$#i', $name, $m) === 1) {
            return 'storage/' . $prefix . $m[1] . '.' . $m[2];
        }
        if (preg_match('#^(.*)\.([^.]+)$#i', $name, $m) === 1) {
            return 'storage/' . $prefix . $m[1] . '.thumb.' . $m[2];
        }
        return '';
    }

    private static function pairedFile(string $file): string
    {
        $relative = self::relativeFromAny(str_replace(DIRECTORY_SEPARATOR, '/', $file));
        if ($relative === '') {
            return '';
        }
        $paired = self::pairedRelative($relative);
        if ($paired === '') {
            return '';
        }
        return rtrim(public_path(), '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, substr($paired, strlen('storage/')));
    }
}
