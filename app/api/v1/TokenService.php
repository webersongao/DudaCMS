<?php
namespace app\api\v1;

class TokenService
{
    public static function secret(): string
    {
        $secret = (string) env('api.jwt_secret', '');
        if ($secret !== '') {
            return $secret;
        }

        // 未配置时使用站点密钥 + 数据库信息派生，保证不同站点不一致
        return hash('sha256', implode('|', [
            (string) config('app.token.key'),
            (string) config('database.connections.mysql.hostname'),
            (string) config('database.connections.mysql.database'),
        ]));
    }

    public static function issue(int $uid, int $ttl = 604800): array
    {
        $now = time();
        $payload = [
            'iss' => 'dudacms',
            'sub' => (string) $uid,
            'iat' => $now,
            'exp' => $now + $ttl,
            'jti' => bin2hex(random_bytes(12)),
        ];

        $segments = [
            self::b64(json_encode(['typ' => 'JWT', 'alg' => 'HS256'], JSON_UNESCAPED_SLASHES)),
            self::b64(json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];
        $signing = implode('.', $segments);
        $segments[] = self::b64(hash_hmac('sha256', $signing, self::secret(), true));

        return [
            'access_token' => implode('.', $segments),
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'expires_at' => $now + $ttl,
        ];
    }

    public static function parse(?string $token): ?array
    {
        if (!$token || substr_count($token, '.') !== 2) {
            return null;
        }

        [$h, $p, $s] = explode('.', $token);
        $header = json_decode(self::unb64($h), true);
        if (!is_array($header) || ($header['alg'] ?? '') !== 'HS256') {
            return null;
        }

        $expected = self::b64(hash_hmac('sha256', $h . '.' . $p, self::secret(), true));
        if (!hash_equals($expected, $s)) {
            return null;
        }

        $payload = json_decode(self::unb64($p), true);
        if (!is_array($payload) || empty($payload['sub']) || empty($payload['exp'])) {
            return null;
        }
        if ((int) $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function unb64(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
