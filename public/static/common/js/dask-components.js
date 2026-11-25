/* DudaCMS 统一组件层（无依赖，挂在 window.DaskComponents）。
   注意：不替换 layer / 原生 dialog / parent.layer 三套既有契约，只提供统一入口与可复用行为。 */
(function (global) {
  'use strict';

  var Dask = global.DaskComponents = global.DaskComponents || {};

  function toNode(value) {
    if (value == null) return document.createTextNode('');
    if (value instanceof Node) return value;
    var wrap = document.createElement('div');
    wrap.innerHTML = String(value);
    var frag = document.createDocumentFragment();
    while (wrap.firstChild) frag.appendChild(wrap.firstChild);
    return frag;
  }

  function pick(obj, names) {
    if (!obj || typeof obj !== 'object') return undefined;
    for (var i = 0; i < names.length; i += 1) {
      if (Object.prototype.hasOwnProperty.call(obj, names[i])) return obj[names[i]];
    }
    return undefined;
  }

  Dask.escapeHtml = function (value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
    });
  };

  function historyState() {
    try {
      return global.history && global.history.state && typeof global.history.state === 'object'
        ? global.history.state : {};
    } catch (e) { return {}; }
  }

  function saveScrollState(listKey) {
    try {
      var state = historyState();
      state.daskScroll = {
        listKey: listKey,
        scrollY: Math.round(global.scrollY || document.documentElement.scrollTop || 0),
        at: Date.now()
      };
      global.history.replaceState(state, '', location.href);
    } catch (e) { /* history.state 超限时静默降级 */ }
  }

  function restoreScrollState(listKey, root) {
    var state = historyState().daskScroll;
    if (!state || state.listKey !== listKey || typeof state.scrollY !== 'number') return false;
    var y = state.scrollY;
    global.requestAnimationFrame(function () {
      global.scrollTo(0, y);
      var lazy = (root || document).querySelectorAll('img[loading="lazy"]');
      Array.prototype.forEach.call(lazy, function (img) {
        if (img.complete) return;
        img.addEventListener('load', function () { global.scrollTo(0, y); }, { once: true });
      });
    });
    return true;
  }

  /* ---------------- InfiniteList ---------------- */
  function InfiniteList(options) {
    var opts = options || {};
    this.root = typeof opts.root === 'string' ? document.querySelector(opts.root) : opts.root;
    if (!this.root) throw new Error('DaskComponents.InfiniteList: root not found');
    this.listEl = typeof opts.listSelector === 'string'
      ? this.root.querySelector(opts.listSelector) : this.root;
    if (!this.listEl) throw new Error('DaskComponents.InfiniteList: list element not found');

    this.url = opts.url || location.href;
    this.renderItem = opts.renderItem || function (item) {
      return String(pick(item, ['title', 'name']) || '');
    };
    this.listKey = opts.listKey || location.pathname;
    this.page = opts.page || 1;
    this.pageSize = opts.pageSize || 10;
    this.dataType = opts.dataType || 'json'; // json | html
    this.pageParam = opts.pageParam || 'page';
    this.sizeParam = opts.sizeParam || 'page_size';
    this.htmlSelector = opts.htmlSelector || this.listSelector || '[data-dask-list-items]';
    this.hasMore = opts.hasMore !== false;
    this.loading = false;
    this.observer = null;
    this.text = opts.text || {
      loading: '加载中…',
      more: '加载更多',
      end: '没有更多了',
      error: '加载失败，点击重试'
    };
    this.onItems = opts.onItems || null;
    this.onError = opts.onError || null;
    this.init();
  }

  InfiniteList.prototype.init = function () {
    var self = this;
    this.sentinel = document.createElement('div');
    this.sentinel.className = 'dask-list-sentinel';
    this.sentinel.setAttribute('aria-hidden', 'true');
    this.statusEl = document.createElement('div');
    this.statusEl.className = 'dask-list-status';

    this.listEl.parentNode.insertBefore(this.sentinel, this.listEl.nextSibling);
    this.sentinel.parentNode.insertBefore(this.statusEl, this.sentinel.nextSibling);

    this.moreButton = document.createElement('button');
    this.moreButton.type = 'button';
    this.moreButton.className = 'dask-list-more';
    this.moreButton.textContent = this.text.more;
    this.moreButton.addEventListener('click', function () { self.load(); });

    this.listEl.addEventListener('click', function (event) {
      var item = event.target.closest ? event.target.closest('[data-dask-list-item]') : null;
      if (item) saveScrollState(self.listKey);
    });

    if ('IntersectionObserver' in global) {
      this.observer = new IntersectionObserver(function (entries) {
        if (entries.some(function (entry) { return entry.isIntersecting; })) self.load();
      }, { rootMargin: '200px 0px' });
      this.observer.observe(this.sentinel);
    }

    this.setStatus('idle');
    if (optsAutoLoad(this)) this.load();
  };

  function optsAutoLoad(instance) {
    // 默认首屏自动加载；opts.page=0 表示由调用方手动 load()。
    return instance.page !== 0;
  }

  InfiniteList.prototype.setStatus = function (type, message, actionLabel, onAction) {
    var self = this;
    this.statusEl.className = 'dask-list-status' + (type === 'error' ? ' is-error' : '');
    this.statusEl.innerHTML = '';
    if (type === 'loading') {
      var spin = document.createElement('span');
      spin.className = 'dask-list-spinner';
      spin.setAttribute('aria-hidden', 'true');
      this.statusEl.appendChild(spin);
      this.statusEl.appendChild(document.createTextNode(message || this.text.loading));
    } else if (type === 'error') {
      this.statusEl.appendChild(document.createTextNode(message || this.text.error));
      if (typeof onAction === 'function') {
        var retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'dask-list-more';
        retry.textContent = actionLabel || '重试';
        retry.addEventListener('click', function () { self.load(); });
        this.statusEl.appendChild(retry);
      }
    } else if (type === 'end') {
      this.statusEl.hidden = false;
      this.statusEl.textContent = message || this.text.end;
    } else if (type === 'idle' && this.hasMore) {
      this.statusEl.appendChild(this.moreButton);
    } else {
      this.statusEl.hidden = true;
    }
    if (type !== 'idle' && type !== 'end') this.statusEl.hidden = false;
  };

  InfiniteList.prototype.normalize = function (data) {
    var payload = data || {};
    var nested = pick(payload, ['data']) || {};
    var answers = pick(nested, ['answers']) || {};
    if (pick(answers, ['list', 'items', 'rows']) !== undefined) payload = answers;
    else if (pick(nested, ['list', 'items', 'rows']) !== undefined) payload = nested;
    var items = pick(payload, ['list', 'items', 'rows']) || [];
    var pagination = pick(payload, ['pagination', 'page_info']) || {};
    return {
      items: Array.isArray(items) ? items : [],
      pagination: pagination,
      page: Number(pick(pagination, ['page', 'current_page']) || payload.page || this.page) || this.page
    };
  };

  InfiniteList.prototype.load = function () {
    if (this.loading || !this.hasMore) return Promise.resolve(false);
    var self = this;
    this.loading = true;
    this.setStatus('loading');

    var url = new URL(this.url, location.href);
    url.searchParams.set(this.pageParam, String(this.page));
    url.searchParams.set(this.sizeParam, String(this.pageSize));

    var accept = this.dataType === 'html' ? 'text/html' : 'application/json';
    return fetch(url.toString(), {
      credentials: 'same-origin',
      headers: { Accept: accept }
    }).then(function (response) {
      if (!response.ok) throw new Error('HTTP ' + response.status);
      return self.dataType === 'html' ? response.text() : response.json();
    }).then(function (payload) {
      var result;
      var frag = document.createDocumentFragment();

      if (self.dataType === 'html') {
        var doc = new DOMParser().parseFromString(payload, 'text/html');
        var container = doc.querySelector(self.htmlSelector) || doc.querySelector('[data-dask-list-items]');
        var nodes = container ? Array.prototype.slice.call(container.children) : [];
        nodes.forEach(function (node) {
          if (node.nodeType === 1 && !node.hasAttribute('data-dask-list-item')) {
            node.setAttribute('data-dask-list-item', '');
          }
          frag.appendChild(node);
        });
        result = {
          items: nodes,
          pagination: { page: self.page, has_more: nodes.length > 0, total_pages: 0 },
          page: self.page
        };
      } else {
        result = self.normalize(payload);
        result.items.forEach(function (item) {
          var node = toNode(self.renderItem(item));
          if (node.nodeType === 11 || node.nodeType === 1) {
            if (node.nodeType === 1 && !node.hasAttribute('data-dask-list-item')) {
              node.setAttribute('data-dask-list-item', '');
            }
            frag.appendChild(node);
          }
        });
      }

      self.listEl.appendChild(frag);
      if (typeof self.onItems === 'function') self.onItems(result.items, result.pagination);

      var more = pick(result.pagination, ['has_more', 'hasMore']);
      self.page = result.page + 1;
      self.hasMore = typeof more === 'boolean'
        ? more
        : result.items.length >= self.pageSize;
      self.setStatus(self.hasMore ? 'idle' : 'end');
      // 不在每次加载时覆盖 history.state：返回时自动加载会把浏览器恢复的临时位置写回，
      // 导致 restoreScroll() 读到一个被污染的位置。滚动位置只在点击列表项时保存。
      try {
        self.root.dispatchEvent(new CustomEvent('dask:list:rendered', {
          detail: { listKey: self.listKey, count: result.items.length, page: result.page },
          bubbles: true
        }));
      } catch (e) { /* CustomEvent 不可用时忽略 */ }
      return result;
    }).catch(function (error) {
      self.setStatus('error', null, null, function () { self.load(); });
      if (typeof self.onError === 'function') self.onError(error);
      return false;
    }).then(function (result) {
      self.loading = false;
      return result;
    });
  };

  InfiniteList.prototype.restoreScroll = function () {
    return restoreScrollState(this.listKey, this.root);
  };

  InfiniteList.prototype.reset = function () {
    this.listEl.innerHTML = '';
    this.page = 1;
    this.hasMore = true;
    this.loading = false;
    this.setStatus('idle');
    return this.load();
  };

  InfiniteList.prototype.destroy = function () {
    if (this.observer) this.observer.disconnect();
    if (this.sentinel && this.sentinel.parentNode) this.sentinel.parentNode.removeChild(this.sentinel);
    if (this.statusEl && this.statusEl.parentNode) this.statusEl.parentNode.removeChild(this.statusEl);
  };

  Dask.InfiniteList = InfiniteList;
  Dask.saveScrollState = saveScrollState;
  Dask.restoreScrollState = restoreScrollState;

  /* ---------------- Modal ---------------- */
  Dask.modal = {
    open: function (options) {
      var opts = options || {};
      var dialog = document.createElement('dialog');
      dialog.className = 'dask-dialog';
      dialog.setAttribute('role', 'dialog');
      dialog.setAttribute('aria-modal', 'true');

      var head = document.createElement('div');
      head.className = 'dask-dialog__head';
      var title = document.createElement('span');
      title.className = 'dask-dialog__title';
      title.textContent = opts.title || '';
      var close = document.createElement('button');
      close.type = 'button';
      close.className = 'dask-dialog__close';
      close.setAttribute('aria-label', '关闭');
      close.textContent = '×';
      close.addEventListener('click', function () { dialog.close(); });
      head.appendChild(title);
      head.appendChild(close);

      var body = document.createElement('div');
      body.className = 'dask-dialog__body';
      if (opts.url) {
        var url = opts.url + (opts.url.indexOf('?') > -1 ? '&' : '?') + '_ajax_open=1';
        body.textContent = '加载中…';
        global.fetch(url, {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) { return response.text(); }).then(function (html) {
          if (!dialog.isConnected) return;
          var parsed = new DOMParser().parseFromString(html, 'text/html');
          var main = parsed.querySelector('#wrapMain') || parsed.body;
          body.innerHTML = (main && main.innerHTML) || '';
        }).catch(function () {
          if (dialog.isConnected) body.textContent = '加载失败，请稍后重试';
        });
      } else {
        body.appendChild(toNode(opts.content || ''));
      }

      dialog.appendChild(head);
      dialog.appendChild(body);

      if (opts.buttons && opts.buttons.length) {
        var foot = document.createElement('div');
        foot.className = 'dask-dialog__foot';
        opts.buttons.forEach(function (label, index) {
          var button = document.createElement('button');
          button.type = 'button';
          button.textContent = label;
          if (index === 0) button.className = 'dask-btn-primary';
          else button.className = 'dask-btn-secondary';
          button.addEventListener('click', function () {
            if (typeof opts.onButton === 'function' && opts.onButton(index, dialog) === false) return;
            dialog.close();
          });
          foot.appendChild(button);
        });
        dialog.appendChild(foot);
      }

      dialog.addEventListener('close', function () {
        if (typeof opts.onClose === 'function') opts.onClose();
        if (dialog.parentNode) dialog.parentNode.removeChild(dialog);
      });
      document.body.appendChild(dialog);
      if (typeof dialog.showModal === 'function') dialog.showModal();
      else dialog.setAttribute('open', '');
      return dialog;
    },
    close: function (target) {
      if (typeof target === 'number' && global.layer) return global.layer.close(target);
      if (target && typeof target.close === 'function') return target.close();
      var open = document.querySelector('dialog.dask-dialog[open]');
      if (open) open.close();
    }
  };

  Dask.rebindModals = function (root) {
    var scope = root || document;
    var nodes = scope.querySelectorAll ? scope.querySelectorAll('[data-dask-modal]') : [];
    Array.prototype.forEach.call(nodes, function (node) {
      if (node.dataset.daskModalBound === '1') return;
      node.dataset.daskModalBound = '1';
      node.addEventListener('click', function (event) {
        event.preventDefault();
        Dask.modal.open({
          title: node.getAttribute('data-title') || '',
          url: node.getAttribute('data-url') || '',
          content: node.getAttribute('data-content') || ''
        });
      });
    });
    return nodes.length;
  };

  /* ---------------- Dropdown ---------------- */
  Dask.initDropdowns = function (root) {
    var scope = root || document;
    var nodes = scope.querySelectorAll ? scope.querySelectorAll('[data-dask-dropdown]') : [];
    Array.prototype.forEach.call(nodes, function (node) {
      if (node.dataset.daskDropdownBound === '1') return;
      node.dataset.daskDropdownBound = '1';
      var trigger = node.querySelector('[data-dask-dropdown-trigger]') || node.firstElementChild;
      var panel = node.querySelector('[data-dask-dropdown-panel]');
      if (!trigger || !panel) return;
      panel.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
      trigger.addEventListener('click', function (event) {
        event.stopPropagation();
        var open = panel.hidden;
        Array.prototype.forEach.call(nodes, function (other) {
          var p = other.querySelector('[data-dask-dropdown-panel]');
          var t = other.querySelector('[data-dask-dropdown-trigger]') || other.firstElementChild;
          if (p) p.hidden = true;
          if (t) t.setAttribute('aria-expanded', 'false');
        });
        panel.hidden = !open;
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    });
    document.addEventListener('click', function () {
      Array.prototype.forEach.call(nodes, function (node) {
        var panel = node.querySelector('[data-dask-dropdown-panel]');
        var trigger = node.querySelector('[data-dask-dropdown-trigger]') || node.firstElementChild;
        if (panel) panel.hidden = true;
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      });
    });
    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') return;
      Array.prototype.forEach.call(nodes, function (node) {
        var panel = node.querySelector('[data-dask-dropdown-panel]');
        if (panel) panel.hidden = true;
      });
    });
    return nodes.length;
  };

  /* ---------------- Toast / Badge ---------------- */
  Dask.toast = function (message, type, timeout) {
    var stack = document.querySelector('.dask-toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'dask-toast-stack';
      stack.setAttribute('aria-live', 'polite');
      document.body.appendChild(stack);
    }
    var toast = document.createElement('div');
    toast.className = 'dask-toast' + (type ? ' dask-toast--' + type : '');
    toast.textContent = String(message || '');
    stack.appendChild(toast);
    global.setTimeout(function () {
      if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, timeout || 3200);
    return toast;
  };

  Dask.setBadge = function (element, count) {
    if (!element) return;
    var value = Number(count) || 0;
    element.textContent = value > 99 ? '99+' : String(value);
    element.hidden = value <= 0;
  };

  /* ---------------- State ---------------- */
  Dask.renderState = function (root, type, options) {
    var opts = options || {};
    var node = typeof root === 'string' ? document.querySelector(root) : root;
    if (!node) return null;
    var state = document.createElement('div');
    state.className = 'dask-state dask-state--' + type;
    var icon = document.createElement('div');
    icon.className = 'dask-state__icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = type === 'error' ? '!' : type === 'empty' ? '—' : '…';
    var title = document.createElement('div');
    title.className = 'dask-state__title';
    title.textContent = opts.title || '';
    var desc = document.createElement('div');
    desc.className = 'dask-state__desc';
    desc.textContent = opts.description || '';
    state.appendChild(icon);
    state.appendChild(title);
    state.appendChild(desc);
    if (opts.actionText && typeof opts.onAction === 'function') {
      var action = document.createElement('button');
      action.type = 'button';
      action.className = 'dask-state__action';
      action.textContent = opts.actionText;
      action.addEventListener('click', opts.onAction);
      state.appendChild(action);
    }
    node.innerHTML = '';
    node.appendChild(state);
    return state;
  };

  Dask.init = function (root) {
    Dask.rebindModals(root);
    Dask.initDropdowns(root);

    // SSR 列表项没有 data-dask-list-item 标记，也要在点击详情前保存滚动位置。
    document.addEventListener('click', function (event) {
      var target = event.target && event.target.closest ? event.target.closest('.dask-list-item a[href]') : null;
      if (!target || target.target === '_blank') return;
      saveScrollState(location.pathname);
    }, true);

    // 服务端页码分页页面返回时按 pathname 恢复列表滚动位置。
    var state = historyState().daskScroll;
    if (state && state.listKey === location.pathname && typeof state.scrollY === 'number' && state.scrollY > 0) {
      restoreScrollState(state.listKey, document);
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { Dask.init(document); });
  } else {
    Dask.init(document);
  }
})(window);
