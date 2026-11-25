{extend name="$theme_block" /}
{block name="header"}{/block}
{block name="main"}
<div class="appMain dask-auth-page dask-page">
    <div class="signBox">
        <div class="signBoxContent">
            {if !$_ajax_open}
            <h1 class="logo ">
                <a href="{$baseUrl}" class="text-primary"><img src="{:asset('brand/logo-bihu-white.svg', false)}" alt="{:get_setting('site_name')}"></a>
            </h1>
            {/if}
            <div class="content dask-card" id="boxMain">
                <div class="SignContainer-content dask-card__body">
                    <div class="SignContainer-inner">
                        <form action="" method="POST">
                            <div class="block mb-0">
                                <ul class="nav nav-tabs nav-tabs-block px-3 mb-1 dask-pjax-tabs dask-card__head">
                                    <li class="nav-item">
                                        <a class="nav-link {if $login_type!='mobile'}active{/if}" href="{:url('account/login?type=email')}" data-pjax="tabMain">{:L('密码登录')}</a>
                                    </li>
                                    {if sms_enabled()}
                                    <li class="nav-item">
                                        <a class="nav-link {if $login_type=='mobile' || get_setting('register_valid_type')=='mobile'}active{/if}" href="{:url('account/login?type=mobile')}" data-pjax="tabMain">{:L('手机登录')}</a>
                                    </li>
                                    {/if}
                                </ul>
                                <div class="block-content tab-content p-3 bg-white" id="tabMain">
                                    <input type="hidden" name="token" value="{:csrf_token()}" />
                                    <input name="return_url" id="returnUrl" type="hidden" value="{:base64_encode($return_url)}">
                                    {if $login_type=='mobile'}
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="mobile" name="mobile" placeholder="{:L('请输入手机号')}" data-dask-validate="required">
                                    </div>
                                    <div class="mb-3 bline d-flex dask-auth-code-row">
                                        <input type="text" class="dask-form-control flex-fill dask-auth-code-input" id="code" name="code" placeholder="{:L('请输入短信验证码')}" data-dask-validate="required">
                                        <button type="button" data-sms='#mobile' class="flex-fill dask-auth-sms-btn">{:L('获取短信验证码')}</button>
                                    </div>
                                    <div class="mb-3">
                                        <button type="button" class="btn w-100 btn-primary login-button">{:L('登录')}</button>
                                    </div>
                                    <div class="mb-3 clearfix font-size-sm dask-auth-spacer">
                                        {if $setting.remember_login_enable=="Y"}
                                        <div class="float-start">
                                            <input type="checkbox" class="remember" name="remember"> {:L('记住我')}
                                        </div>
                                        {/if}
                                        {if $setting.register_type!='close'}
                                        <a class="nav-link text-primary float-end"  data-pjax="boxMain" href="{:url('account/register')}">{:L('没有账号')}？{:L('去注册')}</a>
                                        {/if}
                                    </div>

                                    <script>
                                        $(".login-button").click(function () {
                                            var token = $("input[name='token']").val();
                                            var name = $("#mobile").val();
                                            var code = $("#code").val();
                                            var remember = $(".remember").is(':checked') ? 1 : 0;
                                            var returnUrl = $('#returnUrl').val();
                                            if ((name == null) || (name === "")) {
                                                Dask.api.error("手机号不能为空！");
                                                return false;
                                            }
                                            else if ((code == null) || (code === "")) {
                                                Dask.api.error("手机验证码不能为空！");
                                                return false;
                                            }
                                            else {
                                                // 旧接口要求表单编码并依赖响应里的 url，使用 fetch 保持原有语义
                                                fetch("{:url('account/login')}", {
                                                    method: 'POST',
                                                    credentials: 'same-origin',
                                                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                                    body: new URLSearchParams({ mobile: name, code: code, token: token, remember: remember, return_url: returnUrl })
                                                }).then(function (response) {
                                                    return response.text().then(function (text) {
                                                        try { return JSON.parse(text); }
                                                        catch (e) { return { __raw: text }; }
                                                    });
                                                }).then(function (result) {
                                                    if (result && result.__raw !== undefined) {
                                                        if (daskTrim(result.__raw) !== '') {
                                                            Dask.api.error('发生错误, 返回的信息:' + ' ' + result.__raw);
                                                        }
                                                        return;
                                                    }
                                                    if (result.code > 0) {
                                                        if (isAjaxOpen) {
                                                            parent.window.location.reload();
                                                        }
                                                        window.DaskFrontend.toast(result.msg, 'success');
                                                        window.setTimeout(function () { window.location.href = result.url; }, 1200);
                                                    } else {
                                                        Dask.api.error(result.msg, result.url);
                                                    }
                                                }).catch(function (error) {
                                                    Dask.api.error('发生错误, 返回的信息:' + ' ' + error.message);
                                                });
                                            }
                                        })
                                    </script>
                                    {else/}
                                    <input type="hidden" name="captcha" id="captcha">
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="username" name="username" placeholder="{:L('请输入手机号/邮箱/用户名')}" data-dask-validate="required">
                                    </div>
                                    <div class="mb-3 bline">
                                        <input type="password" class="dask-form-control" id="password" name="password" placeholder="{:L('请输入密码')}" data-dask-validate="required">
                                    </div>

                                    <div class="mb-3 newbut dask-auth-spacer">
                                        <button type="button" class="btn w-100 btn-primary login-button">{:L('登录')}</button>
                                    </div>

                                    <div class="mb-3 clearfix font-size-sm dask-auth-spacer">
                                        <div class="float-start">
                                            {if $setting.remember_login_enable=="Y"}
                                            <label class="me-3">
                                                <input type="checkbox" class="remember" name="remember"> {:L('记住我')}
                                            </label>
                                            {/if}
                                            <a href="{:url('account/find_password')}" target="_blank" class="text-muted font-9">{:L('忘记密码')}？</a>
                                        </div>
                                        {if $setting.register_type!='close'}
                                        <a class="nav-link text-primary float-end"  data-pjax="boxMain" href="{:url('account/register')}">{:L('没有账号')}？{:L('去注册')}</a>
                                        {/if}
                                    </div>

                                    <script>
                                        $(".login-button").click(function () {
                                            {if $setting.enable_frontend_captcha=='Y'}
                                            $('#captcha').captcha({
                                                callback:function (){
                                                    var token = $("input[name='token']").val();
                                                    var name = $("#username").val();
                                                    var password = $("#password").val();
                                                    var remember = $(".remember").is(':checked') ? 1 : 0;
                                                    var returnUrl = $('#returnUrl').val();
                                                    if ((name == null) || (name === "")) {
                                                        Dask.api.error("用户名不能为空！");
                                                        return false;
                                                    }
                                                    else if ((password == null) || (password === "")) {
                                                        Dask.api.error("密码不能为空！");
                                                        return false;
                                                    }
                                                    else {
                                                        // 旧接口要求表单编码并依赖响应里的 url，使用 fetch 保持原有语义
                                                        fetch("{:url('account/login')}", {
                                                            method: 'POST',
                                                            credentials: 'same-origin',
                                                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                                            body: new URLSearchParams({ username: name, password: password, token: token, remember: remember, return_url: returnUrl })
                                                        }).then(function (response) {
                                                            return response.text().then(function (text) {
                                                                try { return JSON.parse(text); }
                                                                catch (e) { return { __raw: text }; }
                                                            });
                                                        }).then(function (result) {
                                                            if (result && result.__raw !== undefined) {
                                                                if (daskTrim(result.__raw) !== '') {
                                                                    Dask.api.error('发生错误, 返回的信息:' + ' ' + result.__raw);
                                                                }
                                                                return;
                                                            }
                                                            if (result.code > 0) {
                                                                if (isAjaxOpen) {
                                                                    parent.window.location.reload();
                                                                }
                                                                window.DaskFrontend.toast(result.msg, 'success');
                                                                window.setTimeout(function () { window.location.href = result.url; }, 1200);
                                                            } else {
                                                                Dask.api.error(result.msg, result.url);
                                                            }
                                                        }).catch(function (error) {
                                                            Dask.api.error('发生错误, 返回的信息:' + ' ' + error.message);
                                                        });
                                                    }
                                                }
                                            });
                                            {else/}
                                                var token = $("input[name='token']").val();
                                                var name = $("#username").val();
                                                var password = $("#password").val();
                                                var remember = $(".remember").is(':checked') ? 1 : 0;
                                                var returnUrl = $('#returnUrl').val();
                                                if ((name == null) || (name === "")) {
                                                    Dask.api.error("用户名不能为空！");
                                                    return false;
                                                }
                                                else if ((password == null) || (password === "")) {
                                                    Dask.api.error("密码不能为空！");
                                                    return false;
                                                }
                                                else {
                                                    // 旧接口要求表单编码并依赖响应里的 url，使用 fetch 保持原有语义
                                                    fetch("{:url('account/login')}", {
                                                        method: 'POST',
                                                        credentials: 'same-origin',
                                                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                                        body: new URLSearchParams({ username: name, password: password, token: token, remember: remember, return_url: returnUrl })
                                                    }).then(function (response) {
                                                        return response.text().then(function (text) {
                                                            try { return JSON.parse(text); }
                                                            catch (e) { return { __raw: text }; }
                                                        });
                                                    }).then(function (result) {
                                                        if (result && result.__raw !== undefined) {
                                                            if (daskTrim(result.__raw) !== '') {
                                                                Dask.api.error('发生错误, 返回的信息:' + ' ' + result.__raw);
                                                            }
                                                            return;
                                                        }
                                                        if (result.code) {
                                                            if (isAjaxOpen) {
                                                                parent.window.location.reload();
                                                            }
                                                            window.DaskFrontend.toast(result.msg, 'success');
                                                            window.setTimeout(function () { window.location.href = result.url; }, 1200);
                                                        } else {
                                                            Dask.api.error(result.msg, result.url);
                                                        }
                                                    }).catch(function (error) {
                                                        Dask.api.error('发生错误, 返回的信息:' + ' ' + error.message);
                                                    });
                                                }
                                            {/if}
                                        })
                                    </script>
                                    {/if}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                {if !empty($thirdConfig['base']['enable'])}
                <div class="pb-4 position-relative mx-3 dask-social">
                    <div class="border-bottom pb-1 position-absolute w-100 dask-social-divider"></div>
                    <span class="d-block bg-white mb-3 position-relative text-center dask-social-label">{:L('社交账号登录')}</span>
                    <span class="d-block text-center">{include file="widget/third_login" /}</span>
                </div>
                {/if}
            </div>
        </div>
        {if !$_ajax_open}
        <div class="signBoxFooter text-center text-white px-6">
            <div class="py-2">
                {volist name="footerMenu" id="v"}
                <a target="_blank" rel="noopener noreferrer" href="{$v.url}">{$v.title}</a>
                {/volist}
            </div>
            <div class="pb-3">
                <span>© 2018 - {:date('Y')} {$setting.site_name} All Rights Reserved.</span>
                <a target="_blank" rel="noopener noreferrer" href="https://icp.gov.moe/?keyword=20248099">{$setting.icp}</a>
            </div>
        </div>
        {/if}
    </div>
</div>
{/block}
{block name="footer"}{/block}
