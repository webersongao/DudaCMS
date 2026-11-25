/* DudaCMS 唯一主题首屏初始化脚本：必须在样式表之前同步加载，避免深色模式闪白。 */
(function (window, document) {
  'use strict';
  var current = document.currentScript;
  var fallback = (current && current.getAttribute('data-default')) || 'auto';
  var valid = ['light', 'dark', 'auto'];
  try {
    var pref = null;
    try { pref = localStorage.getItem('dask_theme'); } catch (e) {}
    if (valid.indexOf(pref) === -1) {
      var match = document.cookie.match(/(?:^|; )dask_theme=([^;]*)/);
      pref = match ? decodeURIComponent(match[1]) : fallback;
    }
    if (valid.indexOf(pref) === -1) pref = 'auto';
    var root = document.documentElement;
    root.setAttribute('data-theme', pref);
    var dark = pref === 'dark' || (pref === 'auto' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
    root.setAttribute('data-dark', dark ? '1' : '0');
    root.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
  } catch (e) {}
})(window, document);
