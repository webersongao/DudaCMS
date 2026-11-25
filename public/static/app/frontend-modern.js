/* 前台现代化小工具：密码显隐、表单即时校验、键盘无障碍、toast 兼容。
   原生 JS，无第三方依赖；事件全部委托到 document，兼容 Turbo 局部替换。 */
(function () {
    'use strict';

    if (window.__daskFrontendModernReady) return;
    window.__daskFrontendModernReady = true;

    // 优先用 DaskApp.dialog.toast；模块未就绪或旧页面回退到 layer 兼容层。
    function toast(message, type) {
        if (window.DaskApp && window.DaskApp.dialog && typeof window.DaskApp.dialog.toast === 'function') {
            window.DaskApp.dialog.toast(message, type || 'info');
            return;
        }
        if (window.layer && typeof window.layer.msg === 'function') {
            window.layer.msg(message);
        }
    }

    window.DaskFrontend = Object.freeze({ toast: toast });

    document.addEventListener('click', function (event) {
        var target = event.target instanceof Element ? event.target : null;
        if (!target) return;
        var trigger = target.closest('[data-action="toggle-password"]');
        if (!trigger) return;
        var field = trigger.closest('.dask-password-field');
        if (!field) return;
        var input = field.querySelector('input[type="password"], input[type="text"]');
        if (!input) return;

        event.preventDefault();
        var reveal = input.getAttribute('type') === 'password';
        input.setAttribute('type', reveal ? 'text' : 'password');
        trigger.setAttribute('aria-pressed', reveal ? 'true' : 'false');

        var showLabel = trigger.getAttribute('data-label-show') || '显示';
        var hideLabel = trigger.getAttribute('data-label-hide') || '隐藏';
        trigger.setAttribute('aria-label', reveal ? hideLabel : showLabel);
        var text = trigger.querySelector('.dask-password-toggle__text');
        if (text) text.textContent = reveal ? hideLabel : showLabel;

        input.focus();
        try {
            var end = input.value.length;
            input.setSelectionRange(end, end);
        } catch (error) {
            /* 某些输入类型不支持 setSelectionRange，忽略即可 */
        }
    });

    function validateRequired(input) {
        var invalid = String(input.value || '').trim() === '';
        input.classList.toggle('is-invalid', invalid);
        if (invalid) {
            input.setAttribute('aria-invalid', 'true');
        } else {
            input.removeAttribute('aria-invalid');
        }
        return !invalid;
    }

    function isRequiredField(target) {
        return target instanceof HTMLInputElement && target.matches('[data-dask-validate="required"]');
    }

    document.addEventListener('input', function (event) {
        if (!isRequiredField(event.target)) return;
        if (event.target.value.trim() !== '' || event.target.classList.contains('is-invalid')) {
            validateRequired(event.target);
        }
    });

    document.addEventListener('blur', function (event) {
        if (!isRequiredField(event.target)) return;
        validateRequired(event.target);
    }, true);

    // 锚点在 role="button" 下应同时响应空格键；data-action 元素已由 dask-modern.js 处理，避免重复触发。
    document.addEventListener('keydown', function (event) {
        if (event.key !== ' ' && event.key !== 'Spacebar') return;
        var target = event.target instanceof Element ? event.target : null;
        if (!target) return;
        var control = target.closest('a[role="button"]');
        if (!control || control.hasAttribute('data-action')) return;
        event.preventDefault();
        control.click();
    });
})();
