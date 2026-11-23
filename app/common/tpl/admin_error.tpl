<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>后台功能提示 - DudaCMS</title>
<link rel="stylesheet" href="/static/common/design/tokens.css">
<script src="/static/common/js/standalone-theme.js"></script>
<style>
  :root { --blue:var(--dask-blue,#056de8); --text:var(--dask-text,#1d2129); --muted:var(--dask-text-3,#86909c); --line:var(--dask-line,#e5e6eb); --bg:var(--dask-bg,#f5f7fa); --danger:var(--dask-danger,#d93026); --surface:var(--dask-surface,#fff); --diag:var(--dask-bg,#fafbfc); }
  * { box-sizing:border-box; }
  body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
         background:var(--bg); color:var(--text); font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Microsoft YaHei",sans-serif; }
  .dask-admin-error { width:100%; max-width:720px; background:var(--surface); border:1px solid var(--line); border-radius:14px;
                    box-shadow:0 12px 36px rgba(29,33,41,.08); padding:36px 40px 32px; }
  .dask-error-mark { width:52px; height:52px; margin:0 auto 18px; border-radius:50%; background:rgba(217,48,38,.12); color:var(--danger);
                   display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; }
  .dask-error-title { margin:0 0 10px; text-align:center; font-size:22px; font-weight:600; }
  .dask-error-message { margin:0; text-align:center; font-size:16px; color:var(--text); line-height:1.7; }
  .dask-error-hint { margin:10px auto 0; max-width:560px; text-align:center; font-size:13px; color:var(--muted); line-height:1.7; }
  .dask-error-diag { margin:26px 0 24px; padding:18px 20px; background:var(--diag); border:1px solid var(--line); border-radius:8px; }
  .dask-error-diag h2 { margin:0 0 12px; font-size:14px; font-weight:600; color:var(--text); }
  .dask-error-diag dl { margin:0; display:grid; grid-template-columns:96px minmax(0,1fr); gap:8px 12px; font-size:13px; line-height:1.6; }
  .dask-error-diag dt { color:var(--muted); }
  .dask-error-diag dd { margin:0; color:var(--text); word-break:break-all; }
  .dask-error-diag code { font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:12px; }
  .dask-error-actions { display:flex; flex-wrap:wrap; justify-content:center; gap:10px; }
  .dask-error-actions a, .dask-error-actions button { min-width:110px; height:38px; padding:0 18px; border-radius:6px;
        font-size:14px; line-height:36px; text-align:center; text-decoration:none; cursor:pointer; transition:.12s ease; }
  .dask-error-primary { background:var(--blue); border:1px solid var(--blue); color:#fff; }
  .dask-error-primary:hover { background:#0457c7; }
  .dask-error-secondary { background:var(--surface); border:1px solid var(--line-2,#dcdfe6); color:var(--text); }
  .dask-error-secondary:hover { border-color:var(--blue); color:var(--blue); }
  .dask-error-copy-state { min-height:20px; margin-top:10px; text-align:center; font-size:12px; color:var(--muted); }
  html[data-dark="1"] .dask-error-primary { color:#fff; }
  @media (max-width:640px) { .dask-admin-error { padding:26px 20px; } .dask-error-diag dl { grid-template-columns:1fr; gap:2px; } .dask-error-diag dd { margin-bottom:8px; } }
</style>
</head>
<body>
<main class="dask-admin-error" role="alert">
  <div class="dask-error-mark" aria-hidden="true">!</div>
  <h1 class="dask-error-title">后台功能暂时不可用</h1>
  <p class="dask-error-message">{$error_message}</p>
  <p class="dask-error-hint">{$error_hint}</p>

  <section class="dask-error-diag">
    <h2>诊断信息（便于定位问题）</h2>
    <dl>
      <dt>错误类型</dt><dd>{$error_type}</dd>
      <dt>功能位置</dt><dd><code>{$error_module} / {$error_controller} / {$error_action}</code></dd>
      <dt>请求地址</dt><dd><code>{$error_url}</code></dd>
      {if $error_file}
      <dt>出错文件</dt><dd><code>{$error_file}{if $error_line}:{$error_line}{/if}</code></dd>
      {/if}
      {if $error_trace}
      <dt>调用堆栈</dt><dd><code>{$error_trace}</code></dd>
      {/if}
    </dl>
  </section>

  <div class="dask-error-actions">
    {if $back_url}<a class="dask-error-secondary" href="{$back_url}">返回上一页</a>{/if}
    <a class="dask-error-primary" href="{$home_url}">返回后台首页</a>
    <button type="button" class="dask-error-secondary" id="daskCopyError">复制诊断信息</button>
  </div>
  <div class="dask-error-copy-state" id="daskCopyState" aria-live="polite"></div>
</main>
<div id="dask-diag-text" hidden>{$error_message}
{$error_hint}
错误类型：{$error_type}
功能位置：{$error_module} / {$error_controller} / {$error_action}
请求地址：{$error_url}
{if $error_file}出错文件：{$error_file}{if $error_line}:{$error_line}{/if}
{/if}</div>
<script>
(function () {
  var button = document.getElementById('daskCopyError');
  var state = document.getElementById('daskCopyState');
  if (!button || !state) return;
  button.addEventListener('click', function () {
    var text = (document.getElementById('dask-diag-text') || {}).textContent || '';
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text.trim()).then(function () {
        state.textContent = '诊断信息已复制';
      }).catch(function () {
        state.textContent = '复制失败，请手动选择诊断信息';
      });
      return;
    }
    state.textContent = '当前浏览器不支持自动复制，请手动选择诊断信息';
  });
})();
</script>
</body>
</html>
