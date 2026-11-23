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

use app\common\library\helper\RandomHelper;
use app\common\library\helper\ThirdAuthHelper;
use app\model\Users;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use app\model\Third;

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
            $this->error('第三方授权失败，请重试', $returnUrl);
        }
        $userInfo['token'] = (string) $this->request->get('token', '');
        session($platform . '-user-info', $userInfo);

        // 需要绑定账号时进入确认页
        if (($this->config['base']['bind_account'] ?? 1)) {
            return redirect((string) url('ThirdAuth/prepare', ['platform' => $platform, 'url' => $returnUrl]));
        }

        if (Service::connect($platform, $userInfo)) {
            return redirect($returnUrl);
        }
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
            $token = RandomHelper::alnum();
            $url = $wechat->getAuthorizeUrl($token);
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
        if ($token === '') {
            $this->ajaxResult(['code' => 0]);
        }
        $third = db('oauth')->where(['token' => $token])->find();
        if (!$third) {
            $this->ajaxResult(['code' => 0]);
        }
        $returnUrl = $this->safeReturnUrl(session('third_redirect_url') ?: $this->returnUrl);
        if ($third['uid'] && Users::directLogin($third['uid'])) {
            $this->ajaxResult(['code' => 1, 'returnUrl' => $returnUrl]);
        }
        $this->ajaxResult(['code' => 0]);
    }

    /**
     * 准备绑定 / 注册确认。
     */
    public function prepare()
    {
        $platform = $this->safePlatform($this->request->param('platform'));
        $returnUrl = $this->safeReturnUrl($this->request->param('url'));
        if ($platform === '') {
            $this->error('无效的登录方式', $returnUrl);
        }

        if ($this->user_id) {
            return redirect((string) url('ThirdAuth/bind', ['platform' => $platform, 'url' => $returnUrl]));
        }

        $userInfo = session($platform . '-user-info');
        if (is_array($userInfo) && !empty($userInfo['openid'])) {
            $third = db('oauth')->where(['platform' => $platform, 'openid' => $userInfo['openid']])->find();
            if ($third && $third['uid'] && Service::connect($platform, $userInfo)) {
                return redirect($returnUrl);
            }
        }

        $this->assign('url', $returnUrl);
        $this->assign('platform', $platform);
        $this->assign('bind_url', (string) url('ThirdAuth/bind', ['platform' => $platform, 'url' => $returnUrl]));
        return $this->fetch();
    }

    /**
     * 绑定第三方账号到当前用户。
     */
    public function bind()
    {
        $platform = $this->safePlatform($this->request->param('platform'));
        $returnUrl = $this->safeReturnUrl($this->request->param('url', $this->request->server('HTTP_REFERER', '')));
        if ($platform === '') {
            $this->error('无效的登录方式', $returnUrl);
        }

        $userInfo = session($platform . '-user-info');
        if (!is_array($userInfo) || empty($userInfo['openid'])) {
            return redirect((string) url('ThirdAuth/connect', ['platform' => $platform, 'url' => $returnUrl]));
        }

        if ($this->user_id && db('oauth')->where('uid', $this->user_id)->where('platform', $platform)->find()) {
            $this->error('已绑定该平台账号，请勿重复绑定', $returnUrl);
        }

        if (!$this->user_id) {
            if (Service::connect($platform, $userInfo)) {
                return redirect($returnUrl);
            }
            $this->error('账号绑定失败，请重试', $returnUrl);
        }

        $time = time();
        $values = [
            'platform'      => $platform,
            'uid'           => $this->user_id,
            'openid'        => $userInfo['openid'],
            'open_username' => $userInfo['user_info']['nickname'] ?? '',
            'access_token'  => $userInfo['access_token'] ?? '',
            'refresh_token' => $userInfo['refresh_token'] ?? '',
            'expires_in'    => (int) ($userInfo['expires_in'] ?? 0),
            'login_time'    => $time,
            'expire_time'   => $time + (int) ($userInfo['expires_in'] ?? 0),
            'token'         => $userInfo['token'] ?? '',
        ];

        try {
            $id = db('oauth')->where(['openid' => $values['openid'], 'platform' => $platform])->value('id');
            if ($id) {
                db('oauth')->where(['id' => $id])->update($values);
            } else {
                Third::create($values);
            }
            $this->success('账号绑定成功', $returnUrl);
        } catch (\Throwable $e) {
            $this->error('账号绑定失败，请重试', $returnUrl);
        }
    }

    /**
     * 解绑第三方账号。
     */
    public function unbind()
    {
        $platform = $this->safePlatform($this->request->param('platform'));
        if ($platform === '') {
            $this->error('无效的登录方式');
        }
        $third = Third::where('uid', $this->user_id)->where('platform', $platform)->find();
        if (!$third) {
            $this->error('未找到指定的账号绑定信息');
        }
        $third->delete();
        $this->success('账号解绑成功');
    }
}
