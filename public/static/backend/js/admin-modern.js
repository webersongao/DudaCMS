/* DudaCMS Admin · progressive enhancement layer.
   Converts generated data-action controls into delegated handlers so new pages
   no longer depend on inline onclick for common table/form operations. */
(() => {
  'use strict';

  const call = (path, ...args) => {
    // DaskAdmin 由 dask-admin.js 暴露为全局对象。
    // 这里对两个全局名做回退，避免旧缓存或脚本顺序差异导致 data-action 静默失效。
    const roots = [window.DaskAdmin].filter(Boolean);
    let fn;
    for (const root of roots) {
      const candidate = path.replace(/^DaskAdmin\./, '').split('.').reduce(
        (acc, key) => (acc ? acc[key] : undefined), root
      );
      if (typeof candidate === 'function') { fn = candidate; break; }
    }
    if (typeof fn !== 'function') {
      console.warn('[admin-modern] handler missing:', path);
      return false;
    }
    fn.apply(window, args);
    return true;
  };

  const attr = (el, name, fallback = '') => el.dataset[name] ?? fallback;

  const actions = {
    'table-search': (el) => call('DaskAdmin.table.search'),
    'form-reset': () => call('DaskAdmin.form.reset'),
    'table-sort': (el) => call('DaskAdmin.table.sort', el),
    'operate-state': (el) => call('DaskAdmin.operate.state', attr(el, 'id'), attr(el, 'field'), attr(el, 'url')),
    'operate-select': (el) => call('DaskAdmin.operate.select', el, attr(el, 'id'), attr(el, 'field')),
    'operate-edit': (el) => call('DaskAdmin.operate.edit', attr(el, 'id')),
    'operate-remove': (el) => call('DaskAdmin.operate.remove', attr(el, 'id')),
    'api-post': (el) => {
      let payload = {};
      try { payload = JSON.parse(decodeURIComponent(attr(el, 'payload', '%7B%7D'))); }
      catch (error) { console.warn('[admin-modern] invalid payload', error); }
      return call('DaskAdmin.api.post', attr(el, 'url'), payload);
    },
    'api-ajax-form': (el) => call('DaskAdmin.api.ajaxForm', attr(el, 'form')),
    'operate-add': () => call('DaskAdmin.operate.add'),
    'operate-edit-selected': () => call('DaskAdmin.operate.edit'),
    'operate-remove-all': () => call('DaskAdmin.operate.removeAll'),
    'operate-tree-status': () => call('DaskAdmin.operate.treeStatus'),
    'operate-select-all': (el) => {
      const url = attr(el, 'url');
      const title = attr(el, 'title');
      const type = attr(el, 'type');
      return type === ''
        ? call('DaskAdmin.operate.selectAll', url, title)
        : call('DaskAdmin.operate.selectAll', url, title, type);
    },
    'operate-top-open': (el) => call('DaskAdmin.operate.topOpen', attr(el, 'url'), attr(el, 'title')),
    'operate-database': (el) => call('DaskAdmin.operate.database', attr(el, 'url'), attr(el, 'title')),
    'table-export': (el) => {
      const status = attr(el, 'status');
      return status === ''
        ? call('DaskAdmin.table.export')
        : call('DaskAdmin.table.export', { status });
    },
  };

  function findActionTarget(event, selector) {
    const target = event.target;
    if (!(target instanceof Element)) return null;
    return target.closest(selector);
  }

  document.addEventListener('click', (event) => {
    const el = findActionTarget(event, '[data-action]');
    if (!el) return;
    const action = el.dataset.action;
    if (action === 'table-sort' || action === 'operate-select') return;
    const handler = actions[action];
    if (!handler) return;
    event.preventDefault();
    el.classList.add('is-active');
    try { handler(el); }
    finally { window.setTimeout(() => el.classList.remove('is-active'), 160); }
  });

  document.addEventListener('change', (event) => {
    const el = findActionTarget(event, '[data-action="table-sort"], [data-action="operate-select"]');
    if (!el) return;
    const handler = actions[el.dataset.action];
    if (handler) handler(el);
  });

  document.addEventListener('blur', (event) => {
    const el = findActionTarget(event, '[data-action="table-sort"]');
    if (el && actions[el.dataset.action]) actions[el.dataset.action](el);
  }, true);

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' && event.key !== ' ') return;
    const el = findActionTarget(event, 'a[data-action]:not([href]), a[data-action][href^="javascript:"], button[data-action], [role="button"][data-action]');
    if (!el) return;
    event.preventDefault();
    el.click();
  });

  const ready = (fn) => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  };

  ready(() => {
  // Sidebar toggle: keep aria state in sync with AdminLTE's body class.
  const pushMenu = document.querySelector('[data-lte-toggle="sidebar"]');
  if (pushMenu) {
    pushMenu.setAttribute('aria-label', '展开或收起导航');
    pushMenu.setAttribute('aria-expanded', String(document.body.classList.contains('sidebar-open')));
    pushMenu.addEventListener('click', () => {
      window.setTimeout(() => {
        pushMenu.setAttribute('aria-expanded', String(document.body.classList.contains('sidebar-open')));
      }, 80);
    });
  }

  // 顶部一级菜单只筛选对应二级菜单，不再把所有侧栏项一次性隐藏。
  const topNav = document.getElementById('topNav');
  if (topNav) {
    topNav.addEventListener('click', (event) => {
      const link = event.target.closest('li > a');
      if (!link) return;
      topNav.querySelectorAll('li.active').forEach((li) => li.classList.remove('active'));
      link.parentElement.classList.add('active');

      const pid = link.dataset.id || '';
      if (pid === '') return;
      const sidebarItems = document.querySelectorAll('#sidebar .sidebar-menu > li[data-pid]');
      if (!sidebarItems.length) return;
      sidebarItems.forEach((li) => li.classList.toggle('d-none', li.dataset.pid !== pid));

      if (link.dataset.url === 'javascript:;') {
        event.preventDefault();
        const child = document.querySelector('#sidebar .sidebar-menu > li[data-pid="' + pid + '"] > a.nav-link');
        if (child && child.getAttribute('href') !== 'javascript:;') child.click();
      }
    });
  }

  // 左侧菜单高亮保持同步。
  document.querySelectorAll('#sidebar .sidebar-menu > li a.nav-link').forEach((link) => {
    link.addEventListener('click', () => {
      if (link.getAttribute('href') === '#') return;
      document.querySelectorAll('#sidebar .sidebar-menu > li a.nav-link').forEach((item) => item.classList.remove('active'));
      link.classList.add('active');
    });
  });

  // 图片 Logo 加载失败时自动回退到文字 Logo / 站点名称。
  const revealLogoText = (img) => {
    img.style.display = 'none';
    const text = img.parentElement && img.parentElement.querySelector('.brand-text');
    if (text) {
      text.hidden = false;
      text.style.display = 'inline-flex';
    }
  };
  document.querySelectorAll('.brand-image[data-logo-fallback]').forEach((img) => {
    if (img.complete && img.naturalWidth === 0) {
      revealLogoText(img);
    } else {
      img.addEventListener('error', () => revealLogoText(img), { once: true });
    }
  });

  // Icon-only controls need an accessible name.
  document.querySelectorAll('.app-header .nav-link').forEach((link) => {
    if (!link.textContent.trim() && !link.getAttribute('aria-label')) {
      const icon = link.querySelector('i.fa, i.fas, i.far, i.fab');
      link.setAttribute('aria-label', icon ? icon.className.replace(/^fa[brs]?\s*/, '').replace(/\s+/g, ' ') : '操作');
    }
  });
  });

  // Uniform loading and network-error feedback for jQuery requests.
  if (window.jQuery) {
    const $ = window.jQuery;
    $.ajaxSetup({ timeout: 15000 });
    $(document)
      .ajaxStart(() => document.body.classList.add('dask-ajax-loading'))
      .ajaxStop(() => document.body.classList.remove('dask-ajax-loading'))
      .ajaxError((event, xhr, settings) => {
        if (xhr.status === 0 || xhr.statusText === 'abort') return;
        const message = `请求失败（HTTP ${xhr.status}）`;
        if (window.Dask && window.Dask.api && typeof window.Dask.api.error === 'function') {
          window.Dask.api.error(message);
        } else {
          console.warn('[admin-modern]', message, settings && settings.url);
        }
      });
  }
})();
