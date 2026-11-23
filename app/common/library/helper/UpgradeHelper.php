<?php
// +----------------------------------------------------------------------
// | DudaCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2026 https://ask.btbk.org
// +----------------------------------------------------------------------
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统，打造私有社交化问答、内部知识存储
// +----------------------------------------------------------------------
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------

namespace app\common\library\helper;

use think\facade\Db;

/**
 * 本地版本升级助手。
 *
 * 只处理本地升级包、本地 SQL 与本地文件合并，不访问任何官方服务器。
 */
class UpgradeHelper
{
    protected static $instance;

    public static function instance(): UpgradeHelper
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 当前程序与数据库版本信息。
     */
    public function getLocalInfo(): array
    {
        $version = config('version');
        return [
            'version'   => (string) ($version['version'] ?? '0.0.0'),
            'build'     => (string) ($version['build'] ?? ''),
            'db_version'=> (string) get_setting('db_version', '404'),
        ];
    }

    /**
     * 本地升级包目录。
     */
    public function packageDir(): string
    {
        return rtrim(root_path('update'), '/\\') . DS . 'packages' . DS;
    }

    /**
     * 已应用升级包归档目录。
     */
    public function appliedDir(): string
    {
        return $this->packageDir() . 'applied' . DS;
    }

    /**
     * 扫描本地升级包。
     */
    public function listLocalPackages(): array
    {
        $dir = $this->packageDir();
        $this->ensureDir($dir);
        $packages = [];
        foreach ((array) glob($dir . '*.zip') as $file) {
            $inspect = $this->inspectPackage((string) $file);
            if (!$inspect['code']) {
                continue;
            }
            $data = $inspect['data'];
            $data['file'] = basename((string) $file);
            $data['size'] = filesize((string) $file);
            $data['mtime'] = date('Y-m-d H:i:s', (int) filemtime((string) $file));
            $packages[] = $data;
        }
        usort($packages, static function ($a, $b) {
            $compare = self::compareVersion($b['version'], $a['version']);
            return $compare !== 0 ? $compare : strcmp((string) $b['mtime'], (string) $a['mtime']);
        });
        return $packages;
    }

    /**
     * 读取升级包元数据，不解压、不执行包内代码。
     */
    public function inspectPackage(string $file): array
    {
        if ($file === '' || !is_file($file)) {
            return ['code' => 0, 'msg' => '升级包不存在'];
        }
        if (strtolower((string) pathinfo($file, PATHINFO_EXTENSION)) !== 'zip') {
            return ['code' => 0, 'msg' => '升级包必须是 ZIP 格式'];
        }

        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            return ['code' => 0, 'msg' => '升级包无法打开或已损坏'];
        }

        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if ($name !== '') {
                $names[] = $name;
            }
        }

        $prefixes = [''];
        foreach ($names as $name) {
            if (preg_match('#^([^/]+)/#', $name, $matches) === 1) {
                $prefixes[] = $matches[1] . '/';
            }
        }
        $prefixes = array_values(array_unique($prefixes));

        $metaFile = '';
        $prefix = '';
        foreach ($prefixes as $candidate) {
            foreach (['info.php', 'version.php'] as $name) {
                if (in_array($candidate . $name, $names, true)) {
                    $prefix = $candidate;
                    $metaFile = $candidate . $name;
                    break 2;
                }
            }
        }
        if ($metaFile === '') {
            $zip->close();
            return ['code' => 0, 'msg' => '升级包缺少 info.php 或 version.php'];
        }

        $meta = $this->parseMeta((string) $zip->getFromName($metaFile));
        $zip->close();

        if ($meta['version'] === '' || !self::checkVersionText($meta['version'])) {
            return ['code' => 0, 'msg' => '升级包版本号格式不正确，应为 6.0.1 这类格式'];
        }

        $hasSql = in_array($prefix . 'sql/update.sql', $names, true);
        $hasProgram = in_array($prefix . 'program/program.php', $names, true);
        $hasScript = false;
        foreach ($names as $name) {
            if (strpos($name, $prefix . 'script/') === 0) {
                $hasScript = true;
                break;
            }
        }

        return [
            'code' => 1,
            'data' => [
                'version'    => $meta['version'],
                'build'      => $meta['build'],
                'description'=> $meta['description'],
                'has_sql'    => $hasSql,
                'has_script' => $hasScript,
                'has_program'=> $hasProgram,
            ],
        ];
    }

    /**
     * 应用本地升级包：SQL → 可选 program.php → 文件覆盖 → 版本文件。
     */
    public function applyLocalPackage(string $file, bool $force = false): array
    {
        $inspect = $this->inspectPackage($file);
        if (!$inspect['code']) {
            return $inspect;
        }
        $package = $inspect['data'];
        $current = $this->getLocalInfo();

        if (!$force && self::compareVersion($package['version'], $current['version']) <= 0) {
            return [
                'code' => 0,
                'msg'  => '升级包版本 ' . $package['version'] . ' 不高于当前版本 ' . $current['version'],
            ];
        }

        $tempDir = runtime_path('update') . 'apply_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . DS;
        if (!$this->ensureDir($tempDir)) {
            return ['code' => 0, 'msg' => '无法创建升级临时目录'];
        }

        $root = $this->extractPackage($file, $tempDir);
        if ($root === null) {
            $this->delDir($tempDir);
            return ['code' => 0, 'msg' => '升级包解压失败或目录结构不正确'];
        }

        $metaContent = '';
        foreach (['info.php', 'version.php'] as $name) {
            if (is_file($root . $name)) {
                $metaContent = (string) file_get_contents($root . $name);
                break;
            }
        }
        $package = array_merge($package, $this->parseMeta($metaContent));

        $backupDir = rtrim(root_path('update'), '/\\') . DS . 'backup' . DS
            . $current['version'] . '_' . date('YmdHis') . DS;
        $this->ensureDir($backupDir);

        $versionFile = config_path() . 'version.php';
        if (is_file($versionFile)) {
            @copy($versionFile, $backupDir . 'version.php');
        }

        $sqlCount = 0;
        if (!empty($package['has_sql'])) {
            $sqlRes = $this->fetchSql($root . 'sql' . DS);
            if (!$sqlRes['code']) {
                $this->delDir($tempDir);
                return $sqlRes;
            }
            $sqlCount = 1;
        }

        if (!empty($package['has_program'])) {
            try {
                (static function ($script) {
                    require $script;
                })($root . 'program' . DS . 'program.php');
            } catch (\Throwable $e) {
                $this->delDir($tempDir);
                return ['code' => 0, 'msg' => '升级程序执行失败：' . $e->getMessage()];
            }
        }

        $fileCount = 0;
        $scriptDir = $root . 'script' . DS;
        if (is_dir($scriptDir)) {
            $merged = $this->copyMerge($scriptDir, root_path(), $backupDir . 'script' . DS);
            if ($merged === false) {
                $this->delDir($tempDir);
                return ['code' => 0, 'msg' => '升级文件合并失败'];
            }
            $fileCount = (int) $merged;
        }

        $build = $package['build'] !== '' ? $package['build'] : date('Ymd');
        $content = "<?php\r\n return " . var_export([
            'version' => $package['version'],
            'build'   => $build,
        ], true) . ";\r\n";
        if (file_put_contents($versionFile, $content) === false) {
            $this->delDir($tempDir);
            return ['code' => 0, 'msg' => '本地版本文件写入失败，请检查 config 目录权限'];
        }

        $appliedDir = $this->appliedDir();
        $this->ensureDir($appliedDir);
        $archived = $appliedDir . date('YmdHis') . '_' . basename($file);
        if (!@rename($file, $archived) && !(copy($file, $archived) && unlink($file))) {
            // 归档失败不影响升级结果，仅记录提示。
            $archived = '';
        }

        $this->delDir($tempDir);

        return [
            'code' => 1,
            'msg'  => '本地升级已完成：' . $current['version'] . ' → ' . $package['version'],
            'data' => [
                'sql'   => $sqlCount,
                'files' => $fileCount,
                'backup'=> $backupDir,
                'archive'=> $archived,
            ],
        ];
    }

    /**
     * 安全解压并返回升级包根目录。
     */
    private function extractPackage(string $file, string $dir): ?string
    {
        if (!ZipHelper::extractSafely($file, $dir)) {
            return null;
        }
        if (is_file($dir . 'info.php') || is_file($dir . 'version.php')) {
            return $dir;
        }
        $children = array_values(array_diff((array) scandir($dir), ['.', '..']));
        if (count($children) === 1 && is_dir($dir . $children[0])) {
            $subDir = $dir . $children[0] . DS;
            if (is_file($subDir . 'info.php') || is_file($subDir . 'version.php')) {
                return $subDir;
            }
        }
        return null;
    }

    /**
     * 从 info.php / version.php 文本中安全解析元数据，不执行 PHP。
     */
    private function parseMeta(string $content): array
    {
        $meta = ['version' => '', 'build' => '', 'description' => ''];
        if ($content === '') {
            return $meta;
        }
        if (preg_match_all('/[\'"](version|build|description)[\'"]\s*=>\s*[\'"]([^\'"]*)[\'"]/i', $content, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $key = strtolower($match[1]);
                if (array_key_exists($key, $meta)) {
                    $meta[$key] = trim($match[2]);
                }
            }
        }
        return $meta;
    }

    private function ensureDir(string $dir): bool
    {
        return is_dir($dir) || mkdir($dir, 0775, true) || is_dir($dir);
    }

    /**
     * 遍历当前目录（不包含下级目录）。
     */
    public function scanDir(string $dir, string $file = '')
    {
        if (trim($dir) === '' || !is_dir($dir)) {
            return false;
        }
        $result = [];
        foreach ((array) scandir($dir) as $item) {
            if ($item !== '..' && $item !== '.' && $item !== $file) {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * 递归合并目录，只覆盖不一致的文件，并把旧文件备份到 backupDir。
     */
    public function copyMerge(string $source, string $target, $backupDir = '')
    {
        if (trim($source) === '' || trim($target) === '') {
            return false;
        }
        $source = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $source);
        $target = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $target);
        $backupDir = (string) $backupDir === '' ? '' : str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $backupDir);

        $source = rtrim($source, '/\\') . DIRECTORY_SEPARATOR;
        $target = rtrim($target, '/\\') . DIRECTORY_SEPARATOR;
        $backupDir = $backupDir === '' ? '' : rtrim($backupDir, '/\\') . DIRECTORY_SEPARATOR;

        $count = 0;
        if (!is_dir($target) && !mkdir($target, 0775, true) && !is_dir($target)) {
            return false;
        }
        if ($backupDir !== '' && !is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
            return false;
        }

        foreach ((array) glob($source . '*', GLOB_NOSORT) as $filename) {
            if (is_dir($filename)) {
                $subCount = $this->copyMerge(
                    $filename,
                    $target . basename($filename),
                    $backupDir === '' ? '' : $backupDir . basename($filename)
                );
                if ($subCount === false) {
                    return false;
                }
                $count += (int) $subCount;
            } elseif (is_file($filename)) {
                $destination = $target . basename($filename);
                if (!file_exists($destination)
                    || md5_file($filename) !== md5_file($destination)
                    || filesize($filename) !== filesize($destination)
                ) {
                    if ($backupDir !== '' && file_exists($destination)) {
                        @copy($destination, $backupDir . basename($filename));
                    }
                    if (!@copy($filename, $destination)) {
                        return false;
                    }
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * 递归删除目录。
     */
    public function delDir(string $dir): bool
    {
        $dir = rtrim($dir, '/\\');
        if ($dir === '' || !is_dir($dir)) {
            return true;
        }
        foreach ((array) scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DS . $item;
            if (is_dir($path) && !is_link($path)) {
                $this->delDir($path);
            } else {
                @unlink($path);
            }
        }
        return @rmdir($dir);
    }

    /**
     * 执行升级包中的 SQL，支持 DELIMITER 与幂等 INSERT。
     */
    public function fetchSql(string $dir): array
    {
        $dir = trim($dir);
        if ($dir === '') {
            return ['code' => 1, 'msg' => '', 'data' => ''];
        }
        $sqlFile = rtrim($dir, '/\\') . DS . 'update.sql';
        if (!is_file($sqlFile)) {
            return ['code' => 1, 'msg' => '', 'data' => ''];
        }

        $prefix = (string) config('database.connections.mysql.prefix');
        $content = (string) file_get_contents($sqlFile);
        $content = $content === '' ? '' : str_ireplace('dask_', $prefix, $content);

        $delimiter = ';';
        $buffer = '';
        $statements = [];
        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
            $trimmed = trim($line);
            if (preg_match('/^DELIMITER\s+(\S+)/i', $trimmed, $matches) === 1) {
                if (trim($buffer) !== '') {
                    $statements[] = $buffer;
                    $buffer = '';
                }
                $delimiter = $matches[1];
                continue;
            }
            if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0) {
                continue;
            }
            $buffer .= $line . "\n";
            if (substr($trimmed, -strlen($delimiter)) === $delimiter) {
                $statements[] = $buffer;
                $buffer = '';
            }
        }
        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        Db::startTrans();
        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if ($statement === '') {
                    continue;
                }
                $statement = preg_replace('/\bINSERT\s+INTO\s+/i', 'INSERT IGNORE INTO ', $statement, 1) ?: $statement;
                Db::execute($statement);
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => 'SQL 执行失败：' . $e->getMessage(), 'data' => $statement ?? ''];
        }
        return ['code' => 1, 'msg' => '', 'data' => count($statements)];
    }

    /**
     * 版本号格式检查。
     */
    public static function checkVersionText($version)
    {
        return preg_match('/^[0-9]{1,3}\.[0-9]{1,2}\.[0-9]{1,2}$/', (string) $version) === 1;
    }

    /**
     * 字符版本号转整数。
     */
    public static function versionToInteger($version)
    {
        if (self::checkVersionText($version)) {
            [$major, $minor, $sub] = explode('.', (string) $version);
            return intval($major * 10000 + $minor * 100 + $sub);
        }
        return false;
    }

    /**
     * 比较两个版本号：1 大于，0 相等，-1 小于。
     */
    public static function compareVersion($version1, $version2): int
    {
        if (!self::checkVersionText($version1) || !self::checkVersionText($version2)) {
            return 0;
        }
        $result = version_compare((string) $version1, (string) $version2);
        return $result > 0 ? 1 : ($result < 0 ? -1 : 0);
    }
}
