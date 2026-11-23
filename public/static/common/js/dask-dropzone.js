/* §14 Dropzone 6.3.5 适配层。
   保留项目现有上传调用签名（Base.guid / create / on(uploadProgress/uploadSuccess/uploadComplete/error)），
   底层改由 Dropzone 处理文件选择、校验、XHR/FormData；服务端水印与附件回收逻辑完全不动。
   按需加载：适配器常驻（约 2KB），Dropzone 本体仅在页面出现上传控件时加载。 */
(function (global) {
  'use strict';

  var asset = global.DaskUtil && global.DaskUtil.asset ? global.DaskUtil.asset : null;
  var loading = null;

  function ensureLibrary() {
    if (typeof global.Dropzone === 'function') return Promise.resolve(true);
    if (!asset) return Promise.resolve(false);
    if (loading) return loading;
    loading = asset.script(
      'vendor/dropzone/dist/dropzone-min.js',
      'dropzone-js',
      function () { return typeof global.Dropzone === 'function'; }
    ).then(function (ok) {
      loading = null;
      return ok;
    });
    return loading;
  }

  function guid() {
    if (global.crypto && typeof global.crypto.randomUUID === 'function') return global.crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = Math.random() * 16 | 0;
      return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
  }

  function acceptedFiles(extensions) {
    if (!extensions) return null;
    return String(extensions).split(',').map(function (e) {
      var v = e.trim().toLowerCase().replace(/^\./, '');
      return v ? '.' + v : '';
    }).filter(Boolean).join(',') || null;
  }

  function fire(handlers, name, a, b) {
    (handlers[name] || []).forEach(function (fn) { fn(a, b); });
  }

  function create(options) {
    options = options || {};
    var el = typeof options.pick === 'string' ? document.querySelector(options.pick) : options.pick;
    if (!el) return null;

    var handlers = {};
    var dz = null;
    var destroyed = false;
    var api = {
      on: function (name, fn) {
        (handlers[name] || (handlers[name] = [])).push(fn);
        return api;
      },
      destroy: function () {
        destroyed = true;
        if (dz) dz.destroy();
      },
      ensure: ensureLibrary
    };

    ensureLibrary().then(function (ok) {
      if (!ok) {
        fire(handlers, 'error', 'UPLOADER_LOAD_FAILED');
        return;
      }
      if (destroyed) return;

      dz = new global.Dropzone(el, {
        url: options.server,
        paramName: 'file',
        acceptedFiles: acceptedFiles(options.accept && options.accept.extensions),
        // 项目 size 单位是 KB（调用方已 *1024），Dropzone 用 MB。
        maxFilesize: options.fileSingleSizeLimit ? Math.max(1, Math.round(options.fileSingleSizeLimit / 1024 / 1024)) : 10240,
        parallelUploads: options.threads || 1,
        maxFiles: null,
        autoProcessQueue: options.auto !== false,
        clickable: el,
        addRemoveLinks: false,
        dictDefaultMessage: '',
        previewTemplate: '<div class="dask-dz-preview" style="display:none"></div>',
        init: function () {
          var drop = this;
          drop.on('sending', function (file, xhr, formData) {
            var extra = options.formData || {};
            Object.keys(extra).forEach(function (k) { formData.append(k, extra[k]); });
          });
          drop.on('uploadprogress', function (file, percentage) {
            fire(handlers, 'uploadProgress', file, percentage);
          });
          drop.on('success', function (file, response) {
            var parsed = response;
            if (typeof parsed === 'string') {
              try { parsed = JSON.parse(parsed); } catch (e) { parsed = null; }
            }
            fire(handlers, 'uploadSuccess', file, parsed);
          });
          drop.on('complete', function (file) {
            fire(handlers, 'uploadComplete', file);
          });
          drop.on('error', function (file, message) {
            var msg = String(message || '');
            if (/type|extension/i.test(msg)) msg = 'Q_TYPE_DENIED';
            else if (/too large|size/i.test(msg)) msg = 'F_EXCEED_SIZE';
            fire(handlers, 'error', msg);
          });
        }
      });
    });

    return api;
  }

  global.DaskUploader = {
    Base: { guid: guid },
    create: create,
    ensure: ensureLibrary
  };
})(window);
