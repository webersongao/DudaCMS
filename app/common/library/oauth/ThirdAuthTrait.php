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

use app\common\library\helper\ThirdAuthHelper;
use app\model\Users;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * 第三方登录控制器共用逻辑（前台与移动端）。
 */
trait ThirdAuthTrait
{
    /** @var Application */
    protected $application;

    /** @var array */
    protected $config = [];

    protected function initThirdAuth(): void
    {
        $config = \app\common\library\helper\ThirdAuthHelper::config();
        $this->config = is_array($config) ? $config : [];
        $this->application = new Application($this->config);
    }

    /**
     * 平台白名单校验，杜绝非法下标与空平台。
     */
    protected function safePlatform($platform): string
    {
        $platform = is_string($platform) ? strtolower(trim($platform)) : '';
        return $this->application->has($platform) ? $platform : '';
    }

    /**
     * 回跳地址只允许站内相对路径，避免开放重定向。
     */
    protected function safeReturnUrl($url): string
    {
        $url = is_string($url) ? trim($url) : '';
        if ($url !== '' && strpos($url, '/') === 0 && strpos($url, '//') !== 0 && strpos($url, '\\') === false) {
            return $url;
        }
        if ($url !== '') {
            $host = (string) request()->host();
            $parts = parse_url($url);
            if (is_array($parts) && empty($parts['scheme']) && empty($parts['host'])) {
                return $url;
            }
            if (is_array($parts) && isset($parts['host']) && hash_equals($host, (string) $parts['host'])) {
                $path = (string) ($parts['path'] ?? '/');
                return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
            }
        }
        return (string) url('/');
    }

    /**
     * 发起授权。
     */
    public function connect()
    {
        $platform = $this->safePlatform($this->request->param('platform'));
        if ($platform === '') {
            $this->error('无效的登录方式');
        }
        if (!ThirdAuthHelper::enabled($platform)) {
            $this->error('该登录方式未启用');
        }

        $returnUrl = $this->safeReturnUrl($this->request->param('url', $this->request->server('HTTP_REFERER', '')));
        session('third_redirect_url', $returnUrl);

        $authorizeUrl = $this->application->{$platform}->getAuthorizeUrl();
        if (!$authorizeUrl) {
            $this->error('该登录方式尚未配置完成');
        }
        return redirect($authorizeUrl);
    }

    /**
     * 授权回调。
     */
    public function callback()
    {
        $platform = $this->safePlatform($this->request->param('platform'));
        $returnUrl = $this->safeReturnUrl(session('third_redirect_url'));
        if ($platform === '') {
            $this->error('无效的登录方式', $returnUrl);
        }
        if (!ThirdAuthHelper::enabled($platform)) {
            $this->error('该登录方式未启用', $returnUrl);
        }
        session('third_redirect_url', null);

        $userInfo = $this->application->{$platform}->getUserInfo();
        if (!is_array($userInfo) || empty($userInfo['openid'])) {
            \think\facade\Log::write(
                '[oauth_callback] userinfo_empty platform=' . $platform,
                'error'
            );
            $this->error('第三方授权失败，请重试', $returnUrl);
        }
        $state = (string) $this->request->param('state', '');
        $userInfo['token'] = '';

        if (Service::connect($platform, $userInfo)) {
            if ($platform === 'wechat' && $state !== '' && session('wechat_login_token') === $state) {
                $uid = (int) db('oauth')->where([
                    'platform' => 'wechat',
                    'openid'   => (string) $userInfo['openid'],
                ])->value('uid');
                if ($uid > 0) {
                    cache('wechat_login_' . $state, $uid, 300);
                }
            }
            \think\facade\Log::write(
                '[oauth_callback] connect_ok platform=' . $platform . ' openid_hash=' . substr(md5((string) $userInfo['openid']), 0, 8),
                'error'
            );
            return redirect($returnUrl);
        }
        \think\facade\Log::write(
            '[oauth_callback] connect_false platform=' . $platform . ' openid_hash=' . substr(md5((string) $userInfo['openid']), 0, 8),
            'error'
        );
        $this->error('登录失败，请稍后重试', $returnUrl);
    }

    /**
     * 微信扫码登录页。
     */
    public function qrcode()
    {
        $wechat = $this->application->wechat;
        if (!$wechat) {
            $this->error('微信登录尚未配置完成');
        }
        try {
            $token = bin2hex(random_bytes(16));
            session('third_state_wechat', $token);
            session('wechat_login_token', $token);
            cache('wechat_login_' . $token, 0, 300);
            $url = $wechat->getAuthorizeUrl();
            $qrCode = new QrCode($url);
            $result = (new PngWriter())->write($qrCode);
            $this->assign(['img' => $result->getDataUri(), 'token' => $token]);
            return $this->fetch();
        } catch (\Throwable $e) {
            $this->error('二维码生成失败，请稍后重试');
        }
    }

    /**
     * 微信扫码登录轮询。
     */
    public function wechat_login()
    {
        $token = (string) $this->request->get('token', '');
        if ($token === '' || session('wechat_login_token') !== $token) {
            $this->ajaxResult(['code' => 0]);
        }
        $uid = (int) cache('wechat_login_' . $token, 0);
        if ($uid <= 0) {
            $this->ajaxResult(['code' => 0]);
        }
        $returnUrl = $this->safeReturnUrl(session('third_redirect_url') ?: $this->returnUrl);
        $ok = Users::directLogin($uid);
        cache('wechat_login_' . $token, null);
        session('wechat_login_token', null);
        if ($ok) {
            $this->ajaxResult(['code' => 1, 'returnUrl' => $returnUrl]);
        }
        $this->ajaxResult(['code' => 0]);
    }

}
