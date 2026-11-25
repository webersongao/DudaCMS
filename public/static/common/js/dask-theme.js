/*!
 * DudaCMS 深浅色主题 + 返回顶部
 * - 主题三态：light / dark / auto（auto 跟随系统 prefers-color-scheme）
 * - 选择持久化到 localStorage，并同步 cookie 便于服务端/跨子域读取
 * - 自动模式下监听系统主题变化实时切换
 * - 返回顶部：滚动超过一屏（视口高度）显示，平滑回顶
 * 不依赖构建工具，纯 UMD 风格全局脚本。
 */
(function (window, document) {
  'use strict';

  var STORAGE_KEY = 'dask_theme';
  var COOKIE_KEY = 'dask_theme';
  var VALID = ['light', 'dark', 'auto'];
  var LABEL = { light: '浅色', dark: '深色', auto: '自动' };
  // 线性 SVG 与顶部私信/通知等图标同风格，避免实心半圆显得突兀。
  var THEME_SVG = {
    light: '<circle cx="8" cy="8" r="3.1"/><path d="M8 1v1.8M8 13.2V15M1 8h1.8M13.2 8H15M3.1 3.1l1.3 1.3M11.6 11.6l1.3 1.3M12.9 3.1l-1.3 1.3M4.4 11.6l-1.3 1.3"/>',
    dark: '<path d="M13.3 10.6A5.9 5.9 0 0 1 5.4 2.7a5.9 5.9 0 1 0 7.9 7.9Z"/>',
    auto: '<circle cx="8" cy="8" r="6.1"/><path d="M8 1.9a6.1 6.1 0 0 1 0 12.2Z" fill="currentColor" fill-opacity=".16" stroke="none"/><path d="M8 1.9v12.2"/>'
  };
  var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

  function readPreference() {
    var saved = null;
    try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) {}
    if (VALID.indexOf(saved) === -1) {
      // 兼容 cookie（服务端渲染首屏可用）
      var m = document.cookie.match(new RegExp('(?:^|; )' + COOKIE_KEY + '=([^;]*)'));
      saved = m ? decodeURIComponent(m[1]) : 'auto';
    }
    return VALID.indexOf(saved) === -1 ? 'auto' : saved;
  }

  function systemDark() { return !!(media && media.matches); }

  function nextPreference(pref) {
    var i = VALID.indexOf(pref);
    return VALID[(i < 0 ? 2 : i + 1) % VALID.length];
  }

  function apply(pref) {
    var dark = pref === 'dark' || (pref === 'auto' && systemDark());
    document.documentElement.setAttribute('data-theme', pref);
    document.documentElement.setAttribute('data-dark', dark ? '1' : '0');
    // Bootstrap 5 原生组件（dropdown / modal / table 等）按 data-bs-theme 着色。
    // dask-theme 是唯一主题真源；此处仅做桥接，避免与 AdminLTE 内置 ColorMode 形成双源。
    document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
    // 通知同页其它脚本（如图表）刷新配色
    try {
      document.dispatchEvent(new CustomEvent('dask:themechange', { detail: { preference: pref, dark: dark } }));
    } catch (e) {}
    updateMenu(pref);
    updateCycleButtons(pref);
  }

  function persist(pref) {
    try { localStorage.setItem(STORAGE_KEY, pref); } catch (e) {}
    document.cookie = COOKIE_KEY + '=' + encodeURIComponent(pref) + '; path=/; max-age=31536000; SameSite=Lax';
  }

  function set(pref) {
    if (VALID.indexOf(pref) === -1) return;
    persist(pref);
    apply(pref);
  }

  function updateMenu(pref) {
    var items = document.querySelectorAll('[data-theme-option]');
    for (var i = 0; i < items.length; i++) {
      items[i].classList.toggle('active', items[i].getAttribute('data-theme-option') === pref);
    }
  }

  function labelFor(pref, btn) {
    return (btn && btn.getAttribute('data-label-' + pref)) || LABEL[pref] || pref;
  }

  function updateCycleButtons(pref) {
    var buttons = document.querySelectorAll('[data-theme-cycle]');
    var next = nextPreference(pref);
    for (var i = 0; i < buttons.length; i++) {
      var btn = buttons[i];
      var label = labelFor(pref, btn) + ' → ' + labelFor(next, btn);
      btn.setAttribute('title', label);
      btn.setAttribute('aria-label', label);
      btn.setAttribute('data-theme-current', pref);
      var icon = btn.querySelector('[data-theme-icon]');
      if (!icon) continue;
      if (icon.tagName && icon.tagName.toLowerCase() === 'svg') {
        icon.innerHTML = THEME_SVG[pref] || THEME_SVG.auto;
        continue;
      }
      // 处理 FontAwesome <i> 形式的图标元素。
      var glyph = pref === 'light' ? 'fa-sun' : (pref === 'dark' ? 'fa-moon' : 'fa-adjust');
      var prefix = String(icon.className).indexOf('fas') === 0 ? 'fas' : 'fa';
      var extra = icon.getAttribute('data-theme-icon-extra') || '';
      icon.className = prefix + ' ' + glyph + (extra ? ' ' + extra : '');
    }
  }

  function initCycle() {
    document.addEventListener('click', function (e) {
      var target = e.target.closest ? e.target.closest('[data-theme-cycle]') : null;
      if (!target) return;
      e.preventDefault();
      set(nextPreference(readPreference()));
    });
  }

  function initToggle() {
    document.addEventListener('click', function (e) {
      var target = e.target.closest ? e.target.closest('[data-theme-option]') : null;
      if (!target) return;
      e.preventDefault();
      set(target.getAttribute('data-theme-option'));
      // 兼容仍残留旧下拉项的页面
      var dropdown = target.closest('.dropdown');
      if (dropdown && window.jQuery) {
        try { window.jQuery(dropdown).removeClass('show').find('.dropdown-menu').removeClass('show'); } catch (err) {}
      }
    });
  }

  function initBackTop() {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'dask-backtop';
    var backTopLabel = (window.DASK_LABELS && window.DASK_LABELS.backTop) || '返回顶部';
    btn.setAttribute('aria-label', backTopLabel);
    btn.title = backTopLabel;
    // 用内联 SVG 取代 iconfont：实测 e9d6 在站内字体中没有真实字形，按钮会显示为空心圆。
    btn.innerHTML = '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="8" r="6.2"/><path d="M8 11.2V4.9M5.4 7.5 8 4.9l2.6 2.6"/></svg>';
    document.body.appendChild(btn);

    var ticking = false;
    function sync() {
      var y = window.pageYOffset || document.documentElement.scrollTop || 0;
      btn.classList.toggle('show', y > (window.innerHeight || 800));
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; window.requestAnimationFrame(sync); }
    }, { passive: true });
    window.addEventListener('resize', sync, { passive: true });
    sync();

    btn.addEventListener('click', function () {
      if (window.jQuery) {
        try { window.jQuery('html, body').animate({ scrollTop: 0 }, 260); return; } catch (e) {}
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // 首次应用（尽早执行，避免闪白）
  apply(readPreference());
  if (media) {
    if (media.addEventListener) media.addEventListener('change', function () {
      if (readPreference() === 'auto') apply('auto');
    });
    else if (media.addListener) media.addListener(function () {
      if (readPreference() === 'auto') apply('auto');
    });
  }

  function refreshButtons() {
    var pref = readPreference();
    updateMenu(pref);
    updateCycleButtons(pref);
  }

  function boot() { initCycle(); initToggle(); initBackTop(); refreshButtons(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();

  // Turbo/pjax 会替换 topbar DOM，但主题脚本只在整页加载时执行一次。
  // 必须在每次页面渲染后重新填充 SVG，否则切换按钮会显示为空按钮。
  document.addEventListener('turbo:load', refreshButtons);
  document.addEventListener('dask:page:rendered', refreshButtons);

  window.DaskTheme = {
    set: set,
    get: readPreference,
    isDark: function () { return document.documentElement.getAttribute('data-dark') === '1'; }
  };
})(window, document);
