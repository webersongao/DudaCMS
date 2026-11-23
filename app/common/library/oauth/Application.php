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

namespace app\common\library\oauth;

class Application
{

    /**
     * 配置信息
     * @var array
     */
    private $config = [];

    /**
     * 服务提供者
     * @var array
     */
    private $providers = [
        'qq'      => 'Qq',
        'weibo'   => 'Weibo',
        'wechat'  => 'Wechat',
        'github'  => 'Github',
        'google'  => 'Google',
        'twitter' => 'Twitter',
    ];

    /**
     * 服务对象信息
     * @var array
     */
    protected $services = [];

    public function __construct($options = [])
    {
        $options = is_array($options) ? $options : [];
        $options = array_intersect_key($options, $this->providers);
        $this->config = array_merge($this->config, $options);
        //注册服务器提供者
        $this->registerProviders();
    }

    /**
     * 平台是否可用（避免对非法平台直接取值触发错误）。
     */
    public function has(string $platform): bool
    {
        return $platform !== '' && isset($this->providers[$platform]);
    }

    /**
     * 注册服务提供者
     */
    private function registerProviders()
    {
        foreach ($this->providers as $k => $v) {
            $this->services[$k] = function () use ($k, $v) {
                $options = $this->config;
                $options['callback'] = isset($options['callback']) && $options['callback'] ? $options['callback'] : (string) url('ThirdAuth/callback', ['platform' => $k], false, true);
                $objName = __NAMESPACE__ . "\\sdk\\{$v}";
                return new $objName($options);
            };
        }
    }

    public function __set($key, $value)
    {
        $this->services[$key] = $value;
    }

    public function __get($key)
    {
        if (!is_string($key) || !$this->has($key)) {
            return null;
        }
        return isset($this->services[$key]) ? $this->services[$key]($this) : null;
    }
}
