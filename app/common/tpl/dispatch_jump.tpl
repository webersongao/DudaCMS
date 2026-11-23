{__NOLAYOUT__}
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>跳转提示</title>
    <link rel="stylesheet" href="/static/common/design/tokens.css">
    <script>
    (function () {
        try {
            var pref = localStorage.getItem('dask_theme');
            if (!pref) pref = 'auto';
            if (['light', 'dark', 'auto'].indexOf(pref) === -1) pref = 'auto';
            document.documentElement.setAttribute('data-theme', pref);
            var dark = pref === 'dark' || (pref === 'auto' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-dark', dark ? '1' : '0');
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        } catch (e) {}
        document.addEventListener('click', function (e) {
            var target = e.target && e.target.closest ? e.target.closest('[data-action="history-back"]') : null;
            if (!target) return;
            e.preventDefault();
            window.history.back();
        });
    })();
    </script>
    <style type="text/css">
        *{box-sizing:border-box;margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Microsoft YaHei","Noto Sans SC",Arial,sans-serif;-webkit-font-smoothing:antialiased}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;color:var(--dask-text,#333);background:rgba(0,0,0,.7)}
        .dask-page{width:100%;display:flex;justify-content:center}
        .dask-card{background:var(--dask-surface,#fff);border:1px solid var(--dask-line,#e5e7eb);border-radius:var(--dask-r-lg,12px);box-shadow:0 12px 32px rgba(0,0,0,.18)}
        .dask-state{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;width:min(480px,100%);padding:28px 24px;text-align:center}
        .system-message h1{font-size:1.3rem;font-weight:400;line-height:1.5;overflow-wrap:anywhere}
        .system-message .jump{font-size:14px;line-height:1.6;color:var(--dask-text-2,#666)}
        .system-message .jump a{color:var(--dask-blue,#0d6efd)}
        .dask-actions{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;width:100%;margin-top:6px}
        .dask-btn-primary,.dask-btn-secondary{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:8px 18px;border-radius:var(--dask-r-sm,6px);font-size:15px;text-decoration:none;cursor:pointer;transition:background .15s,border-color .15s}
        .dask-btn-secondary{background:transparent;border:1px solid var(--dask-line,#cfd4da);color:var(--dask-text,#333)}
        .dask-btn-primary{background:var(--dask-primary-bg,#0d6efd);border:1px solid transparent;color:#fff}
        html[data-dark="1"] .dask-btn-secondary{color:var(--dask-text,#e8eaed)}
        .clearfix:after{clear:both;display:block;visibility:hidden;height:0;content:"."}
        @media (max-width:480px){.system-message h1{font-size:1.15rem}}
        @media (max-width:360px){.dask-actions{flex-direction:column}.dask-btn-primary,.dask-btn-secondary{width:100%}}
    </style>
</head>
<body>
<?php $codeText = $code == 1 ? 'success' : ($code == 0 ? 'error' : 'info'); $url = empty($url) ? '/' : $url; ?>
<main class="dask-page">
    <section class="system-message {$codeText} dask-card dask-state">
        <div class="image"><img src="/static/common/image/{$codeText}.svg" alt="" width="80" /></div>
        <h1><?php echo strip_tags((string) $msg); ?></h1>
        <p class="jump">页面将在 <span id="wait"><?php echo (int) $wait; ?></span> 秒后自动跳转</p>
        <p class="clearfix dask-actions">
            <a href="javascript:;" data-action="history-back" class="dask-btn-secondary">返回上一页</a>
            <a id="href" href="{$url}" class="dask-btn-primary">立即跳转</a>
        </p>
    </section>
</main>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            }
        }, 1000);
    })();
</script>
</body>
</html>
