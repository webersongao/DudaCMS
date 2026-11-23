{__NOLAYOUT__}
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>正在跳转</title>
    <link rel="stylesheet" href="/static/common/design/tokens.css">
    <script>
    (function () {
        try {
            var pref = localStorage.getItem('dask_theme') || 'auto';
            if (['light', 'dark', 'auto'].indexOf(pref) === -1) pref = 'auto';
            document.documentElement.setAttribute('data-theme', pref);
            var dark = pref === 'dark' || (pref === 'auto' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-dark', dark ? '1' : '0');
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        } catch (e) {}
    })();
    </script>
    <style>
        *{box-sizing:border-box}
        body{min-height:100vh;margin:0;display:flex;align-items:center;justify-content:center;padding:16px;color:var(--dask-text,#333);background:var(--dask-bg,#f6f6f6);font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Microsoft YaHei",Arial,sans-serif}
        .dask-page{width:100%;display:flex;justify-content:center}
        .dask-card{width:min(360px,100%);padding:32px 24px;text-align:center;background:var(--dask-surface,#fff);border:1px solid var(--dask-line,#e5e7eb);border-radius:var(--dask-r-lg,12px)}
        .dots{display:flex;justify-content:center;gap:8px;margin-bottom:16px}
        .dot{width:12px;height:12px;border-radius:50%;background:var(--dask-primary-bg,#0d6efd);animation:pulse 1s infinite alternate}
        .dot:nth-child(2){animation-delay:.15s}.dot:nth-child(3){animation-delay:.3s}.dot:nth-child(4){animation-delay:.45s}.dot:nth-child(5){animation-delay:.6s}
        @keyframes pulse{from{opacity:.3;transform:scale(.8)}to{opacity:1;transform:scale(1.2)}}
        .tip{font-size:14px;line-height:1.6;color:var(--dask-text-2,#666)}
    </style>
</head>
<body>
<main class="dask-page">
    <div class="dask-card">
        <div class="dots" aria-hidden="true"><i class="dot"></i><i class="dot"></i><i class="dot"></i><i class="dot"></i><i class="dot"></i></div>
        <p class="tip">正在跳转，请稍候…</p>
    </div>
</main>
<script type="text/javascript">
    (function(){
        var wait = {$wait},
            href = "{$url}";
        var interval = setInterval(function(){
            if (--wait <= 0) {
                window.location.href = href;
                clearInterval(interval);
            }
        }, 1000);
    })();
</script>
</body>
</html>
