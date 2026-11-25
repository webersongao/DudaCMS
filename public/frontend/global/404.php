{__NOLAYOUT__}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="{:html_lang()}" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404-{:L('对不起')}！{:L('您访问的页面不存在')}</title>
    <script src="{$assetBase}common/js/dask-theme-init.js?v={:asset_version('common/js/dask-theme-init.js')}" data-default="{:get_setting('theme_mode','auto')}"></script>
    <script>
    document.addEventListener('click', function (e) {
        var target = e.target && e.target.closest ? e.target.closest('[data-action="history-back"]') : null;
        if (!target) return;
        e.preventDefault();
        window.history.back();
    });
    </script>
    <link rel="stylesheet" href="{$assetBase}common/design/tokens.css?v={:asset_version('common/design/tokens.css')}">
    <style type="text/css">
        *{box-sizing:border-box;}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;background:var(--dask-bg,#e6e8eb);color:var(--dask-text,#333);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","Microsoft YaHei",sans-serif;-webkit-font-smoothing:antialiased;}
        .dask-page{width:100%;display:flex;justify-content:center;}
        .dask-card{background:var(--dask-surface,#fff);border:1px solid var(--dask-line,#e5e7eb);border-radius:var(--dask-r-lg,12px);box-shadow:0 12px 32px rgba(0,0,0,.08);}
        .dask-state{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;width:min(560px,100%);padding:28px 24px 30px;text-align:center;}
        .head404{width:min(580px,100%);max-height:234px;aspect-ratio:580/234;background:url('{:asset('brand/error-404.png', false)}') no-repeat center / contain;}
        .txtbg404{position:relative;display:flex;align-items:center;justify-content:center;width:min(499px,100%);min-height:169px;aspect-ratio:499/169;}
        .txtbg404::before{content:"";position:absolute;inset:0;background:url('{:asset('brand/error-404-text.png', false)}') no-repeat center / contain;opacity:.12;}
        .txtbg404 .txtbox{position:relative;z-index:1;width:90%;color:var(--dask-text,#3a3a3a);font-size:13px;line-height:1.7;}
        .txtbg404 .txtbox p{margin:5px 0;overflow-wrap:anywhere;}
        .txtbg404 .txtbox .paddingBox{padding-top:8px;}
        .txtbg404 .txtbox p a{font-weight:600;color:var(--dask-blue,#0d6efd);text-decoration:none;}
        .txtbg404 .txtbox p a:hover{text-decoration:underline;}
        .dask-actions{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;margin-top:8px;}
        .dask-btn-primary,.dask-btn-secondary{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:8px 18px;border-radius:var(--dask-r-sm,6px);font-size:14px;text-decoration:none;cursor:pointer;transition:background .15s,border-color .15s;}
        .dask-btn-primary{background:var(--dask-primary-bg,#0d6efd);border:1px solid transparent;color:#fff;}
        .dask-btn-primary:hover{background:var(--dask-primary-bg-hover,#0b5ed7);}
        .dask-btn-secondary{background:transparent;border:1px solid var(--dask-line,#cfd4da);color:var(--dask-text,#333);}
        .dask-btn-secondary:hover{border-color:var(--dask-blue,#0d6efd);color:var(--dask-blue,#0d6efd);}
        @media (max-width:767px){
            body{align-items:flex-start;padding:12px;}
            .dask-state{padding:20px 14px 24px;}
            .txtbg404{min-height:150px;aspect-ratio:auto;}
        }
        @media (max-width:360px){
            .dask-actions{flex-direction:column;}
            .dask-btn-primary,.dask-btn-secondary{width:100%;}
        }
    </style>
</head>
<body>
<main class="dask-page">
    <div class="dask-card dask-state">
        <div class="head404" aria-hidden="true"></div>
        <div class="txtbg404">
            <div class="txtbox">
                <p>{:L('对不起')}，{:L('您请求的页面不存在')}、{:L('或已被删除')}、{:L('或暂时不可用')}</p>
                <p class="paddingBox">{:L('请点击以下链接继续浏览网页')}</p>
            </div>
        </div>
        <div class="dask-actions">
            <a style="cursor:pointer" href="javascript:;" data-action="history-back" class="dask-btn-secondary">{:L('返回上一页面')}</a>
            <a href="/" class="dask-btn-primary">{:L('返回网站首页')}</a>
        </div>
    </div>
</main>
</body>
</html>
