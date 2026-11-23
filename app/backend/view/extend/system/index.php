{extend name="block" /}
{block name="main"}
<div class="container-fluid p-3" id="sysOpsPage">
    <div class="card card-rounded">
        <div class="card-header border-bottom-0 pb-0">
            <ul class="nav nav-tabs nav-tabs-alt sysops-tabs" role="tablist">
                {volist name="tabs" id="tab" key="k"}
                <li class="nav-item" role="presentation">
                    <button class="nav-link {if $k == 1}active{/if}" type="button" role="tab"
                            data-sysops-tab="{$tab.key}" data-sysops-src="{$tab.url}"
                            aria-selected="{if $k == 1}true{else}false{/if}">
                        <i class="{$tab.icon} me-1"></i>{$tab.title}
                    </button>
                </li>
                {/volist}
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="sysops-frame-wrap">
                {volist name="tabs" id="tab" key="k"}
                <iframe class="sysops-frame {if $k != 1}d-none{/if}"
                        data-sysops-frame="{$tab.key}"
                        {if $k == 1}src="{$tab.url}"{/if}
                        title="{$tab.title}" frameborder="0" scrolling="auto"></iframe>
                {/volist}
            </div>
        </div>
    </div>
</div>

<style>
.sysops-tabs { flex-wrap: nowrap !important; overflow-x: auto; overflow-y: hidden; scrollbar-width: none; }
.sysops-tabs::-webkit-scrollbar { display: none; }
.sysops-tabs .nav-item { flex: 0 0 auto; }
.sysops-tabs .nav-link { border: 0; background: transparent; font-weight: 500; color: var(--adm-text-2, #6b7280); padding: 12px 16px; cursor: pointer; white-space: nowrap; }
.sysops-tabs .nav-link.active { color: var(--adm-primary, #056DE8); box-shadow: inset 0 -2px 0 var(--adm-primary, #056DE8); }
@media (max-width: 575.98px) {
    .sysops-tabs { overflow-x: visible; padding-left: 0 !important; padding-right: 0 !important; gap: 0 !important; }
    .sysops-tabs .nav-item { flex: 1 1 0; text-align: center; }
    .sysops-tabs .nav-link { padding: 11px 0 !important; font-size: 11px; text-align: center; justify-content: center; white-space: nowrap; }
    .sysops-tabs .nav-link i { display: none; }
}
.sysops-frame-wrap { position: relative; min-height: 720px; }
.sysops-frame { width: 100%; height: 720px; border: 0; display: block; background: #fff; }
.sysops-frame.d-none { display: none; }
</style>

<script>
(function () {
    var page = document.getElementById('sysOpsPage');
    if (!page) return;
    var tabs = page.querySelectorAll('[data-sysops-tab]');
    var frames = page.querySelectorAll('[data-sysops-frame]');

    // iframe 高度自适应内容：表格等异步渲染会延迟撑高，需多次适配
    function fitFrame(frame) {
        try {
            var doc = frame.contentDocument;
            if (!doc || !doc.body) return;
            var h = Math.max(doc.body.scrollHeight, 720);
            frame.style.height = h + 'px';
        } catch (e) { /* 跨域时忽略 */ }
    }
    function fitFrameRepeatedly(frame, times) {
        var left = times || 5;
        var tick = function () {
            fitFrame(frame);
            if (--left > 0 && !frame.classList.contains('d-none')) {
                setTimeout(tick, 300);
            }
        };
        tick();
    }
    frames.forEach(function (f) {
        f.addEventListener('load', function () { fitFrameRepeatedly(f, 8); });
        // 监听 iframe 内部 DOM 变化（异步加载的表格/分页撑高后再次适配）
        f.addEventListener('load', function () {
            try {
                var body = f.contentDocument && f.contentDocument.body;
                if (!body || !window.MutationObserver) return;
                var debounce;
                var observer = new MutationObserver(function () {
                    clearTimeout(debounce);
                    debounce = setTimeout(function () { fitFrame(f); }, 150);
                });
                observer.observe(body, { childList: true, subtree: true });
            } catch (e) { /* 忽略 */ }
        });
    });

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-sysops-tab');
            tabs.forEach(function (t) {
                var on = t === tab;
                t.classList.toggle('active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            frames.forEach(function (f) {
                var isTarget = f.getAttribute('data-sysops-frame') === key;
                f.classList.toggle('d-none', !isTarget);
                // 懒加载：首次切换到该 Tab 时才设置 src
                if (isTarget && !f.getAttribute('src')) {
                    f.setAttribute('src', tab.getAttribute('data-sysops-src'));
                } else if (isTarget) {
                    fitFrame(f);
                }
            });
        });
    });

    // 父页面尺寸变化时重新适配当前可见 iframe
    window.addEventListener('resize', function () {
        frames.forEach(function (f) { if (!f.classList.contains('d-none')) fitFrame(f); });
    });
})();
</script>
{/block}
