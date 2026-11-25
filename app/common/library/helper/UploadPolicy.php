<?php
// +----------------------------------------------------------------------
// | DudaCMS 统一上传策略：路径白名单、目录与文件名生成、类型归一。
// +----------------------------------------------------------------------

namespace app\common\library\helper;

class UploadPolicy
{
    /** 允许的业务目录；禁止 .. 与任何未声明路径。 */
    public const PATHS = [
        'common', 'avatar', 'question', 'answer', 'article', 'topic', 'column',
        'feature', 'help', 'inbox', 'form', 'editor', 'noise',
    ];

    public static function normalizePath($path, string $default = 'common'): string
    {
        $path = strtolower(trim((string) $path));
        $path = str_replace(['..', '\\', '/'], '', $path);
        return in_array($path, self::PATHS, true) ? $path : $default;
    }

    /** img/image 统一为 img；其余文件统一为 file。 */
    public static function normalizeType($type): string
    {
        $type = strtolower(trim((string) $type));
        return in_array($type, ['img', 'image'], true) ? 'img' : 'file';
    }

    /** 统一相对目录：业务目录/YYYYMMDD。 */
    public static function relativeDir(string $path): string
    {
        return self::normalizePath($path) . '/' . date('Ymd');
    }

    /** 统一随机文件名；保留小写安全扩展名。 */
    public static function fileName(string $original, string $fallbackExt = 'bin'): string
    {
        $original = str_replace('\\', '/', basename($original));
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $ext = preg_match('/^[a-z0-9]{1,10}$/', $ext) ? $ext : $fallbackExt;
        return bin2hex(random_bytes(16)) . '.' . $ext;
    }

    /** 缩略图文件名统一规则。 */
    public static function thumbName(string $file): string
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $base = $ext !== '' ? substr($file, 0, -(strlen($ext) + 1)) : $file;
        return $base . '.thumb.' . $ext;
    }

    /** 服务端签发附件批次凭证，并绑定到当前用户。 */
    public static function issueAccessKey(int $uid): string
    {
        $key = bin2hex(random_bytes(16));
        $keys = \think\facade\Session::get('upload.access_keys', []);
        $keys = is_array($keys) ? $keys : [];
        foreach ($keys as $savedKey => $meta) {
            if (($meta['time'] ?? 0) < time() - 86400) {
                unset($keys[$savedKey]);
            }
        }
        if (count($keys) >= 20) {
            array_shift($keys);
        }
        $keys[$key] = ['uid' => $uid, 'time' => time()];
        session('upload.access_keys', $keys);
        return $key;
    }

    /** 校验凭证是否由服务端签发且属于当前用户。 */
    public static function verifyAccessKey(string $key, int $uid): bool
    {
        if ($key === '' || $uid <= 0) {
            return false;
        }
        $keys = \think\facade\Session::get('upload.access_keys', []);
        if (!is_array($keys) || !isset($keys[$key])) {
            return false;
        }
        $meta = $keys[$key];
        return (int) ($meta['uid'] ?? 0) === $uid && (int) ($meta['time'] ?? 0) >= time() - 86400;
    }
}
