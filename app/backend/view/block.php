{if !$_ajax}
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{:csrf_token()}">
    <script src="{$assetBase}common/js/dask-csrf.js?v={:asset_version('common/js/dask-csrf.js')}"></script>
    <title>{:isset($page_title) && $page_title  ? $page_title : (isset($breadCrumb['left']) ? $breadCrumb['left'][0] : get_setting('site_name'))} - 后台管理</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <base href="{$baseUrl}/" />
    <link href="{:asset('brand/favicon.ico', false)}" rel="shortcut icon" type="image/x-icon"/>
    <!-- 主题首屏防闪与交互增强收敛到同一脚本；须在样式前同步执行主题引导 -->
    <script src="{$assetBase}app/backend-modern.js?v={:asset_version('app/backend-modern.js')}" data-turbo-permanent></script>
    <!-- layui 仍为上传/日期/数据表等遗留模块提供底层能力；UI 由 backend-modern 统一接管 -->
    <link rel="stylesheet" href="{$assetBase}vendor/layui/css/layui.css?v={:asset_version('vendor/layui/css/layui.css')}">
    <link rel="stylesheet" href="{$assetBase}app/backend-modern.css?v={:asset_version('app/backend-modern.css')}">
    <link rel="stylesheet" href="{$assetBase}vendor/select2/css/select2.min.css?v={:asset_version('vendor/select2/css/select2.min.css')}">
    <link rel="stylesheet" href="{$assetBase}backend/plugins/fontawesome-free/css/all.min.css?v={:asset_version('backend/plugins/fontawesome-free/css/all.min.css')}">
    <link rel="stylesheet" href="{$assetBase}backend/plugins/icheck-bootstrap/icheck-bootstrap.min.css?v={:asset_version('backend/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}">
    <link rel="stylesheet" href="{$assetBase}common/fonts/fonts.min.css?v={:asset_version('common/fonts/fonts.min.css')}">
    <link rel="stylesheet" href="{$assetBase}vendor/adminlte/dist/css/adminlte.min.css?v={:asset_version('vendor/adminlte/dist/css/adminlte.min.css')}">
    <!-- P1：admin.css + tokens.css + admin-modern.css 三层按原顺序合并为单文件，减少请求 -->
    <link rel="stylesheet" href="{$assetBase}backend/css/admin-bundle.css?v={:asset_version('backend/css/admin-bundle.css')}">

    <script src="{$assetBase}vendor/jquery/dist/jquery.min.js?v={:asset_version('vendor/jquery/dist/jquery.min.js')}"></script>
    <script src="{$assetBase}common/js/dask-string.js?v={:asset_version('common/js/dask-string.js')}"></script>
    <script src="{$assetBase}common/js/dask-util.js?v={:asset_version('common/js/dask-util.js')}"></script>
    <script src="{$assetBase}vendor/bootstrap/dist/js/bootstrap.bundle.min.js?v={:asset_version('vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}"></script>
    <script src="{$assetBase}vendor/adminlte/dist/js/adminlte.min.js?v={:asset_version('vendor/adminlte/dist/js/adminlte.min.js')}"></script>
    <script src="{$assetBase}vendor/layui/layui.all.js?v={:asset_version('vendor/layui/layui.all.js')}"></script>
    <!-- layer 兼容层：经典脚本同步定义 window.layer（dialog.js 由兼容层内部动态 import），
         保证后续 dask-admin.js 与内联脚本执行时 layer.* 已可用 -->
    <script src="{$assetBase}app/dask-dialog-compat.js?v={:asset_version('app/dask-dialog-compat.js')}" data-turbo-permanent></script>
    <!-- 上传/树形选择/取色器/代码编辑器/bootstrap-table 均按页按需注入，见 global/form/layout.php 与 global/table/layout.php -->
    <script src="{$assetBase}common/js/dask-dropzone.js?v={:asset_version('common/js/dask-dropzone.js')}"></script>
    <script src="{$assetBase}vendor/select2/js/select2.full.min.js?v={:asset_version('vendor/select2/js/select2.full.min.js')}"></script>
    <script src="{$assetBase}common/js/dask-lightbox.js?v={:asset_version('common/js/dask-lightbox.js')}"></script>
    <script>
        window.G_BASE_URL = '{$base_url}';
        window.userId = parseInt("{$user_id|default='0'}");
        window.baseUrl = '{$base_url}';
        window.cdnUrl = '{$cdnUrl}';
        window.thisController = "{$thisController|default=''}";
        window.thisAction = "{$thisAction|default=''}";
        window.staticUrl = '{$assetBase}';
        window.upload_image_ext = "{$setting.upload_image_ext}";
        window.upload_file_ext = "{$setting.upload_file_ext}";
        window.upload_image_size = "{$setting.upload_image_size}";
        window.upload_file_size = "{$setting.upload_file_size}";
        window.isAjax = "{$_ajax}";
        window.isAjaxOpen = "{$_ajax_open}";
        // 将后台 pjax_enable 配置接线到 Turbo 开关。
        window.pjaxEnable = "{:get_setting('pjax_enable') == 'N' ? 'N' : 'Y'}";
        // 按需加载资源的逐文件版本；不能用 dask-util.js 自身的 hash 代替。
        window.assetVersions = {:asset_versions_json(['common/js/jquery.qrcode.min.js', 'vendor/dropzone/dist/dropzone-min.js', 'vendor/photoswipe/dist/photoswipe.css', 'vendor/photoswipe/dist/umd/photoswipe.umd.min.js'])};
        // 供 backend-modern.js 使用的模板变量；上面的旧全局变量保留以兼容既有脚本。
        window.DASK_CONFIG = {
            baseUrl: window.baseUrl,
            staticUrl: window.staticUrl,
            controller: window.thisController,
            action: window.thisAction,
            isAjax: window.isAjax,
            isAjaxOpen: window.isAjaxOpen
        };
    </script>
    <script src="{$assetBase}backend/js/dask-admin.js?v={:asset_version('backend/js/dask-admin.js')}"></script>
    <script src="{$assetBase}common/js/dask-form.js?v={:asset_version('common/js/dask-form.js')}"></script>
    <script src="{$assetBase}vendor/turbo/dist/turbo.es2017-umd.js?v={:asset_version('vendor/turbo/dist/turbo.es2017-umd.js')}"></script>
    <script src="{$assetBase}common/js/dask-turbo.js?v={:asset_version('common/js/dask-turbo.js')}"></script>
    <script src="{$assetBase}backend/js/admin-modern.js?v={:asset_version('backend/js/admin-modern.js')}"></script>
    <script src="{$assetBase}common/js/dask-theme.js?v={:asset_version('common/js/dask-theme.js')}"></script>
</head>

<!-- sidebar-mini：折叠时侧栏收缩为图标栏（保留 Logo 与主菜单入口），而非完全滑出隐藏 -->
<body class="layout-fixed sidebar-expand-lg sidebar-mini sidebar-without-hover dask-overflow-auto">
{if !$_ajax_open}
<div class="app-wrapper" id="wrapMain">
    {block name="top"}
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="切换侧栏"><svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M2 4h12M2 8h12M2 12h12"/></svg></a>
            </li>
        </ul>
        <div class="dask-admin-topnav navbar-nav">
            <ul class="js_left_menu top-nav" id="topNav">
                {$_nav|raw}
            </ul>
        </div>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item dask-theme-toggle">
                <a class="nav-link" href="javascript:;" data-theme-cycle data-theme-current="auto" aria-label="外观模式" title="外观模式（点击切换浅色/深色/自动）">
                    <svg class="dask-theme-svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" data-theme-icon aria-hidden="true"><circle cx="8" cy="8" r="6.1"></circle><path d="M8 1.9a6.1 6.1 0 0 1 0 12.2Z" fill="currentColor" fill-opacity=".16" stroke="none"></path><path d="M8 1.9v12.2"></path></svg>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{$baseUrl}" target="_blank" aria-label="访问前台">
                    <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 7 8 2.5 13.5 7v6a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V7Z"/><path d="M6.5 13.5V9h3v4.5"/></svg>
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false" aria-label="清理缓存">
                    <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13.2 6.4A5.4 5.4 0 0 0 3.4 4.8M2.8 9.6a5.4 5.4 0 0 0 9.8 1.6"/><path d="M13.5 2.7v3.7H9.8M2.5 13.3V9.6h3.7"/></svg>
                </a>
                <div class="dropdown-menu font-size-sm text-center" aria-labelledby="dropdownClear">
                    <a class="dropdown-item dask-ajax-get" data-url="{:url('index/clear',['type'=>'cache'])}"
                       href="javascript:void(0)">清除缓存</a>
                    <a class="dropdown-item dask-ajax-get" data-url="{:url('index/clear',['type'=>'log'])}"
                       href="javascript:void(0)">清除日志</a>
                    <a class="dropdown-item dask-ajax-get" data-url="{:url('index/clear',['type'=>'tmp'])}"
                       href="javascript:void(0)">清除临时文件</a>
                    <a class="dropdown-item dask-ajax-get" data-url="{:url('index/clear',['type'=>'all'])}"
                       href="javascript:void(0)">清除全部</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false" aria-label="待处理事项">
                    <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6a4 4 0 0 1 8 0c0 3.2.8 4.5 1.4 5H2.6C3.2 10.5 4 9.2 4 6Z"/><path d="M6.4 13a1.7 1.7 0 0 0 3.2 0"/></svg>
                    {if $notify_count}
                    <span class="badge text-bg-danger navbar-badge" style="top: 2px;right: -6px">{$notify_count}</span>
                    {/if}
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end" style="left: inherit; right: 0;">
                    <span class="dropdown-item dropdown-header">{$notify_count} 个问题待处理</span>
                    {volist name="notify_list" id="v"}
                    <div class="dropdown-divider"></div>
                    <a href="{$v.url}" class="dropdown-item">
                        {$v.text}
                    </a>
                    {/volist}
                </div>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{$user_info.avatar}" class="d-inline-block" width="20" height="20">
                    <span class="d-inline-block">{$user_info.name}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end" style="left: inherit; right: 0;">
                    <div class="p-3 text-center bg-primary">
                        <a href="javascript:;">
                            <img class="img-avatar img-avatar-thumb" width="48" height="48" src="{$user_info.avatar ? : '/static/common/image/avatar-default.png'}" alt="">
                        </a>
                    </div>
                    <div class="p-2 text-center">
                        <a class="dropdown-item align-items-center dask-ajax-get"  data-url="{:url('admin/Index/logout')}" href="#">
                            <span>{:L('退出登录')}</span>
                        </a>
                    </div>
                </div>
            </li>
        </ul>
        </div>
    </nav>
    {/block}

    {block name="left"}
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{$baseUrl}" class="brand-link bg-white" title="{$admin_logo_text}">
                <img src="{:asset('brand/logo-dudacms.svg', false)}" alt="{$admin_logo_text}" class="brand-image brand-image-compact" data-logo-fallback>
                <span class="brand-text">{$admin_logo_text}</span>
            </a>
        </div>
        <div class="sidebar-wrapper" id="sidebar">
            <nav class="mt-3 mb-2">
                <ul class="sidebar-menu nav nav-pills no_radius flex-column nav-child-indent js_left_menu_show"
                    data-lte-toggle="treeview" role="menu" data-accordion="true">
                    {$_menu|raw}
                </ul>
            </nav>
        </div>
    </aside>
    {/block}

    <main class="app-main dask-overflow-auto">
        <div id="mainTab">
            <!--后台全局页面提示钩子-->
            
            {if $breadCrumb && $breadCrumb.left.0}
            <div class="bg-white mb-1">
                <div class="p-3">
                    <div class="container-fluid">
                        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                            <h1 class="flex-sm-fill h3 my-2">
                                {$breadCrumb.left.0} <small class="d-sm-inline-block mt-2 mt-sm-0 font-size-base font-w400 text-muted">{$breadCrumb.left.1}</small>
                            </h1>
                            <nav class="flex-sm-00-auto ml-sm-3 d-xs-none" aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-alt mb-0">
                                    <li class="breadcrumb-item"><a href="{:url('Index/index')}">后台首页</a></li>
                                    <li class="breadcrumb-item" aria-current="page">
                                        <a class="link-fx" href="{:url($breadCrumb.right.url)}">{$breadCrumb.right.title}</a>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            {/if}
            <div class="container-fluid">
                {if isset($email_tips) && $email_tips}
                <div class="alert alert-warning mt-3">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <p>您还未配置邮箱，未配置邮箱可能会导致用户无法完成注册;去 <a href="{:url('admin.Config/config',['group'=>3])}"  style="text-decoration: none;">【配置邮箱】</a> </p>
                </div>
                {/if}

            </div>
            {block name="main"} {/block}
        </div>
    </main>

    {block name="footer"}
    <footer class="app-footer">
        <strong>© {:date('Y')} {:get_setting('site_name')} 保留所有权利。</strong>
        <div class="float-end d-none d-sm-inline-block">
            <b>{:get_setting('site_name')} V{:config('version.version')}</b>
        </div>
    </footer>
    {/block}
</div>
{else/}
<!--后台全局页面提示钩子-->

<div id="wrapMain" class="dask-overflow-auto">
{block name="main"} {/block}
</div>
{/if}
</body>
</html>
{else/}
{block name="main"} {/block}
{/if}
