<?php
namespace app\api\v1;

use app\model\Users;
use think\App;
use think\Request;

abstract class Base
{
    protected App $app;
    protected Request $request;
    protected int $uid = 0;
    protected ?array $tokenPayload = null;
    protected ?array $user = null;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app->request;
        $this->parseToken();
    }

    protected function parseToken(): void
    {
        $header = (string) $this->request->header('Authorization', '');
        if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $m)) {
            $payload = TokenService::parse(trim($m[1]));
            if ($payload) {
                $uid = (int) $payload['sub'];
                $status = (int) \think\facade\Db::name('users')->where('uid', $uid)->value('status');
                if ($status === 1) {
                    $this->tokenPayload = $payload;
                    $this->uid = $uid;
                }
            }
        }
    }

    protected function requireLogin(): void
    {
        if (!$this->uid) {
            throw new ApiException(1001, '请先登录', 401);
        }
    }

    protected function user(): array
    {
        $this->requireLogin();
        if ($this->user === null) {
            $info = Users::getUserInfo($this->uid);
            if (!$info) {
                throw new ApiException(1001, '登录状态已失效', 401);
            }
            $this->user = $info;
        }
        return $this->user;
    }

    protected function input(): array
    {
        $data = $this->request->param();
        if (is_array($data) && $data) {
            return $data;
        }

        // 兼容 application/json 请求体
        $contentType = (string) $this->request->header('Content-Type', '');
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $json = json_decode((string) $raw, true);
            if (is_array($json)) {
                return $json;
            }
        }

        return is_array($data) ? $data : [];
    }

    protected function intParam(string $name, int $default = 0): int
    {
        return (int) $this->request->param($name, $default);
    }

    protected function stringParam(string $name, string $default = ''): string
    {
        $value = $this->request->param($name, $default);
        return is_string($value) ? trim($value) : $default;
    }

    protected function pagination(int $page, int $pageSize, int $totalPages, bool $hasMore): array
    {
        return [
            'page' => $page,
            'page_size' => $pageSize,
            'total_pages' => $totalPages,
            'has_more' => $hasMore,
        ];
    }
}
