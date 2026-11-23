/*!
 * DudaCMS 内置 Vditor 适配层
 * - 统一同步 getHTML() 到隐藏 textarea，保持站点 HTML 存储契约
 * - 配置化工具栏、模式、上传大小、代码高亮、内容主题
 * - 深浅色跟随站点 dask:themechange 事件
 * - 默认关闭编辑态块浮层（上移 / 下移 / 删除），工作台不提供全屏入口
 */
(function (window, document) {
  'use strict';

  var D = window.DaskEditor = window.DaskEditor || {};
  D.configs = D.configs || {};
  D.instances = D.instances || {};

  function isDark() {
    return document.documentElement.getAttribute('data-dark') === '1';
  }

  function uiTheme(dark) {
    return dark ? 'dark' : 'classic';
  }

  function previewTheme(contentTheme, dark) {
    if (contentTheme === 'auto') {
      return dark ? 'dark' : 'light';
    }
    if (contentTheme === 'light' || contentTheme === 'dark' || contentTheme === 'ant-design') {
      return contentTheme;
    }
    return dark ? 'dark' : 'light';
  }

  function buildToolbar(cfg) {
    var uploadable = !!(cfg.upload && cfg.upload.url);
    var toolbar;

    if (cfg.preset === 'simple') {
      toolbar = [
        'bold', 'italic', 'strike', 'link',
        '|', 'list', 'ordered-list', 'quote',
        '|', 'code', 'inline-code'
      ];
      if (uploadable) {
        toolbar.push('|', 'upload');
      }
      toolbar.push('|', 'undo', 'redo', 'edit-mode');
      return toolbar;
    }

    toolbar = [
      'headings', 'bold', 'italic', 'strike', 'link',
      '|', 'list', 'ordered-list', 'check', 'outdent', 'indent', 'quote',
      '|', 'code', 'inline-code',
      '|', 'table', 'line',
      '|', 'undo', 'redo',
      '|', 'edit-mode'
    ];
    if (uploadable) {
      toolbar.splice(toolbar.indexOf('table'), 0, 'upload');
    }
    return toolbar;
  }

  // 站点上传接口 code=1 成功，Vditor 约定 code=0 成功，这里只做协议转换。
  function adaptUploadResponse(files, responseText) {
    var result = null;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      result = null;
    }

    var succMap = {};
    var errFiles = [];
    if (result && result.code && result.url) {
      var name = (files && files[0] && files[0].name) ? files[0].name : 'file';
      succMap[name] = result.url;
    } else {
      errFiles = (files || []).map(function (file) { return file.name; });
    }

    return JSON.stringify({
      code: 0,
      msg: (result && result.msg) ? result.msg : '',
      data: { errFiles: errFiles, succMap: succMap }
    });
  }

  function stripBlockPopover(instance) {
    var popover = instance && instance.vditor && instance.vditor.wysiwyg && instance.vditor.wysiwyg.popover;
    if (!popover) {
      return;
    }

    function strip() {
      var selectors = 'button[data-type="up"],button[data-type="down"],button[data-type="remove"]';
      var nodes = popover.querySelectorAll(selectors);
      for (var i = 0; i < nodes.length; i++) {
        if (nodes[i].parentNode) {
          nodes[i].parentNode.removeChild(nodes[i]);
        }
      }
      // 浮层被清空后不再占位；链接编辑面板仍有子节点时保持显示。
      popover.style.display = popover.childNodes.length ? '' : 'none';
    }

    strip();
    if (!popover.__daskFloatObserver && window.MutationObserver) {
      popover.__daskFloatObserver = new MutationObserver(strip);
      popover.__daskFloatObserver.observe(popover, { childList: true, subtree: true });
    }
    if (instance) {
      instance.__daskPopover = popover;
    }
  }

  D.init = function (name) {
    var cfg = D.configs[name];
    var textarea = document.getElementById(name);
    var container = document.getElementById('vditor_' + name);
    var wrapper = textarea ? textarea.closest('.dask-editor') : null;
    if (!cfg || !textarea || !container) {
      return;
    }

    // AJAX 弹层（如回答编辑器）会反复插入同名 textarea/container。
    // 若旧实例已经不在当前文档中，必须先销毁，否则新容器只会留下空白。
    var existing = D.instances[name];
    if (existing) {
      var oldContainer = existing.__daskContainer;
      if (!oldContainer || oldContainer !== container || !document.body.contains(oldContainer)) {
        try {
          var popover = existing.__daskPopover;
          if (popover && popover.__daskFloatObserver) {
            popover.__daskFloatObserver.disconnect();
          }
          if (typeof existing.destroy === 'function') {
            existing.destroy();
          }
        } catch (e) {}
        delete D.instances[name];
      } else {
        return;
      }
    }
    if (typeof window.Vditor !== 'function') {
      if (window.layer && window.layer.msg) {
        window.layer.msg('编辑器资源加载失败，请刷新页面重试');
      }
      return;
    }

    if (!cfg.floatToolbar && wrapper) {
      wrapper.classList.add('dask-editor--no-float');
    }

    // 历史内容以转义 HTML 入库；textarea.value 已是真实 HTML。
    // 写入容器后由 Vditor 通过 HTML2Md 转为内部 Markdown，富文本 / Markdown 双模式共用一份数据。
    container.innerHTML = textarea.value || '';

    var instance = null;
    var options = {
      cdn: cfg.cdn,
      lang: 'zh_CN',
      icon: 'ant',
      theme: uiTheme(isDark()),
      mode: cfg.mode,
      height: cfg.isMobile ? 380 : 520,
      minHeight: 260,
      placeholder: '请输入内容（支持富文本与 Markdown 切换）',
      toolbar: buildToolbar(cfg),
      toolbarConfig: { hide: false, pin: false },
      cache: { enable: !!cfg.cache, id: 'dask-editor-' + name },
      counter: { enable: !!cfg.counter, max: 0, type: 'markdown' },
      preview: {
        theme: { current: previewTheme(cfg.contentTheme, isDark()) },
        hljs: { enable: !!cfg.codeHighlight },
        math: { engine: '' },
        markdown: { toc: false, mark: true }
      },
      after: function () {
        sync();
        if (!cfg.floatToolbar) {
          // after 可能在构造函数返回前触发，延后一拍确保 instance 已登记。
          setTimeout(function () { stripBlockPopover(D.instances[name]); }, 0);
        }
      },
      input: sync,
      blur: sync
    };

    if (cfg.upload && cfg.upload.url) {
      options.upload = {
        url: cfg.upload.url,
        fieldName: cfg.upload.field,
        multiple: false,
        max: cfg.upload.max,
        accept: cfg.upload.accept,
        withCredentials: true,
        timeout: cfg.upload.timeout,
        format: adaptUploadResponse
      };
    }

    function sync() {
      try {
        if (instance && instance.vditor && instance.vditor.currentMode) {
          textarea.value = instance.getHTML();
        }
      } catch (e) {
        // 编辑器尚未就绪时保留原 textarea 内容。
      }
    }

    instance = new window.Vditor(container, options);
    instance.__daskContainer = container;
    D.instances[name] = instance;
    sync();

    var form = textarea.closest('form');
    if (form && !form.__daskEditorBound) {
      form.__daskEditorBound = true;
      form.addEventListener('submit', function () {
        Object.keys(D.instances).forEach(function (key) {
          var target = document.getElementById(key);
          var ins = D.instances[key];
          try {
            if (target && ins && ins.vditor && ins.vditor.currentMode) {
              target.value = ins.getHTML();
            }
          } catch (e) {}
        });
      });
    }
  };

  document.addEventListener('dask:themechange', function (event) {
    var dark = !!(event && event.detail && event.detail.dark);
    Object.keys(D.instances).forEach(function (name) {
      var instance = D.instances[name];
      var cfg = D.configs[name] || {};
      try {
        if (instance && typeof instance.setTheme === 'function') {
          instance.setTheme(uiTheme(dark), previewTheme(cfg.contentTheme, dark));
        }
      } catch (e) {}
    });
  });
})(window, document);
