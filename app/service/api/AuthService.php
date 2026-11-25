<?php
namespace app\service\api;

use app\api\v1\ApiException;
use app\api\v1\TokenService;
use app\model\Users;

class AuthService
{
    public function login(string $account, string $password): array
    {
        $account = trim($account);
        if ($account === '' || $password === '') {
            throw new ApiException(1004, '账号和密码不能为空', 422);
        }
        RateLimitService::byIp('login', 20, 60);

        $user = Users::authenticate($account, $password);
        if (!$user) {
            throw new ApiException(2001, Users::getError() ?: '账号或密码错误');
        }

        return $this->tokenResult((int) $user['uid']);
    }

    public function register(string $username, string $password, string $email = ''): array
    {
        $username = trim($username);
        RateLimitService::byIp('register', 5, 300);
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_]{2,19}$/', $username)) {
            throw new ApiException(1004, '用户名需为 3-20 位字母、数字或下划线', 422);
        }
        if (mb_strlen($password) < 6) {
            throw new ApiException(1004, '密码至少 6 位', 422);
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ApiException(1004, '邮箱格式不正确', 422);
        }

        $extend = [];
        if ($email !== '') {
            $extend['email'] = $email;
        }

        $uid = Users::registerUser($username, $password, $extend, false, 'app');
        if (!$uid) {
            throw new ApiException(2001, Users::getError() ?: '注册失败');
        }

        return $this->tokenResult((int) $uid);
    }

    public function me(int $uid): array
    {
        $user = Users::getUserInfo($uid);
        if (!$user) {
            throw new ApiException(1001, '用户不存在', 401);
        }
        return Presenter::user($user, true);
    }

    private function tokenResult(int $uid): array
    {
        $user = Users::getUserInfo($uid);
        if (!$user) {
            throw new ApiException(5000, '用户信息读取失败', 500);
        }

        return array_merge(TokenService::issue($uid), [
            'user' => Presenter::user($user, true),
        ]);
    }
}
