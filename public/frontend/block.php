{if !$_ajax}
<!doctype html>
<html lang="{:html_lang()}" style="height: auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{:csrf_token()}">
    <script src="{$assetBase}common/js/dask-csrf.js?v={:asset_version('common/js/dask-csrf.js')}"></script>
    {if request()->isSsl()}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    {/if}
    <base href="{$baseUrl}/">
    <link rel="icon" href="{:get_setting('site_favicon') ?: asset('brand/favicon.ico', false)}" type="image/x-icon">
    <meta name="referrer" content="never">
    <title>{block name="meta_title"}{$_page_title|default=""}{/block}</title>
    <meta name="title" content="{$_page_title|default=''}" />
    <meta name="keywords" content="{block name='meta_keywords'}{$_page_keywords|default=''}{/block}">
    <meta name="description" content="{block name='meta_description'}{$_page_description|default=''}{/block}">
    <meta name="image" content="{block name='meta_image'}{$static_url}image/ameta.webp{/block}">
    <!-- P1：Swiper 仅在分类横滑页加载（question/article/topic 列表） -->
    {if in_array($thisController,['question','article','topic']) && $thisAction=='index'}
    <link rel="stylesheet" href="{$assetBase}vendor/swiper/swiper.min.css?v={:asset_version('vendor/swiper/swiper.min.css')}">
    {/if}
    <link rel="stylesheet" href="{$assetBase}common/fonts/fonts.min.css?v={:asset_version('common/fonts/fonts.min.css')}">
    <link rel="stylesheet" href="{$assetBase}vendor/bootstrap/dist/css/bootstrap.min.css?v={:asset_version('vendor/bootstrap/dist/css/bootstrap.min.css')}">
    <!-- P1：验证码仅在登录/注册/找回密码与发布页加载 -->
    {if ($thisController=='account' && in_array($thisAction,['login','register','find_password'])) || (in_array($thisController,['question','article']) && $thisAction=='publish')}
    <link rel="stylesheet" type="text/css" href="{$assetBase}vendor/captcha/css/captcha.css?v={:asset_version('vendor/captcha/css/captcha.css')}" />
    {/if}
    <script src="{$assetBase}common/js/dask-theme-init.js?v={:asset_version('common/js/dask-theme-init.js')}" data-default="{:get_setting('theme_mode','auto')}"></script>
    <link rel="stylesheet" href="{$assetBase}common/design/tokens.css?v={:asset_version('common/design/tokens.css')}">
    <link rel="stylesheet" type="text/css" href="{$static_url}css/app.min.css?v={:asset_version('frontend/css/app.min.css')}">
    <link rel="stylesheet" type="text/css" href="{$static_url}css/zhihu-blue.css?v={:asset_version('frontend/css/zhihu-blue.css')}">
    <link rel="stylesheet" type="text/css" href="{$assetBase}common/css/dask-components.css?v={:asset_version('common/css/dask-components.css')}">
    <link rel="stylesheet" type="text/css" href="{$assetBase}common/css/dask-layout.css?v={:asset_version('common/css/dask-layout.css')}">
    <!-- 认证页/表单/无障碍补丁：统一收口到一处，避免各模板重复内联样式 -->
    <!-- layui 仍为上传/日期等遗留模块提供底层能力；UI 由 frontend-modern 统一接管 -->
    <link rel="stylesheet" type="text/css" href="{$assetBase}vendor/layui/css/layui.css?v={:asset_version('vendor/layui/css/layui.css')}">
    <link rel="stylesheet" type="text/css" href="{$assetBase}app/frontend-modern.css?v={:asset_version('app/frontend-modern.css')}">
    {block name="meta_style"}{/block}
    <script type="text/javascript" src="{$assetBase}vendor/jquery/dist/jquery.min.js?v={:asset_version('vendor/jquery/dist/jquery.min.js')}"></script>
    <script type="text/javascript" src="{$assetBase}common/js/dask-string.js?v={:asset_version('common/js/dask-string.js')}"></script>
    <script type="text/javascript" src="{$assetBase}common/js/dask-util.js?v={:asset_version('common/js/dask-util.js')}"></script>
    <script type="text/javascript" src="{$assetBase}vendor/bootstrap/dist/js/bootstrap.bundle.min.js?v={:asset_version('vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}"></script>
    {if in_array($thisController,['question','article','topic']) && $thisAction=='index'}
    <script type="text/javascript" src="{$assetBase}vendor/swiper/swiper.min.js?v={:asset_version('vendor/swiper/swiper.min.js')}"></script>
    {/if}
    <script type="text/javascript" src="{$assetBase}vendor/layui/layui.all.js?v={:asset_version('vendor/layui/layui.all.js')}"></script>
    <!-- layer.* 兼容层：替代 layui 的 layer 实现，保留前台原有 layer.msg / layer.open 调用语义 -->
    <script type="text/javascript" src="{$assetBase}app/dask-dialog-compat.js?v={:asset_version('app/dask-dialog-compat.js')}"></script>
    <!-- 上传按需：适配器常驻，Dropzone 本体仅在有上传控件时由 dask-dropzone.js 注入 -->
    <script type="text/javascript" src="{$assetBase}common/js/dask-dropzone.js?v={:asset_version('common/js/dask-dropzone.js')}"></script>
    <!-- 灯箱按需：dask-lightbox.js 仅在用户点击图片时动态注入 PhotoSwipe -->
    <script src="{$assetBase}common/js/dask-lightbox.js?v={:asset_version('common/js/dask-lightbox.js')}"></script>
    <!-- 二维码按需：app.js 仅在页面存在分享二维码容器时注入 qrcode 插件 -->
    <!-- 代码高亮按需：仅详情页加载样式与脚本（Vditor 随包 highlight.js） -->
    {if in_array($thisController,['question','article','topic']) && $thisAction=='detail'}
    <link rel="stylesheet" href="{$assetBase}vendor/highlight/styles/tomorrow.css?v={:asset_version('vendor/highlight/styles/tomorrow.css')}">
    <script type="text/javascript" src="{$assetBase}vendor/vditor/dist/js/highlight.js/highlight.min.js?v={:asset_version('vendor/vditor/dist/js/highlight.js/highlight.min.js')}"></script>
    {/if}
    <script>
        window.userId = parseInt("{$user_id|default='0'}");
        window.userName = "{$user_info['nick_name']|default=''}";
        window.baseUrl = '{$baseUrl}';
        window.cdnUrl = '{$cdnUrl}';
        window.thisController = "{$thisController|default=''}";
        window.thisAction = "{$thisAction|default=''}";
        window.staticUrl = '{$assetBase}';
        window.upload_image_ext = "{$setting.upload_image_ext}";
        window.upload_file_ext = "{$setting.upload_file_ext}";
        window.upload_image_size = "{$setting.upload_image_size}";
        window.upload_file_size = "{$setting.upload_file_size}";
        window.isAjax = parseInt("{$_ajax?1:0}");
        window.isAjaxOpen = parseInt("{$_ajax_open?1:0}");
        window.pjaxEnable = "{$setting.pjax_enable=='Y' ? 1 : 0}";
        // 按需加载资源的逐文件版本；不能用 dask-util.js 自身的 hash 代替。
        window.assetVersions = {:asset_versions_json(['common/js/jquery.qrcode.min.js', 'vendor/dropzone/dist/dropzone-min.js', 'vendor/photoswipe/dist/photoswipe.css', 'vendor/photoswipe/dist/umd/photoswipe.umd.min.js'])};
        //代码高亮
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.hljs) return;
            document.querySelectorAll('pre code').forEach(function(block) {
                if (typeof hljs.highlightElement === 'function') {
                    hljs.highlightElement(block);
                }
            });
        });
    </script>
    {if ($thisController=='account' && in_array($thisAction,['login','register','find_password'])) || (in_array($thisController,['question','article']) && $thisAction=='publish')}
    <script type="text/javascript" src="{$assetBase}vendor/captcha/captcha.js?v={:asset_version('vendor/captcha/captcha.js')}"></script>
    {/if}
    <!-- Turbo 替换 body 时保留全局脚本节点，避免重复绑定 document/window 事件 -->
    <script id="g-dask" data-turbo-permanent src="{$assetBase}common/js/dask.js?v={:asset_version('common/js/dask.js')}" type="text/javascript"></script>
    <script id="g-dask-components" data-turbo-permanent src="{$assetBase}common/js/dask-components.js?v={:asset_version('common/js/dask-components.js')}" type="text/javascript"></script>
    <script id="g-dask-form" data-turbo-permanent src="{$assetBase}common/js/dask-form.js?v={:asset_version('common/js/dask-form.js')}" type="text/javascript"></script>
    <script id="g-turbo" data-turbo-permanent src="{$assetBase}vendor/turbo/dist/turbo.es2017-umd.js?v={:asset_version('vendor/turbo/dist/turbo.es2017-umd.js')}" type="text/javascript"></script>
    <script id="g-dask-turbo" data-turbo-permanent src="{$assetBase}common/js/dask-turbo.js?v={:asset_version('common/js/dask-turbo.js')}" type="text/javascript"></script>
    <script id="g-dask-list-init" data-turbo-permanent src="{$assetBase}common/js/dask-list-init.js?v={:asset_version('common/js/dask-list-init.js')}" type="text/javascript"></script>
    <script id="g-dask-modern" data-turbo-permanent src="{$assetBase}common/js/dask-modern.js?v={:asset_version('common/js/dask-modern.js')}"></script>
    <script>window.DASK_LABELS = window.DASK_LABELS || { backTop: "{:L('返回顶部')}" };</script>
    <script id="g-dask-theme" data-turbo-permanent src="{$assetBase}common/js/dask-theme.js?v={:asset_version('common/js/dask-theme.js')}"></script>
    <script id="g-app" data-turbo-permanent src="{$static_url}js/app.js?v={:asset_version('frontend/js/app.js')}"></script>
    <script id="g-frontend-modern" data-turbo-permanent src="{$assetBase}app/frontend-modern.js?v={:asset_version('app/frontend-modern.js')}"></script>
    <script id="g-esm" data-turbo-permanent type="module" src="{$assetBase}app/index.js?v={:asset_version('app/index.js')}"></script>
    {block name="meta_script"} {/block}
    {literal}
    <script>
        /* PWA：同一套 SSR 页面由根作用域 Service Worker 提供离线能力 */
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js', {
                    scope: '/'
                }).catch(function() {});
            });
        }
    </script>
    {/literal}
</head>

<body id="pageMain" class="dask-overflow-auto {if get_theme_setting('common.filter-grey','N')=='Y'}filter-grey{/if}{if !$showHero} no-hero{/if}{if $thisController=='index' && !$showHero} home-no-hero{/if}{if $thisController=='account' && !$_ajax_open && !$_ajax} dask-account-page{/if}" style="height: auto">
    {$_style|raw}
    {if !$_ajax && !$_ajax_open}
    <!--全局自定义头部html-->
    {$setting.header_html|htmlspecialchars_decode|raw}
    {/if}
    {block name="style"} <!--自定义样式-->{/block}
    {if !$_ajax_open && !$_ajax}
    <div class="container-fluid w-container-fluid" id="headerMain">
        {block name="header"}
        {if $showHero}
        {include file="widget/common/hero" /}
        {else/}
        <div class="navbox-2 {:get_theme_setting('common.fixed_navbar')=='Y' ? 'suspension' : 'suspensions'}">
            <div class="container nav-auto">
                <div class="navbar navbar-expand-lg navbar-light">
                    <a class="navbar-brand" href="{$baseUrl}">{if $setting.site_logo}<img class="logoimg-2" style="max-width: 192px;" src="{$setting.site_logo}">{else/}<span class="bh-nav-logo fw-bold">{$setting.site_name}</span>{/if}</a>
                    <div class="bh-mobile-navbar-toggler d-lg-none">
                        {if $user_id}
                        <div class="dropdown bh-mobile d-inline-block me-4 position-relative">
                            <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-header-inbox>
                                <i class="iconfont bh-nav-icon">&#xe628</i>
                                {if $user_id && $user_info['inbox_unread']}
                                <span class="badge text-bg-danger rounded-pill position-absolute" data-inbox-unread style="right:-20px">{$user_info['inbox_unread']<=99 ? $user_info['inbox_unread']: '99+' }</span>
                                        {/if}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" style="width: 310px !important;">
                                <div class="bg-light text-center">
                                    <h5 class="dropdown-header text-uppercase py-2">{:L('私信消息')}</h5>
                                </div>
                                <div class="nav-items mb-0 dask-overflow-auto" id="topInboxBox_Toggler" data-inbox-list style="max-height: 400px"></div>
                                <div class="p-2 border-top">
                                    <a class="btn btn-sm btn-light w-100 text-center" href="{:url('inbox/index')}">
                                        <i class="fa fa-fw fa-arrow-down me-1"></i> {:L('加载更多')}...
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown bh-mobile d-inline-block me-4 position-relative">
                            <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-header-notify>
                                <i class="iconfont bh-nav-icon">&#xe689</i>
                                {if $user_id && $user_info['notify_unread']}
                                <span class="badge text-bg-danger rounded-pill position-absolute" data-notify-unread style="right:-20px">{$user_info['notify_unread']<=99 ? $user_info['notify_unread'] : '99+' }</span>
                                        {/if}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" style="width: 310px !important;">
                                <div class="bg-light clearfix py-2 px-3">
                                    <h5 class="text-uppercase float-start fw-bold font-9 mb-0">{:L('通知消息')}</h5>
                                    <a class="text-muted float-end font-8" href="javascript:;" data-action="user-header-notify-read-all">{:L('全部已读')}</a>
                                </div>
                                <div class="nav-items mb-0 dask-overflow-auto" id="topNotifyBox_Toggler" data-notify-list style="max-height: 400px"></div>
                                <div class="p-2 border-top">
                                    <a class="btn btn-sm btn-light w-100 text-center" href="{:url('notify/index')}">
                                        <i class="fa fa-fw fa-arrow-down me-1"></i> {:L('加载更多')}...
                                    </a>
                                </div>
                            </div>
                        </div>
                        {/if}
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </div>
                    <div class="collapse navbar-collapse newnavbar" id="navbarNavDropdown" style="z-index: 1001;justify-content: space-between">
                        <div class="dask-nav-container position-relative">
                            <ul class="navbar-nav navbar-new-nav">
                                {foreach $navMenu as $k=>$v}
                                {if $k<5}
                                    <li class="nav-item {$v.active ? 'cur' : ''} clearfix">
                                    {if $v.child_list}
                                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{$v.title}</a>
                                    <div class="dropdown-menu text-center bh-toggle-position" aria-labelledby="navbarDropdownMenuLink">
                                        {volist name="$v['child_list']" id="v1"}
                                        <a class="dropdown-item" href="{$v1.url}" data-pjax="pageMain" title="{$v1.title}">{$v1.title}</a>
                                        {/volist}
                                    </div>
                                    {else/}
                                    <a class="nav-link" href="{$v.url}" data-pjax="pageMain" title="{$v.title}">{$v.title}</a>
                                    {/if}
                                    </li>
                                    {/if}
                                    {/foreach}
                                    {if count($navMenu)>5}
                                    <li class="nav-item clearfix">
                                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{:L('更多')}</a>
                                        <div class="dropdown-menu text-center bh-toggle-position" aria-labelledby="navbarDropdownMenuLink">
                                            {foreach $navMenu as $k=>$v1}
                                            {if $k>=5}
                                            <a class="dropdown-item" href="{$v1.url}" data-pjax="pageMain" title="{:L($v1.title)}">{$v1.title}</a>
                                            {/if}
                                            {/foreach}
                                        </div>
                                    </li>
                                    {/if}
                            </ul>
                        </div>
                        <div class="nav-form position-relative" style="text-align:right;z-index: 2">
                            <form class="position-relative dask-form-search d-inline-block" action="{:url('search/index')}" method="get" id="awGlobalSearch">
                                <div class="searchbox">
                                    <input id="globalSearchInput" class="dask-search-input" autocomplete="off" placeholder="{:L('请输入想搜索的内容')}" value="{:input('get.q')}" name="q" type="text" required>
                                    <label class="si si-magnifier dask-icon-svg" data-action="form-submit" data-form="#awGlobalSearch">{:dask_icon('search','',16)}</label>
                                    <div class="dask-ui-dropdown" style="display: none">
                                        <div class="mod-body">
                                            <p class="title text-center p-3 fw-bold">{:L('请输入关键字进行搜索')}</p>
                                            <div class="dask-ui-dropdown-list dask-common-list dask-overflow-auto text-start px-3"></div>
                                            <p class="search p-3 text-start"><span>{:L('查看更多')} "</span><a href="javascript:;" data-action="form-submit" data-form="#awGlobalSearch" class="text-danger fw-bold"></a>" {:L('的搜索结果')}</p>
                                        </div>
                                        <div class="mod-footer px-2 py-1">
                                            <a href="{:url('question/publish')}" data-pjax="pageMain" class="btn btn-primary btn-small float-end">{:L('发起问题')}</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            {if $user_id}
                            <div class="dropdown d-inline-block me-4 position-relative">
                                <a href="javascript:;" class="btn btn-sm gradientBtn px-3 text-white" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{:L('发起')}</a>
                                <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm">
                                    <div class="text-center d-block py-2 dask-nav dask-ui-dropdown-nav text-center dask-answer-sort" style="min-width: 100px">
                                        {if($user_info['permission']['publish_question_enable']=='Y')}
                                        <a href="{:url('question/publish')}" class="py-1 dropdown-item" target="_blank">{:L('问题')}</a>
                                        {/if}
                                        {if($user_info['permission']['publish_article_enable']=='Y')}
                                        <a href="{:url('article/publish')}" class="py-1 dropdown-item" target="_blank">{:L('文章')} </a>
                                        {/if}
                                        {volist name=":config('dask.publish')" id="v"}
                                        <a href="{:url($v['url'])} " class="py-1 dropdown-item" target="_blank">{$v.title}</a>
                                        {/volist}

                                        
                                    </div>
                                </div>
                            </div>
                            <div class="d-none d-lg-block">
                                <div class="dropdown bh-mobile d-inline-block me-4 position-relative">
                                    <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-header-inbox>
                                        <i class="iconfont bh-nav-icon">&#xe628</i>
                                        {if $user_id && $user_info['inbox_unread']}
                                        <span class="badge text-bg-danger rounded-pill position-absolute" data-inbox-unread style="right:-20px">{$user_info['inbox_unread']<=99 ? $user_info['inbox_unread']: '99+' }</span>
                                                {/if}
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" style="width: 310px !important;">
                                        <div class="bg-light text-center">
                                            <h5 class="dropdown-header text-uppercase py-2">{:L('私信消息')}</h5>
                                        </div>
                                        <div class="nav-items mb-0 dask-overflow-auto" id="topInboxBox" data-inbox-list style="max-height: 400px"></div>
                                        <div class="p-2 border-top">
                                            <a class="btn btn-sm btn-light w-100 text-center" href="{:url('inbox/index')}">
                                                <i class="fa fa-fw fa-arrow-down me-1"></i> {:L('加载更多')}...
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown bh-mobile d-inline-block me-4 position-relative">
                                    <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-header-notify>
                                        <i class="iconfont bh-nav-icon">&#xe689</i>
                                        {if $user_id && $user_info['notify_unread']}
                                        <span class="badge text-bg-danger rounded-pill position-absolute" data-notify-unread style="right:-20px">{$user_info['notify_unread']<=99 ? $user_info['notify_unread'] : '99+' }</span>
                                                {/if}
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" style="width: 310px !important;">
                                        <div class="bg-light clearfix py-2 px-3">
                                            <h5 class="text-uppercase float-start fw-bold font-9 mb-0">{:L('通知消息')}</h5>
                                            <a class="text-muted float-end font-8" href="javascript:;" data-action="user-header-notify-read-all">{:L('全部已读')}</a>
                                        </div>
                                        <div class="nav-items mb-0 dask-overflow-auto" id="topNotifyBox" data-notify-list style="max-height: 400px"></div>
                                        <div class="p-2 border-top">
                                            <a class="btn btn-sm btn-light w-100 text-center" href="{:url('notify/index')}">
                                                <i class="fa fa-fw fa-arrow-down me-1"></i> {:L('加载更多')}...
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown bh-mobile d-inline-block position-relative">
                                <a href="javascript:;" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <img class="rounded" src="{:user_avatar_url($user_info['avatar'] ?? '')}" style="width: 22px;height: 22px">
                                    <span class="d-none d-sm-inline-block ms-1">{$user_info.nick_name}</span>
                                    <i class="fa fa-fw fa-angle-down d-none d-sm-inline-block"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" aria-labelledby="page-header-user-dropdown">
                                    <div class="p-3 text-center bg-primary">
                                        <a href="{:url('creator/index')}">
                                            <img class="img-avatar img-avatar-thumb" style="border-radius: 50%" width="48" height="48" src="{:user_avatar_url($user_info['avatar'] ?? '')}" alt="">
                                        </a>
                                    </div>
                                    <div class="p-2 text-center">
                                        <a class="dropdown-item align-items-center" href="{$user_info.url}">
                                            <span>{:L('个人主页')}</span>
                                        </a>
                                        <a class="dropdown-item align-items-center" href="{:url('creator/index')}">
                                            <span>{:L('用户中心')}</span>
                                        </a>
                                        <a class="dropdown-item align-items-center" href="{:url('setting/profile')}">
                                            <span>{:L('个人资料')}</span>
                                        </a>
                                        <div role="separator" class="dropdown-divider"></div>
                                        {if $user_info['group_id']==1 || $user_info['group_id']==2}
                                        <a class="dropdown-item align-items-center" target="_blank" href="/{:config('app.admin')}">
                                            <span>{:L('管理后台')}</span>
                                        </a>
                                        {/if}
                                        <a class="dropdown-item align-items-center" data-dask-confirm="{:L('确定退出登录吗？')}" data-dask-action="{:url('account/logout')}" href="#">
                                            <span>{:L('退出登录')}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            {else/}
                            <a {if $theme_config['common']['login_type']=='dialog' }href="javascript:;" data-action="user-login" {else/}href="{:url('account/login')}" {/if} class="btn btn-sm gradientBtn px-3 logon-but d-inline-block">{:L('登录')}</a>
                            {/if}

                        </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}
        {/block}
    </div>
    <!--全局页面内容顶部钩子-->
    
    {/if}

    <div style="height: auto;{$theme_config['common']['fixed_navbar']=='Y' && !$_ajax_open && !$_ajax && !in_array($thisAction,['login','register','find_password']) && !($thisController=='column' && $thisAction=='index') && (!($thisController=='index' && $thisAction=='index') || get_theme_setting('home.search_enable')=='N') ? 'margin-top:80px' : ''}" id="wrapMain">
        {block name="main"} {/block}
    </div>

    {if !$_ajax_open && !$_ajax}
    <!--全局页面内容底部钩子-->
    
    {/if}

    {block name="footer"}
    {if !$_ajax}
    {if !$_ajax_open}
    <div class="foot">
        <div class="container justify-content-center">
            <div class="foot-main">
                <div class="foot-copyright">© 2018 - {:front_time(time(), 'Y')} {$setting.site_name} 保留所有权利。</div>
                <div class="foot-links">
                    {if $setting.icp}<span class="foot-sep">·</span><a target="_blank" rel="noopener noreferrer" href="https://icp.gov.moe/?keyword=20248099">{$setting.icp}</a>{/if}
                    {if $footerMenu}{foreach $footerMenu as $k=>$v}<span class="foot-sep">·</span><a href="{$v.url}" title="{$v.title}" target="_blank">{$v.title}</a>{/foreach}{/if}
                    {include file="widget/lang_switch" /}
                </div>
            </div>
        </div>
    </div>
    <a class="dask-back-top hidden-xs" href="javascript:;" data-action="scroll-top" aria-label="{:L('返回顶部')}">{:dask_icon('circle-arrow-up','',23)}</a>
    {if $user_id && !$user_info['is_valid_email'] && $user_info['email'] && $setting['register_valid_type']=='email'}
    <script>
        var str = '<div class="p-3"><a href="javascript:;" class="dask-ajax-get text-danger" data-url="{:url('account/send_valid_mail')}">{:L('你的邮箱 %s 还未验证, 点击这里重新发送验证邮件', $user_info.email)}</a></div>';
        var width = $(window).width() > 600 ? '600px' : '85%';
        layer.open({
            title: '',
            type: 1,
            scrollbar: false,
            shade: 0.7,
            area: [width],
            content: str,
        })
    </script>
    {/if}

    {if $user_id && $user_info['is_first_login']}
    <script>
        window.daskFirstLoginTitle = "{:L('完善个人资料')}";
        Dask.User.firstLogin();
    </script>
    {/if}

    <script>
        // 吸顶导航：题图页与通用页共用同一套逻辑。
        // 吸顶后的不透明背景、层级与文字颜色由 zhihu-blue.css 的 .suspension 规则统一控制，
        // 避免旧内联颜色样式与覆盖层互相打架。
        (function() {
            var $nav = $('.navbox, .navbox-2');
            var topMain = $nav.first().outerHeight() || 60;
            var stuck = null;

            function syncNav() {
                var next = $(window).scrollTop() > topMain + 200;
                if (next === stuck) return;
                stuck = next;
                $nav.toggleClass('suspension', stuck);
                // Logo 颜色由 CSS 按 .w-top-img / .suspension 背景自动适配，
                // 这里不再改 src，避免切换 tab 时 logo 风格跳变。
            }

            // Turbo 页面替换后脚本会重跑；用命名空间先解绑避免重复监听。
            $(window).off('scroll.daskStickyNav').on('scroll.daskStickyNav', syncNav);
            syncNav();
        })();
    </script>

    {/if}
    <div id="dask-ajax-box"></div>
    {/if}
    {/block}

    <!--自动加载js文件-->
    {$_script|raw}

    {if !$_ajax && !$_ajax_open}
    <!--全局自定义底部html-->
    {$setting.footer_html|htmlspecialchars_decode|raw}
    {/if}
    {block name="script"} <!--自定义js脚本-->{/block}

    <form id="attach-download-form" action="{:url('upload/download')}" method="post">
        <input type="hidden" id="attach-name" name="name" value="">
        <input type="hidden" id="attachType" name="type" value="0">
    </form>
</body>

</html>
{else/}
<div style="{$theme_config['common']['fixed_navbar']=='Y' && !$_ajax_open && !$_ajax && !in_array($thisAction,['login','register','find_password']) && !($thisController=='column' && $thisAction=='index') && (!($thisController=='index' && $thisAction=='index') || get_theme_setting('home.search_enable')=='N') ? 'margin-top:80px' : ''}" id="wrapMain">
    {block name="main"} {/block}
    {block name="script"} <!--自定义js脚本-->{/block}
</div>
{/if}