{__NOLAYOUT__}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{:get_setting('site_name')} - {:L('网站维护中')}</title>
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
    <link rel="stylesheet" href="{$assetBase}common/design/tokens.css?v={:asset_version('common/design/tokens.css')}">
    <meta name="title" content="{:get_setting('site_name')} - {:L('网站维护中')}"/>
    <meta name="keywords" content="{:get_setting('site_name')}, {:get_setting('seo_keywords')}"/>
    <meta name="image" content="{$assetBase}frontend/image/ameta.webp">
    <meta name="description" content="{:get_setting('site_description')}"/>
    <style type="text/css">
        *{box-sizing:border-box;}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;background:var(--dask-bg,#e6e8eb);color:var(--dask-text,#333);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","Microsoft YaHei",sans-serif;-webkit-font-smoothing:antialiased;}
        .dask-page{width:100%;display:flex;justify-content:center;}
        .dask-card{background:var(--dask-surface,#fff);border:1px solid var(--dask-line,#e5e7eb);border-radius:var(--dask-r-lg,12px);box-shadow:0 12px 32px rgba(0,0,0,.08);}
        .dask-state{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;width:min(560px,100%);padding:28px 24px 30px;text-align:center;}
        .head404{width:min(580px,100%);max-height:234px;aspect-ratio:580/234;background:url('{:asset('brand/error-404.png', false)}') no-repeat center / contain;}
        .txtbg404{position:relative;display:flex;align-items:center;justify-content:center;width:min(499px,100%);min-height:169px;aspect-ratio:499/169;}
        .txtbg404::before{content:"";position:absolute;inset:0;background:url('{:asset('brand/error-404-text.png', false)}') no-repeat center / contain;opacity:.12;}
        .txtbg404 .txtbox{position:relative;z-index:1;width:92%;color:var(--dask-text,#3a3a3a);font-size:16px;line-height:1.7;overflow-wrap:anywhere;}
        .txtbg404 .txtbox p{margin:5px 0;}
        @media (max-width:767px){
            body{align-items:flex-start;padding:12px;}
            .dask-state{padding:20px 14px 24px;}
            .txtbg404{min-height:150px;aspect-ratio:auto;}
            .txtbg404 .txtbox{font-size:15px;}
        }
        @media (max-width:360px){
            .txtbg404 .txtbox{font-size:14px;}
        }
    </style>
</head>
<body>
<main class="dask-page">
    <div class="dask-card dask-state">
        <div class="head404" aria-hidden="true"></div>
        <div class="txtbg404">
            <div class="txtbox">
                <p>{:L('对不起')}，{:L('网站维护中')}</p>
                <!--<p>{:L('预计开放时间：2024-07-01 12：00')}</p>-->
                <p>{:L('如您需要沟通其他事宜，请联系站长：')}ask@btbk.org</p>
            </div>
        </div>
    </div>
</main>
</body>
</html>
