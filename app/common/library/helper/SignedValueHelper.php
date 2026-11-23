<?php

namespace app\common\library\helper;

/**
 * 通用 HMAC-SHA256 签名值：替代旧 authCode() 的可逆混淆。
 *
 * 签名值与业务上下文绑定，密钥来自安装期生成的 app.token.key；
 * 输出格式为 base64url(payload|expire).hex(hmac)，超时或篡改一律拒绝。
 */
final class SignedValueHelper
{
    public static function sign(string $context, string $payload, int $ttl = 0): string
    {
        $expire = $ttl > 0 ? time() + $ttl : 0;
        $data = $payload . '|' . $expire;
        $body = rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        return $body . '.' . self::mac($context, $data);
    }

    public static function verify(string $context, string $token): ?string
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || $parts[0] === '' || !preg_match('/^[0-9a-f]{64}$/', $parts[1])) {
            return null;
        }
        $data = base64_decode(strtr($parts[0], '-_', '+/'), true);
        if ($data === false) {
            return null;
        }
        if (!hash_equals(self::mac($context, $data), $parts[1])) {
            return null;
        }
        $sep = strrpos($data, '|');
        if ($sep === false) {
            return null;
        }
        $expire = (int) substr($data, $sep + 1);
        if ($expire > 0 && $expire <= time()) {
            return null;
        }
        return substr($data, 0, $sep);
    }

    private static function mac(string $context, string $data): string
    {
        return hash_hmac('sha256', $context . "\0" . $data, (string) config('app.token.key'));
    }
}
