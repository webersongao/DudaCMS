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
                                        <input id="username" name="username" placeholder="{$account_hints.username}" type="text" class="dask-form-control" data-dask-validate="required">
                                    </div>
                                    <div class="mb-3 bline">
                                        <input type="password" id="password" name="password" placeholder="{$account_hints.password}" class="dask-form-control" data-dask-validate="required">
                                    </div>
                                    <!-- <div class="mb-3 bline d-flex">
                                    <input type="invitation_code" id="inviteCode" name="invitation_code" placeholder="{:L('邀请码')}（︎{if get_setting('invite_register_enable') =='Y'}不{/if}可为空）" class="dask-form-control" value="{:input('invitation_code')}"/>
                                    <a type="button" target="_blank" href="/page/invitate" class="flex-fill text-primary" style="min-width: 100px;line-height: 38px;text-align: right">{:L('获取邀请码')}</a>
                                    </div> -->
                                    {if $register_type=='mobile'}
                                    <input type="hidden" class="register_type" value="mobile"/>
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="mobile" name="mobile" placeholder="{:L('请输入手机号')}" data-dask-validate="required">
                                    </div>
                                    <div class="mb-3 bline d-flex dask-auth-code-row">
                                        <input type="text" id="code" class="dask-form-control flex-fill dask-auth-code-input" name="code" placeholder="{:L('请输入短信验证码')}" data-dask-validate="required">
                                        <button type="button" data-sms='#mobile' class="flex-fill dask-auth-sms-btn">{:L('获取短信验证码')}</button>
                                    </div>
                                    <div class="mb-3 newbut dask-auth-spacer">
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
                                                window.DaskFrontend.toast("用户名不能为空！");
                                                return false;
                                            }
                                            else if ((password == null) || (password === "")) {
                                                window.DaskFrontend.toast("密码不能为空！");
                                                return false;
                                            }else if(!Dask.common.isPhoneNumber(mobile))
                                            {
                                                window.DaskFrontend.toast("请输入有效的手机号码！");
                                                return false;
                                            }else if(code === "")
                                            {
                                                window.DaskFrontend.toast("请输入短信验证码");
                                                return false;
                                            }
                                            else {
                                                // 旧接口要求表单编码并依赖响应里的 url，使用 fetch 保持原有语义
                                                fetch("{:url('account/register')}", {
                                                    method: 'POST',
                                                    credentials: 'same-origin',
                                                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                                    body: new URLSearchParams({ username: name, password: password, token: token, mobile: mobile, code: code, invitation_code: inviteCode, return_url: returnUrl })
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
                                                    var msg = result.msg ? result.msg : '注册成功';
                                                    if (result.code > 0) {
                                                        Dask.api.success(msg, result.url);
                                                    } else {
                                                        Dask.api.error(msg, result.url);
                                                    }
                                                }).catch(function (error) {
                                                    Dask.api.error('发生错误, 返回的信息:' + ' ' + error.message);
                                                });
                                            }
                                        })
                                    </script>
                                    {/if}

                                    {if $register_type=='email'}
                                    <input type="hidden" class="register_type" value="email"/>
                                    <input type="hidden" name="captcha" id="captcha">
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="email" name="email" placeholder="{:L('请输入邮箱')}" data-dask-validate="required">
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
                                                        // 旧接口要求表单编码并依赖响应里的 url，使用 fetch 保持原有语义
                                                        fetch("{:url('account/register')}", {
                                                            method: 'POST',
                                                            credentials: 'same-origin',
                                                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                                            body: new URLSearchParams({ username: name, password: password, token: token, email: email, invitation_code: inviteCode, return_url: returnUrl })
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
                                                            var msg = result.msg ? result.msg : '操作成功';
                                                            if (result.code > 0) {
                                                                Dask.api.success(msg, result.url);
                                                            } else {
                                                                Dask.api.error(msg, result.url);
                                                            }
                                                        }).catch(function (error) {
                                                            Dask.api.error('发生错误, 返回的信息:' + ' ' + error.message);
                                                        });
                                                    }
                                                })
                                                {else/}
                                                    var token = $("input[name='__token__']").val();
                                                    var password = $("#password").val();
                                                    var inviteCode = $('#inviteCode').length > 0 ? $("#inviteCode").val() : '';
                                                    var email = $("#email").val();
                                                    var returnUrl = $('#returnUrl').val();
                                                    // 旧接口要求表单编码并依赖响应里的 url，使用 fetch 保持原有语义
                                                    fetch("{:url('account/register')}", {
                                                        method: 'POST',
                                                        credentials: 'same-origin',
                                                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                                        body: new URLSearchParams({ username: name, password: password, token: token, email: email, invitation_code: inviteCode, register_type: 'email', return_url: returnUrl })
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
                                                        var msg = result.msg ? result.msg : '操作成功';
                                                        if (result.code > 0) {
                                                            Dask.api.success(msg, result.url);
                                                        } else {
                                                            Dask.api.error(msg, result.url);
                                                        }
                                                    }).catch(function (error) {
                                                        Dask.api.error('发生错误, 返回的信息:' + ' ' + error.message);
                                                    });
                                                {/if}
                                            })
                                        })
                                    </script>
                                    {/if}

                                    {if $register_type=='all'}
                                    <input type="hidden" class="register_type" value="mobile"/>
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="mobile" name="mobile" placeholder="{:L('请输入手机号')}" data-dask-validate="required">
                                    </div>
                                    <div class="mb-3 bline d-flex dask-auth-code-row">
                                        <input type="text" id="code" class="dask-form-control flex-fill dask-auth-code-input" name="code" placeholder="{:L('请输入短信验证码')}" data-dask-validate="required">
                                        <button type="button" data-sms='#mobile' class="flex-fill dask-auth-sms-btn">{:L('获取短信验证码')}</button>
                                    </div>
                                    <div class="mb-3 bline">
                                        <input type="text" class="dask-form-control" id="email" name="email" placeholder="{:L('请输入邮箱')}" data-dask-validate="required">
                                    </div>

                                    <div class="mb-3 newbut dask-auth-spacer">
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
                                                // 旧接口要求表单编码并依赖响应里的 url，使用 fetch 保持原有语义
                                                fetch("{:url('account/register')}", {
                                                    method: 'POST',
                                                    credentials: 'same-origin',
                                                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                                    body: new URLSearchParams({ username: name, password: password, token: token, mobile: mobile, code: code, email: email, invitation_code: inviteCode, return_url: returnUrl })
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
                                                    var msg = result.msg ? result.msg : '注册成功';
                                                    if (result.code > 0) {
                                                        Dask.api.success(msg, result.url);
                                                    } else {
                                                        Dask.api.error(msg, result.url);
                                                    }
                                                }).catch(function (error) {
                                                    Dask.api.error('发生错误, 返回的信息:' + ' ' + error.message);
                                                });
                                            }
                                        })
                                    </script>
                                    {/if}

                                    <div class="text-muted text-start font-8  pb-3">
                                        {:L('注册即代表同意')}<a href="#" role="button" aria-label="{:L('查看注册协议')}" class="register-agreement">《{:L('注册协议')}》</a>
                                        <a class="nav-link text-primary float-end"  data-pjax="boxMain" href="{:url('account/login')}">{:L('已有账号')}？{:L('去登录')}</a>
                                    </div>
                                    <script type="text/html" id="agreement">
                                        <div class="dask-agreement-content">
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
                <span>© 2018 - {:front_time(time(), 'year')} {$setting.site_name} All Rights Reserved.</span>
                <a target="_blank" rel="noopener noreferrer" href="https://icp.gov.moe/?keyword=20248099">{$setting.icp}</a>
            </div>
        </div>
        {/if}
    </div>
</div>
{/block}
{block name="footer"}{/block}
