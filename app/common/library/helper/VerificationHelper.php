<?php

namespace app\common\library\helper;

/**
 * 身份真实性门槛：邮箱或手机号二选一验证通过，才允许发布/回答/点赞/关注等修改活动。
 *
 * 规则集中在此，避免各控制器重复实现导致遗漏：
 * - 未启用短信时，只有邮箱验证通过算通过；
 * - 启用短信时，邮箱或手机号任一验证通过即可；
 * - 验证入口、账号设置、退出等操作在白名单内，不拦截。
 */
class VerificationHelper
{
    /**
     * 验证门槛是否启用。
     * 若邮件与短信都不可用，用户无法完成验证，此时不强制门槛以免整站瘫痪。
     */
    public static function enabled(): bool
    {
        if (!config('dask.verification.enabled', true)) {
            return false;
        }
        return MailHelper::isConfigured() || SmsService::isEnabled();
    }

    /**
     * 用户是否已完成身份真实性验证。
     * @param array $user Users::getUserInfo() 返回的用户信息
     */
    public static function isVerified(array $user): bool
    {
        if (!empty($user['is_valid_email']) || !empty($user['is_valid_mobile'])) {
            return true;
        }
        return false;
    }

    /** 当前操作是否在“允许未验证访问”白名单内。 */
    public static function isAllowed(string $controller, string $action): bool
    {
        $route = strtolower($controller . '/' . $action);
        $allow = (array) config('dask.verification.allow_unverified', []);
        return in_array($route, array_map('strtolower', $allow), true);
    }

    /**
     * 给未验证用户的引导提示（区分有无可用邮箱/手机号）。
     */
    public static function guideMessage(array $user): string
    {
        $hasEmail = trim((string) ($user['email'] ?? '')) !== '';
        $hasMobile = trim((string) ($user['mobile'] ?? '')) !== '';
        if ($hasEmail) {
            return '请先验证邮箱后再进行此操作';
        }
        if ($hasMobile && SmsService::isEnabled()) {
            return '请先验证手机号后再进行此操作';
        }
        return '请先绑定并验证邮箱或手机号后再进行此操作';
    }
}
