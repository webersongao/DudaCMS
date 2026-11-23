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

    /**
     * 获取配置
     * @param string $name
     * @param mixed $default
     * @param bool $update
     * @return mixed
     */
    public static function getConfigs(string $name = '', $default = null, $update = false)
    {
        static $config = [];
        if (empty($config) || $update) {
            foreach (db('config')->select() as $k => $v) {
                if (in_array($v['type'], ['select', 'checkbox', 'images', 'files'])) {
                    $v['value'] = explode(',', $v['value']);
                }
                if ($v['type'] === 'array') {
                    $v['value'] = json_decode($v['option'], true);
                }

                if (in_array($v['type'], ['text', 'textarea'])) {
                    $v['value'] = htmlspecialchars_decode($v['value']);
                }

                $config[$v['name']] = $v['value'];
            }
        }
        if ($name !== '' && isset($config[$name]) && !$config[$name] && $default) {
            $config[$name] = $default;
        }
        return $name ? ($config[$name] ?? $default) : $config;
    }
}
