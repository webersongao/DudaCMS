/**
 * 私信弹窗脚本：由 Dask.api.open 只注入一次，之后每次开窗调用 daskModalInit.inbox(容器)。
 * 节点、收件人都必须按本次弹窗取，否则第二次打开时还绑在第一次那个已销毁的容器上。
 */
window.daskModalInit = window.daskModalInit || {};

(function () {
    var streamObserver = null;
    var emotionCache = null;
    var panelCloseBound = false;

    function insertAtCursor(field, text) {
        var start = field.selectionStart == null ? field.value.length : field.selectionStart;
        var end = field.selectionEnd == null ? start : field.selectionEnd;
        field.value = field.value.slice(0, start) + text + field.value.slice(end);
        var cursor = start + text.length;
        field.focus();
        field.setSelectionRange(cursor, cursor);
    }

    function loadEmotions() {
        if (emotionCache) return Promise.resolve(emotionCache);
        return fetch('/static/common/image/emotion/emotion.json').then(function (r) { return r.json(); }).then(function (list) {
            emotionCache = Array.isArray(list) ? list : [];
            return emotionCache;
        }).catch(function () { emotionCache = []; return emotionCache; });
    }

    window.daskModalInit.inbox = function (layero) {
        var root = $(layero);
        var stream = root.find('#inbox-dialog-container');
        if (!stream.length) {
            return;
        }
        var recipientUid = root.find('input[name=recipient_uid]').val();
        var scrollLatest = function () {
            stream[0].scrollTop = stream[0].scrollHeight;
        };

        if (streamObserver) {
            streamObserver.disconnect();
        }
        function watchStream(node) {
            if (!('MutationObserver' in window)) return;
            if (streamObserver) streamObserver.disconnect();
            streamObserver = new MutationObserver(function () {
                requestAnimationFrame(function () {
                    requestAnimationFrame(scrollLatest);
                });
            });
            streamObserver.observe(node, { childList: true, subtree: true });
        }
        watchStream(stream[0]);
        // 供发送后重载使用：旧 stream 节点被替换后，观察者要改绑到新节点。
        window.daskModalInit._inboxWatchStream = watchStream;

        Dask.api.ajaxLoadMore('#inbox-dialog-container', baseUrl + '/ajax/dialog', { recipient_uid: recipientUid });
        setTimeout(scrollLatest, 150);

        // QQ 表情：token（如 [微笑]）随正文入库，展示时由 render_inbox_message 统一还原成图片。
        var field = root.find('textarea[name=message]')[0];
        var panel = root.find('.dask-im-emoji-panel')[0];
        var emojiBtn = root.find('[data-im-tool=emoji]')[0];
        var imageBtn = root.find('[data-im-tool=image]')[0];
        var fileInput = root.find('input[type=file][name=inbox_image]')[0];
        if (!field || !panel) return;

        // 回车发送：Enter 发送、Shift+Enter 换行；中文输入法组词中的 Enter 不触发发送。
        // 绑定在本次弹窗的 textarea 上，避免全局委托叠加导致重复发送。
        var composing = false;
        field.addEventListener('compositionstart', function () { composing = true; });
        field.addEventListener('compositionend', function () { composing = false; });
        field.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' || e.shiftKey || composing || e.isComposing || e.keyCode === 229) {
                return;
            }
            e.preventDefault();
            var sendBtn = root.find('.dask-ajax-submit')[0];
            if (sendBtn) sendBtn.click();
        });

        if (!panel.dataset.ready) {
            panel.dataset.ready = '1';
            loadEmotions().then(function (items) {
                items.forEach(function (item) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.title = item.text || '';
                    btn.setAttribute('aria-label', item.text || '表情');
                    var img = document.createElement('img');
                    img.src = '/static/common/image/emotion/' + item.name + '.png';
                    img.alt = item.text || '';
                    btn.appendChild(img);
                    btn.addEventListener('click', function () { insertAtCursor(field, item.text); });
                    panel.appendChild(btn);
                });
            });
        }
        if (emojiBtn) {
            emojiBtn.onclick = function () { panel.hidden = !panel.hidden; };
        }
        if (!panelCloseBound) {
            panelCloseBound = true;
            document.addEventListener('click', function closePanel(event) {
                document.querySelectorAll('.dask-im-emoji-panel').forEach(function (p) {
                    if (!p.hidden && !p.contains(event.target) && !event.target.closest('[data-im-tool=emoji]')) {
                        p.hidden = true;
                    }
                });
            });
        }
        if (imageBtn && fileInput) {
            imageBtn.onclick = function () { fileInput.click(); };
            fileInput.onchange = function () {
                var file = fileInput.files && fileInput.files[0];
                if (!file) return;
                var key = 'im-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
                var data = new FormData();
                data.append('file', file);
                imageBtn.disabled = true;
                var previous = imageBtn.textContent;
                imageBtn.textContent = '上传中…';
                fetch(baseUrl + '/upload/index.html?path=inbox&access_key=' + encodeURIComponent(key) + '&upload_type=img', {
                    method: 'POST', body: data, credentials: 'same-origin'
                }).then(function (r) { return r.json(); }).then(function (result) {
                    var url = result && (result.url || result.location || (result.data && Object.values(result.data)[0]));
                    if (!url || (result && Number(result.code) !== 1 && result.state !== 'SUCCESS')) {
                        throw new Error((result && result.msg) || '图片上传失败');
                    }
                    // 入库只存相对路径，展示时由 DomainRewrite 适配当前域名
                    var path = String(url).replace(/^https?:\/\/[^/]+/i, '');
                    insertAtCursor(field, '[img]' + path + '[/img]');
                    panel.hidden = true;
                    layer.msg(result.msg || '上传成功');
                }).catch(function (error) {
                    layer.msg(error.message || '图片上传失败');
                }).finally(function () {
                    imageBtn.disabled = false;
                    imageBtn.textContent = previous;
                    fileInput.value = '';
                });
            };
        }
    };
})();

$(document).on('click', '.dask-ajax-submit', function (e) {
    var btn = this;
    var form = $(btn).closest('form');
    if (!form.length || btn.disabled) return;

    var field = form.find('textarea[name=message]')[0];
    if (!field || daskTrim(field.value) === '') {
        layer.msg('请输入私信内容');
        field && field.focus();
        return;
    }

    btn.disabled = true;
    var originalText = $(btn).text();
    $(btn).text('发送中…');

    $.ajax({
        url: form.attr('action'),
        dataType: 'json',
        type: 'post',
        data: form.serialize(),
        success: function (result) {
            if (result.code > 0) {
                // 消息流是 <form> 的兄弟节点，必须按弹窗容器查找，不能只在 form 内找。
                var layer = $(btn).closest('.layui-layer');
                var scope = layer.length ? layer : $(document);
                var stream = scope.find('#inbox-dialog-container, [data-inbox-stream]').first();
                var recipientUid = form.find('input[name=recipient_uid]').val();
                field.value = '';
                form.find('input[type=file][name=inbox_image]').val('');
                // 发送后整段重载对话流。消息按 id ASC 每页 5 条，新消息在最后一页；
                // layui.flow 实例绑定在旧节点上并持有分页游标，直接 empty/append 会被它干扰，
                // 因此换一个全新的 stream 节点（旧实例随之脱离），再逐页拉取全部追加。
                // 不给新节点复用 id：旧 layui.flow 实例按 '#inbox-dialog-container' 选择器
                // 渲染时会命中新节点并清空我们追加的内容。用 data 属性标识即可。
                var fresh = $('<div class="dask-im-stream" data-inbox-stream="1"></div>');
                stream.replaceWith(fresh);
                if (window.daskModalInit._inboxWatchStream) {
                    window.daskModalInit._inboxWatchStream(fresh[0]);
                }
                function loadPage(page, lastPage) {
                    $.ajax({
                        type: 'POST',
                        url: baseUrl + '/ajax/dialog?_ajax=1&page=' + page,
                        data: { recipient_uid: recipientUid },
                        success: function (html) {
                            var m = String(html).match(/data-total="(\d+)"/);
                            if (m) lastPage = Math.max(1, parseInt(m[1], 10) || 1);
                            fresh.append(html);
                            if (page < lastPage) {
                                loadPage(page + 1, lastPage);
                            } else {
                                fresh[0].scrollTop = fresh[0].scrollHeight;
                            }
                        }
                    });
                }
                loadPage(1, 1);
            } else {
                layer.msg(result.msg);
            }
        },
        error: function (error) {
            if (daskTrim(error.responseText) !== '') {
                layer.closeAll();
                Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
            }
        },
        complete: function () {
            btn.disabled = false;
            $(btn).text(originalText);
        }
    });
});
