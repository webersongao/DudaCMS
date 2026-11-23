{extend name="$theme_block" /}
{block name="header"}{/block}
{block name="main"}
{if !$_ajax_open}
<style>
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
</style>
{/if}
<style>
    /* 认证页统一收掉固定导航预留的顶部空白，避免顶部白条 */
    #wrapMain { margin-top: 0 !important; }
    .signBoxFooter a{color: #fff !important;}
    .signBoxFooter{background:rgba(0,0,0,.5);border-radius:12px;margin:0 12px 12px;padding-left:12px !important;padding-right:12px !important;padding-top:6px;padding-bottom:6px;}
    .signBox .logo {
        height: 80px;
        margin-bottom: 24px;
        line-height: 80px;
        font-weight: 600;
        text-align: center;
        font-size: 2rem;
    }
    .SignContainer-content {
        margin: 0 auto;
        text-align: center;
    }
    .SignContainer-inner {
        position: relative;
        overflow: hidden;
    }
    .logo a{color:#ffffff !important;}
    .bline{display:flex; align-items:center; margin-bottom:20px;}
    .bline input{ width:100%}
    @media (max-width: 767px){
        .signBoxContent{padding: 16px 8px;}
        .signBox .logo{height: auto;padding-top: 12px;margin-bottom: 12px;line-height: 1.2;font-size: 1.6rem;}
        .bline{margin-bottom: 12px;}
    }
    @media (max-width: 360px){
        .signBox .logo{font-size: 1.4rem;}
        .signBoxFooter{padding-left: 8px !important;padding-right: 8px !important;}
        .SignContainer-content.dask-card__body{padding: 12px;}
    }
</style>
<div class="appMain dask-auth-page dask-page" style="{$theme_config['common']['fixed_navbar']=='Y' && !$_ajax_open && !$_ajax ? 'margin-top:-80px' : ''}" >
    <div class="signBox">
        <div class="signBoxContent">
            {if !$_ajax_open}
            <h1 class="logo ">
                <a href="{$baseUrl}" class="text-primary">{:get_setting('site_name')}</a>
            </h1>
            {/if}
            <div class="content dask-card" style="padding: 0" id="boxMain">
                <div class="SignContainer-content dask-card__body">
                    <div class="SignContainer-inner">
                        <form action="" method="POST">
                            <div class="block mb-0">
                                {if get_setting('invite_register_enable') =='Y'}
                                    <div class="text-muted text-center pt-3">
                                    <a class="text-danger" href="{:url('account/register')}" data-pjax="tabMain">{:L('本站仅限邀请码注册!!!')}</a>
                                    </div>
                                {/if}
                                <ul class="nav nav-tabs nav-tabs-block p-3 mb-1 dask-pjax-tabs dask-card__head">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="{:url('account/register')}" data-pjax="tabMain">{:L('用户注册')}</a>
                                    </li>
                                </ul>
                                <div class="block-content tab-content p-3 bg-white" id="tabMain">
                                    {:csrf_field()}
                                    <input name="return_url" id="returnUrl" type="hidden" value="{:base64_encode($return_url)}">
                                    <div class="mb-3 bline">
                                        <input id="username" name="username" placeholder="{:L('用户名')}" type="text" class="dask-form-control" value=""/>
                                    </div>
                                    <div class="mb-3 bline">
                                        <input type="password" id="password" name="password" placeholder="{:L('登录密码')}" class="dask-form-control" value=""/>
                                    </div>
                                    <!-- <div class="mb-3 bline d-flex">
                                    <input type="invitation_code" id="inviteCode" name="invitation_code" placeholder="{:L('邀请码')}（︎{if get_setting('invite_register_enable') =='Y'}不{/if}可为空）" class="dask-form-control" value="{:input('invitation_code')}"/>
                                    <a type="button" target="_blank" href="/page/invitate" class="flex-fill text-primary" style="min-width: 100px;line-height: 38px;text-align: right">{:L('获取邀请码')}</a>
                                    </div> -->
                                    {if $register_type=='mobile'}
                                    <input type="hidden" class="register_type" value="mobile"/>
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="mobile" name="mobile" placeholder="{:L('请输入手机号')}">
                                    </div>
                                    <div class="mb-3 bline d-flex" style="border-bottom: 1px solid #ebebeb">
                                        <input type="text" style="border-bottom: 0" id="code" class="dask-form-control flex-fill" name="code" placeholder="{:L('请输入短信验证码')}">
                                        <button type="button" data-sms='#mobile' class="flex-fill text-primary" style="min-width: 110px;line-height: 38px;outline: none;border: none;background: none">{:L('获取短信验证码')}</button>
                                    </div>
                                    <div class="mb-3 newbut" style=" margin-top:30px;">
                                        <button type="button" class="btn w-100 btn-primary login-button">{:L('注册')}</button>
                                    </div>
                                    <script>
                                        $(".login-button").click(function () {
                                            var token = $("input[name='__token__']").val();
                                            var name = $("#username").val();
                                            var password = $("#password").val();
                                            var mobile = daskTrim($("#mobile").val());
                                            var code = daskTrim($("#code").val());
                                            var inviteCode = $('#inviteCode').length > 0 ? $("#inviteCode").val() : '';
                                            var returnUrl = $('#returnUrl').val();
                                            if ((name == null) || (name === "")) {
                                                layer.msg("用户名不能为空！");
                                                return false;
                                            }
                                            else if ((password == null) || (password === "")) {
                                                layer.msg("密码不能为空！");
                                                return false;
                                            }else if(!Dask.common.isPhoneNumber(mobile))
                                            {
                                                layer.msg("请输入有效的手机号码！");
                                                return false;
                                            }else if(code === "")
                                            {
                                                layer.msg("请输入短信验证码");
                                                return false;
                                            }
                                            else {
                                                $.ajax({
                                                    url: "{:url('account/register')}",
                                                    type: "POST",
                                                    data: {
                                                        username: name,
                                                        password: password,
                                                        token:token,
                                                        mobile:mobile,
                                                        code:code,
                                                        invitation_code:inviteCode,
                                                        return_url : returnUrl
                                                    },
                                                    dataType:"json",
                                                    success: function (result) {
                                                        let msg = result.msg ? result.msg : '注册成功';
                                                        if (result.code > 0) {
                                                            Dask.api.success(msg, result.url)
                                                        } else {
                                                            Dask.api.error(msg, result.url)
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
                                    {/if}

                                    {if $register_type=='email'}
                                    <input type="hidden" class="register_type" value="email"/>
                                    <input type="hidden" name="captcha" id="captcha">
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="email" name="email" placeholder="{:L('请输入邮箱')}">
                                    </div>
                                    <div class="mb-3 newbut">
                                        <button type="button" class="btn w-100 btn-primary login-button">{:L('注册')}</button>
                                    </div>
                                    <script>
                                        $(document).ready(function ()
                                        {
                                            $(".login-button").click(function () {
                                                var name = $("#username").val();
                                                {if $setting.enable_frontend_captcha=='Y'}
                                                $('#captcha').captcha({
                                                    callback: function () {
                                                        var token = $("input[name='__token__']").val();
                                                        var password = $("#password").val();
                                                        var inviteCode = $('#inviteCode').length > 0 ? $("#inviteCode").val() : '';
                                                        var email = $("#email").val();
                                                        var returnUrl = $('#returnUrl').val();
                                                        $.ajax({
                                                            url: "{:url('account/register')}",
                                                            type: "POST",
                                                            data: {
                                                                username: name,
                                                                password: password,
                                                                token:token,
                                                                email:email,
                                                                invitation_code:inviteCode,
                                                                return_url : returnUrl
                                                            },
                                                            dataType:"json",
                                                            success: function (result) {
                                                                let msg = result.msg ? result.msg : '操作成功';
                                                                if (result.code > 0) {
                                                                    Dask.api.success(msg, result.url)
                                                                } else {
                                                                    Dask.api.error(msg, result.url)
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
                                                {else/}
                                                    var token = $("input[name='__token__']").val();
                                                    var password = $("#password").val();
                                                    var inviteCode = $('#inviteCode').length > 0 ? $("#inviteCode").val() : '';
                                                    var email = $("#email").val();
                                                    var returnUrl = $('#returnUrl').val();
                                                    $.ajax({
                                                        url: "{:url('account/register')}",
                                                        type: "POST",
                                                        data: {
                                                            username: name,
                                                            password: password,
                                                            token:token,
                                                            email:email,
                                                            invitation_code:inviteCode,
                                                            register_type:"email",
                                                            return_url : returnUrl
                                                        },
                                                        dataType:"json",
                                                        success: function (result) {
                                                            let msg = result.msg ? result.msg : '操作成功';
                                                            if (result.code > 0) {
                                                                Dask.api.success(msg, result.url)
                                                            } else {
                                                                Dask.api.error(msg, result.url)
                                                            }
                                                        },
                                                        error: function (error) {
                                                            if (daskTrim(error.responseText) !== '') {
                                                                Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
                                                            }
                                                        }
                                                    })
                                                {/if}
                                            })
                                        })
                                    </script>
                                    {/if}

                                    {if $register_type=='all'}
                                    <input type="hidden" class="register_type" value="mobile"/>
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="mobile" name="mobile" placeholder="{:L('请输入手机号')}">
                                    </div>
                                    <div class="mb-3 bline d-flex" style="border-bottom: 1px solid #ebebeb">
                                        <input type="text" style="border-bottom: 0" id="code" class="dask-form-control flex-fill" name="code" placeholder="{:L('请输入短信验证码')}">
                                        <button type="button" data-sms='#mobile' class="flex-fill text-primary" style="min-width: 110px;line-height: 38px;outline: none;border: none;background: none">{:L('获取短信验证码')}</button>
                                    </div>
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="email" name="email" placeholder="{:L('请输入邮箱')}">
                                    </div>

                                    <div class="mb-3 newbut" style=" margin-top:30px;">
                                        <button type="button" class="btn w-100 btn-primary login-button">{:L('注册')}</button>
                                    </div>
                                    <script>
                                        $(".login-button").click(function () {
                                            var token = $("input[name='__token__']").val();
                                            var name = $("#username").val();
                                            var password = $("#password").val();
                                            var mobile = daskTrim($("#mobile").val());
                                            var code = daskTrim($("#code").val());
                                            var email = $("#email").val();
                                            var inviteCode = $('#inviteCode').length > 0 ? $("#inviteCode").val() : '';
                                            var returnUrl = $('#returnUrl').val();
                                            if ((name == null) || (name === "")) {
                                                Dask.api.error("用户名不能为空！");
                                                return false;
                                            }
                                            else if ((password == null) || (password === "")) {
                                                Dask.api.error("密码不能为空！");
                                                return false;
                                            }else if(!Dask.common.isPhoneNumber(mobile))
                                            {
                                                Dask.api.error("请输入有效的手机号码！");
                                                return false;
                                            }else if(code === "")
                                            {
                                                Dask.api.error("请输入短信验证码");
                                                return false;
                                            }
                                            else {
                                                $.ajax({
                                                    url: "{:url('account/register')}",
                                                    type: "POST",
                                                    data: {
                                                        username: name,
                                                        password: password,
                                                        token:token,
                                                        mobile:mobile,
                                                        code:code,
                                                        email:email,
                                                        invitation_code:inviteCode,
                                                        return_url:returnUrl
                                                    },
                                                    dataType:"json",
                                                    success: function (result) {
                                                        let msg = result.msg ? result.msg : '注册成功';
                                                        if (result.code > 0) {
                                                            Dask.api.success(msg, result.url)
                                                        } else {
                                                            Dask.api.error(msg, result.url)
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
                                    {/if}

                                    <div class="text-muted text-start font-8  pb-3">
                                        {:L('注册即代表同意')}<a href="javascript:;" class="register-agreement">《{:L('注册协议')}》</a>
                                        <a class="nav-link text-primary float-end"  data-pjax="boxMain" href="{:url('account/login')}">{:L('已有账号')}？{:L('去登录')}</a>
                                    </div>
                                    <script type="text/html" id="agreement">
                                        <div style="padding: 30px">
                                            {$agreement|raw}
                                        </div>
                                    </script>
                                    <script>
                                        $('.register-agreement').click(function (){
                                            layer.open({
                                                type: 1,
                                                title: '注册协议',
                                                closeBtn: 1,
                                                area: ['90%', '90%'],
                                                shadeClose: true,
                                                content: $('#agreement').html()
                                            });
                                        });
                                    </script>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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
