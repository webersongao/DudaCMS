<?php

namespace app\validate;

use think\Validate;
use think\facade\Cache;

use app\model\Users;
use app\common\service\SmsService;
use app\common\library\helper\SiteConfigHelper;

class User extends Validate
{
    protected $rule = [
        'mobile' => 'require|checkMobile',
        'username'  => 'require|checkUsername',
        'password'   => 'require|checkPassword',
        're_password' => 'require|confirm:password',
        'code' => 'require|checkCode',
    ];

    protected $message = [
        'mobile.require' => '请输入手机号码',
        'code.require' => '请输入短信验证码',
        'username.require' => '用户名不能为空',
        'password.require'     => '密码不能为空',
        're_password' => '再次输入的密码不一致'
    ];

    // 验证场景
    protected $scene = [
        'register' => ['username', 'password'],
        'password' => ['password', 're_password'],
        'mobile_reset_password' => ['mobile', 'password', 're_password', 'code']
    ];

    // 手机号检查
    public function checkMobile($value, $rule, $data = [])
    {
        if (!preg_match('/^1[3-9]\d{9}$/', $value)) return '请输入正确的手机号码';
        if (!Users::checkUserExist($value)) return '未找到使用该手机号的用户';

        return true;
    }

    // 验证码检查
    public function checkCode($value, $rule, $data = [])
    {
        if (!SmsService::verify($data['mobile'], $value)) {
            return '短信验证码不正确';
        }
        // 验证码一次性使用，防止重放
        Cache::set(SmsService::cacheKey((string) $data['mobile']), null);
        return true;
    }

    // 用户名检查
    protected function checkUsername($value, $rule, $data = [])
    {
        $min = (int) SiteConfigHelper::account('username_min_length', 2);
        $max = (int) SiteConfigHelper::account('username_max_length', 15);

        $len = mb_strlen($value);
        if ($len < $min || $len > $max) {
            return "用户名长度需为 {$min} - {$max} 个字符";
        }
        // 验证用户名是否只包含字母、数字和下划线
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_]*$/', $value)) {
            return '用户名只能包含字母、数字和下划线，且不能以数字或下划线以外的字符开头';
        }

        if (Users::checkUserExist($value)) return '该用户名已被使用，请换一个试试';

        return true;
    }

    // 密码检查
    protected function checkPassword($value, $rule, $data = [])
    {
        $min = (int) SiteConfigHelper::account('password_min_length', 8);
        $max = (int) SiteConfigHelper::account('password_max_length', 30);

        $len = mb_strlen($value);
        if ($len < $min || $len > $max) {
            return "密码长度需为 {$min} - {$max} 位";
        }

        $types = (array) SiteConfigHelper::account('password_types', []);
        if (!$types) {
            return true;
        }
        $hints = [];
        if (in_array('number', $types, true)) {
            if (!preg_match('/[0-9]/', $value)) {
                $hints[] = '数字';
            }
        }
        if (in_array('letter', $types, true)) {
            if (!preg_match('/[a-zA-Z]/', $value)) {
                $hints[] = '字母';
            }
        }
        if (in_array('special', $types, true)) {
            if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
                $hints[] = '特殊符号';
            }
        }
        if ($hints) {
            return '密码需包含' . implode('、', $hints);
        }

        return true;
    }
}
