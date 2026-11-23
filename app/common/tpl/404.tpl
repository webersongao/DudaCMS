{__NOLAYOUT__}
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404-对不起！您访问的页面不存在</title>
    {literal}
    <script>
    (function () {
        try {
            var pref = localStorage.getItem('dask_theme');
            if (!pref) {
                var m = document.cookie.match(/(?:^|; )dask_theme=([^;]*)/);
                pref = m ? decodeURIComponent(m[1]) : 'auto';
            }
            if (['light','dark','auto'].indexOf(pref) === -1) pref = 'auto';
            document.documentElement.setAttribute('data-theme', pref);
            var dark = pref === 'dark' || (pref === 'auto' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-dark', dark ? '1' : '0');
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        } catch (e) {}
    })();
    </script>
    {/literal}
    <link rel="stylesheet" href="/static/common/design/tokens.css">
    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: var(--dask-bg, #494949);
            color: var(--dask-text);
            font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", sans-serif;
        }
        .dask-page { width: 100%; display: flex; justify-content: center; }
        .dask-card {
            width: min(600px, 100%);
            overflow: hidden;
            border-radius: 12px;
            background: var(--dask-surface);
            box-shadow: 0 16px 48px rgba(0,0,0,.28);
        }
        .head404 {
            width: 100%;
            aspect-ratio: 580 / 234;
            background: url(/static/brand/error-404.png) center / contain no-repeat;
        }
        .txtbg404 {
            position: relative;
            width: 100%;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 20px;
            background: var(--dask-blue-tint, #e9ebef);
        }
        .txtbg404::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url(/static/brand/error-404-text.png) center / contain no-repeat;
            opacity: .12;
        }
        .txtbox {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            color: var(--dask-text-2, var(--dask-muted, #646464));
            font-size: 13px;
            line-height: 1.7;
            text-align: center;
            overflow-wrap: anywhere;
        }
        .txtbox p { margin: 4px 0; }
        .txtbox .paddingbox { padding-top: 8px; }
        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            padding: 16px;
            background: var(--dask-surface);
        }
        .actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 8px 18px;
            border: 1px solid var(--dask-line);
            border-radius: 6px;
            color: var(--dask-text);
            font-size: 14px;
            text-decoration: none;
        }
        .actions a.primary { background: var(--dask-blue); border-color: var(--dask-blue); color: #fff; }
        .actions a:hover { text-decoration: underline; }
        @media (max-width: 767px) {
            body { align-items: flex-start; padding: 12px; }
            .head404 { aspect-ratio: 580 / 240; }
            .txtbg404 { min-height: 132px; padding: 14px 16px; }
            .actions { flex-direction: column; }
            .actions a { width: 100%; }
        }
    </style>
</head>
<body>
<main class="dask-page">
    <div class="dask-card">
        <div class="head404" aria-hidden="true"></div>
        <div class="txtbg404">
            <div class="txtbox">
                <p>对不起，您请求的页面不存在、或已被删除、或暂时不可用</p>
                <p class="paddingbox">请点击以下链接继续浏览网页</p>
            </div>
        </div>
        <div class="actions">
            <a href="javascript:;" onclick="history.back()">返回上一页面</a>
            <a class="primary" href="/">返回网站首页</a>
        </div>
    </div>
</main>
</body>
</html>
