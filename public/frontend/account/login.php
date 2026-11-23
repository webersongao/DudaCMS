{extend name="$theme_block" /}
{block name="header"}{/block}
{block name="main"}
<style>
    /* 认证页统一收掉固定导航预留的顶部空白，避免顶部白条 */
    #wrapMain { margin-top: 0 !important; }
    {if !$_ajax_open}
    .signBox{
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-orient: vertical;
        -webkit-box-direction: normal;
        -ms-flex-direction: column;
        flex-direction: column;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        background-image: url('{$static_url}image/body-bg.jpg');
        background-repeat: no-repeat;
        background-color: #b8e5f8;
        background-position: center;
        background-size: cover;
        width: 100%;
        min-height: 100vh;
        overflow: auto;
    }
    .signBoxContent{
        -webkit-box-flex: 1;
        -ms-flex: 1 1;
        flex: 1 1;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-orient: vertical;
        -webkit-box-direction: normal;
        -ms-flex-direction: column;
        flex-direction: column;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: center;
        width: 100%;
        margin-top: 0;
        padding: 24px 12px;
        border-radius: 2px;
        min-height: 0;
        height: auto;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
    }
    .content {
        box-sizing: border-box;
        margin: 0 auto;
        width: 100%;
        max-width: 489px;
        padding: 0;
        background-color: #FFFFFF;
        box-shadow: 0 1px 3px rgba(18,18,18,0.1);
        border-radius: 3px;
        overflow: hidden;
    }
    {/if}
    .signBoxFooter,
    .signBoxFooter a{color:#fff !important;}
    .signBoxFooter{padding-left: 12px !important;padding-right: 12px !important;}
    .signBox .logo {
        height: auto;
        margin-bottom: 24px;
        line-height: 1;
        text-align: center;
    }
    .signBox .logo a{display:inline-block;line-height:1;}
    .signBox .logo img{display:inline-block;width:210px;max-width:86%;height:auto;vertical-align:middle;}
    .SignContainer-content {
        margin: 0 auto;
        text-align: center;
    }
    .SignContainer-inner {
        position: relative;
        overflow: hidden;
    }
    .logo a{color:#ffffff !important;}
    .bline{display:flex; align-items:center; margin-bottom:1rem;}
    .bline input{ width:100%}
    /* 社交账号图标统一为等大 44px 触控目标 */
    .dask-social a{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin: 0 4px 8px;
        border: 1px solid var(--dask-line, #e9ecef);
        border-radius: 50%;
        background: var(--dask-surface, #fff);
        line-height: 1;
        text-decoration: none;
    }
    .dask-social a i,
    .dask-social a svg{font-size: 1.5rem !important;line-height: 1;}
    /* 底部不再加深色底衬/阴影；统一用白字配轻文字阴影压在紫色题图上。 */
    .signBoxFooter{background:transparent !important;box-shadow:none !important;border-radius:0;margin:0 12px 12px;padding-top:6px;padding-bottom:6px;text-shadow:0 1px 3px rgba(0,0,0,.55) !important;}
    html body .signBoxFooter,
    html body .signBoxFooter a{color:#fff !important;text-shadow:0 1px 3px rgba(0,0,0,.55) !important;}
    @media (max-width: 767px){
        .signBoxContent{padding: 16px 8px;}
        .signBox .logo{height: auto;padding-top: 12px;margin-bottom: 12px;line-height: 1;}
        .signBox .logo img{width:180px;}
    }
    @media (max-width: 360px){
        .signBox .logo img{width:160px;}
        .signBoxFooter{padding-left: 8px !important;padding-right: 8px !important;}
        .bline{margin-bottom: 12px;}
        .SignContainer-content.dask-card__body{padding: 12px;}
    }
</style>
<div class="appMain dask-auth-page dask-page" style="margin-top: 0 !important;">
    <div class="signBox">
        <div class="signBoxContent">
            {if !$_ajax_open}
            <h1 class="logo ">
                <a href="{$baseUrl}" class="text-primary"><img src="{:asset('brand/logo-bihu-white.svg', false)}" alt="{:get_setting('site_name')}"></a>
            </h1>
            {/if}
            <div class="content dask-card" style="padding: 0" id="boxMain">
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
                                        <input type="text" class="dask-form-control" id="mobile" name="mobile" placeholder="{:L('请输入手机号')}">
                                    </div>
                                    <div class="mb-3 bline d-flex" style="border-bottom: 1px solid #ebebeb">
                                        <input type="text" style="border-bottom: 0" class="dask-form-control flex-fill" id="code" name="code" placeholder="{:L('请输入短信验证码')}">
                                        <button type="button" data-sms='#mobile' class="flex-fill text-primary" style="min-width: 110px;line-height: 38px;outline: none;border: none;background: none">{:L('获取短信验证码')}</button>
                                    </div>
                                    <div class="mb-3">
                                        <button type="button" class="btn w-100 btn-primary login-button">{:L('登录')}</button>
                                    </div>
                                    <div class="mb-3 clearfix font-size-sm" style=" margin-top:30px;">
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
                                                $.ajax({
                                                    url: "{:url('account/login')}",
                                                    type: "POST",
                                                    data: { mobile: name, code: code,token:token,remember:remember,return_url:returnUrl},
                                                    dataType:"json",
                                                    success: function (result) {
                                                        if (result.code > 0) {
                                                            if (isAjaxOpen){
                                                                parent.window.location.reload();
                                                            }
                                                            layer.msg(result.msg,function (){
                                                                window.location.href = result.url;
                                                            })
                                                        } else {
                                                            Dask.api.error(result.msg, result.url)
                                                        }
                                                    },
                                                    error: function (error) {
                                                        if (daskTrim(error.responseText) !== '') {
                                                            Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
                                                        }
                                                    }
                                                })
                                            }
                                        })
                                    </script>
                                    {else/}
                                    <input type="hidden" name="captcha" id="captcha">
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="username" name="username" placeholder="{:L('请输入手机号/邮箱/用户名')}">
                                    </div>
                                    <div class="mb-3 bline">
                                        <input type="password" class="dask-form-control" id="password" name="password" placeholder="{:L('请输入密码')}">
                                    </div>

                                    <div class="mb-3 newbut" style=" margin-top:30px;">
                                        <button type="button" class="btn w-100 btn-primary login-button">{:L('登录')}</button>
                                    </div>

                                    <div class="mb-3 clearfix font-size-sm" style=" margin-top:30px;">
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
                                                        $.ajax({
                                                            url: "{:url('account/login')}",
                                                            type: "POST",
                                                            data: { username: name, password: password,token:token,remember:remember,return_url:returnUrl},
                                                            dataType:"json",
                                                            success: function (result) {
                                                                if (result.code > 0) {
                                                                    if (isAjaxOpen){
                                                                        parent.window.location.reload();
                                                                    }
                                                                    layer.msg(result.msg,function (){
                                                                        window.location.href = result.url;
                                                                    })
                                                                } else {
                                                                    Dask.api.error(result.msg, result.url)
                                                                }
                                                            },
                                                            error: function (error) {
                                                                if (daskTrim(error.responseText) !== '') {
                                                                    Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
                                                                }
                                                            }
                                                        })
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
                                                    $.ajax({
                                                        url: "{:url('account/login')}",
                                                        type: "POST",
                                                        data: { username: name, password: password,token:token,remember:remember,return_url:returnUrl},
                                                        dataType:"json",
                                                        success: function (result) {
                                                            if (result.code) {
                                                                if (isAjaxOpen){
                                                                    parent.window.location.reload();
                                                                }
                                                                layer.msg(result.msg,function (){
                                                                    window.location.href = result.url;
                                                                })
                                                            } else {
                                                                Dask.api.error(result.msg, result.url)
                                                            }
                                                        },
                                                        error: function (error) {
                                                            if (daskTrim(error.responseText) !== '') {
                                                                Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
                                                            }
                                                        }
                                                    })
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
                    <div class="border-bottom pb-1 position-absolute w-100" style="top: 6px"></div>
                    <span class="d-block bg-white mb-3 position-relative text-center" style="width: 120px;margin: 0 auto;z-index: 9">{:L('社交账号登录')}</span>
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
