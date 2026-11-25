<?php
// +----------------------------------------------------------------------
// | DudaCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2026 https://ask.btbk.org
// +----------------------------------------------------------------------
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统，打造私有社交化问答、内部知识存储
// +----------------------------------------------------------------------
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------
namespace app\model;

/**
 * 配置管理模型
 * Class Config
 * @package app\model
 */
class Config extends BaseModel
{
    protected $name = 'config';

    /** 敏感配置：后台表单不回显明文，读取时允许用环境变量覆盖 */
    public const SECRET_KEYS = [
        'email_password', 'search_engine_password',
        'third_qq_app_secret', 'third_wechat_app_secret', 'third_weibo_app_secret',
        'third_github_app_secret', 'third_google_app_secret', 'third_twitter_app_secret',
        'sms_ali_access_key_secret', 'sms_tencent_secret_id', 'sms_tencent_access_key_secret',
    ];

    /**
     * 获取配置。
     *
     * 修正点：
     * - array/images/files 从 option(JSON) 读取，修复 images/files 被错误 explode('0') 的问题；
     * - 合法假值（0 / '0' / ''）不再被默认值吞掉；
     * - 敏感项支持环境变量 DASK_<KEY> 覆盖，避免密钥明文入库。
     */
    public static function getConfigs(string $name = '', $default = null, $update = false)
    {
        static $config = [];
        if (empty($config) || $update) {
            $config = [];
            foreach (db('config')->select() as $v) {
                if (in_array($v['type'], ['array', 'images', 'files'], true)) {
                    $decoded = json_decode((string) $v['option'], true);
                    $v['value'] = is_array($decoded)
                        ? $decoded
                        : ((string) $v['value'] === '' ? [] : explode(',', (string) $v['value']));
                } elseif (in_array($v['type'], ['select', 'checkbox'], true)) {
                    $v['value'] = (string) $v['value'] === '' ? [] : explode(',', (string) $v['value']);
                } elseif (in_array($v['type'], ['text', 'textarea', 'editor', 'code', 'html'], true)) {
                    $v['value'] = htmlspecialchars_decode((string) $v['value']);
                }
                $config[$v['name']] = $v['value'];
            }
            foreach (self::SECRET_KEYS as $secret) {
                $env = env('dask_' . $secret, null);
                if ($env !== null && $env !== '') {
                    $config[$secret] = (string) $env;
                }
            }
        }
        if ($name === '') {
            return $config;
        }
        if (!array_key_exists($name, $config)) {
            return $default;
        }
        $value = $config[$name];
        if (($value === '' || $value === null || $value === []) && $default !== null) {
            return $default;
        }
        return $value;
    }

    /** 编程式写入配置（自动失效进程内缓存） */
    public static function setConfig(string $name, $value): bool
    {
        $row = db('config')->where('name', $name)->find();
        if (!$row) {
            return false;
        }
        if (in_array($row['type'], ['array', 'images', 'files'], true)) {
            $items = is_array($value) ? array_values($value) : [];
            $data = [
                'value'  => 0,
                'option' => json_encode($items, JSON_UNESCAPED_UNICODE),
            ];
        } elseif (is_array($value)) {
            $data = ['value' => implode(',', $value)];
        } else {
            $data = ['value' => (string) $value];
        }
        db('config')->where('name', $name)->update($data);
        self::flush();
        return true;
    }

    /** 显式失效配置缓存（保存后必须调用） */
    public static function flush(): void
    {
        self::getConfigs('', null, true);
    }
}
