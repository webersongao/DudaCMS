/* 后台 UI 现代化增强层（经典脚本，可在 <head> 同步加载）。
   职责：主题首屏防闪、表单字段级校验、脏数据离开提示、密码显隐切换、
   图标选择器交互。所有绑定均幂等，Turbo/PJAX 重挂载后自动重新初始化。 */
(function () {
  'use strict';

  // ---------- 主题首屏防闪：必须在样式生效前同步执行 ----------
  (function applyTheme() {
    try {
      var pref = localStorage.getItem('dask_theme');
      if (!pref) {
        var match = document.cookie.match(/(?:^|; )dask_theme=([^;]*)/);
        pref = match ? decodeURIComponent(match[1]) : 'auto';
      }
      if (['light', 'dark', 'auto'].indexOf(pref) === -1) pref = 'auto';
      document.documentElement.setAttribute('data-theme', pref);
      var dark = pref === 'dark' ||
        (pref === 'auto' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
      document.documentElement.setAttribute('data-dark', dark ? '1' : '0');
      document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
    } catch (error) { /* 隐私模式下存储不可用时保持默认主题 */ }
  })();

  var delegatedReady = false;
  var beforeUnloadReady = false;

  function byId(id) {
    return document.getElementById(id);
  }

  function isVisible(el) {
    if (!el || el.disabled) return false;
    if (el.type === 'hidden') return false;
    return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
  }

  function fieldValue(el) {
    if (el.type === 'checkbox' || el.type === 'radio') {
      if (!el.checked) return '';
      return el.value == null || el.value === '' ? 'on' : el.value;
    }
    return el.value == null ? '' : String(el.value);
  }

  function formSelector(form) {
    var controls = form.querySelectorAll('input, select, textarea');
    return Array.prototype.filter.call(controls, function (el) {
      return el.name && el.type !== 'hidden' && !el.disabled;
    });
  }

  function snapshot(form) {
    var parts = [];
    Array.prototype.forEach.call(formSelector(form), function (el) {
      parts.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(fieldValue(el)));
    });
    return parts.join('&');
  }

  function rememberClean(form) {
    form.setAttribute('data-dask-clean-snapshot', snapshot(form));
    form.setAttribute('data-dask-dirty', '0');
  }

  function isDirty(form) {
    if (form.getAttribute('data-dask-dirty') === '1') return true;
    var clean = form.getAttribute('data-dask-clean-snapshot');
    return clean !== null && clean !== snapshot(form);
  }

  // ---------- 字段级校验 ----------
  function errorContainer(field) {
    return field.closest('.mb-3, .form-group') || field.parentNode;
  }

  function clearError(field) {
    field.classList.remove('is-invalid');
    field.removeAttribute('aria-invalid');
    var box = errorContainer(field).querySelector('.dask-field-error');
    if (box && box.parentNode) box.parentNode.removeChild(box);
  }

  function setError(field, message) {
    field.classList.add('is-invalid');
    field.setAttribute('aria-invalid', 'true');
    var container = errorContainer(field);
    var box = container.querySelector('.dask-field-error');
    if (!box) {
      box = document.createElement('div');
      box.className = 'invalid-feedback d-block dask-field-error';
      container.appendChild(box);
    }
    box.textContent = message;
  }

  function labelOf(field) {
    var container = field.closest('.mb-3, .form-group');
    var label = container && container.querySelector('label');
    return (label && label.textContent.replace(/\s*\*\s*$/, '').trim()) || field.name || '该字段';
  }

  function hasRule(field, rule) {
    var rules = String(field.getAttribute('data-rules') || field.getAttribute('data-rule') || '');
    return rules.split(/[\s,]+/).indexOf(rule) !== -1;
  }

  function validateField(field) {
    if (!isVisible(field)) return null;
    var value = fieldValue(field).trim();
    var label = labelOf(field);
    var required = field.required || field.classList.contains('required') ||
      field.getAttribute('data-required') === '1' || hasRule(field, 'required');

    if (required && !value) return label + '不能为空';
    if (!value) return null;

    var isNumber = field.type === 'number' || field.classList.contains('number') || hasRule(field, 'number');
    if (isNumber && !/^-?\d+(?:\.\d+)?$/.test(value)) return label + '必须是数字';

    var isEmail = field.type === 'email' || field.classList.contains('email') || hasRule(field, 'email');
    if (isEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return label + '邮箱格式不正确';

    var isUrl = field.type === 'url' || field.classList.contains('url') || hasRule(field, 'url');
    if (isUrl) {
      try {
        var parsed = new URL(value);
        if (!/^https?:$/.test(parsed.protocol)) return label + '必须是有效的 http(s) 地址';
      } catch (error) {
        return label + '必须是有效的 http(s) 地址';
      }
    }
    return null;
  }

  // 密码确认：优先 data-confirm-field / data-match，其次按 name 启发式配对。
  function confirmPartner(field, form) {
    var ref = field.getAttribute('data-confirm-field') || field.getAttribute('data-match');
    if (ref) {
      var direct = byId(ref) || form.querySelector('[name="' + ref.replace(/"/g, '\\"') + '"]');
      if (direct && direct !== field) return direct;
    }
    var name = field.name || field.id || '';
    if (!/password|pwd/i.test(name)) return null;
    var peers = Array.prototype.filter.call(form.querySelectorAll('input[type="password"]'), function (el) {
      return el !== field;
    });
    for (var i = 0; i < peers.length; i++) {
      var peerName = peers[i].name || peers[i].id || '';
      if (/confirm|repeat|again|^re_?/i.test(peerName)) return peers[i];
    }
    for (var j = 0; j < peers.length; j++) {
      if (/confirm|repeat|again|^re_?/i.test(name)) return peers[j];
    }
    return null;
  }

  function isConfirmField(field) {
    var name = field.name || field.id || '';
    return /confirm|repeat|again|^re_?/i.test(name) ||
      field.hasAttribute('data-confirm-field') || field.hasAttribute('data-match');
  }

  function validateForm(form) {
    var invalid = [];
    var fields = formSelector(form);
    Array.prototype.forEach.call(fields, function (field) {
      // 确认框由下面的配对校验负责，避免同一错误报两次
      if (field.type === 'password' && isConfirmField(field)) return;
      var message = validateField(field);
      clearError(field);
      if (message) {
        setError(field, message);
        invalid.push(field);
      }
    });

    // 密码确认：只从“原密码”一侧发起比较
    Array.prototype.forEach.call(fields, function (field) {
      if (field.type !== 'password' || isConfirmField(field)) return;
      var partner = confirmPartner(field, form);
      if (!partner) return;
      var sourceValue = fieldValue(field);
      var confirmValue = fieldValue(partner);
      if (!sourceValue && !confirmValue) return;
      if (sourceValue !== confirmValue) {
        clearError(partner);
        setError(partner, '两次输入的密码不一致');
        invalid.push(partner);
      }
    });

    if (invalid.length) {
      try { invalid[0].focus(); } catch (error) { /* 焦点不可用时忽略 */ }
      var text = invalid[0].closest('.mb-3, .form-group');
      var feedback = text && text.querySelector('.dask-field-error');
      var message = feedback ? feedback.textContent : '请检查表单填写';
      if (window.layer && typeof window.layer.msg === 'function') {
        window.layer.msg(message, { icon: 2, time: 2000 });
      }
      return false;
    }
    return true;
  }

  function activeForm(target) {
    if (!target || !target.closest) return null;
    var form = target.closest('#awFormBuilder, form[data-dask-modern-form]');
    return form || null;
  }

  // 提交按钮是 type="button"，其 AJAX 提交由 document 上的 jQuery 委托处理；
  // 这里在捕获阶段先校验，失败时阻断事件继续传播。
  function onBeforeSubmit(event) {
    var form = activeForm(event.target);
    if (!form || form.getAttribute('data-dask-modern-skip') === '1') return;
    if (!validateForm(form)) {
      event.preventDefault();
      event.stopPropagation();
      return;
    }
    rememberClean(form);
  }

  function onFieldChange(event) {
    var form = activeForm(event.target);
    if (!form) return;
    if (event.target && event.target.classList) {
      var field = event.target;
      if (field.classList.contains('is-invalid')) clearError(field);
    }
    if (isDirty(form)) form.setAttribute('data-dask-dirty', '1');
  }

  // ---------- 密码显隐切换 ----------
  function initPasswordToggles(root) {
    var scope = root && root.nodeType === 1 ? root : document;
    var inputs = [];
    if (scope.matches && scope.matches('input[type="password"]')) inputs.push(scope);
    inputs = inputs.concat(Array.prototype.slice.call(
      scope.querySelectorAll('input[type="password"]')
    ));
    inputs.forEach(function (input) {
      if (input.getAttribute('data-dask-password-ready') === '1') return;
      input.setAttribute('data-dask-password-ready', '1');
      var group = document.createElement('div');
      group.className = 'input-group dask-password-group';
      input.parentNode.insertBefore(group, input);
      group.appendChild(input);
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'btn btn-outline-secondary dask-password-toggle';
      button.textContent = '显示';
      button.setAttribute('aria-label', '显示密码');
      button.setAttribute('aria-pressed', 'false');
      button.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.textContent = show ? '隐藏' : '显示';
        button.setAttribute('aria-label', show ? '隐藏密码' : '显示密码');
        button.setAttribute('aria-pressed', show ? 'true' : 'false');
      });
      group.appendChild(button);
    });
  }

  function initForms(root) {
    var scope = root && root.nodeType === 1 ? root : document;
    var forms = [];
    if (scope.matches && scope.matches('#awFormBuilder, form[data-dask-modern-form]')) forms.push(scope);
    forms = forms.concat(Array.prototype.slice.call(
      scope.querySelectorAll('#awFormBuilder, form[data-dask-modern-form]')
    ));
    forms.forEach(function (form) {
      if (form.getAttribute('data-dask-modern-ready') !== '1') {
        form.setAttribute('data-dask-modern-ready', '1');
        rememberClean(form);
      }
    });
    initPasswordToggles(scope);
  }

  function bindDelegates() {
    if (delegatedReady) return;
    delegatedReady = true;
    document.addEventListener('click', onBeforeSubmit, true);
    document.addEventListener('submit', onBeforeSubmit, true);
    document.addEventListener('input', onFieldChange, true);
    document.addEventListener('change', onFieldChange, true);

    // 图标选择器（原 icons.php 内联脚本收敛到这里）
    document.addEventListener('click', function (event) {
      var icon = event.target && event.target.closest
        ? event.target.closest('.js-icon-list .dask-icon') : null;
      if (!icon) return;
      var node = icon.querySelector('i');
      if (!node || !window.parent || window.parent === window) return;
      var name = String(node.className || '').replace('fa-2x', '').trim();
      var $ = window.jQuery;
      if ($) {
        $('.dask-form-icon', window.parent.document).val(name);
        $('.dask-form-icon-select', window.parent.document)
          .attr('class', name + ' dask-form-icon-select');
      }
      if (window.parent.layer) {
        var index = typeof window.parent.layer.getFrameIndex === 'function'
          ? window.parent.layer.getFrameIndex(window.name) : 0;
        if (typeof window.parent.layer.close === 'function') window.parent.layer.close(index);
      }
    });

    if (!beforeUnloadReady) {
      beforeUnloadReady = true;
      window.addEventListener('beforeunload', function (event) {
        var forms = document.querySelectorAll('#awFormBuilder, form[data-dask-modern-form]');
        var dirty = Array.prototype.some.call(forms, isDirty);
        if (!dirty) return;
        event.preventDefault();
        event.returnValue = '';
        return '';
      });
    }
  }

  function boot() {
    bindDelegates();
    initForms(document);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }
  document.addEventListener('dask:page:rendered', function () { initForms(document); });
  document.addEventListener('dask:ajax-loaded', function () { initForms(document); });
})();
