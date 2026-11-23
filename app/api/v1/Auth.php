<?php
namespace app\api\v1;

use app\service\api\AuthService;

class Auth extends Base
{
    public function login()
    {
        $input = $this->input();
        $data = (new AuthService())->login(
            (string) ($input['account'] ?? $input['username'] ?? ''),
            (string) ($input['password'] ?? '')
        );
        return Response::ok($data, '登录成功');
    }

    public function register()
    {
        $input = $this->input();
        $data = (new AuthService())->register(
            (string) ($input['username'] ?? ''),
            (string) ($input['password'] ?? ''),
            (string) ($input['email'] ?? '')
        );
        return Response::ok($data, '注册成功');
    }

    public function me()
    {
        $this->requireLogin();
        return Response::ok((new AuthService())->me($this->uid));
    }

    public function refresh()
    {
        $this->requireLogin();
        return Response::ok(TokenService::issue($this->uid), '已刷新');
    }

    public function logout()
    {
        // JWT 无状态：客户端删除本地 token 即可；服务端保留审计入口
        return Response::ok([], '已退出');
    }
}
