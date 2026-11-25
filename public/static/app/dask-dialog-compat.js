/* window.layer 兼容层：用原生 <dialog> + dialog.js 的 dask-toast 承接既有 layer.* 调用。
   可作为经典脚本或 ES module 加载：dialog.js 之后加载时直接复用其 dialog 实例；
   独立加载时动态导入 dialog.js 与其 <dask-toast> 元素定义。不支持的参数安全忽略。 */
(function () {
  'use strict';

  // 已被本适配层或原生 layer 接管时不重复覆盖。
  if (window.layer && window.layer.__daskCompat) return;

  var seq = 0;
  var records = new Map();
  var lastIndex = 0;
  var dialogPromise = null;

  function fallbackToast(message, type, timeout) {
    var stack = document.querySelector('.dask-toast-stack[data-dask-app-toast-stack]');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'dask-toast-stack';
      stack.setAttribute('aria-live', 'polite');
      stack.setAttribute('data-dask-app-toast-stack', '');
      document.body.appendChild(stack);
    }
    var item = document.createElement('dask-toast');
    item.className = 'dask-toast dask-toast--' + type;
    item.setAttribute('data-dask-app-toast', '');
    item.setAttribute('data-timeout', String(timeout));
    item.textContent = String(message || '');
    stack.appendChild(item);
    window.setTimeout(function () {
      if (item.parentNode) item.parentNode.removeChild(item);
    }, timeout);
    return item;
  }

  // 优先复用前端 index.js 暴露的 dialog.js 实例，否则运行时导入；
  // 经典脚本中 import() 同样可用，导入失败时退回等价的 dask-toast 实现。
  function getDialog() {
    if (window.DaskApp && window.DaskApp.dialog && isFunction(window.DaskApp.dialog.toast)) {
      return Promise.resolve(window.DaskApp.dialog);
    }
    if (!dialogPromise) {
      dialogPromise = Promise.all([
        import('./dialog.js'),
        import('./elements.js')
      ]).then(function (modules) {
        return modules[0].dialog;
      }).catch(function () {
        return { toast: fallbackToast };
      });
    }
    return dialogPromise;
  }

  function isFunction(value) {
    return typeof value === 'function';
  }

  function isObject(value) {
    return !!value && typeof value === 'object' && !Array.isArray(value) &&
      !(typeof Node !== 'undefined' && value instanceof Node);
  }

  // layer 支持 msg(content, options, end) / msg(content, end) / confirm(content, yes, cancel)
  // 等重载，这里统一拆成 options + 回调列表（保持回调的先后顺序）。
  function parseArgs(args) {
    var options = {};
    var callbacks = [];
    for (var i = 1; i < args.length; i++) {
      var arg = args[i];
      if (isFunction(arg)) {
        callbacks.push(arg);
      } else if (isObject(arg)) {
        Object.keys(arg).forEach(function (key) {
          options[key] = arg[key];
        });
      }
    }
    return { options: options, callbacks: callbacks };
  }

  function nextIndex() {
    seq += 1;
    return seq;
  }

  function normalizeSize(value) {
    if (typeof value === 'number') return value + 'px';
    var text = String(value == null ? '' : value).trim();
    return /^\d+(?:\.\d+)?$/.test(text) ? text + 'px' : text;
  }

  function applyArea(element, area, iframeMode) {
    if (!area) return;
    var width = Array.isArray(area) ? area[0] : area;
    var height = Array.isArray(area) ? area[1] : null;
    if (width) element.style.width = normalizeSize(width);
    if (height) {
      element.style.maxHeight = normalizeSize(height);
      if (iframeMode) element.style.height = normalizeSize(height);
    }
  }

  function makeShell(title, showClose) {
    var element = document.createElement('dialog');
    element.className = 'dask-dialog';
    element.setAttribute('role', 'dialog');
    element.setAttribute('aria-modal', 'true');

    var head = document.createElement('div');
    head.className = 'dask-dialog__head';
    var titleNode = document.createElement('span');
    titleNode.className = 'dask-dialog__title';
    titleNode.textContent = title == null || title === '' ? '提示' : String(title);
    head.appendChild(titleNode);
    if (showClose !== false) {
      var close = document.createElement('button');
      close.type = 'button';
      close.className = 'dask-dialog__close';
      close.setAttribute('aria-label', '关闭');
      close.textContent = '\u00d7';
      head.appendChild(close);
    } else {
      head.style.justifyContent = 'center';
    }

    var body = document.createElement('div');
    body.className = 'dask-dialog__body';
    var foot = document.createElement('div');
    foot.className = 'dask-dialog__foot';

    element.appendChild(head);
    element.appendChild(body);
    element.appendChild(foot);
    return { element: element, body: body, foot: foot };
  }

  function setContent(body, content, options, index) {
    if (content == null) return;
    if (typeof Node !== 'undefined' && content instanceof Node) {
      body.appendChild(content);
      return;
    }
    var text = String(content);
    if (options.type === 2 || options.type === 'iframe') {
      var iframe = document.createElement('iframe');
      iframe.src = text;
      iframe.name = 'layui-layer-iframe' + index;
      iframe.setAttribute('frameborder', '0');
      iframe.setAttribute('title', String(options.title || '弹出层'));
      iframe.style.cssText = 'width:100%;height:100%;min-height:320px;border:0;background:transparent;';
      body.style.padding = '0';
      body.appendChild(iframe);
      return;
    }
    // layer 的 content 允许 HTML 片段（业务代码大量依赖 <br>/<font> 等内联标记）。
    body.innerHTML = text;
  }

  function addButtons(foot, labels, primary, secondary, index, onDone) {
    foot.textContent = '';
    var list = Array.isArray(labels) && labels.length ? labels : null;
    if (!list) return;
    list.forEach(function (label, position) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = position === 0 ? 'dask-btn-primary' : 'dask-btn-secondary';
      button.textContent = String(label);
      button.setAttribute('data-dask-dialog-value', position === 0 ? 'confirm' : 'cancel');
      button.addEventListener('click', function () {
        if (position === 0 && isFunction(primary)) primary(index);
        else if (position > 0 && isFunction(secondary)) secondary(index);
        onDone(position === 0 ? 'confirm' : 'cancel');
      });
      foot.appendChild(button);
    });
  }

  function getLastRecordIndex() {
    var keys = Array.from(records.keys());
    return keys.length ? keys[keys.length - 1] : 0;
  }

  function close(index, reason) {
    if (index === undefined || index === null) index = lastIndex;
    if (index === 'all') {
      closeAll();
      return;
    }
    var record = records.get(index);
    if (!record) return; // 幂等：业务代码常在回调里再次 layer.close(index)
    records.delete(index);
    if (record.timer) window.clearTimeout(record.timer);
    if (record.element) {
      try {
        if (typeof record.element.close === 'function' && record.element.open) record.element.close();
      } catch (error) { /* dialog 已关闭时忽略 */ }
      if (record.cleanup) {
        try { record.cleanup(); } catch (error) { /* 自定义清理失败不影响关闭 */ }
      } else if (record.element.parentNode) {
        record.element.parentNode.removeChild(record.element);
      }
    }
    if (reason === 'yes' && isFunction(record.yes)) {
      try { record.yes(index, record.element); } catch (error) { console.error('[layer] yes callback', error); }
    }
    if (isFunction(record.end)) {
      try { record.end(index); } catch (error) { console.error('[layer] end callback', error); }
    }
    if (lastIndex === index) lastIndex = getLastRecordIndex();
  }

  // cancel 回调返回 false 时保持弹层打开，与 layer 语义一致。
  function requestClose(record, index, reason) {
    if (reason === 'cancel' && isFunction(record.cancel)) {
      var result;
      try { result = record.cancel(index, record.element); } catch (error) { console.error('[layer] cancel callback', error); }
      if (result === false) return false;
    }
    close(index, reason);
    return true;
  }

  function openShell(options, callbacks, kind) {
    var index = nextIndex();
    var title = options.title == null || options.title === '' ? '提示' : String(options.title);
    var shell = makeShell(title, options.closeBtn !== 0);
    var element = shell.element;
    var yes = isFunction(options.yes) ? options.yes : callbacks[0];
    var cancel = isFunction(options.cancel) ? options.cancel : callbacks[1];
    var labels = Array.isArray(options.btn) && options.btn.length
      ? options.btn
      : (kind === 'alert' ? ['确定'] : ['确定', '取消']);

    var record = {
      index: index,
      type: kind,
      element: element,
      yes: yes,
      cancel: cancel,
      end: isFunction(options.end) ? options.end : null,
      timer: null,
      cleanup: null
    };

    setContent(shell.body, options.content, options, index);
    applyArea(element, options.area, options.type === 2 || options.type === 'iframe');

    function onDone(reason) {
      if (reason !== 'confirm') {
        requestClose(record, index, 'cancel');
        return;
      }
      if (kind === 'open') {
        // layer.open 的 yes 不自动关闭，由调用方自行 layer.close(index)。
        if (isFunction(yes)) {
          try { yes(index, element); } catch (error) { console.error('[layer] yes callback', error); }
        }
        return;
      }
      // alert/confirm：先执行 yes 再关闭，保证回调里 layer.close(index) 幂等。
      if (!requestClose(record, index, 'yes')) return;
    }

    addButtons(shell.foot, labels, null, null, index, onDone);

    var closeButton = element.querySelector('.dask-dialog__close');
    if (closeButton) {
      closeButton.addEventListener('click', function () { requestClose(record, index, 'cancel'); });
    }
    element.addEventListener('cancel', function (event) {
      event.preventDefault();
      requestClose(record, index, 'cancel');
    });
    element.addEventListener('click', function (event) {
      if (event.target === element && options.shadeClose !== false) requestClose(record, index, 'cancel');
    });

    document.body.appendChild(element);
    records.set(index, record);
    lastIndex = index;
    if (typeof element.showModal === 'function') element.showModal();
    else element.setAttribute('open', '');
    return index;
  }

  function iconToToastType(icon) {
    if (icon === 1) return 'success';
    if (icon === 2 || icon === 6) return 'error';
    if (icon === 3 || icon === 4) return 'warning';
    return 'info';
  }

  var layer = {
    __daskCompat: true,
    v: '3-backend-compat',

    msg: function (content) {
      var parsed = parseArgs(Array.prototype.slice.call(arguments, 1));
      var options = parsed.options;
      var time = Number(options.time);
      if (!isFinite(time) || time <= 0) time = 2400;
      var type = iconToToastType(options.icon);
      var index = nextIndex();
      var record = {
        index: index,
        type: 'msg',
        element: null,
        timer: null,
        cleanup: function () {
          var item = record.element;
          if (!item) return;
          if (isFunction(item.dismiss)) item.dismiss();
          else if (item.parentNode) item.parentNode.removeChild(item);
        }
      };
      getDialog().then(function (instance) {
        if (records.has(index) && instance && isFunction(instance.toast)) {
          record.element = instance.toast(content, type, time);
        }
      });
      if (isFunction(parsed.callbacks[0])) {
        record.timer = window.setTimeout(function () {
          close(index, 'end');
        }, time + 40);
      }
      records.set(index, record);
      lastIndex = index;
      return index;
    },

    alert: function (content) {
      var parsed = parseArgs(Array.prototype.slice.call(arguments, 1));
      var options = parsed.options;
      options.content = content;
      if (!isFunction(options.yes) && isFunction(parsed.callbacks[0])) options.yes = parsed.callbacks[0];
      if (options.title == null || options.title === '') options.title = '提示';
      return openShell(options, parsed.callbacks, 'alert');
    },

    confirm: function (content) {
      var parsed = parseArgs(Array.prototype.slice.call(arguments, 1));
      var options = parsed.options;
      options.content = content;
      if (isFunction(parsed.callbacks[0])) options.yes = parsed.callbacks[0];
      if (isFunction(parsed.callbacks[1])) options.cancel = parsed.callbacks[1];
      if (options.title == null || options.title === '') options.title = '操作确认';
      if (!Array.isArray(options.btn) || !options.btn.length) options.btn = ['确定', '取消'];
      return openShell(options, parsed.callbacks, 'confirm');
    },

    load: function () {
      var args = Array.prototype.slice.call(arguments);
      var options = isObject(args[1]) ? args[1] : {};
      var index = nextIndex();
      var shell = makeShell('', false);
      shell.element.classList.add('dask-dialog--loading');
      shell.head = shell.element.querySelector('.dask-dialog__head');
      if (shell.head) shell.head.remove();
      shell.foot.textContent = '';
      var spinner = document.createElement('div');
      spinner.className = 'spinner-border text-primary';
      spinner.setAttribute('role', 'status');
      var hidden = document.createElement('span');
      hidden.className = 'visually-hidden';
      hidden.textContent = '加载中';
      spinner.appendChild(hidden);
      shell.body.appendChild(spinner);
      var record = {
        index: index,
        type: 'load',
        element: shell.element,
        timer: null,
        cleanup: null
      };
      applyArea(shell.element, options.area, false);
      document.body.appendChild(shell.element);
      records.set(index, record);
      lastIndex = index;
      if (typeof shell.element.showModal === 'function') shell.element.showModal();
      else shell.element.setAttribute('open', '');
      return index;
    },

    open: function (options) {
      var config = isObject(options) ? Object.assign({}, options) : {};
      return openShell(config, [], 'open');
    },

    close: function (index) {
      close(index, 'close');
    },

    closeAll: function (type) {
      var wanted = String(type || '');
      Array.from(records.keys()).forEach(function (index) {
        var record = records.get(index);
        var kind = record ? record.type : '';
        var match = !wanted ||
          (wanted === 'page' && (kind === 'open' || kind === 'confirm' || kind === 'alert')) ||
          (wanted === 'dialog' && (kind === 'open' || kind === 'confirm' || kind === 'alert')) ||
          (wanted === 'loading' && kind === 'load') ||
          (wanted === 'message' && kind === 'msg');
        if (match) close(index, 'close');
      });
    },

    getFrameIndex: function (name) {
      var match = String(name || '').match(/(\d+)\s*$/);
      return match ? parseInt(match[1], 10) : 0;
    },

    ready: function (callback) {
      if (isFunction(callback)) window.setTimeout(callback, 0);
    },

    // 以下方法在现有调用中只作为“存在性”判断或纯展示，安全降级为空实现。
    config: function () {},
    setTop: function () {},
    full: function () {},
    min: function () {},
    restore: function () {},
    style: function () {},
    title: function () {},
    photos: function () {},
    tab: function () {},
    prompt: function () {}
  };

  window.layer = layer;
  window.DaskLayer = layer;
})();
