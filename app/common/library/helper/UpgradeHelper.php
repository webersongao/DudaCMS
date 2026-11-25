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
use think\facade\Cache;
use think\facade\Event;

/**
 * 在线版本升级助手。
 *
 * 升级步骤由 update/upgrade/manifest.php 统一声明，对应幂等 SQL 随 git 同步到
 * update/upgrade/ 目录；后台与数据库已应用版本比较，提示相邻版本升级。
 * 老版本包没有清单时仍按 SQL 文件名与文件头元数据扫描。
 * 全程不访问任何外部服务器，不依赖上传 ZIP 升级包。
 */
class UpgradeHelper
{
    protected static $instance;

    /** 无任何成功升级记录时的数据库基准版本 */
    const BASELINE_DB_VERSION = '1.0.0';

    /** 仅保留最近 N 条成功记录；失败/进行中记录全部保留 */
    const KEEP_SUCCESS_LOGS = 5;

    public static function instance(): UpgradeHelper
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 当前程序版本与数据库已应用版本。
     *
     * 程序版本来自 config/version.php（随代码同步）；数据库版本来自
     * dask_upgrade_log 的最新成功记录，无记录时视为基准版本。
     */
    public function getLocalInfo(): array
    {
        $version = config('version');
        $programVersion = (string) ($version['version'] ?? '0.0.0');
        return [
            'version'    => $programVersion,
            'build'      => (string) ($version['build'] ?? ''),
            'db_version' => $this->getDbVersion(),
        ];
    }

    /** 程序版本（代码版本） */
    public function getProgramVersion(): string
    {
        $version = config('version');
        return (string) ($version['version'] ?? '0.0.0');
    }

    /** 升级记录表名（含前缀） */
    public function logTable(): string
    {
        return (string) config('database.connections.mysql.prefix') . 'upgrade_log';
    }

    private function logTableExists(): bool
    {
        $table = $this->logTable();
        try {
            $rows = Db::query("SHOW TABLES LIKE '" . addslashes($table) . "'");
            return !empty($rows);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** 确保升级记录表存在（幂等，供首次升级自举） */
    public function ensureLogTable(): void
    {
        $table = $this->logTable();
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(20) NOT NULL DEFAULT '' COMMENT '升级到的版本',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=进行中 1=成功 2=失败',
  `sql_file` varchar(100) NOT NULL DEFAULT '' COMMENT 'SQL文件',
  `sql_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行SQL条数',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '说明',
  `backup_path` varchar(255) NOT NULL DEFAULT '' COMMENT '升级前备份路径',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据库升级记录'";
        Db::execute($sql);
    }

    /** 数据库已应用版本：最新成功记录的版本，无记录取基准版本 */
    public function getDbVersion(): string
    {
        if (!$this->logTableExists()) {
            return self::BASELINE_DB_VERSION;
        }
        try {
            $version = Db::name('upgrade_log')->where('status', 1)->order('id', 'desc')->value('version');
            return $version ? (string) $version : self::BASELINE_DB_VERSION;
        } catch (\Throwable $e) {
            return self::BASELINE_DB_VERSION;
        }
    }

    /** 升级 SQL 目录（随代码 git 同步） */
    public function upgradeSqlDir(): string
    {
        return root_path() . 'update' . DS . 'upgrade' . DS;
    }

    /**
     * 解析升级 SQL 文件名，支持两种格式：
     *   update1.0.0to1.0.1.sql  → from=1.0.0, to=1.0.1（推荐，显式声明相邻版本）
     *   1.0.1.sql               → to=1.0.1, from 由相邻文件推断
     */
    private function parseUpgradeFileName(string $name): ?array
    {
        if (preg_match('/^update([0-9]+\.[0-9]+\.[0-9]+)to([0-9]+\.[0-9]+\.[0-9]+)$/i', $name, $m) === 1) {
            if (self::checkVersionText($m[1]) && self::checkVersionText($m[2])) {
                return ['from' => $m[1], 'to' => $m[2]];
            }
            return null;
        }
        if (self::checkVersionText($name)) {
            return ['from' => '', 'to' => $name];
        }
        return null;
    }

    /** 读取升级清单；老版本包没有清单时返回空数组，走 SQL 扫描兼容路径 */
    private function loadManifest(): array
    {
        $file = $this->upgradeSqlDir() . 'manifest.php';
        if (!is_file($file)) {
            return [];
        }
        try {
            $data = include $file;
        } catch (\Throwable $e) {
            return [];
        }
        return is_array($data) ? $data : [];
    }

    /**
     * 汇总一条升级记录：清单优先，SQL 头部元数据兜底。
     */
    private function buildUpgradeInfo(string $version, string $from, string $file, string $path, array $manifestItem, array $meta): array
    {
        $title = (string) ($manifestItem['title'] ?? '') ?: (string) ($meta['title'] ?? '');
        $summary = (string) ($manifestItem['summary'] ?? '') ?: (string) ($meta['summary'] ?? '');
        $requires = (string) ($manifestItem['requires'] ?? '') ?: (string) ($meta['requires'] ?? '');
        if ($requires === '') {
            $requires = $from;
        }
        $description = $title !== ''
            ? $title . ($summary !== '' ? '：' . $summary : '')
            : (string) ($meta['description'] ?? '');
        $risk = strtolower((string) ($manifestItem['risk'] ?? '') ?: (string) ($meta['risk'] ?? ''));

        return [
            'version'     => $version,
            'from'        => $from,
            'file'        => $file,
            'path'        => $path,
            'size'        => (int) filesize($path),
            'mtime'       => date('Y-m-d H:i:s', (int) filemtime($path)),
            'description' => $description,
            'title'       => $title,
            'summary'     => $summary,
            'risk'        => $risk,
            'requires'    => $requires,
            'verify'      => (string) ($manifestItem['verify'] ?? '') ?: (string) ($meta['verify'] ?? ''),
        ];
    }

    /**
     * 扫描所有升级步骤，以目标版本为键。
     * 优先读取 update/upgrade/manifest.php；清单外的 SQL 仍按文件名/头部扫描，兼容旧版本包。
     * @return array<string, array>
     */
    public function listUpgradeFiles(): array
    {
        $dir = $this->upgradeSqlDir();
        $manifest = $this->loadManifest();
        $files = [];
        $claimed = [];

        foreach ($manifest as $version => $item) {
            if (!self::checkVersionText($version) || !is_array($item)) {
                continue;
            }
            $version = (string) $version;
            $file = (string) ($item['file'] ?? '');
            if ($file === '' || basename($file) !== $file) {
                continue;
            }
            $path = $dir . $file;
            if (!is_file($path)) {
                continue;
            }
            $meta = $this->parseUpgradeMeta($path);
            $parsed = $this->parseUpgradeFileName(pathinfo($file, PATHINFO_FILENAME));
            $from = (string) ($item['from'] ?? '') ?: ($meta['requires'] !== '' ? $meta['requires'] : (string) ($parsed['from'] ?? ''));
            $files[$version] = $this->buildUpgradeInfo($version, $from, $file, $path, $item, $meta);
            $claimed[$file] = true;
        }

        foreach ((array) glob($dir . '*.sql') as $filePath) {
            $path = (string) $filePath;
            $file = basename($path);
            if (isset($claimed[$file])) {
                continue;
            }
            $meta = $this->parseUpgradeMeta($path);
            $parsed = $this->parseUpgradeFileName(pathinfo($file, PATHINFO_FILENAME));
            if ($parsed === null) {
                continue;
            }
            $version = $meta['version'] !== '' ? $meta['version'] : $parsed['to'];
            if (!self::checkVersionText($version) || isset($files[$version])) {
                continue;
            }
            $from = $meta['requires'] !== '' ? $meta['requires'] : $parsed['from'];
            $files[$version] = $this->buildUpgradeInfo($version, $from, $file, $path, [], $meta);
        }

        // 对未声明 from 的文件，按版本号推断其前置版本
        $versions = array_keys($files);
        usort($versions, [self::class, 'compareVersion']);
        foreach ($versions as $i => $to) {
            if ($files[$to]['from'] === '') {
                $files[$to]['from'] = $i > 0 ? $versions[$i - 1] : self::BASELINE_DB_VERSION;
            }
        }
        uksort($files, [self::class, 'compareVersion']);
        return $files;
    }

    /**
     * 为升级列表页准备展示数据：
     * - 标记每个版本的状态；
     * - 「已应用」仅保留最近的 5 个，未升级版本全量展示。
     */
    public function prepareDisplayFiles(array $files, ?array $pending, array $info, int $appliedLimit = 5): array
    {
        $marked = [];
        foreach ($files as $version => $file) {
            $versionText = (string) $version;
            if ($pending && $pending['version'] === $versionText) {
                $file['status_label'] = '待升级';
                $file['status_class'] = 'text-bg-warning';
                $file['_applied'] = false;
            } elseif (self::compareVersion($versionText, (string) $info['db_version']) <= 0) {
                $file['status_label'] = '已应用';
                $file['status_class'] = 'text-bg-success';
                $file['_applied'] = true;
            } else {
                $file['status_label'] = '等待前置版本';
                $file['status_class'] = 'text-bg-secondary';
                $file['_applied'] = false;
            }
            $marked[$versionText] = $file;
        }

        $applied = [];
        foreach ($marked as $version => $file) {
            if (!empty($file['_applied'])) {
                $applied[] = $version;
            }
        }
        // 按版本号从大到小，只保留最近 N 个已应用版本。
        usort($applied, [self::class, 'compareVersion']);
        $applied = array_reverse($applied);
        $hide = array_slice($applied, max(0, $appliedLimit));

        $result = [];
        foreach ($marked as $version => $file) {
            if (in_array((string) $version, $hide, true)) {
                continue;
            }
            unset($file['_applied']);
            $result[$version] = $file;
        }
        return $result;
    }

    /**
     * 解析 SQL 文件头元数据（借鉴成熟升级组件的文件即清单设计）。
     * 支持 @version/@requires/@title/@summary/@risk；无元数据时回退到普通注释说明。
     */
    private function parseUpgradeMeta(string $file): array
    {
        $meta = ['version' => '', 'requires' => '', 'title' => '', 'summary' => '', 'risk' => '', 'verify' => '', 'description' => ''];
        $lines = @file($file, FILE_IGNORE_NEW_LINES);
        if (!$lines) {
            return $meta;
        }
        $plain = [];
        foreach ($lines as $line) {
            $trim = trim((string) $line);
            if ($trim === '') {
                continue;
            }
            if (strpos($trim, '--') !== 0) {
                break;
            }
            $text = trim(substr($trim, 2));
            if (preg_match('/^@([a-z_]+)\s+(.+)$/i', $text, $m) === 1) {
                $key = strtolower($m[1]);
                $value = trim($m[2]);
                if ($key === 'summary') {
                    $meta['summary'] .= ($meta['summary'] !== '' ? ' ' : '') . $value;
                } elseif (array_key_exists($key, $meta)) {
                    $meta[$key] = $value;
                }
                continue;
            }
            // 跳过纯分隔线，其余普通说明收集起来作为兜底描述
            if (strpos($text, '=') === 0 || strpos($text, 'DudaCMS') === 0) {
                continue;
            }
            if (count($plain) < 3) {
                $plain[] = $text;
            }
        }
        $meta['description'] = implode(' ', $plain);
        $meta['risk'] = strtolower($meta['risk']);
        return $meta;
    }

    /**
     * 获取下一个待升级版本。
     *
     * 严格相邻：仅当某升级文件的 from 版本等于当前数据库版本、
     * 且 to 版本不高于程序版本时，才判定为待升级。从根本上禁止跨版本。
     */
    public function getPendingUpgrade(): ?array
    {
        $dbVersion = $this->getDbVersion();
        $programVersion = $this->getProgramVersion();
        $files = $this->listUpgradeFiles();

        foreach ($files as $info) {
            $fromMatches = self::compareVersion((string) $info['from'], $dbVersion) === 0;
            $withinProgram = self::compareVersion((string) $info['version'], $programVersion) <= 0;
            $isAdvance = self::compareVersion((string) $info['version'], $dbVersion) > 0;
            if ($fromMatches && $withinProgram && $isAdvance) {
                return $info;
            }
        }
        return null;
    }

    /** 升级历史（含进行中/成功/失败，全部保留） */
    public function listUpgradeLogs(int $limit = 0): array
    {
        if (!$this->logTableExists()) {
            return [];
        }
        try {
            $query = Db::name('upgrade_log')->order('id', 'desc');
            if ($limit > 0) {
                $query->limit($limit);
            }
            return $query->select()->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** 升级锁键（Cache 文件驱动，跨请求持久） */
    private function lockKey(): string
    {
        return 'dask_db_upgrade_lock';
    }

    /** 尝试获取升级锁，返回 true 表示获取成功 */
    private function acquireLock(): bool
    {
        if (Cache::has($this->lockKey())) {
            return false;
        }
        // 5 分钟自动过期，防止进程崩溃导致死锁
        return Cache::set($this->lockKey(), [
            'time' => time(),
            'pid'  => getmypid(),
        ], 300);
    }

    private function releaseLock(): void
    {
        Cache::delete($this->lockKey());
    }

    /** 当前锁状态（供页面提示，防止多人同时操作） */
    public function lockStatus(): ?array
    {
        $lock = Cache::get($this->lockKey());
        if (!$lock || !is_array($lock)) {
            return null;
        }
        $age = time() - (int) ($lock['time'] ?? 0);
        return ['age' => $age, 'stale' => $age > 300, 'time' => $lock['time'] ?? 0];
    }

    /**
     * 备份根目录：统一放在 runtime/backup（运行时目录，必然可写、git 忽略、不增加根目录）。
     */
    public function backupRoot(): string
    {
        return rtrim(runtime_path(), '/\\') . DS . 'backup';
    }

    /**
     * 只读预检：升级前检查环境，不写任何数据。
     */
    public function preflight(): array
    {
        $checks = [];
        $checks[] = ['label' => 'PHP 版本 ≥ 8.2', 'ok' => version_compare(PHP_VERSION, '8.2', '>='), 'detail' => PHP_VERSION];
        $checks[] = ['label' => 'PDO_MySQL 扩展已启用', 'ok' => extension_loaded('pdo_mysql'), 'detail' => 'pdo_mysql'];
        $backupRoot = $this->backupRoot();
        // runtime 目录必然存在且可写（session/缓存/日志均写此处）；若 backup 子目录尚未创建，
        // 备份执行时会用 mkdir 递归创建，因此这里检查 runtime 根目录可写即可。
        $probe = $backupRoot;
        while (!is_dir($probe) && dirname($probe) !== $probe) {
            $probe = dirname($probe);
        }
        $checks[] = [
            'label'  => '备份目录就绪',
            'ok'     => is_dir($probe) && is_writable($probe),
            'detail' => $backupRoot . ($probe !== $backupRoot ? '（父目录 ' . $probe . '）' : ''),
        ];
        $sqlDir = $this->upgradeSqlDir();
        $checks[] = ['label' => '升级脚本目录可读', 'ok' => is_dir($sqlDir) && is_readable($sqlDir), 'detail' => $sqlDir];
        $ok = true;
        foreach ($checks as $c) {
            $ok = $ok && $c['ok'];
        }
        return ['ok' => $ok, 'checks' => $checks];
    }

    /**
     * 执行在线升级：加锁 → 校验相邻版本 → 记录 → 备份 → 事务执行 SQL → 自检 → 写结果。
     */
    public function runUpgrade(string $version): array
    {
        if (!self::checkVersionText($version)) {
            return ['code' => 0, 'msg' => '版本号格式不正确'];
        }
        // 获取锁本身也可能因缓存驱动异常而抛错，纳入捕获，避免返回 500 HTML
        // 让前端 r.json() 再次遭遇 Unexpected token '<'
        try {
            $locked = $this->acquireLock();
        } catch (\Throwable $e) {
            return ['code' => 0, 'msg' => '无法获取升级锁：' . $e->getMessage()];
        }
        if (!$locked) {
            $lock = $this->lockStatus();
            return ['code' => 0, 'msg' => '另一个升级任务正在进行中，请稍后再试' . ($lock ? '（已持续 ' . $lock['age'] . ' 秒）' : '')];
        }

        try {
            $pending = $this->getPendingUpgrade();
            if (!$pending) {
                return ['code' => 0, 'msg' => '当前没有待执行的数据库升级'];
            }
            if ($pending['version'] !== $version) {
                return ['code' => 0, 'msg' => '请按相邻版本逐级升级，当前应升级到 ' . $pending['version']];
            }

            $pre = $this->preflight();
            if (!$pre['ok']) {
                return ['code' => 0, 'msg' => '升级前预检未通过，请检查环境后重试'];
            }

            // 自举：确保记录表存在
            $this->ensureLogTable();

            // 写进行中记录
            $logId = Db::name('upgrade_log')->insertGetId([
                'version'   => $version,
                'status'    => 0,
                'sql_file'  => $pending['file'],
                'sql_count' => 0,
                'remark'    => '开始升级',
                'add_time'  => time(),
            ]);

            // 升级前全库备份
            $backup = $this->backupBeforeUpgrade($version);
            if (!$backup['code']) {
                Db::name('upgrade_log')->where('id', $logId)->update([
                    'status' => 2, 'remark' => '备份失败，已中止升级：' . $backup['msg'],
                ]);
                return ['code' => 0, 'msg' => '升级前备份失败，已中止升级：' . $backup['msg']];
            }

            // 事务执行 SQL（语句级幂等守卫）
            $result = $this->executeSqlFile($pending['path']);
            if (!$result['code']) {
                Db::name('upgrade_log')->where('id', $logId)->update([
                    'status'      => 2,
                    'remark'      => '失败：' . $result['msg'],
                    'backup_path' => $backup['path'],
                ]);
                return ['code' => 0, 'msg' => $result['msg'] . '（可用升级前备份还原）'];
            }

            // 升级后自检：清单/SQL 声明的 verify（失败不回滚数据，但明确告警，便于人工确认）
            $verify = $this->runVerify($pending);
            $remark = '升级成功' . ($result['skipped'] ? '（跳过 ' . $result['skipped'] . ' 条已应用语句）' : '');
            if (!$verify['ok']) {
                $remark .= '；自检告警：' . $verify['msg'];
            }
            Db::name('upgrade_log')->where('id', $logId)->update([
                'status'      => 1,
                'sql_count'   => (int) $result['data'],
                'remark'      => $remark,
                'backup_path' => $backup['path'],
            ]);
            $this->pruneSuccessLogs();

            // 触发升级成功事件，便于业务扩展（如清缓存、刷新配置）
            try {
                Event::trigger('db_upgrade_success', ['version' => $version, 'previous' => $pending['from']]);
                Cache::clear();
            } catch (\Throwable $e) {
                // 事件/清缓存失败不影响升级结果
            }

            return [
                'code' => 1,
                'msg'  => '数据库已成功升级到 ' . $version,
                'data' => [
                    'version' => $version,
                    'sql'     => (int) $result['data'],
                    'skipped' => (int) ($result['skipped'] ?? 0),
                    'backup'  => $backup['path'],
                    'verify'  => $verify,
                ],
            ];
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * 升级后自检：校验清单（或 SQL 头部）声明的 verify 项。
     * 支持 column:表.列 / table:表 / index:表.索引，用 SHOW 语句规避权限问题。
     */
    private function runVerify(array $pending): array
    {
        $verifyRaw = (string) ($pending['verify'] ?? '');
        if ($verifyRaw === '') {
            return ['ok' => true, 'msg' => '', 'items' => []];
        }
        $prefix = (string) config('database.connections.mysql.prefix');
        $cache = [];
        $items = [];
        $allOk = true;
        $failed = [];
        foreach (explode(',', $verifyRaw) as $token) {
            $token = trim($token);
            if ($token === '' || strpos($token, ':') === false) {
                continue;
            }
            [$kind, $target] = array_pad(explode(':', $token, 2), 2, '');
            $kind = strtolower(trim($kind));
            $ok = false;
            if ($kind === 'table') {
                $ok = $this->tableExists($prefix . trim($target), $cache);
            } elseif ($kind === 'column' && strpos($target, '.') !== false) {
                [$t, $c] = explode('.', $target, 2);
                $ok = $this->columnExists($prefix . trim($t), trim($c), $cache);
            } elseif ($kind === 'index' && strpos($target, '.') !== false) {
                [$t, $idx] = explode('.', $target, 2);
                $ok = $this->indexExists($prefix . trim($t), trim($idx), $cache);
            }
            $items[] = ['token' => $token, 'ok' => $ok];
            $allOk = $allOk && $ok;
            if (!$ok) {
                $failed[] = $token;
            }
        }
        return [
            'ok'  => $allOk,
            'msg' => $failed ? '自检未通过：' . implode(', ', $failed) : '',
            'items' => $items,
        ];
    }

    /** 保留最近 N 条成功记录，删除更早的成功记录；其它状态全部保留 */
    private function pruneSuccessLogs(): void
    {
        $ids = Db::name('upgrade_log')->where('status', 1)->order('id', 'desc')
            ->limit(self::KEEP_SUCCESS_LOGS)->column('id');
        $query = Db::name('upgrade_log')->where('status', 1);
        if ($ids) {
            $query->where('id', 'not in', $ids);
        }
        $query->delete();
    }

    /**
     * 升级前全库备份。
     */
    private function backupBeforeUpgrade(string $version): array
    {
        // BackupHelper 的 array_map('addslashes', $row) 在 PHP 8.1+ 遇到 NULL 会触发
        // E_DEPRECATED；TP 的错误处理会将其转为异常而中断备份。此处临时屏蔽 deprecated。
        $oldLevel = error_reporting();
        error_reporting($oldLevel & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        try {
            $dir = $this->backupRoot() . DS . 'database' . DS
                . 'before_upgrade_' . $version . '_' . date('YmdHis') . DS;
            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                $result = ['code' => 0, 'msg' => '无法创建备份目录：' . $dir];
            } else {
                $backup = BackupHelper::getInstance([
                    'path'     => $dir,
                    'part'     => 20971520,
                    'compress' => 0,
                    'level'    => 9,
                ]);
                $tables = Db::query('SHOW TABLES');
                foreach ($tables as $row) {
                    $table = (string) reset($row);
                    if ($table === '') {
                        continue;
                    }
                    // 任一表备份写入失败即中止升级，避免"无备份却继续升级"的致命风险
                    if (false === $backup->setFile()->backup($table, 0)) {
                        return ['code' => 0, 'msg' => '备份数据表 ' . $table . ' 失败（磁盘空间或目录权限不足）'];
                    }
                }
                $result = ['code' => 1, 'path' => $dir, 'msg' => ''];
            }
        } catch (\Throwable $e) {
            $result = ['code' => 0, 'msg' => $e->getMessage()];
        } finally {
            error_reporting($oldLevel);
        }
        return $result;
    }

    /**
     * 执行单个 SQL 文件：
     *   - 前缀替换 / DELIMITER / 词法切分（正确处理引号内分号、注释）
     *   - 语句级幂等守卫（SHOW COLUMNS/TABLES，规避 information_schema 权限问题）
     *   - INSERT 自动转 INSERT IGNORE
     *   - 事务包裹，失败整体回滚
     */
    private function executeSqlFile(string $file): array
    {
        if (!is_file($file)) {
            return ['code' => 0, 'msg' => '升级 SQL 文件不存在', 'data' => 0];
        }
        $prefix = (string) config('database.connections.mysql.prefix');
        $content = (string) file_get_contents($file);
        $content = str_ireplace('dask_', $prefix, $content);

        $statements = $this->splitSql($content);
        $schemaCache = []; // 语句级守卫的结构查询缓存
        $statement = '';
        $executed = 0;
        $skipped = 0;
        Db::startTrans();
        try {
            foreach ($statements as $statement) {
                $guard = $this->inferGuard($statement);
                if ($guard['type'] !== 'none' && $this->guardSatisfied($guard, $schemaCache)) {
                    $skipped++;
                    continue;
                }
                $sql = preg_replace('/\bINSERT\s+INTO\s+/i', 'INSERT IGNORE INTO ', $statement, 1) ?: $statement;
                Db::execute($sql);
                $executed++;
            }
            Db::commit();
            return ['code' => 1, 'msg' => '', 'data' => $executed, 'skipped' => $skipped];
        } catch (\Throwable $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => 'SQL 执行失败：' . $e->getMessage() . ' [语句: ' . mb_substr($statement, 0, 120) . ']', 'data' => 0];
        }
    }

    /**
     * 词法切分 SQL：跟踪引号、反引号、行/块注释，遇顶层分号断开。
     * @return string[]
     */
    private function splitSql(string $sql): array
    {
        $result = [];
        $buf = '';
        $quote = '';
        $len = strlen($sql);
        $i = 0;
        while ($i < $len) {
            $ch = $sql[$i];
            $next = ($i + 1 < $len) ? $sql[$i + 1] : '';
            if ($quote !== '') {
                $buf .= $ch;
                if ($ch === '\\' && $quote !== '`' && $next !== '') {
                    $buf .= $next;
                    $i += 2;
                    continue;
                }
                if ($ch === $quote) {
                    $quote = '';
                }
                $i++;
                continue;
            }
            if ($ch === "'" || $ch === '"' || $ch === '`') {
                $quote = $ch;
                $buf .= $ch;
                $i++;
                continue;
            }
            if (($ch === '-' && $next === '-' && ($i + 2 >= $len || in_array($sql[$i + 2], [' ', "\t", "\r", "\n"], true))) || $ch === '#') {
                $eol = strpos($sql, "\n", $i);
                $i = ($eol === false ? $len : $eol + 1);
                continue;
            }
            if ($ch === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                $i = ($end === false ? $len : $end + 2);
                continue;
            }
            if ($ch === ';') {
                if (trim($buf) !== '') {
                    $result[] = trim($buf);
                }
                $buf = '';
                $i++;
                continue;
            }
            $buf .= $ch;
            $i++;
        }
        if (trim($buf) !== '') {
            $result[] = trim($buf);
        }
        return $result;
    }

    /**
     * 推断语句的幂等守卫类型。
     * @return array{type:string, table:string, name:string}
     */
    private function inferGuard(string $statement): array
    {
        $s = trim((string) preg_replace('/\s+/', ' ', $statement));
        $id = '`?([A-Za-z0-9_$]+)`?';
        // CREATE TABLE [IF NOT EXISTS] x
        if (preg_match('/^CREATE\s+(?:TEMPORARY\s+)?TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?' . $id . '/i', $s, $m)) {
            return ['type' => 'table_missing', 'table' => $m[1], 'name' => ''];
        }
        // ALTER TABLE x ADD [COLUMN] col
        if (preg_match('/^ALTER\s+TABLE\s+' . $id . '\s+ADD\s+(?:COLUMN\s+)?' . $id . '/i', $s, $m)) {
            // 排除 ADD KEY/INDEX/PRIMARY/CONSTRAINT 等索引约束（这些不是列）
            if (!preg_match('/^(KEY|INDEX|PRIMARY|UNIQUE|CONSTRAINT|FOREIGN|CHECK|FULLTEXT|SPATIAL)$/i', $m[2])) {
                return ['type' => 'column_missing', 'table' => $m[1], 'name' => $m[2]];
            }
        }
        // ALTER TABLE x ADD [UNIQUE] KEY/INDEX name
        if (preg_match('/^ALTER\s+TABLE\s+' . $id . '\s+ADD\s+(?:UNIQUE\s+|PRIMARY\s+|FULLTEXT\s+|SPATIAL\s+)?(?:KEY|INDEX)\s+' . $id . '/i', $s, $m)) {
            return ['type' => 'index_missing', 'table' => $m[1], 'name' => $m[2]];
        }
        // ALTER TABLE x DROP [COLUMN] col
        if (preg_match('/^ALTER\s+TABLE\s+' . $id . '\s+DROP\s+(?:COLUMN\s+)?' . $id . '/i', $s, $m)) {
            if (!preg_match('/^(KEY|INDEX|PRIMARY|CONSTRAINT|FOREIGN|CHECK)$/i', $m[2])) {
                return ['type' => 'column_exists', 'table' => $m[1], 'name' => $m[2]];
            }
        }
        return ['type' => 'none', 'table' => '', 'name' => ''];
    }

    /**
     * 守卫判定：返回 true 表示"条件已满足，应跳过该语句"。
     * 全部用 SHOW 语句，规避 information_schema 无权限问题。
     */
    private function guardSatisfied(array $guard, array &$cache): bool
    {
        $table = $guard['table'];
        $name = $guard['name'];
        switch ($guard['type']) {
            case 'table_missing':
                return $this->tableExists($table, $cache);
            case 'column_missing':
                return $this->columnExists($table, $name, $cache);
            case 'column_exists':
                return !$this->columnExists($table, $name, $cache);
            case 'index_missing':
                return $this->indexExists($table, $name, $cache);
            default:
                return false;
        }
    }

    private function tableExists(string $table, array &$cache): bool
    {
        $key = 't:' . $table;
        if (!array_key_exists($key, $cache)) {
            try {
                $rows = Db::query("SHOW TABLES LIKE '" . addslashes($table) . "'");
                $cache[$key] = !empty($rows);
            } catch (\Throwable $e) {
                $cache[$key] = false;
            }
        }
        return $cache[$key];
    }

    private function columnExists(string $table, string $column, array &$cache): bool
    {
        $key = 'c:' . $table . '.' . $column;
        if (!array_key_exists($key, $cache)) {
            try {
                $rows = Db::query("SHOW COLUMNS FROM `{$table}` LIKE '" . addslashes($column) . "'");
                $cache[$key] = !empty($rows);
            } catch (\Throwable $e) {
                $cache[$key] = false;
            }
        }
        return $cache[$key];
    }

    private function indexExists(string $table, string $index, array &$cache): bool
    {
        $key = 'i:' . $table . '.' . $index;
        if (!array_key_exists($key, $cache)) {
            try {
                $rows = Db::query("SHOW INDEX FROM `{$table}` WHERE Key_name = '" . addslashes($index) . "'");
                $cache[$key] = !empty($rows);
            } catch (\Throwable $e) {
                $cache[$key] = false;
            }
        }
        return $cache[$key];
    }

    /** 版本号格式检查 */
    public static function checkVersionText($version)
    {
        return preg_match('/^[0-9]{1,3}\.[0-9]{1,2}\.[0-9]{1,2}$/', (string) $version) === 1;
    }

    /** 字符版本号转整数 */
    public static function versionToInteger($version)
    {
        if (self::checkVersionText($version)) {
            [$major, $minor, $sub] = explode('.', (string) $version);
            return intval($major * 10000 + $minor * 100 + $sub);
        }
        return false;
    }

    /** 版本号比较 */
    public static function compareVersion($version1, $version2): int
    {
        if (!self::checkVersionText($version1) || !self::checkVersionText($version2)) {
            return 0;
        }
        $result = version_compare((string) $version1, (string) $version2);
        return $result > 0 ? 1 : ($result < 0 ? -1 : 0);
    }
}
