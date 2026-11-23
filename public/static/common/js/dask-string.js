/* 字符串工具：替代 jQuery 4 已移除的 $.trim。
   约定：null/undefined 返回空串，其余按 String(value).trim() 处理。 */
(function () {
  'use strict';
  window.daskTrim = function (value) {
    return value === null || value === undefined ? '' : String(value).trim();
  };
})();
