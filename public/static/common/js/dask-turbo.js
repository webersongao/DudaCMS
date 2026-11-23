/* §14 Turbo 适配层（经典脚本，Turbo UMD 之后加载）。
   职责：
   1) 全站表单仍走既有 dask-ajax-form / dask-form.js，禁止 Turbo 接管表单提交；
   2) 把 Turbo 的导航完成事件桥接为 dask:page:rendered，供 ESM loader 与表单层重挂载；
   3) 保留项目自建的 history.state.daskScroll 返回滚动恢复，Turbo 只负责导航与历史。 */
(function () {
  'use strict';
  if (!window.Turbo) return;

  // 表单提交继续由项目自己的 AJAX 层处理，避免登录/发布/后台增删改被 Turbo Visit 劫持。
  if (Turbo.config && Turbo.config.forms) Turbo.config.forms.mode = 'off';

  // 后台“pjax_enable”开关继续作为整站页面导航开关使用（语义等价，不再需要 jquery-pjax）。
  if (window.pjaxEnable === 0 && Turbo.session) Turbo.session.drive = false;

  function pageRendered() {
    document.dispatchEvent(new CustomEvent('dask:page:rendered'));
  }
  document.addEventListener('turbo:load', pageRendered);
  document.addEventListener('turbo:frame-load', pageRendered);

  // 返回列表时沿用 dask-components.js 的地址级滚动位置；Turbo 自身的滚动恢复不覆盖它。
  document.addEventListener('turbo:load', function () {
    if (!window.DaskComponents || typeof window.DaskComponents.restoreScrollState !== 'function') return;
    try {
      window.DaskComponents.restoreScrollState(location.pathname, document);
    } catch (e) { /* 历史状态不可用时静默降级 */ }
  });

  window.DaskTurbo = { visit: function (url) { return Turbo.visit(url); } };
})();
