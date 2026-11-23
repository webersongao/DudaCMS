/* DudaCMS Admin · login form progressive enhancement. */
(() => {
  'use strict';

  const form = document.getElementById('adminLoginForm');
  if (!form) return;

  const config = window.DASK_ADMIN_LOGIN || {};
  const button = form.querySelector('.login-button');
  const buttonText = form.querySelector('.login-button-text');
  const errorBox = document.getElementById('loginError');
  let submitting = false;

  const setError = (message) => {
    if (!errorBox) return;
    errorBox.textContent = message || '';
    errorBox.classList.toggle('is-visible', Boolean(message));
  };

  const setLoading = (loading) => {
    submitting = loading;
    if (button) button.disabled = loading;
    if (buttonText) buttonText.textContent = loading ? '登录中…' : '登录';
  };

  const readCredentials = () => {
    const username = form.elements.username.value.trim();
    const password = form.elements.password.value;
    const token = form.elements.__token__ ? form.elements.__token__.value : '';
    return {
      username,
      password,
      token,
    };
  };

  const submitLogin = async () => {
    if (submitting) return;
    setError('');
    let credentials;
    try {
      credentials = readCredentials();
    } catch (error) {
      setError(error.message);
      return;
    }

    setLoading(true);
    try {
      const response = await fetch(form.action, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: new URLSearchParams({
          username: credentials.username,
          password: credentials.password,
          token: credentials.token,
        }),
      });

      let result;
      try {
        result = await response.json();
      } catch (error) {
        throw new Error(response.ok ? '登录响应格式异常' : `登录失败（HTTP ${response.status}）`);
      }

      if (result.code > 0) {
        window.location.href = result.url || config.dashboardUrl || '/admin.php';
        return;
      }
      setError(result.msg || result.message || '用户名或密码错误');
    } catch (error) {
      setError(error.message || '网络异常，请稍后重试');
    } finally {
      setLoading(false);
    }
  };

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (submitting) return;
    setError('');

    if (!form.elements.username.value.trim()) {
      setError('请输入用户名');
      form.elements.username.focus();
      return;
    }
    if (!form.elements.password.value) {
      setError('请输入密码');
      form.elements.password.focus();
      return;
    }

    if (config.captcha) {
      const holder = window.jQuery ? window.jQuery('#captcha') : null;
      if (!holder || typeof holder.captcha !== 'function') {
        setError('验证码组件加载失败，请刷新页面重试');
        return;
      }
      holder.captcha({ callback: submitLogin });
      return;
    }
    submitLogin();
  });
})();
