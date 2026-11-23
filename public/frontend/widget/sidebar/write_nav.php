<style>
.bh-sidesignHeader {
    background:url({$static_url}image/bh-logincard.png) no-repeat;
    background-size: 100%;
    border: 8px solid transparent;
}
</style>
{if $user_id}
<div class="r-box w-fun write-nav-box mb-2">
    <div class="row">
        <a class="col-4 text-center" href="{:url('question/index?sort=unresponsive')}">
            <span class="bh-quick-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4.5 5.5h9"/><path d="M4.5 12h4.5"/><path d="M4.5 18.5h4.5"/>
                    <path d="M13.6 16.7 19 11.3a1.9 1.9 0 0 0-2.7-2.7l-5.4 5.4-.9 3.6 3.6-.9Z"/>
                </svg>
            </span>
            <h6>{:L('写回答')}</h6>
        </a>
        <a class="col-4 text-center" href="{:url('question/publish')}">
            <span class="bh-quick-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="8.4"/>
                    <path d="M9.6 9.3a2.5 2.5 0 1 1 3.3 2.4c-.7.3-1 .8-1 1.6"/>
                    <circle cx="11.9" cy="16.9" r="1" fill="currentColor" stroke="none"/>
                </svg>
            </span>
            <h6>{:L('提问题')}</h6>
        </a>
        <a class="col-4 text-center" href="{:url('article/publish')}">
            <span class="bh-quick-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 3.5h8l4.2 4.2V19a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 4.2 19V5a1.5 1.5 0 0 1 1.5-1.5Z"/>
                    <path d="M14 3.7v4.1h4.1"/><path d="M8 12.2h7"/><path d="M8 16.2h5.5"/>
                </svg>
            </span>
            <h6>{:L('写文章')}</h6>
        </a>
    </div>
</div>
<div class="dask-publish-fab" data-publish-fab aria-label="{:L('快捷发布')}">
    <div class="dask-publish-fab-menu" role="menu" aria-hidden="true">
        <a class="dask-publish-fab-item" role="menuitem" href="{:url('question/index?sort=unresponsive')}" aria-label="{:L('写回答')}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4.5 5.5h9"/><path d="M4.5 12h4.5"/><path d="M4.5 18.5h4.5"/>
                <path d="M13.6 16.7 19 11.3a1.9 1.9 0 0 0-2.7-2.7l-5.4 5.4-.9 3.6 3.6-.9Z"/>
            </svg>
            <span class="dask-publish-fab-label">{:L('写回答')}</span>
        </a>
        <a class="dask-publish-fab-item" role="menuitem" href="{:url('question/publish')}" aria-label="{:L('提问题')}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="8.4"/>
                <path d="M9.6 9.3a2.5 2.5 0 1 1 3.3 2.4c-.7.3-1 .8-1 1.6"/>
                <circle cx="11.9" cy="16.9" r="1" fill="currentColor" stroke="none"/>
            </svg>
            <span class="dask-publish-fab-label">{:L('提问题')}</span>
        </a>
        <a class="dask-publish-fab-item" role="menuitem" href="{:url('article/publish')}" aria-label="{:L('写文章')}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 3.5h8l4.2 4.2V19a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 4.2 19V5a1.5 1.5 0 0 1 1.5-1.5Z"/>
                <path d="M14 3.7v4.1h4.1"/><path d="M8 12.2h7"/><path d="M8 16.2h5.5"/>
            </svg>
            <span class="dask-publish-fab-label">{:L('写文章')}</span>
        </a>
    </div>
    <button type="button" class="dask-publish-fab-main" data-publish-fab-main aria-expanded="false" aria-label="{:L('快捷发布')}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
            <path d="M12 5.5v13"/><path d="M5.5 12h13"/>
        </svg>
    </button>
</div>
<script>
(function () {
    if (window.__daskPublishFabReady) return;
    window.__daskPublishFabReady = true;
    function closeAll(except) {
        document.querySelectorAll('.dask-publish-fab.is-open').forEach(function (fab) {
            if (fab === except) return;
            fab.classList.remove('is-open');
            var btn = fab.querySelector('[data-publish-fab-main]');
            var menu = fab.querySelector('.dask-publish-fab-menu');
            if (btn) btn.setAttribute('aria-expanded', 'false');
            if (menu) menu.setAttribute('aria-hidden', 'true');
        });
    }
    document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest ? e.target.closest('[data-publish-fab-main]') : null;
        if (btn) {
            e.preventDefault();
            var fab = btn.closest('[data-publish-fab]');
            var open = !fab.classList.contains('is-open');
            closeAll(fab);
            fab.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            var menu = fab.querySelector('.dask-publish-fab-menu');
            if (menu) menu.setAttribute('aria-hidden', open ? 'false' : 'true');
            return;
        }
        if (!e.target || !e.target.closest || !e.target.closest('[data-publish-fab]')) closeAll();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll();
    });
})();
</script>
{else/}
<div class="bh-md-card">
    <div class="bh-sidesign">
        <div class="bh-sidesignHeader">
            <div class="bh-sidesignTitle">{:L('上 %s', get_setting('site_name'))}<br>{:get_setting('site_tagline', '与世界分享你的装逼技巧与水平')}</div>
                <div class="bh-sidesign-action">
                    <a href="/account/login" class="user-sidebtn bh-sidebtn-primary">{:L('登录')}</a>
                    <a href="/account/register" class="user-sidebtn bh-sidebtn-blue">注册</a>
                </div>
            </div>
        <div class="bh-sidesignFooter">
            <div class="bh-md-card bh-sidesign-focus">
                <a href="#" class="bh-sidecard-link">
                <div class="bh-sidecard-layout">
                  <img class="bh-sidecard-qrcode" src="{:asset('brand/qrcode.png', false)}" alt="扫一扫">
                  <div class="bh-sidecard-content">
                        <div class="bh-sidecard-title">{:L('最新动态、精彩推荐')}</div>
                        <div class="bh-sidecard-description">{:L('手机扫一扫，装逼没烦恼')}</div>
                  </div>
                </div>
            </a>
        </div>
      </div>
    </div>
</div>
{/if}