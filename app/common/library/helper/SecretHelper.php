<?php
// +----------------------------------------------------------------------
// | DudaCMS 敏感字段加解密：AES-256-GCM，密钥由应用密钥派生，无需额外运维配置。
// +----------------------------------------------------------------------

namespace app\common\library\helper;

class SecretHelper
{
    private const PREFIX = 'enc:v1:';

    private static function key(): string
    {
        $material = (string) env('dask_secret_key', '');
        if ($material === '') {
            $appKey = \think\facade\Config::get('app.token.key', '');
            $material = $appKey . '|duda-cms-field-encryption';
        }
        return hash('sha256', $material, true);
    }

    public static function encrypt($value): string
    {
        $value = (string) $value;
        if ($value === '' || str_starts_with($value, self::PREFIX)) {
            return $value;
        }
        if (!function_exists('openssl_encrypt')) {
            return 'plain:v1:' . $value;
        }
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($value, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            return 'plain:v1:' . $value;
        }
        return self::PREFIX . base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt($value): string
    {
        $value = (string) $value;
        if (str_starts_with($value, 'plain:v1:')) {
            return substr($value, strlen('plain:v1:'));
        }
        if (!str_starts_with($value, self::PREFIX) || !function_exists('openssl_decrypt')) {
            return $value;
        }
        $raw = base64_decode(substr($value, strlen(self::PREFIX)), true);
        if ($raw === false || strlen($raw) < 28) {
            return '';
        }
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        return $plain === false ? '' : $plain;
    }
}
