/*!
 * DudaCMS 图片灯箱 —— 基于 PhotoSwipe 5（替换已废弃的 fancybox 2）。
 * 复用站点既有的 data-fancybox 标记作为选择器与分组键，模板无需改动。
 * 按需加载：仅当用户点击灯箱链接时才注入 PhotoSwipe 的 CSS/JS，避免全站白担请求。
 */
(function (window, document) {
  'use strict';

  var SELECTOR = 'a[data-fancybox], a[data-fancybox-group]';
  // 路径与版本统一由 Dask.util.asset 推导，前后台 assetBase 不同也无需各自解析。
  var asset = window.DaskUtil && window.DaskUtil.asset ? window.DaskUtil.asset : null;

  function links() {
    return [].slice.call(document.querySelectorAll(SELECTOR));
  }

  function toItems(list) {
    return list.map(function (a) {
      var href = a.getAttribute('href') || a.getAttribute('data-src') || a.src || '';
      var img = a.querySelector('img');
      var caption = a.getAttribute('data-caption') || a.getAttribute('title') || (img && img.alt) || '';
      var w = parseInt(a.getAttribute('data-pswp-width') || (img && img.getAttribute('width')), 10);
      var h = parseInt(a.getAttribute('data-pswp-height') || (img && img.getAttribute('height')), 10);
      var item = { src: href, alt: caption };
      if (w > 0 && h > 0) { item.width = w; item.height = h; }
      return item;
    });
  }

  function injectCss() {
    if (!asset) return Promise.resolve(false);
    var done = asset.css('vendor/photoswipe/dist/photoswipe.css', 'photoswipe-css');
    // 保留历史标记，便于既有回归与人工排查识别懒加载样式。
    var link = document.querySelector('link[data-dask-asset="photoswipe-css"]');
    if (link) link.dataset.daskPswp = '1';
    return done;
  }

  /** 首次使用时动态加载 PhotoSwipe UMD，返回 Promise<boolean>。 */
  function ensureLibrary() {
    if (!asset) return Promise.resolve(false);
    injectCss();
    return asset.script(
      'vendor/photoswipe/dist/umd/photoswipe.umd.min.js',
      'photoswipe-js',
      function () { return typeof window.PhotoSwipe === 'function'; }
    );
  }

  function open(clicked) {
    ensureLibrary().then(function (ok) {
      if (!ok) return;
      var list = links();
      var index = Math.max(0, list.indexOf(clicked));
      var pswp = new window.PhotoSwipe({
        dataSource: toItems(list),
        index: index,
        bgOpacity: 0.9,
        showHideAnimationType: 'fade',
        padding: { top: 40, bottom: 40, left: 20, right: 20 }
      });
      pswp.init();
    });
  }

  document.addEventListener('click', function (e) {
    var a = e.target && e.target.closest ? e.target.closest(SELECTOR) : null;
    if (!a) return;
    var href = a.getAttribute('href') || '';
    if (!/\.(png|jpe?g|gif|webp|bmp)(\?|$)/i.test(href)) return;
    e.preventDefault();
    open(a);
  });

  window.DaskLightbox = { open: open, links: links, ensureLibrary: ensureLibrary };
})(window, document);
