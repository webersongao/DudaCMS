/* 独立页面（安装/升级/异常提示）主题初始化。
   这类页面不走主站 block 布局，无法加载 dask-theme.js，故提供精简版：
   读取 localStorage/cookie 的 dask_theme 偏好，设置 html[data-theme] 与 data-dark。
   需在 <head> 尽早同步执行以避免浅色闪屏。 */
(function (window, document) {
    'use strict';
    var STORAGE_KEY = 'dask_theme';
    var COOKIE_KEY = 'dask_theme';
    var VALID = ['light', 'dark', 'auto'];
    var pref = null;
    try { pref = window.localStorage.getItem(STORAGE_KEY); } catch (e) {}
    if (VALID.indexOf(pref) === -1) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + COOKIE_KEY + '=([^;]*)'));
        pref = m ? decodeURIComponent(m[1]) : 'auto';
    }
    if (VALID.indexOf(pref) === -1) pref = 'auto';
    var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    var dark = pref === 'dark' || (pref === 'auto' && media && media.matches);
    var root = document.documentElement;
    root.setAttribute('data-theme', pref);
    root.setAttribute('data-dark', dark ? '1' : '0');
    root.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
})(window, document);
