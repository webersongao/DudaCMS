<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{:csrf_token()}">
    <script src="/static/common/js/dask-csrf.js"></script>
    <title>管理后台登录 - {:get_setting('site_name')}</title>
    <meta name="description" content="">
    <meta name="theme-color" content="#056DE8">
    <link rel="stylesheet" href="/static/common/design/tokens.css?v={:asset_version('common/design/tokens.css')}">
    <link rel="stylesheet" href="/static/vendor/adminlte/dist/css/adminlte.min.css?v={:asset_version('vendor/adminlte/dist/css/adminlte.min.css')}">
    <link rel="stylesheet" href="/static/vendor/captcha/css/captcha.css">
    <link rel="stylesheet" href="/static/backend/css/admin-modern.css?v={:asset_version('backend/css/admin-modern.css')}">
    <script>
        window.DASK_ADMIN_LOGIN = {
            captcha: {if $setting.enable_backend_captcha=='Y'}true{else/}false{/if},
            dashboardUrl: "{:url('Index/index')}"
        };
        window.baseUrl = '{$baseUrl}';
    </script>
    <script src="/static/vendor/layui/layui.all.js?v={:asset_version('vendor/layui/layui.all.js')}"></script>
    <script src="/static/vendor/jquery/dist/jquery.min.js?v={:asset_version('vendor/jquery/dist/jquery.min.js')}"></script>
    <script src="/static/vendor/bootstrap/dist/js/bootstrap.bundle.min.js?v={:asset_version('vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}"></script>
    <script src="/static/common/js/dask-string.js?v={:asset_version('common/js/dask-string.js')}"></script>
    <script src="/static/vendor/captcha/captcha.js"></script>
    <script defer src="/static/backend/js/admin-login.js?v={:asset_version('backend/js/admin-login.js')}"></script>
</head>
<body class="admin-login-page">
<main class="signBox">
    <section class="login-shell">
        <div class="login-card">
            <div class="login-brand" aria-hidden="true">
                <span class="login-brand-mark">逼</span>
            </div>
            <h1 class="login-title">管理后台登录</h1>
            <p class="login-subtitle">请使用管理员账号继续</p>

            <form id="adminLoginForm" class="login-form" action="{:url('Index/login')}" method="post" novalidate>
                <input type="hidden" name="captcha" id="captcha">
                {:csrf_field()}

                <div class="mb-3">
                    <label class="visually-hidden" for="username">用户名</label>
                    <input id="username" type="text" class="form-control dask-form-control"
                           name="username" autocomplete="username" spellcheck="false"
                           placeholder="用户名" required
                           {if isset($user_info)}value="{$user_info['user_name']}" readonly{/if}>
                </div>

                <div class="mb-3">
                    <label class="visually-hidden" for="password">密码</label>
                    <input id="password" type="password" class="form-control dask-form-control"
                           name="password" autocomplete="current-password"
                           placeholder="密码" required>
                </div>

                <p id="loginError" class="login-error" role="alert" aria-live="polite"></p>

                <button type="submit" class="btn btn-primary w-100 login-button">
                    <span class="login-button-text">登录</span>
                </button>
            </form>
        </div>
    </section>
</main>
</body>
</html>
