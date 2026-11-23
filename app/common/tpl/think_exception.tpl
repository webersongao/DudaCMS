<?php
$cdnurl = function_exists('config') ? config('view_replace_str.__CDN__') : '';
$publicurl = function_exists('config') ? config('view_replace_str.__PUBLIC__') : '/';
$debug = function_exists('config') ? config('app_debug') : false;

$lang = [
    'An error occurred' => '发生错误',
    'Home' => '返回首页',
    'Feedback' => '反馈问题',
    'The page you are looking for is temporarily unavailable' => '这个页面暂时无法访问',
    'You can return to the previous page and try again' => '可能是网络波动或页面地址已变化，你可以返回首页继续浏览。',
    'Try again' => '刷新重试',
];

$langSet = '';
if (isset($_GET['lang'])) {
    $langSet = strtolower($_GET['lang']);
} elseif (isset($_COOKIE['think_var'])) {
    $langSet = strtolower($_COOKIE['think_var']);
} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
    $langSet = strtolower($matches[1]);
}
$langSet = $langSet && in_array($langSet, ['zh-cn', 'en']) ? $langSet : 'zh-cn';
$langSet == 'en' && $lang = array_combine(array_keys($lang), array_keys($lang));

$siteName = '';
try {
    if (function_exists('get_setting')) {
        $siteName = (string) get_setting('site_name');
    }
} catch (\Throwable $e) {
    $siteName = '';
}
$siteName = $siteName !== '' ? $siteName : 'DudaCMS';
$e = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
$safeMessage = $e($message ?? '');
$jsonMessage = json_encode((string) ($message ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="<?=$e($langSet)?>">
<head>
    <meta charset="utf-8">
    <title><?=$e($lang['An error occurred'])?> - <?=$e($siteName)?></title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="stylesheet" href="/static/common/design/tokens.css">
    <script src="/static/common/js/standalone-theme.js"></script>
    <style>
        :root {
            --blue: var(--dask-blue, #056de8);
            --blue-dark: var(--dask-blue-hover, #0457c7);
            --text: var(--dask-text, #121212);
            --muted: var(--dask-text-3, #8590a6);
            --line: var(--dask-line, #e5e5e5);
            --bg: var(--dask-bg, #f6f6f6);
            --surface: var(--dask-surface, #fff);
            --diag: var(--dask-bg, #f7f8fa);
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; min-height: 100%; }
        body {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 24px;
            background: var(--bg); color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", "Noto Sans SC", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .dask-error-card {
            width: 100%; max-width: 520px; padding: 40px 32px 32px;
            background: var(--surface); border: 1px solid var(--line); border-radius: 12px;
            box-shadow: 0 12px 36px rgba(18, 18, 18, .06); text-align: center;
        }
        .dask-error-brand { margin-bottom: 28px; color: var(--blue); font-size: 18px; font-weight: 600; letter-spacing: -.2px; }
        .dask-error-icon {
            width: 72px; height: 72px; margin: 0 auto 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: var(--dask-blue-tint, #eaf3ff); color: var(--blue);
        }
        .dask-error-icon svg { width: 36px; height: 36px; }
        .dask-error-title { margin: 0 0 12px; font-size: 24px; line-height: 1.4; font-weight: 500; }
        .dask-error-desc { margin: 0 auto; max-width: 380px; color: var(--muted); font-size: 14px; line-height: 1.8; }
        .dask-error-debug { margin: 18px auto 0; padding: 10px 12px; border-radius: 6px; background: var(--diag); color: var(--muted); font-size: 12px; text-align: left; word-break: break-all; }
        .dask-error-actions { display: flex; gap: 12px; justify-content: center; margin-top: 28px; }
        .dask-error-btn {
            min-width: 132px; height: 40px; padding: 0 20px; border-radius: 6px;
            border: 1px solid transparent; font-size: 14px; line-height: 38px;
            text-decoration: none; cursor: pointer; transition: background .12s ease, border-color .12s ease, transform .12s ease;
        }
        .dask-error-btn:active { transform: translateY(1px); }
        .dask-error-btn-primary { background: var(--blue); border-color: var(--blue); color: #fff; }
        .dask-error-btn-primary:hover { background: var(--blue-dark); border-color: var(--blue-dark); color: #fff; }
        html[data-dark="1"] .dask-error-btn-primary { color: #fff; }
        .dask-error-btn-secondary { background: var(--surface); border-color: var(--line); color: var(--text); }
        .dask-error-btn-secondary:hover { border-color: var(--blue); color: var(--blue); }
        @media (max-width: 480px) {
            body { padding: 16px; align-items: flex-start; }
            .dask-error-card { margin-top: 8vh; padding: 32px 20px 24px; }
            .dask-error-title { font-size: 20px; }
            .dask-error-actions { flex-direction: column; }
            .dask-error-btn { width: 100%; }
        }
    </style>
</head>
<body>
<main class="dask-error-card" role="main">
    <div class="dask-error-brand"><?=$e($siteName)?></div>
    <div class="dask-error-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 7v6"></path>
            <path d="M12 17h.01"></path>
        </svg>
    </div>
    <h1 class="dask-error-title"><?=$e($debug ? ($message ?: $lang['An error occurred']) : $lang['The page you are looking for is temporarily unavailable'])?></h1>
    <p class="dask-error-desc"><?=$e($lang['You can return to the previous page and try again'])?></p>
    <?php if ($debug && $safeMessage !== ''): ?>
        <div class="dask-error-debug"><?=$safeMessage?></div>
    <?php endif; ?>
    <div class="dask-error-actions">
        <a class="dask-error-btn dask-error-btn-primary" href="/"><?=$e($lang['Home'])?></a>
        <button class="dask-error-btn dask-error-btn-secondary" id="daskErrorFeedback" type="button"><?=$e($lang['Feedback'])?></button>
    </div>
</main>
<script>
    (function () {
        var btn = document.getElementById('daskErrorFeedback');
        if (!btn) return;
        btn.addEventListener('click', function () {
            btn.disabled = true;
            btn.textContent = '提交中…';
            fetch('/ajax/report_bug', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'msg=' + encodeURIComponent(<?=$jsonMessage?>)
            }).then(function (r) { return r.json(); }).then(function (data) {
                alert(data && data.code ? '反馈成功，感谢你的帮助！' : '反馈已提交');
            }).catch(function () {
                alert('反馈暂时不可用，请稍后再试');
            }).finally(function () {
                btn.disabled = false;
                btn.textContent = <?=json_encode($lang['Feedback'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)?>;
            });
        });
    })();
</script>
</body>
</html>
