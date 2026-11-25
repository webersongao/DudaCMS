{__NOLAYOUT__}
<!DOCTYPE html>
<html lang="{:html_lang()}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>跳转提示</title>
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
        *{box-sizing:border-box;margin:0;padding:0;font-family:Lantinghei SC,Open Sans,Arial,Hiragino Sans GB,Microsoft YaHei,"微软雅黑",STHeiti,WenQuanYi Micro Hei,SimSun,sans-serif;-webkit-font-smoothing:antialiased}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;color:var(--dask-text,#333);background-color:rgba(0,0,0,.7);}
        .dask-page{width:100%;display:flex;justify-content:center;}
        .dask-card{background:var(--dask-surface,#fff);border:1px solid var(--dask-line,#e5e7eb);border-radius:var(--dask-r-lg,12px);box-shadow:0 12px 32px rgba(0,0,0,.18);}
        .dask-state{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;width:min(480px,100%);padding:28px 24px;text-align:center;}
        .system-message h1{margin:0;font-weight:400;font-size:1.3rem;line-height:1.4;overflow-wrap:anywhere;}
        .system-message .image{margin:0;}
        .system-message .jump{font-size:14px;line-height:1.6;color:var(--dask-text-2,#666);}
        .system-message .jump a{color:var(--dask-blue,#0d6efd)}
        .system-message p{font-size:9pt;line-height:20px}
        .dask-actions{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;width:100%;margin-top:6px;}
        .dask-btn-primary,.dask-btn-secondary{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:8px 18px;border-radius:var(--dask-r-sm,6px);font-size:1pc;text-decoration:none;cursor:pointer;transition:background .15s,border-color .15s;}
        .dask-btn-secondary{background:transparent;border:1px solid var(--dask-line,#cfd4da);color:var(--dask-text,#333);}
        .dask-btn-primary{background:var(--dask-primary-bg,#0d6efd);border:1px solid transparent;color:#fff;}
        .copyright p{color:#919191;text-align:center;font-size:10px}
        .clearfix:after{clear:both;display:block;visibility:hidden;height:0;content:"."}
        @media (max-width:480px){.system-message h1{font-size:1.15rem;}}
        @media (max-width:360px){.dask-actions{flex-direction:column;}.dask-btn-primary,.dask-btn-secondary{width:100%;}}
    </style>
</head>
<body>
<?php $codeText = $code == 1 ? 'success' : ($code == 0 ? 'error' : 'info'); $url = empty($url) ? '/' : $url; ?>
<main class="dask-page">
    <div class="system-message {$codeText} dask-card dask-state">
        <div class="image">
            <img src="/static/common/image/{$codeText}.svg" alt="" width="80" />
        </div>
        <h1 style="font-size: 1.3rem"><?php echo(strip_tags($msg));?></h1>
        <p class="jump">
            页面将在 <span id="wait"><?php echo($wait);?></span> 秒后自动跳转
        </p>
        <p class="clearfix dask-actions">
            <a href="#" role="button" data-action="history-back" class="btn btn-grey dask-btn-secondary">返回上一页</a>
            <a id="href" href="{$url}" class="btn btn-primary dask-btn-primary">立即跳转</a>
        </p>
    </div>
</main>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                <?php if(input('_ajax_open')){?>
                parent.layer.closeAll();
                location.href = href;
                <?php }else{?>
                location.href = href;
                <?php } ?>
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>
