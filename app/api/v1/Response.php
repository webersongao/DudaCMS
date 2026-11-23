<?php
namespace app\api\v1;

use think\Response as ThinkResponse;

class Response
{
    public static function requestId(): string
    {
        $rid = $_SERVER['HTTP_X_REQUEST_ID'] ?? '';
        if (!$rid) {
            $rid = bin2hex(random_bytes(8));
        }
        return preg_replace('/[^A-Za-z0-9._-]/', '', $rid) ?: bin2hex(random_bytes(8));
    }

    public static function ok($data = [], string $message = 'ok', int $httpStatus = 200): ThinkResponse
    {
        return json([
            'code' => 0,
            'message' => $message,
            'data' => $data,
            'request_id' => self::requestId(),
            'timestamp' => time(),
        ], $httpStatus);
    }

    public static function error(int $code, string $message, int $httpStatus = 200, $data = null): ThinkResponse
    {
        return json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'request_id' => self::requestId(),
            'timestamp' => time(),
        ], $httpStatus);
    }
}
