/* DudaCMS Frontend · delegated interactions.
   Replaces inline onclick handlers for the common Dask.User actions. */
(() => {
  'use strict';

  window.Dask = window.Dask || {};
  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
  window.Dask.modernEscapeAttr = escapeHtml;
  window.Dask.modernEscapeHtml = escapeHtml;

  const attr = (el, name, fallback = '') => el.getAttribute(`data-${name}`) ?? fallback;

  const call = (path, ...args) => {
    const fn = path.split('.').reduce((acc, key) => (acc ? acc[key] : undefined), window);
    if (typeof fn !== 'function') {
      console.warn('[dask-modern] handler missing:', path);
      return false;
    }
    fn.apply(window, args);
    return true;
  };

  const actions = {
    'user-agree': (el) => call('Dask.User.agree', el, attr(el, 'type'), attr(el, 'id')),
    'user-against': (el) => call('Dask.User.against', el, attr(el, 'type'), attr(el, 'id')),
    'user-focus': (el) => call('Dask.User.focus', el, attr(el, 'type'), attr(el, 'id')),
    'user-favorite': (el) => call('Dask.User.favorite', el, attr(el, 'type'), attr(el, 'id')),
    'user-report': (el) => call('Dask.User.report', el, attr(el, 'type'), attr(el, 'id')),
    'user-invite': (el) => call('Dask.User.invite', el, attr(el, 'id')),
    'user-inbox': (el) => {
      const name = attr(el, 'name');
      const dialog = attr(el, 'dialog');
      return dialog === ''
        ? call('Dask.User.inbox', name)
        : call('Dask.User.inbox', name, dialog === 'true');
    },
    'user-thanks': (el) => call('Dask.User.thanks', el, attr(el, 'id')),
    'user-uninterested': (el) => call('Dask.User.uninterested', el, attr(el, 'type'), attr(el, 'id')),
    'user-force-fold-answer': (el) => call('Dask.User.forceFoldAnswer', el, attr(el, 'id')),
    'user-save-column-article': (el) => call(
      'Dask.User.saveColumnArticle',
      attr(el, 'article-id'),
      attr(el, 'column-id'),
      el,
    ),
    'user-delete-notify': (el) => call('Dask.User.deleteNotify', el, attr(el, 'id')),
    'user-read-notify': (el) => call('Dask.User.readNotify', el, attr(el, 'id')),
    'user-header-notify-read-all': () => call('Dask.User.headerNotifyReadAll'),
    'user-share': (el) => call(
      'Dask.User.share',
      attr(el, 'title'),
      attr(el, 'url'),
      attr(el, 'desc'),
      attr(el, 'type'),
    ),
    'user-login': () => call('Dask.User.login'),
    'user-draft': (el) => call('Dask.User.draft', el, attr(el, 'type'), attr(el, 'id')),
    'column-recommend-save': (el) => call('saveColumnRecommendArticle', attr(el, 'id')),
    'column-recommend-unsave': (el) => call('unSaveColumnRecommendArticle', attr(el, 'id')),
    'form-submit': (el) => {
      const selector = attr(el, 'form');
      const hasJq = !!window.jQuery;
      const form = hasJq ? window.jQuery(selector) : document.querySelector(selector);
      if (!form || (hasJq && !form.length)) return false;
      // 空搜索词直接提交会落到空结果页，先在前端拦一道并给可感知提示
      const input = hasJq ? form.find('input[name="q"]').first() : form.querySelector('input[name="q"]');
      if (input) {
        const value = hasJq ? input.val() : input.value;
        if (!String(value ?? '').trim()) {
          if (hasJq) input.trigger('focus'); else input.focus();
          if (window.layer && typeof window.layer.msg === 'function') window.layer.msg('先输入点什么再搜吧');
          return false;
        }
      }
      if (hasJq) {
        form.trigger('submit');
        return true;
      }
      if (typeof form.requestSubmit === 'function') form.requestSubmit();
      else form.submit();
      return true;
    },
    'common-copy-text': (el) => call('Dask.common.copyText', attr(el, 'text')),
    'layer-alert': (el) => call('layer.alert', attr(el, 'message')),
    'plain-alert': (el) => call('layer.alert', attr(el, 'message')),
    'layer-close-all': () => call('parent.layer.closeAll'),
    'history-back': () => window.history.back(),
    'scroll-top': () => {
      if (window.jQuery && typeof window.jQuery.scrollTo === 'function') {
        window.jQuery.scrollTo(0, 600);
        return true;
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return true;
    },
  };

  const findTarget = (event) => {
    const target = event.target;
    return target instanceof Element ? target.closest('[data-action]') : null;
  };

  document.addEventListener('click', (event) => {
    const el = findTarget(event);
    if (!el) return;
    const handler = actions[el.dataset.action];
    if (!handler) return;
    event.preventDefault();
    el.classList.add('is-active');
    try { handler(el); }
    finally { window.setTimeout(() => el.classList.remove('is-active'), 160); }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' && event.key !== ' ') return;
    const el = findTarget(event);
    if (!el || !actions[el.dataset.action]) return;
    if (el.matches('a, button, [role="button"], [tabindex]')) {
      event.preventDefault();
      el.click();
    }
  });

  if (window.jQuery) {
    window.jQuery.ajaxSetup({ timeout: 15000 });
    window.jQuery(document)
      .ajaxStart(() => document.body.classList.add('dask-ajax-loading'))
      .ajaxStop(() => document.body.classList.remove('dask-ajax-loading'));
  }
})();
