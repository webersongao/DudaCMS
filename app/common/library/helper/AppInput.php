<?php
// +----------------------------------------------------------------------
// | DudaCMS 统一输入层：请求参数类型化、白名单与安全过滤。
// +----------------------------------------------------------------------

namespace app\common\library\helper;

/**
 * 所有控制器读取请求参数时应优先使用本助手。
 */
class AppInput
{
    /** 从 request()->param() 安全读取整数。 */
    public static function int(string $name, int $default = 0, ?int $min = null, ?int $max = null): int
    {
        $value = request()->param($name, $default);
        return self::normalizeInt($value, $default, $min, $max);
    }

    public static function normalizeInt($value, int $default = 0, ?int $min = null, ?int $max = null): int
    {
        if (is_bool($value)) {
            $result = $value ? 1 : 0;
        } elseif (is_numeric($value)) {
            $result = (int) $value;
        } else {
            $result = $default;
        }
        if ($min !== null) $result = max($min, $result);
        if ($max !== null) $result = min($max, $result);
        return $result;
    }

    /** 字符串：默认 trim + 去控制字符 + 截断。 */
    public static function string(string $name, string $default = '', int $maxLength = 255): string
    {
        return self::normalizeString(request()->param($name, $default), $default, $maxLength);
    }

    public static function normalizeString($value, string $default = '', int $maxLength = 255): string
    {
        if (!is_scalar($value)) {
            return $default;
        }
        $value = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $value) ?? '');
        return $maxLength > 0 ? mb_substr($value, 0, $maxLength) : $value;
    }

    /** 兼容 Y/N、1/0、true/false 的布尔值。 */
    public static function bool(string $name, bool $default = false): bool
    {
        return self::normalizeBool(request()->param($name, null), $default);
    }

    public static function normalizeBool($value, bool $default = false): bool
    {
        if ($value === null || $value === '') return $default;
        if (is_bool($value)) return $value;
        $value = strtolower(trim((string) $value));
        if (in_array($value, ['y', '1', 'true', 'on', 'yes'], true)) return true;
        if (in_array($value, ['n', '0', 'false', 'off', 'no'], true)) return false;
        return $default;
    }

    /** 数组：可选键白名单与逐项归一化。 */
    public static function array(string $name, array $allowedKeys = [], ?callable $normalizer = null): array
    {
        $value = request()->param($name, []);
        return self::normalizeArray($value, $allowedKeys, $normalizer);
    }

    public static function normalizeArray($value, array $allowedKeys = [], ?callable $normalizer = null): array
    {
        if (!is_array($value)) return [];
        if ($allowedKeys) $value = array_intersect_key($value, array_flip($allowedKeys));
        if ($normalizer) $value = array_map($normalizer, $value);
        return $value;
    }

    /** JSON 字符串安全解析。 */
    public static function json(string $name, $default = null)
    {
        $value = request()->param($name, null);
        if (!is_string($value) || $value === '') return $default;
        $result = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $result : $default;
    }

    /** 富文本入库前统一净化。 */
    public static function richText(string $name, int $maxLength = 200000): string
    {
        return HtmlHelper::purify(self::string($name, '', $maxLength));
    }

    /** 邮箱：合法小写地址，否则空串。 */
    public static function email(string $name): string
    {
        $email = strtolower(self::string($name, '', 255));
        return MailHelper::isEmail($email) ? $email : '';
    }

    /** 站内 URL：仅允许白名单协议。 */
    public static function url(string $name, string $fallback = ''): string
    {
        $value = self::string($name, '', 1000);
        return PresentationHelper::safeUrl($value, $fallback);
    }
}
