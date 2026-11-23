/* 表单字段初始化收敛层（前后台共用）。
   原各 form/items 模板内联 <script> Now 统一为 data-dask-field 声明式：
   - upload / tree（P2 阶段已完成）保留；
   - laydate / color(Pickr) / code(CodeMirror) / select2 / tags / array / ajax-html 由本文件委托初始化，
     支持 DOM ready、pjax 重挂载与动态插入节点，data-dask-ready 防重复初始化。 */
(function () {
    'use strict';

    // ---------- upload（保留原逻辑） ----------
    function resolveUploader() {
        if (typeof window.upload === 'function') return window.upload;
        if (window.Dask && window.Dask.upload && typeof window.Dask.upload.upload === 'function') {
            return window.Dask.upload.upload;
        }
        return null;
    }
    function initUpload(root) {
        var scope = root && root.nodeType === 1 ? root : document;
        var nodes = [];
        if (scope.matches && scope.matches('[data-dask-upload]')) nodes.push(scope);
        if (scope.querySelectorAll) {
            nodes = nodes.concat(Array.prototype.slice.call(scope.querySelectorAll('[data-dask-upload]')));
        }
        nodes.forEach(function (el) {
            if (el.dataset.daskUploadReady === '1') return;
            var fn = resolveUploader();
            if (!fn) return;
            var d = el.dataset;
            fn(d.list, d.picker, d.preview, d.name, d.multiple === 'true',
               d.ext || '', d.size || '', d.type || 'file', d.path || 'common');
            el.dataset.daskUploadReady = '1';
        });
    }

    // ---------- jstree（保留原逻辑） ----------
    function initTrees(root) {
        var scope = root && root.nodeType === 1 ? root : document;
        var $ = window.jQuery;
        if (!$ || !$.fn || !$.fn.jstree) return;
        var nodes = [];
        if (scope.matches && scope.matches('[data-dask-tree]')) nodes.push(scope);
        if (scope.querySelectorAll) {
            nodes = nodes.concat(Array.prototype.slice.call(scope.querySelectorAll('[data-dask-tree]')));
        }
        nodes.forEach(function (el) {
            if (el.dataset.daskTreeReady === '1') return;
            var name = el.dataset.name;
            var source = $('#' + el.dataset.source).text().trim();
            var treeData = [];
            try { treeData = source ? JSON.parse(source) : []; }
            catch (error) { console.warn('[tree] invalid JSON data', error); }
            var $el = $(el);
            $el.jstree({
                core: { data: treeData },
                checkbox: { keep_selected_style: false, three_state: false, tie_selection: true, cascade: 'undetermined' },
                plugins: ['checkbox', 'wholerow']
            });
            $el.on('changed.jstree', function (e, data) {
                $('input[name="' + name + '"]').val(data.selected.join(','));
            });
            el.dataset.daskTreeReady = '1';
        });
    }

    // ---------- layui laydate（date/datetime/daterange/time） ----------
    function initLaydate(el) {
        var layui = window.layui;
        if (!layui || !layui.laydate || el.dataset.daskReady === '1') return;
        layui.laydate.render({
            elem: el,
            type: el.dataset.layType || 'date',
            format: el.dataset.layFormat || 'yyyy-MM-dd',
            range: el.dataset.layRange || false,
            trigger: 'click'
        });
        el.dataset.daskReady = '1';
    }

    // ---------- Pickr 颜色选择器 ----------
    function initColor(el) {
        if (el.dataset.daskReady === '1' || typeof window.Pickr === 'undefined') return;
        var name = el.dataset.colorName || '';
        var input = document.getElementById(name) || el.querySelector('input[type="text"]');
        var trigger = el.querySelector('.color-pickr-trigger');
        if (!input || !trigger) return;
        var pickr = window.Pickr.create({
            el: trigger,
            theme: 'classic',
            default: input.value || '#000000',
            components: {
                preview: true, opacity: false, hue: true,
                interaction: { hex: true, rgba: false, hsla: false, hsva: false, cmyk: false, input: true, clear: false, save: true }
            }
        });
        pickr.on('change', function (color) {
            var hex = color ? color.toHEXA().toString() : '';
            input.value = hex;
            var icon = trigger.querySelector('i');
            if (icon) icon.style.color = hex;
        });
        pickr.on('save', function () { pickr.hide(); });
        el.dataset.daskReady = '1';
    }

    // ---------- CodeMirror 代码编辑 ----------
    function syncCodeEditors() {
        var nodes = document.querySelectorAll('textarea[data-dask-field="code"][data-dask-ready="1"]');
        Array.prototype.forEach.call(nodes, function (el) {
            if (el._daskEditor) el.value = el._daskEditor.getValue();
        });
    }
  function initCode(el) {
    if (el.dataset.daskReady === '1' || !window.DaskCodeMirror) return;
    // CodeMirror 6：离线 IIFE 包暴露兼容适配层，保留 textarea 回写契约。
    // basicSetup 已内置行号/折叠/括号匹配/活动行；明暗主题由适配层跟随 data-dark。
    el._daskEditor = window.DaskCodeMirror.fromTextArea(el, {
      mode: el.dataset.cmMode || 'text/plain'
    });
    el.dataset.daskReady = '1';
  }

    // ---------- select2 ----------
    function initSelect2(el) {
        var $ = window.jQuery;
        if (!$ || !$.fn || !$.fn.select2 || el.dataset.daskReady === '1') return;
        var d = el.dataset;
        var option = {};
        if (d.s2HasOptions === '0' && (d.s2Url || '')) {
            var url = d.s2Url || '';
            var frontend = d.s2Flavor === 'frontend';
            option = {
                language: "zh-CN", allowClear: !frontend,
                ajax: {
                    type: "POST", delay: 250, url: url, dataType: 'json',
                    data: function (params) {
                        var rowsKey = frontend ? 'pagesize' : 'page_size';
                        var rows = frontend ? params.pagesize : params.page_size;
                        var ret = { keyWord: params.term || '', page: params.page || 1 };
                        ret[rowsKey] = rows || 10;
                        return ret;
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        var results = data.data || [];
                        if (frontend && params.page == 1) {
                            results.unshift({ id: '', name: '', text: d.s2Placeholder || '' });
                        }
                        return { results: results, pagination: { more: (params.page) < data.last_page } };
                    },
                    cache: true
                }
            };
            var defaultValue = d.s2Value || '';
            if (defaultValue) {
                $.ajax({
                    type: "POST", url: url, data: { value: defaultValue }, dataType: "json",
                    success: function (result) {
                        if (frontend) {
                            if (result && result.key !== undefined) {
                                $(el).append("<option selected value='" + result.key + "'>" + result.value + "</option>").trigger('change');
                            }
                        } else {
                            var str = '';
                            $.each(result.data || [], function (i, item) {
                                str += '<option selected value="' + item.id + '">' + item.text + '</option>';
                            });
                            if (str) $(el).append(str).trigger('change');
                        }
                    }
                });
            }
        }
        $(el).select2(option);
        el.dataset.daskReady = '1';
    }

    // ---------- jquery-tags-input ----------
    function initTags(el) {
        var $ = window.jQuery;
        if (!$ || !$.fn || el.dataset.daskReady === '1') return;
        // jquery.tagsinput.js 由表单项模板在 body 内加载，可能晚于本脚本的 bootstrap。
        // 插件未就绪时短暂轮询重试，避免因加载顺序导致 tags 永不初始化。
        if (!$.fn.tagsInput) {
            if (el.dataset.daskTagsRetry === '1') return;
            el.dataset.daskTagsRetry = '1';
            var tries = 0;
            var timer = setInterval(function () {
                tries++;
                if (window.jQuery && window.jQuery.fn.tagsInput) {
                    clearInterval(timer);
                    el.dataset.daskTagsRetry = '0';
                    initTags(el);
                } else if (tries >= 30) {
                    clearInterval(timer);
                    el.dataset.daskTagsRetry = '0';
                }
            }, 100);
            return;
        }
        $(el).tagsInput({
            width: 'auto', height: 'auto',
            placeholderColor: '#666666', defaultText: el.dataset.tagsText || '添加标签'
        });
        el.dataset.daskReady = '1';
    }

    // ---------- array 键值表（统一委托，只绑定一次） ----------
    function initArrayOnce() {
        var $ = window.jQuery;
        if (!$ || window.__daskArrayReady) return;
        window.__daskArrayReady = true;
        $(document).on('click', '[data-dask-field="array"] .btn-append', function () {
            var table = $(this).parents('table');
            var name = table.attr('data-array-name') || $(this).data('name') || '';
            var html = '<tr><td><input type="text" class="form-control form-control-sm" name="' + name + '[key][]"></td>' +
                '<td><input type="text" class="form-control form-control-sm" name="' + name + '[value][]"></td>' +
                '<td><a href="javascript:;" class="btn-remove btn btn-danger"><i class="fa fa-trash"></i></a></td></tr>';
            table.find('tbody').append(html);
        });
        $(document).on('click', '[data-dask-field="array"] .btn-remove', function () {
            $(this).parents('tr').remove();
        });
    }

    // ---------- ajax-html 联动加载 ----------
    function initAjaxHtml(el) {
        var $ = window.jQuery;
        if (!$ || el.dataset.daskReady === '1') return;
        var d = el.dataset;
        var url = d.ahUrl || '';
        var trigger = d.ahTrigger || '';
        var value = d.ahValue || '';
        var $box = $(el).find('#ajaxHtml');
        function load(u) {
            $box.html('<i class="fa fa-spinner fa-spin fa-fw"></i>');
            $.ajax({ type: "GET", url: u, success: function (html) { $box.html(html); } });
        }
        function withParam(v) { return url + (url.indexOf("?") > -1 ? "&" : "?") + trigger + "=" + v; }
        if (trigger) {
            $('#form_group_' + trigger + ' select').off('change.daskAjaxHtml').on('change.daskAjaxHtml', function () {
                load(withParam($(this).val()));
            });
            if (value) load(withParam(value));
        } else if (!value) {
            load(url);
        }
        el.dataset.daskReady = '1';
    }

    // ---------- 统一字段分发 ----------
    function initFields(root) {
        var scope = root && root.nodeType === 1 ? root : document;
        var nodes = [];
        if (scope.matches && scope.matches('[data-dask-field]')) nodes.push(scope);
        if (scope.querySelectorAll) {
            nodes = nodes.concat(Array.prototype.slice.call(scope.querySelectorAll('[data-dask-field]')));
        }
        nodes.forEach(function (el) {
            switch (el.dataset.daskField) {
                case 'laydate': initLaydate(el); break;
                case 'color': initColor(el); break;
                case 'code': initCode(el); break;
                case 'select2': initSelect2(el); break;
                case 'tags': initTags(el); break;
                case 'array': initArrayOnce(); break;
                case 'ajax-html': initAjaxHtml(el); break;
            }
        });
    }

    function bootstrap() {
        initUpload(document); initTrees(document); initFields(document);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
document.addEventListener('dask:page:rendered', bootstrap);
document.addEventListener('dask:ajax-loaded', bootstrap);
    // 代码编辑器：提交/操作按钮点击前同步 textarea 值
    document.addEventListener('click', function (e) {
        if (e.target && e.target.closest && e.target.closest('.dask-ajax-form')) syncCodeEditors();
    }, true);
    document.addEventListener('submit', function (e) {
        if (e.target && e.target.querySelector && e.target.querySelector('textarea[data-dask-field="code"]')) syncCodeEditors();
    }, true);

    if (window.MutationObserver) {
        var observer = new MutationObserver(function (records) {
            records.forEach(function (record) {
                Array.prototype.forEach.call(record.addedNodes, function (node) {
                    if (node.nodeType === 1) {
                        initUpload(node); initTrees(node); initFields(node);
                    }
                });
            });
        });
        observer.observe(document.documentElement, { childList: true, subtree: true });
    }

    window.DaskForm = { init: initFields };
})();
