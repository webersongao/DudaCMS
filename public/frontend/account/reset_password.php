{extend name="$theme_block" /}
{block name="header"}{/block}
{block name="main"}

<style>
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
        background-size: cover;
        width: 100%;
        height: 100vh;
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
        /* justify-content: center; */
        margin-top: 60px;
        border-radius: 2px;
        min-height: 640px;
        height: calc(100% - 42px);
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
    }
    .content {
        box-sizing: border-box;
        margin: 0;
        min-width: 400px;
        padding: 0;
        background-color: #FFFFFF;
        box-shadow: 0 1px 3px rgba(18,18,18,0.1);
        border-radius:3px;
        max-width: 489px;
        overflow: hidden;
    }
    {/if}
    .signBoxFooter a{color: #f6f6f6;}
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
    .nav-tabs-block {
        background-color: #fff;
        border-bottom: none;
    }
    .nav-tabs-block .nav-link {
        border-color: transparent;
        border-radius: 0;
        color: #575757;
        font-size: 1rem;
        padding: .8rem 0;
        margin-right: 1rem;
    }
    .nav-tabs-block .nav-link:hover{
        background: none;
    }
    .nav-tabs-block .nav-link.active, .nav-tabs-block .nav-item.show .nav-link {
        color: #575757;
        background-color: #fff;
        border-color: transparent;
        border-bottom: 3px solid #06f;
    }
    .logo a{color:#ffffff !important;}
    .bline{display:flex; align-items:center; margin-bottom:1rem;}
    .bline input{ width:100%}
    .newbut .btn{ background:#3182f7; border:none; height:38px;}
    .nav-tabs{ height:64px;}
    .nav-tabs a.nav-link{ height:60px; line-height:60px;}
    .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active{ height:60px; line-height:60px;}
</style>
<main class="appMain" style="{$theme_config['common']['fixed_navbar']=='Y' && !$_ajax_open && !$_ajax ? 'margin-top:-80px' : ''}" >
    <div class="signBox">
        <div class="signBoxContent">
            {if !$_ajax_open}
            <h1 class="logo ">
                <a href="{$baseUrl}" class="text-primary"><img src="{:asset('brand/logo-bihu-white.svg', false)}" alt="{:get_setting('site_name')}"></a>
            </h1>
            {/if}
            <div class="content" style="padding: 0" id="boxMain">
                <div class="SignContainer-content">
                    <div class="SignContainer-inner">
                        <form action="" method="POST">
                            <input type="hidden" name="active_code" value="{$active_code}">
                            <div class="block mb-0">
                                <ul class="nav nav-tabs nav-tabs-block px-3 mb-1 dask-pjax-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="javascript:;">{:L('重置密码')}</a>
                                    </li>
                                    {if $setting.register_type == 'open'}
                                    <li class="nav-item ms-auto" style=" float:right">
                                        <a class="nav-link text-primary"  data-pjax="boxMain" href="{:url('account/register')}">{:L('没有账号')}？{:L('去注册')}</a>
                                    </li>
                                    {/if}
                                </ul>
                                <div class="block-content tab-content p-3 bg-white" id="tabMain">
                                    <input type="hidden" name="token" value="{:csrf_token()}" />

                                    <div class="mb-3 bline">
                                        <input type="password" class="dask-form-control" name="password" placeholder="{:L('请输入新的密码')}">
                                    </div>
                                    <div class="mb-3 bline">
                                        <input type="password" class="dask-form-control" name="re_password" placeholder="{:L('请再次输入新的密码')}">
                                    </div>

                                    <div class="mb-3 newbut" style=" margin-top:30px;">
                                        <button type="button" class="btn w-100 btn-primary login-button dask-ajax-form">{:L('立即提交')}</button>
                                    </div>

                                    <div class="mb-3 clearfix font-size-sm" style=" margin-top:30px;">
                                        {if $setting.register_type == 'open'}
                                        <a class="nav-link text-primary float-end"  data-pjax="boxMain" href="{:url('account/register')}">{:L('没有账号')}？{:L('去注册')}</a>
                                        {/if}
                                    </div>
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
</main>
{/block}
{block name="footer"}{/block}
