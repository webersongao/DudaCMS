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
namespace app\common\library\helper;

/**
 * 第三方登录系统配置。
 *
 * 配置项统一保存在 dask_config 表，通过后台「系统配置 → 第三方登录」维护；
 * 这里把扁平的数据库键转换成 OAuth SDK 使用的分组结构。
 */
class ThirdAuthHelper
{
    /** 支持的平台白名单 */
    public const PLATFORMS = ['qq', 'wechat', 'weibo', 'github', 'google', 'twitter'];

    /** 各平台默认授权范围（数据库值为空时回退） */
    private const DEFAULT_SCOPES = [
        'qq'      => 'get_user_info',
        'wechat'  => 'snsapi_userinfo',
        'weibo'   => '',
        'github'  => 'read:user user:email',
        'google'  => 'openid email profile',
        'twitter' => 'tweet.read users.read offline.access',
    ];

    /**
     * 返回与旧插件 config 相同结构的配置数组，保证 SDK 逻辑无需分支。
     */
    public static function config(): array
    {
        // 用 null 作默认值，区分「配置项不存在」与「后台明确全部取消勾选」。
        // getConfigs 会把空字符串/空数组替换为非 null 的默认值；若这里继续传
        // 'qq,wechat,weibo'，全部关闭会被误判为默认开启，前台仍能看见三个按钮。
        $enable = get_setting('third_enable', null);
        $enabled = [];
        if ($enable === null) {
            // 配置行不存在（如旧库升级未完成）时才回退默认。
            $enable = 'qq,wechat,weibo';
        }
        if (is_array($enable)) {
            $enabled = array_values(array_filter(array_map('strval', $enable), 'strlen'));
        } elseif (is_string($enable)) {
            $enabled = array_values(array_filter(array_map('trim', explode(',', $enable)), 'strlen'));
        }
        $enabled = array_values(array_intersect($enabled, self::PLATFORMS));

        $config = [
            'base' => [
                'enable' => $enabled,
            ],
        ];

        foreach (self::PLATFORMS as $platform) {
            $config[$platform] = [
                'app_id'     => (string) get_setting('third_' . $platform . '_app_id', ''),
                'app_secret' => (string) get_setting('third_' . $platform . '_app_secret', ''),
                'scope'      => (string) (get_setting('third_' . $platform . '_scope', self::DEFAULT_SCOPES[$platform]) ?: self::DEFAULT_SCOPES[$platform]),
            ];
        }

        return $config;
    }

    /**
     * 平台是否在启用列表中。
     */
    public static function enabled(string $platform): bool
    {
        return in_array($platform, self::config()['base']['enable'], true);
    }

    /**
     * 是否至少启用了一个平台。
     */
    public static function enabledAny(): bool
    {
        return !empty(self::config()['base']['enable']);
    }
}
