/* Dask 公共工具库（无依赖，原生实现）。
   统一挂载 window.Dask.util；dask.js 在既有 window.Dask 对象上扩展，故 util 不会被覆盖。
   约定：util 只放通用能力；http/dialog/upload/theme 等领域能力仍留在各自模块。 */
(function (global) {
  'use strict';

  var util = {};
  // 由自身 src 推导 static 根路径与版本号，前后台 assetBase 不同也无需额外配置。
  var SELF = (document.currentScript && document.currentScript.src) || '';
  var BASE_MATCH = SELF.match(/^(.*\/static)\//);
  var VER_MATCH = SELF.match(/[?&]v=([^&]*)/);
  var ASSET_BASE = global.staticUrl || (BASE_MATCH ? BASE_MATCH[1] + '/' : '/static/');
  var ASSET_VERSION = VER_MATCH ? VER_MATCH[1] : '1.0.0';
  var pendingScripts = {};

  /** 单个 DOM 节点是否可见（有布局盒即视为可见，等价 jQuery :visible）。 */
  function nodeVisible(node) {
    return !!(node && node.nodeType === 1 && node.getClientRects && node.getClientRects().length);
  }
  /** 是否可见：接受 DOM 节点或类数组集合（jQuery 对象，任一可见即为 true，对齐 .is(':visible')）。 */
  util.isVisible = function (el) {
    if (!el) return false;
    if (el.nodeType === 1) return nodeVisible(el);
    if (typeof el.length === 'number') {
      for (var i = 0; i < el.length; i += 1) if (nodeVisible(el[i])) return true;
    }
    return false;
  };
  util.isHidden = function (el) {
    return !util.isVisible(el);
  };

  /** HTML 转义（文本与属性共用一份映射）。 */
  var ESC = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
  util.escapeHtml = function (value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) { return ESC[ch]; });
  };
  util.escapeAttr = util.escapeHtml;

  /** 从对象取第一个存在的键值。 */
  util.pick = function (obj, names) {
    if (!obj || typeof obj !== 'object') return undefined;
    for (var i = 0; i < names.length; i += 1) {
      if (Object.prototype.hasOwnProperty.call(obj, names[i])) return obj[names[i]];
    }
    return undefined;
  };

  /** 生成唯一 ID（优先原生 UUID）。 */
  util.guid = function () {
    if (global.crypto && typeof global.crypto.randomUUID === 'function') return global.crypto.randomUUID();
    return 'dask-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  };

  /** 去空白：null/undefined 返回空串。 */
  util.trim = function (value) {
    return value === null || value === undefined ? '' : String(value).trim();
  };

  /** Cookie 读写（统一 dask_ 命名空间，供主题等模块复用）。 */
  util.cookie = {
    get: function (name) {
      var parts = (document.cookie || '').split(';');
      for (var i = 0; i < parts.length; i += 1) {
        var seg = util.trim(parts[i]);
        if (seg.substring(0, name.length + 1) === name + '=') {
          try { return decodeURIComponent(seg.substring(name.length + 1)); } catch (e) { return ''; }
        }
      }
      return '';
    },
    set: function (name, value, options) {
      options = options || {};
      var text = name + '=' + encodeURIComponent(value);
      if (options.maxAge != null) text += '; max-age=' + options.maxAge;
      else if (options.days != null) text += '; max-age=' + (options.days * 86400);
      text += '; path=' + (options.path || '/');
      text += '; SameSite=' + (options.sameSite || 'Lax');
      if (options.secure) text += '; Secure';
      document.cookie = text;
    },
    remove: function (name, path) {
      document.cookie = name + '=; max-age=0; path=' + (path || '/') + '; SameSite=Lax';
    }
  };

  /** 按点分路径从全局取函数并调用（统一 dask-modern / admin-modern 的 call 逻辑）。 */
  util.callPath = function (path) {
    var args = Array.prototype.slice.call(arguments, 1);
    var fn = path;
    if (typeof path === 'string') {
      fn = path.split('.').reduce(function (acc, key) { return acc == null ? undefined : acc[key]; }, global);
    }
    if (typeof fn === 'function') return fn.apply(null, args);
    return undefined;
  };

  /** 统一跳转：Turbo 可用时走 Turbo，否则普通跳转。 */
  util.navigate = function (url) {
    if (global.DaskTurbo && typeof global.DaskTurbo.visit === 'function') global.DaskTurbo.visit(url);
    else global.location.href = url;
  };

  /** 统一视口高度（避免 innerHeight / $(window).height() 混用）。 */
  util.viewportHeight = function () {
    return global.innerHeight || document.documentElement.clientHeight || 800;
  };

  /** 静态资源按需加载：全站统一键名、去重、Promise 化，避免每个模块各写一套注入胶水。 */
  util.asset = {
    base: function () { return ASSET_BASE; },
    version: function () { return ASSET_VERSION; },
    url: function (path) {
      var p = String(path).replace(/^\/+/, '');
      var versions = global.assetVersions || {};
      var v = versions[p] || ASSET_VERSION;
      return ASSET_BASE + p + (/\?/.test(p) ? '&' : '?') + 'v=' + encodeURIComponent(v);
    },
    css: function (path, key) {
      key = key || String(path).replace(/[^\w-]/g, '-');
      var found = document.querySelector('link[data-dask-asset="' + key + '"]');
      if (found) return Promise.resolve(true);
      return new Promise(function (resolve) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.dataset.daskAsset = key;
        link.href = util.asset.url(path);
        link.onload = function () { resolve(true); };
        link.onerror = function () { resolve(false); };
        document.head.appendChild(link);
      });
    },
    script: function (path, key, ready) {
      key = key || String(path).replace(/[^\w-]/g, '-');
      if (typeof ready === 'function' && ready()) return Promise.resolve(true);
      if (pendingScripts[key]) return pendingScripts[key];
      var found = document.querySelector('script[data-dask-asset="' + key + '"]');
      if (found) {
        return new Promise(function (resolve) {
          var done = false;
          function finish() {
            if (done) return;
            done = true;
            resolve(typeof ready === 'function' ? !!ready() : true);
          }
          found.addEventListener('load', finish);
          found.addEventListener('error', function () { if (!done) { done = true; resolve(false); } });
          setTimeout(finish, 30000);
        });
      }
      pendingScripts[key] = new Promise(function (resolve) {
        var script = document.createElement('script');
        script.dataset.daskAsset = key;
        script.src = util.asset.url(path);
        script.onload = function () { resolve(typeof ready === 'function' ? !!ready() : true); };
        script.onerror = function () { resolve(false); };
        document.head.appendChild(script);
      }).then(function (ok) {
        delete pendingScripts[key];
        return ok;
      });
      return pendingScripts[key];
    }
  };
  util.loadCss = util.asset.css;
  util.loadScript = util.asset.script;

  global.DaskUtil = util;
  global.Dask = global.Dask || {};
  global.Dask.util = util;
})(window);
