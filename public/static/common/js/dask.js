/* Dask 前台主逻辑。结尾把 DaskUtil 合并进 Dask.util。 */
var Dask = {
    config: {
        cacheUserData: [],
        cashTopicData: [],
        card_box_hide_timer: '',
        card_box_show_timer: '',
        dropdown_list_xhr: '',
        loading_timer: '',
        loading_bg_count: 12,
        loading_mini_bg_count: 9,
        notification_timer: '',
        time: {
            waitTime: 3000
        }
    },

    api: {
        /**
         * 加载更多
         * @param element
         * @param url
         * @param data
         * @param callback
         * @param dataType
         */
        ajaxLoadMore: function (element, url, data, callback, dataType) {
            url = url ? url : $(element).data('url');
            url = url + (url.indexOf("?") > -1 ? "&" : "?") + "_ajax=1";
            let isAuto = true;
            layui.flow.load({
                isAuto: isAuto,
                elem: element,
                done: function (page, next) {
                    var requestUrl = url + (url.indexOf("?") > -1 ? "&" : "?") + "page=" + page;
                    $.ajax({
                        type: data ? 'POST' : 'GET',
                        url: requestUrl,
                        data: data,
                        dataType: dataType ? dataType : '',
                        success: function (res) {
                            if (typeof (callback) != 'function') {
                                if (res.data != undefined) {
                                    var total = res.data.last_page;
                                    next(res.data.html, page < total);
                                } else {
                                    var total = $($(res)[0]).data('total');
                                    next(res, page < total);
                                }
                            } else {
                                callback(res, page, next);
                            }
                        }
                    });
                }
            });
        },

        /**
         * 发送Ajax请求
         * @param options
         * @param success
         * @param error
         * @returns {*|jQuery}
         */
        ajax: function (options, success, error) {
            options = typeof options === 'string' ? { url: options } : options;
            options.url = options.url + (options.url.indexOf("?") > -1 ? "&" : "?") + "_ajax=1";
            options = $.extend({
                type: "POST",
                                dataType: "json",
                success: function (ret) {
                    if (typeof success === 'function') {
                        success(ret);
                    } else {
                        Dask.events.onAjaxSuccess(ret, success);
                    }
                },
                error: function (xhr) {
                    if (typeof error === 'function') {
                        error(xhr);
                    } else {
                        var ret = { code: xhr.status, msg: xhr.statusText, data: null };
                        Dask.events.onAjaxError(ret, error);
                    }
                }
            }, options);
            return $.ajax(options);
        },

        /**
         * ajax POST提交
         * @param url
         * @param data
         * @param success
         * @param error
         * @returns {*|jQuery}
         */
        post: function (url, data, success, error) {
            url = url + (url.indexOf("?") > -1 ? "&" : "?") + "_ajax=1";
            return $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                                success: function (ret) {
                    if (ret.code === 1) {
                        if (typeof success === 'function') {
                            success(ret);
                        } else {
                            Dask.events.onAjaxSuccess(ret, success);
                        }
                    } else {
                        if (typeof error === 'function') {
                            error(ret);
                        } else {
                            Dask.events.onAjaxError(ret, error);
                        }
                    }
                },
                error: function (xhr) {
                    let ret = { code: xhr.status, msg: xhr.statusText, data: null };
                    Dask.events.onAjaxError(ret, error);
                }
            });
        },

        /**
         * ajax GET提交
         * @param url
         * @param success
         * @param error
         * @returns {*|jQuery}
         */
        get: function (url, success, error, dataType) {
            $.ajax({
                type: 'GET',
                url: url,
                dataType: dataType ? dataType : 'json',
                                /*beforeSend: function(){
                    layer.load();
                },*/
                success: function (ret) {
                    if (typeof success != 'function') {
                        if (result.code > 0) {
                            Dask.api.success(result.msg, result.url)
                        } else {
                            Dask.api.error(result.msg, result.url)
                        }
                    } else {
                        success || success(result);
                    }
                },
                error: function (xhr) {
                    let ret = { code: xhr.status, msg: xhr.statusText, data: null };
                    Dask.events.onAjaxError(ret, error);
                }
            });
        },

        /**
         * ajax表单提交
         * @param element 表单标识
         * @param success 成功回调
         */
        ajaxForm: function (element, success) {
            let url = $(element).attr('action');
            $.ajax({
                url: url,
                dataType: 'json',
                type: 'post',
                                data: $(element).serialize(),
                success: function (result) {
                    if (typeof success != 'function') {
                        let msg = result.msg ? result.msg : '操作成功';
                        if (result.code > 0) {
                            Dask.api.success(msg, result.url)
                        } else {
                            Dask.api.error(msg, result.url)
                        }
                    } else {
                        success || success(result);
                    }
                },
                error: function (error) {
                    if (daskTrim(error.responseText) !== '') {
                        layer.closeAll();
                        Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
                    }
                }
            });
        },

        success: function (message, url) {
            if (message) {
                layer.msg(message, function () {
                    parent.layer.closeAll() || layer.closeAll();
                    if (typeof url !== 'undefined' && url) {
                        window.location.href = url;
                    }
                })
            } else {
                parent.layer.closeAll() || layer.closeAll();
                if (typeof url !== 'undefined' && url) {
                    window.location.href = url;
                }
            }
        },

        error: function (message, url) {
            if (message) {
                var index = layer.alert(message, {
                    yes: function () {
                        layer.close(index);
                        if (typeof url !== 'undefined' && url) {
                            window.location.href = url;
                        }
                    }
                })
            } else {
                if (typeof url !== 'undefined' && url) {
                    window.location.href = url;
                }
            }
        },

        msg: function (message, url) {
            layer.msg(message, {}, function () {
                if (typeof url !== 'undefined' && url) {
                    parent.layer.closeAll() || layer.closeAll();
                    window.location.href = url;
                } else {
                    window.location.reload();
                }
            })
        },

        /**
         * 打开一个弹出窗口
         * @param url
         * @param title
         * @param options
         * @returns {*}
         */
        // 已注入过的弹窗模块脚本，避免同一份脚本重复执行
        _modalLoadedScripts: {},

        /**
         * 弹窗模块脚本契约：脚本自身只注入一次，但把初始化函数挂到
         * window.daskModalInit[脚本文件名]；每次弹窗打开都带上本次 layer 容器再执行一遍。
         * 否则第二次开窗时，脚本里的节点、查询参数还停在第一次的那个弹窗上。
         */
        _runModalScripts: function (scripts, layero) {
            scripts.forEach(function (src) {
                var matched = src.match(/\/([^\/?#]+)\.js/);
                var name = matched ? matched[1] : '';
                var run = function () {
                    var init = window.daskModalInit && window.daskModalInit[name];
                    if (typeof init === 'function') {
                        init(layero);
                    }
                };
                if (Dask.api._modalLoadedScripts[src]) {
                    run();
                    return;
                }
                Dask.api._modalLoadedScripts[src] = true;
                var tag = document.createElement('script');
                tag.onload = run;
                tag.src = src;
                (document.body || document.head).appendChild(tag);
            });
        },

        /** 执行布局 script 块中的内联脚本；每次开窗都重新执行并按本次弹窗作用域初始化。 */
        _runModalInlineScripts: function (codes, layero) {
            codes.forEach(function (code) {
                try {
                    new Function('layero', code)(layero);
                } catch (error) {
                    console.error('[dask][modal-script]', error);
                }
            });
        },

        /**
         * 从服务端返回的完整 HTML 文档里提取弹窗内容片段与模块级脚本。
         * 原生弹窗不再用 iframe 加载整套框架，只取 #wrapMain 内容 + 该控制器的模块 js。
         */
        _parseModalHtml: function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var main = doc.getElementById('wrapMain') || doc.body;
            // 框架级脚本路径片段（主页面已加载，弹窗内不得重复加载）
            var framework = ['jquery', 'bootstrap', 'swiper', 'layui', 'dropzone',
                'captcha', '/dask.js', 'dask-modern',
                'dask-theme', '/app.js', '/static/app/index.js'];
            var moduleScripts = [];
            Array.prototype.forEach.call(doc.querySelectorAll('script[src]'), function (s) {
                var src = s.getAttribute('src');
                if (!src) return;
                var isFramework = framework.some(function (f) { return src.indexOf(f) > -1; });
                // 模块脚本目录在模板重组后迁到 /static/frontend/js/，旧约定 /static/js/ 仍兼容。
                // 框架级脚本由 framework 名单排除（含 /static/frontend/js/app.js 的 '/app.js'）。
                var isModule = src.indexOf('/static/js/') > -1 || src.indexOf('/static/frontend/js/') > -1;
                if (!isFramework && isModule) moduleScripts.push(src);
            });
            // 布局 script 块中的内联脚本（位于 #wrapMain 之外），需单独执行。
            var inlineScripts = [];
            Array.prototype.forEach.call(doc.querySelectorAll('script:not([src])'), function (s) {
                if (!main.contains(s) && s.textContent.trim() !== '') {
                    inlineScripts.push(s.textContent);
                }
            });
            // body 内容（去掉会污染主页面的 html/body 高度样式由调用方局部化）
            var content = main.innerHTML;
            return { content: content, scripts: moduleScripts, inlineScripts: inlineScripts };
        },

        /**
         * 打开一个弹出窗口（原生弹窗：ajax 注入内容，无 iframe、单滚动区）
         */
        open: function (url, title, options) {
            options = options || {};
            title = options.title ? options.title : (title ? title : "");
            url = url + (url.indexOf("?") > -1 ? "&" : "?") + "_ajax_open=1";
            var requestedWidth = options.width ? options.width : ($(window).width() > 600 ? '600px' : '95%');
            var width = requestedWidth > $(window).width() ? '90%' : requestedWidth;
            var height = options.height ? options.height : ($(window).height() > 550 ? '550px' : '90%');
            var area = !options.height ? [width] : [width, height];
            var max = options.max ? true : false;

            return $.ajax({
                url: url,
                dataType: 'html',
                type: 'GET',
                success: function (html) {
                    // 服务端返回 JSON 错误时直接提示，不开窗
                    var trimmed = daskTrim(html);
                    if (trimmed.charAt(0) === '{') {
                        try {
                            var result = JSON.parse(trimmed);
                            return layer.msg(result.msg || '操作失败', { time: 800 });
                        } catch (e) {}
                    }
                    var parsed = Dask.api._parseModalHtml(html);
                    var layerOptions = $.extend({
                        type: 1,
                        title: title,
                        shadeClose: true,
                        scrollbar: false,
                        shade: 0.7,
                        maxmin: max,
                        moveOut: true,
                        // 弹窗内容页自带操作按钮，不再显示兼容层默认“确定/取消”。
                        btn: [],
                        area: area,
                        content: parsed.content,
                        success: function (layero, index) {
                            layer.setTop(layero);
                            $(layero).data("callback", options.callback);
                            Dask.api._runModalInlineScripts(parsed.inlineScripts || [], layero);
                            Dask.api._runModalScripts(parsed.scripts, layero);
                        }
                    }, options);
                    // 防止调用方传入的 type/content 覆盖原生实现
                    layerOptions.type = 1;
                    layerOptions.content = parsed.content;
                    return layer.open(layerOptions);
                },
                error: function (xhr) {
                    var msg = daskTrim(xhr.responseText || '');
                    Dask.api.error(msg ? ('加载失败：' + msg.slice(0, 200)) : '加载失败，请稍后重试');
                }
            });
        },

        ajaxModal: function (url, title, width) {
            if (!title) title = '';
            url = url + (url.indexOf("?") > -1 ? "&" : "?") + "_ajax_open=1";
            width = width ? width : ($(window).width() > 600 ? '600px' : '95%');
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'html',
                success: function (ret) {
                    layer.open({
                        title: title,
                        type: 1,
                        fix: false, //不固定
                        maxmin: false,
                        shade: 0.8,
                        area: width,
                        content: ret,
                        scrollbar: false,
                        moveOut: true,
                        btn: [],
                    });
                },
            });
        },

        htmlModal: function (element, title, width) {
            if (!title) title = '';
            width = width ? width : ($(window).width() > 600 ? '600px' : '95%');
            layer.open({
                title: title,
                type: 1,
                fix: false, //不固定
                maxmin: false,
                area: width,
                shade: 0.8,
                content: $(element).html(),
                scrollbar: false,
                moveOut: true,
                btn: [],
            });
        },

        //post方式打开
        postOpen: function (url, title, data, options) {
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'html',
                success: function () {
                    return Dask.api.open(url, title, options);
                }
            });
        },
        downLoad: function (url, data) {
            $.ajax({
                url: url,
                type: "POST",
                data: data,
                success: function (response, status, request) {
                    var disp = request.getResponseHeader('Content-Disposition');
                    if (disp && disp.search('attachment') !== -1) {  //判断是否为文件
                        var form = $('<form method="POST" action="' + url + '">');
                        $.each(data, function (k, v) {
                            form.append($('<input type="hidden" name="' + k +
                                '" value="' + v + '">'));
                        });
                        $('body').append(form);
                        form.submit();
                    }
                }
            });
        },
    },

    events: {
        //请求成功的回调
        onAjaxSuccess: function (ret, onAjaxSuccess) {
            let data = typeof ret.data !== 'undefined' ? ret.data : null;
            let url = typeof ret.url !== 'undefined' ? ret.url : null;
            let msg = typeof ret.msg !== 'undefined' && ret.msg ? ret.msg : '';
            if (typeof onAjaxSuccess === 'function') {
                var result = onAjaxSuccess.call(this, data, ret);
                if (result === false)
                    return;
            }
            Dask.api.success(msg, url);
        },
        //请求错误的回调
        onAjaxError: function (ret, onAjaxError) {
            let data = typeof ret.data !== 'undefined' ? ret.data : null;
            let url = typeof ret.url !== 'undefined' ? ret.url : null;
            let msg = typeof ret.msg !== 'undefined' && ret.msg ? ret.msg : '';
            if (typeof onAjaxError === 'function') {
                var result = onAjaxError.call(this, data, ret);
                if (result === false) {
                    return;
                }
            }
            Dask.api.error(msg, url);
        },
        //服务器响应数据后
        onAjaxResponse: function (response) {
            response = typeof response === 'object' ? response : JSON.parse(response);
            return response;
        }
    },

    User: {
        /**
         * 切换语言
         */
        /*登录*/
        login: function () {
            let that = this;
            let options = $.extend({}, $(that).data() || {});
            let url = baseUrl + '/account/login/'
            if (typeof options.url === 'undefined' && $(that).attr("data-url")) {
                options.url = $(that).attr("data-url");
            }
            var width = Dask.common.isMobile() ? '90%' : '500px';
            return Dask.api.open(url, '', { width: width });
        },

        /*首次登录*/
        firstLogin: function () {
            return Dask.api.open(baseUrl + '/account/welcome_first_login', '', {
                title: window.daskFirstLoginTitle || '',
                width: '560px',
                btn: [],
                shadeClose: false
            });
        },

        // @人功能
        at_user_lists: function (selector, limit) {
            $(selector).keyup(function (e) {
                var _this = $(this),
                    flag = _getCursorPosition($(this)[0]).start;
                if ($(this).val().charAt(flag - 1) == '@') {
                    _init();
                    $('#dask-ajax-box .content_cursor').html($(this).val().substring(0, flag));
                } else {
                    var lis = $('.dask-invite-dropdown li');
                    switch (e.which) {
                        case 38:
                            var _index;
                            if (!lis.hasClass('active')) {
                                lis.eq(lis.length - 1).addClass('active');
                            }
                            else {
                                $.each(lis, function (i, e) {
                                    if ($(this).hasClass('active')) {
                                        $(this).removeClass('active');
                                        if ($(this).index() == 0) {
                                            _index = lis.length - 1;
                                        }
                                        else {
                                            _index = $(this).index() - 1;
                                        }
                                    }
                                });
                                lis.eq(_index).addClass('active');
                            }
                            break;
                        case 40:
                            var _index;
                            if (!lis.hasClass('active')) {
                                lis.eq(0).addClass('active');
                            }
                            else {
                                $.each(lis, function (i, e) {
                                    if ($(this).hasClass('active')) {
                                        $(this).removeClass('active');
                                        if ($(this).index() == lis.length - 1) {
                                            _index = 0;
                                        }
                                        else {
                                            _index = $(this).index() + 1;
                                        }
                                    }
                                });
                                lis.eq(_index).addClass('active');
                            }
                            break;
                        case 13:
                            $.each($('.dask-invite-dropdown li'), function (i, e) {
                                if ($(this).hasClass('active')) {
                                    $(this).click();
                                }
                            });
                            break;
                        default:
                            if ($('.dask-invite-dropdown')[0]) {
                                var ti = 0;
                                for (var i = flag; i > 0; i--) {
                                    if ($(this).val().charAt(i) == "@") {
                                        ti = i;
                                        break;
                                    }
                                }
                                $.get(baseUrl + '/ajax.search/search_result/?type=users&q=' + encodeURIComponent($(this).val().substring(flag, ti).replace('@', '')) + '&limit=' + limit, function (result) {
                                    var list = result.data.list;
                                    if ($('.dask-invite-dropdown')[0]) {
                                        if (result.code != 0) {
                                            var html = '';
                                            $('.dask-invite-dropdown').html('');
                                            $.each(list, function (i, a) {
                                                html += '<li><img src="' + a.avatar + '"/><a>' + a.name + '</a></li>'
                                            });
                                            $('.dask-invite-dropdown').append(html);
                                            _display();
                                            $('.dask-invite-dropdown li').click(function () {
                                                _this.val(_this.val().substring(0, ti) + '@' + $(this).find('a').text() + " ").focus();
                                                $('.dask-invite-dropdown').detach();
                                            });
                                        }
                                        else {
                                            $('.dask-invite-dropdown').hide();
                                        }
                                    }
                                    if (_this.val().length == 0) {
                                        $('.dask-invite-dropdown').hide();
                                    }
                                }, 'json');
                            }
                    }
                }
            });
            $(selector).keydown(function (e) {
                var key = e.which;
                if (Dask.util.isVisible($('.dask-invite-dropdown'))) {
                    if (key == 38 || key == 40 || key == 13) {
                        return false;
                    }
                }
            });

            //初始化插入定位符
            function _init() {
                if (!$('.content_cursor')[0]) {
                    $('#dask-ajax-box').append('<span class="content_cursor"></span>');
                }
                var left = $(selector).offset().left,
                    top = $(selector).offset().top + $(selector).height();
                $('#dask-ajax-box').find('.content_cursor').css({
                    'left': parseInt($(selector).offset().left + parseInt($(selector).css('padding-left')) + 2),
                    'top': parseInt($(selector).offset().top + parseInt($(selector).css('padding-left')) - 25)
                });

                if (!$('.dask-invite-dropdown')[0]) {
                    $('#dask-ajax-box').append('<ul class="dask-invite-dropdown"></ul>');
                }
            };

            //初始化列表和三角型
            function _display() {
                $('.dask-invite-dropdown').css({
                    'left': $('.content_cursor').offset().left + $('.content_cursor').innerWidth(),
                    'top': $('.content_cursor').offset().top - 85
                }).show();
            };

            //获取当前textarea光标位置
            function _getCursorPosition(textarea) {
                var rangeData = {
                    text: "",
                    start: 0,
                    end: 0
                };

                textarea.focus();

                if (textarea.setSelectionRange) { // W3C
                    rangeData.start = textarea.selectionStart;
                    rangeData.end = textarea.selectionEnd;
                    rangeData.text = (rangeData.start != rangeData.end) ? textarea.value.substring(rangeData.start, rangeData.end) : "";
                } else if (document.selection) { // IE
                    var i,
                        oS = document.selection.createRange(),
                        // Don't: oR = textarea.createTextRange()
                        oR = document.body.createTextRange();
                    oR.moveToElementText(textarea);

                    rangeData.text = oS.text;
                    rangeData.bookmark = oS.getBookmark();

                    // object.moveStart(sUnit [, iCount])
                    // Return Value: Integer that returns the number of units moved.
                    for (i = 0; oR.compareEndPoints('StartToStart', oS) < 0 && oS.moveStart("character", -1) !== 0; i++) {
                        // Why? You can alert(textarea.value.length)
                        if (textarea.value.charAt(i) == '\n') {
                            i++;
                        }
                    }
                    rangeData.start = i;
                    rangeData.end = rangeData.text.length + rangeData.start;
                }

                return rangeData;
            };
        },

        /**
         * 发送私信
         */
        inbox: function (user_name, isDialog) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }

            isDialog = isDialog ? isDialog : true;

            if (userName == user_name || user_name == userId) {
                return layer.msg('自己不可以给自己发送私信');
            }

            var url = user_name ? baseUrl + '/ajax/inbox?user_name=' + user_name : baseUrl + '/ajax/inbox';
            var title = user_name ? '与 ' + user_name + ' 对话' : '新私信';

            if (isDialog) {
                return Dask.api.open(url, title, { btn: [], width: '620px', shadeClose: true })
            } else {
                Dask.common.openNewWindow(url);
            }
        },

        /**
         * 已读私信
         * @param element
         * @param id
         */
        /**
         * 用户赞同
         * @param element
         * @param itemType
         * @param itemId
         * @returns {boolean}
         */
        agree: function (element, itemType, itemId) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }
            if (!Dask.User.lockVote(itemType, itemId)) {
                return false;
            }
            let that = $(element);
            let hasClass = that.hasClass('active') ? 1 : 0;
            let voteValue = hasClass ? 0 : 1;
            $.ajax({
                url: baseUrl + '/ajax/set_vote/',
                dataType: 'json',
                type: 'post',
                data: {
                    item_id: itemId,
                    item_type: itemType,
                    vote_value: voteValue
                },
                success: function (result) {
                    let value = result.data.vote_value;
                    if (result.code) {
                        if (!value && hasClass) {
                            that.removeClass('active');
                        }
                        if (!value && !hasClass) {
                            that.parents('.dz').find('.dask-ajax-against').removeClass('active');
                            that.parent().parent('div').find('.dask-ajax-against').removeClass('active');
                        }
                        if (value === 1) {
                            that.addClass('active');
                            that.parents('.dz').find('.dask-ajax-against').removeClass('active');
                            that.parent().parent('div').find('.dask-ajax-against').removeClass('active');
                        }
                        that.find('span').text(result.data.agree_count);
                    }
                    layer.msg(result.msg);
                },
                error: function (error) {
                    if (daskTrim(error.responseText) !== '') {
                        layer.msg('发生错误, 返回的信息:' + ' ' + error.responseText);
                    }
                },
                complete: function () {
                    Dask.User.unlockVote(itemType, itemId);
                }
            });
        },

        /**
         * 赞/踩请求在途锁：同一内容只允许一个投票请求在飞，避免连点产生多次写入
         */
        lockVote: function (itemType, itemId) {
            let key = itemType + ':' + itemId;
            if (Dask.User._votingKeys[key]) {
                return false;
            }
            Dask.User._votingKeys[key] = 1;
            return true;
        },

        unlockVote: function (itemType, itemId) {
            delete Dask.User._votingKeys[itemType + ':' + itemId];
        },

        _votingKeys: {},

        /**
         * 用户反对
         * @param element
         * @param itemType
         * @param itemId
         * @returns {boolean}
         */
        against: function (element, itemType, itemId) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }
            if (!Dask.User.lockVote(itemType, itemId)) {
                return false;
            }
            let that = $(element);
            let hasClass = that.hasClass('active') ? 1 : 0;
            let voteValue = hasClass ? 0 : -1;

            $.ajax({
                url: baseUrl + '/ajax/set_vote/',
                dataType: 'json',
                type: 'post',
                data: {
                    item_id: itemId,
                    item_type: itemType,
                    vote_value: voteValue
                },
                success: function (result) {
                    let value = result.data.vote_value;
                    if (result.code > 0) {
                        if (!value && hasClass) {
                            that.removeClass('active');
                        }
                        if (!value && !hasClass) {
                            that.parents('.dz').find('.dask-ajax-agree').removeClass('active');
                            that.parents('.dz').find('.dask-ajax-agree').find('span').text(result.data.agree_count)

                            that.parent().parent('div').find('.dask-ajax-agree').removeClass('active');
                            that.parent().parent('div').find('.dask-ajax-agree').find('span').text(result.data.agree_count)
                        }
                        if (value === -1) {
                            that.addClass('active');
                            that.parents('.actions').find('.dask-ajax-agree').removeClass('active');
                            that.parents('.actions').find('.dask-ajax-agree').find('span').text(result.data.agree_count)

                            that.parent().parent('div').find('.dask-ajax-agree').removeClass('active');
                            that.parent().parent('div').find('.dask-ajax-agree').find('span').text(result.data.agree_count)
                        }
                    }
                    layer.msg(result.msg);
                },
                complete: function () {
                    Dask.User.unlockVote(itemType, itemId);
                }
            });
        },

        /**
         * 全局关注
         * @param element
         * @param type
         * @param id
         */
        focus: function (element, type, id) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }
            let that = $(element);
            switch (type) {
                case 'topic':
                case 'user':
                case 'question':
                case 'column':
                case 'favorite':
                default:
                    Dask.api.post(
                        baseUrl + '/ajax/update_focus/',
                        { id: id, type: type },
                        function (res) {
                            if (res.code) {
                                if (that.hasClass('ygz')) {
                                    that.removeClass('ygz').addClass('gz');
                                    that.text('关注');
                                    Dask.api.success('取消关注成功');
                                } else {
                                    that.addClass('ygz').removeClass('gz');
                                    that.text('已关注');
                                    Dask.api.success('关注成功');
                                }

                                if (that.parent().find('.focus-count')) {
                                    that.parent().find('.focus-count').text(res.data.count);
                                }
                            }
                        });
                    break;
            }
        },

        /**
         * 用户收藏
         * @param element
         * @param itemType
         * @param itemId
         * @returns {boolean}
         */
        favorite: function (element, itemType, itemId) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }

            return Dask.api.open(baseUrl + '/favorite/dialog?item_type=' + itemType + '&item_id=' + itemId, '用户收藏', {});
        },

        /**
         * 感谢回答
         * @param element
         * @param itemId
         * @returns {boolean|*|jQuery}
         */
        thanks: function (element, itemId) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }
            return Dask.api.ajax(baseUrl + '/answer/thanks?id=' + itemId, function (res) {
                if (res.code) {
                    $(element).removeAttr('data-action').addClass('active');
                    $(element).find('span').text('已感谢');
                }
                layer.msg(res.msg);
            });
        },

        /**
         * 通用不感兴趣
         * @param element
         * @param itemType
         * @param itemId
         * @returns {boolean|*}
         */
        uninterested: function (element, itemType, itemId) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }
            return Dask.api.post(baseUrl + '/ajax/uninterested', { id: itemId, type: itemType }, function (res) {
                if (res.code) {
                    $(element).parent().detach();
                }
                layer.msg(res.msg);
            });
        },

        /**
         * 通用举报
         * @param element
         * @param itemType
         * @param itemId
         * @returns {boolean}
         */
        report: function (element, itemType, itemId) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }
            let that = $(element);
            layer.confirm('确定要举报吗？', {}, function () {
                layer.closeAll();
                Dask.api.open(baseUrl + '/ajax/report?item_type=' + itemType + '&item_id=' + itemId, '', that.data())
            });
        },

        /**
         * 公共邀请
         * @param element
         * @param itemId
         * @returns {boolean}
         */
        invite: function (element, itemId) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }
            let that = $(element);
            Dask.api.open(baseUrl + '/ajax/invite?question_id=' + itemId, '', that.data());
        },

        /**
         * 保存草稿
         * @param element
         * @param item_type
         * @param itemId
         * @returns {boolean}
         */
        draft: function (element, item_type, itemId) {
            if (!parseInt(userId)) {
                Dask.User.login();
                return false;
            }

            let form = $($(element).parents('form')[0]);
            let formData = {};
            let t = form.serializeArray();
            $.each(t, function () {
                formData[this.name] = this.value;
            });

            $.ajax({
                url: baseUrl + '/ajax/save_draft',
                dataType: 'json',
                type: 'post',
                data: {
                    data: formData,
                    item_id: itemId,
                    item_type: item_type
                },
                success: function (result) {
                    let msg = result.msg ? result.msg : '保存成功';
                    if (result.code > 0) {
                        Dask.api.success(msg)
                    } else {
                        Dask.api.error(msg)
                    }
                },
                error: function (error) {
                    if (daskTrim(error.responseText) !== '') {
                        layer.closeAll();
                        Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
                    }
                }
            });
        },

        /**
         * 用户卡片
         * @param selector
         * @param type
         * @param time
         */
        showCard: function (selector, type, time) {
            time = time ? time : 300;
            var verifiedBadgeHtml = function (data) {
                if (!data.verified_icon) return '';
                var label = Dask.modernEscapeHtml(data.verified_name || '已认证');
                return '<span class="dask-verified-badge" title="' + label + '" aria-label="' + label + '">' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg>' +
                    '</span>';
            };
            $(document).on('mouseover', selector, function () {
                clearTimeout(Dask.config.card_box_hide_timer);
                var _this = $(this);
                var ajaxBox = $('#dask-ajax-box');
                Dask.config.card_box_show_timer = setTimeout(function () {
                    var url;
                    var uid = _this.attr('data-id');
                    var html;
                    if (type === 'user') {
                        url = baseUrl + '/ajax/get_user_info?uid=';
                        //判断用户id or 话题id 是否存在
                        if (uid) {
                            if (Dask.config.cacheUserData.length == 0) {
                                $.get(url + uid, function (result) {
                                    var data = result.data;
                                    var focusText = data.is_focus ? '已关注' : '关注';
                                    var focusClass = data.is_focus ? 'ygz' : 'gz';
                                    var signature = data.signature ? data.signature : '这家伙啥都没留下..';
                                    var foot = '';
                                    var onLineText = '';
                                    var groupName = data.group_name ? data.group_name : '未知组';
                                    var verifiedBadge = verifiedBadgeHtml(data);
                                    if (data.check_online == 'Y') {
                                        onLineText = data.is_online ? ' <span class="badge text-bg-success">在线</span>' : ' <span class="badge text-bg-danger">离线</span>';
                                    }
                                    //动态插入盒子
                                    if (userId && userId != uid) {
                                        foot = '<div class="dask-mod-footer clearfix">' +
                                            '<span>' +
                                            '<a class="text-muted" href="javascript:void(0)" data-action="user-inbox" data-name="' + Dask.modernEscapeAttr(data.nick_name) + '"><i class="icon icon-inbox"></i> 私信</a>&nbsp;&nbsp;&nbsp;&nbsp;' +
                                            '</span>' +
                                            '<a class="btn btn-primary follow btn-sm float-end ' + focusClass + '" href="javascript:void(0)" data-action="user-focus" data-type="user" data-id="' + data.uid + '"><span>' + focusText + '</span> <em>|</em> <b>' + data.fans_count + '</b></a>' +
                                            '</div>';
                                    }
                                    html = '<div id="dask-ui-card-tips" class="dask-ui-card-tips dask-ui-card-tips-user">' +
                                        '<div class="dask-mod">' +
                                        '<div class="dask-mod-head" style="height: auto">' +
                                        '<a href="' + data.url + '" class="img dask-avatar-wrap">' +
                                        '<img src="' + data.avatar + '" alt="' + data.name + '" onerror="this.src=\'/static/common/image/default-avatar.svg\'" width="50" height="50"/>' +
                                        verifiedBadge +
                                        '</a>' +
                                        '<p class="title clearfix">' +
                                        '<a href="' + data.url + '" class="name" data-id="' + data.uid + '">' + data.name + '</a> <span class="badge text-bg-success">' + groupName + '</span> ' + onLineText +
                                        '</p>' +
                                        '<p class="dask-user-center-follow-meta mt-3 font-7">' +
                                        '<span>成长值: <em class="dask-text-color-green">' + data.credit + '</em></span>' +
                                        '<span>赞同: <em class="dask-text-color-orange">' + data.agree_count + '</em></span>' +
                                        '</p>' +
                                        '</div>' +
                                        '<div class="dask-mod-body pb-3">' +
                                        '<p class="font-9 dask-two-line">' + signature + '</p>' +
                                        '</div>' + foot +
                                        '</div>' +
                                        '</div>';

                                    ajaxBox.html(html).show();
                                    //判断是否为游客or自己
                                    if (userId === data.uid || data.uid < 0) {
                                        $('#dask-ui-card-tips .dask-mod-footer').hide();
                                    }
                                    _init();
                                    //缓存
                                    if (html) {
                                        Dask.config.cacheUserData.push(html);
                                    }
                                }, 'json');
                            } else {
                                var flag = 0;
                                //遍历缓存中是否含有此id的数据
                                $.each(Dask.config.cacheUserData, function (i, a) {
                                    if (a.match('data-id="' + uid + '"')) {
                                        ajaxBox.html(a);
                                        $('#dask-ui-card-tips').removeAttr('style');
                                        _init();
                                        flag = 1;
                                    }
                                });
                                if (flag === 0) {
                                    $.get(url + uid, function (result) {
                                        var data = result.data;
                                        var focusText = data.is_focus ? '已关注' : '关注';
                                        var focusClass = data.is_focus ? 'ygz' : 'gz';
                                        var signature = data.signature ? data.signature : '这家伙啥都没留下..';
                                        var onLineText = '';
                                        var groupName = data.group_name ? data.group_name : '未知组';
                                        var verifiedBadge = verifiedBadgeHtml(data);
                                        if (data.check_online == 'Y') {
                                            onLineText = data.is_online ? ' <span class="badge text-bg-success">在线</span>' : ' <span class="badge text-bg-danger">离线</span>';
                                        }
                                        //动态插入盒子
                                        html = '<div id="dask-ui-card-tips" class="dask-ui-card-tips dask-ui-card-tips-user">' +
                                            '<div class="dask-mod">' +
                                            '<div class="dask-mod-head" style="height: auto">' +
                                            '<a href="' + data.url + '" class="img dask-avatar-wrap">' +
                                            '<img src="' + data.avatar + '" onerror="this.src=\'/static/common/image/default-avatar.svg\'" alt="' + data.name + '" width="50" height="50"/>' +
                                            verifiedBadge +
                                            '</a>' +
                                            '<p class="title clearfix">' +
                                            '<a href="' + data.url + '" class="name" data-id="' + data.uid + '">' + data.name + '</a> <span class="badge text-bg-success">' + groupName + '</span>' + onLineText +
                                            '</p>' +
                                            '<p class="dask-user-center-follow-meta mt-3 font-7">' +
                                            '<span>成长值: <em class="dask-text-color-green">' + data.credit + '</em></span>' +
                                            '<span>赞同: <em class="dask-text-color-orange">' + data.agree_count + '</em></span>' +
                                            '</p>' +
                                            '</div>' +
                                            '<div class="dask-mod-body pb-2">' +
                                            '<p class="font-9 dask-two-line">' + signature + '</p>' +
                                            '</div>' +
                                            '<div class="dask-mod-footer clearfix">' +
                                            '<span>' +
                                            '<a class="text-muted" href="javascript:void(0)" data-action="user-inbox" data-name="' + Dask.modernEscapeAttr(data.nick_name) + '"><i class="icon icon-inbox"></i> 私信</a>&nbsp;&nbsp;&nbsp;&nbsp;' +
                                            '</span>' +
                                            '<a class="btn btn-primary btn-sm follow float-end ' + focusClass + '" data-action="user-focus" data-type="user" data-id="' + data.uid + '"><span>' + focusText + '</span> <em>|</em> <b>' + data.fans_count + '</b></a>' +
                                            '</div>' +
                                            '</div>' +
                                            '</div>';
                                        ajaxBox.html(html).show();
                                        //判断是否为游客or自己
                                        if (userId === data.uid || data.uid < 0) {
                                            $('#dask-ui-card-tips .dask-mod-footer').hide();
                                        }

                                        _init();
                                        //缓存
                                        if (html) {
                                            Dask.config.cacheUserData.push(html);
                                        }
                                    }, 'json');
                                }
                            }
                        }
                    }

                    // 话题悬浮卡片：统一结构，避免旧 .dask-mod 规则导致图标与标题重叠
                    var renderTopicCard = function (data) {
                        var esc = Dask.modernEscapeHtml;
                        var title = esc(data.title);
                        var desc = esc(data.description || '暂无话题简介');
                        var isFocus = data.is_focus ? 'active ygz' : 'gz';
                        return '<div id="dask-ui-card-tips" class="dask-ui-card-tips dask-topic-card">' +
                            '<div class="dask-topic-card-head">' +
                                '<a class="dask-topic-card-icon" href="' + esc(data.url) + '" target="_blank">' +
                                    '<img src="' + esc(data.pic || '/static/common/image/topic.svg') + '" alt="' + title + '" onerror="this.src=\'/static/common/image/topic.svg\'" width="44" height="44">' +
                                '</a>' +
                                '<div class="dask-topic-card-body">' +
                                    '<a class="dask-topic-card-title" href="' + esc(data.url) + '" target="_blank">' + title + '</a>' +
                                    '<p class="dask-topic-card-desc">' + desc + '</p>' +
                                '</div>' +
                            '</div>' +
                            '<div class="dask-topic-card-footer">' +
                                '<span class="dask-topic-card-stat">讨论 <strong>' + esc(data.discuss) + '</strong></span>' +
                                '<a class="btn btn-sm btn-primary follow dask-topic-card-follow ' + isFocus + '" href="javascript:;" data-action="user-focus" data-type="topic" data-id="' + esc(data.id) + '">' +
                                    '<span>' + (data.is_focus ? '已关注' : '关注') + '</span><em>|</em><b>' + esc(data.focus) + '</b>' +
                                '</a>' +
                            '</div>' +
                        '</div>';
                    };

                    if (type === 'topic') {
                        url = baseUrl + '/ajax/get_topic_info?id=';
                        //判断用户id or 话题id 是否存在
                        if (uid) {
                            if (Dask.config.cashTopicData.length === 0) {
                                $.get(url + uid, function (result) {
                                    var data = result.data;
                                    var focusText = data.is_focus ? '已关注' : '关注';
                                    var focusClass = data.is_focus ? 'ygz' : 'gz';
                                    //动态插入盒子
                                    html = renderTopicCard(data);

                                    ajaxBox.html(html).show();
                                    if (!userId) {
                                        $('#dask-ui-card-tips .mod-footer .follow').hide();
                                    }
                                    _init();
                                    //缓存
                                    if (html) {
                                        Dask.config.cashTopicData.push(html);
                                    }
                                }, 'json');
                            } else {
                                var flag = 0;
                                //遍历缓存中是否含有此id的数据
                                $.each(Dask.config.cashTopicData, function (i, a) {
                                    if (a.match('data-id="' + uid + '"')) {
                                        ajaxBox.html(a);
                                        $('#dask-ui-card-tips').removeAttr('style');
                                        _init();
                                        flag = 1;
                                    }
                                });
                                if (flag === 0) {
                                    $.get(url + uid, function (result) {
                                        var data = result.data;
                                        var focusText = data.is_focus ? '已关注' : '关注';
                                        var focusClass = data.is_focus ? 'ygz' : 'gz';
                                        //动态插入盒子
                                        html = renderTopicCard(data);
                                        ajaxBox.html(html).show();
                                        //判断是否为游客or自己
                                        if (!userId) {
                                            $('#dask-ui-card-tips .dask-mod-footer .follow').hide();
                                        }
                                        _init();
                                        //缓存
                                        if (html) {
                                            Dask.config.cashTopicData.push(html);
                                        }
                                    }, 'json');
                                }
                            }
                        }
                    }

                    //初始化
                    function _init() {
                        var left = _this.offset().left,
                            top = _this.offset().top + _this.height() + 20,
                            nTop = _this.offset().top - $(window).scrollTop();

                        var cardBox = $('#dask-ui-card-tips');
                        //判断下边距离不足情况
                        if (nTop + cardBox.innerHeight() > $(window).height()) {
                            top = _this.offset().top - (cardBox.innerHeight()) - 10;
                        }

                        //判断右边距离不足情况
                        if (left + cardBox.innerWidth() > $(window).width()) {
                            left = _this.offset().left - cardBox.innerWidth() + _this.innerWidth();
                        }
                        left = Math.max(left, 20);
                        cardBox.css({ left: left, top: top }).fadeIn();
                    }
                }, time);
            });

            $(document).on('mouseout', selector, function () {
                clearTimeout(Dask.config.card_box_show_timer);
                Dask.config.card_box_hide_timer = setTimeout(function () {
                    $('#dask-ui-card-tips').fadeOut();
                }, 600);
            });
        },

        /**
         * 删除通知
         * @param element
         * @param id
         */
        deleteNotify: function (element, id) {
            Dask.api.post(baseUrl + '/notify/delete', { id: id }, function (res) {
                if (res.code) {
                    $(element).parents('.dask-hcard').detach();
                    $(element).parents('.header-inbox-item').detach();
                    $('.header-notify-count').text(parseInt($('.header-notify-count').text()) - 1);
                }
            });
        },

        /**
         * 折叠回复
         * @param element
         * @param id
         */
        forceFoldAnswer: function (element, id) {
            var url = $(element).data('url');
            layer.confirm('是否折叠该回答?<br>折叠的回复是被你或者被大多数用户认为不感兴趣的回复才会被折叠', {
                btn: ['确认', '取消']
            }, function () {
                Dask.api.post(url, { answer_id: id }, function (res) {
                    if (res.code) {
                        $(element).parents('.dask-answer-item').detach();
                        Dask.events.onAjaxSuccess(res)
                    } else {
                        Dask.events.onAjaxError(res)
                    }
                });
            }, function () {
                layer.closeAll();
            });
        },

        /**
         * 已读通知
         * @param element
         * @param id
         */
        readNotify: function (element, id) {
            Dask.api.post(baseUrl + '/notify/read', { id: id }, function (res) {
                if (res.code) {
                    $(element).hide();
                    $(element).parents('.header-inbox-item').detach();
                    // 桌面与窄屏各有一份未读数徽标，必须同步更新。
                    $('[data-notify-unread]').each(function () {
                        var n = parseInt($(this).text(), 10) - 1;
                        if (!(n > 0)) $(this).detach();
                        else $(this).text(n);
                    });
                }
            });
        },

        headerNotifyReadAll: function () {
            layer.load();
            Dask.api.get(baseUrl + '/notify/read_all', function (res) {
                $('[data-notify-unread]').detach();
                $('[data-notify-list]').empty();
            });
            layer.closeAll();
        },

        share: function (title, url, desc, type) {
            let target_url;
            const enc = function (value) { return encodeURIComponent(value || ''); };
            switch (type) {
                //分享QQ好友
                case 'qq':
                    target_url = 'https://connect.qq.com/widget/shareqq/index.html?url=' + enc(url) + '&sharesource=qzone&title=' + enc(title) + '&desc=' + enc(desc);
                    window.open(target_url, '_blank', 'noopener');
                    break;
                //分享新浪微博
                case 'weibo':
                    target_url = 'https://service.weibo.com/share/share.php?url=' + enc(url) + '&title=' + enc(title);
                    window.open(target_url, '_blank', 'noopener');
                    break;
                case 'qzone':
                    target_url = 'https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' + enc(url) + '&title=' + enc(title) + '&desc=' + enc(desc);
                    window.open(target_url, '_blank', 'noopener');
                    break;
                case 'twitter':
                    target_url = 'https://twitter.com/intent/tweet?url=' + enc(url) + '&text=' + enc(title);
                    window.open(target_url, '_blank', 'noopener');
                    break;
                case 'telegram':
                    target_url = 'https://t.me/share/url?url=' + enc(url) + '&text=' + enc(title);
                    window.open(target_url, '_blank', 'noopener');
                    break;
            }
        },

        /**
         * 收录文章至专栏
         * @param article_id
         * @param column_id
         */
        saveColumnArticle: function (article_id, column_id, element) {
            var that = $(element);
            var url = baseUrl + '/ajax.column/collect';
            Dask.api.post(url, { article_id: article_id, column_id: column_id }, function (res) {
                var msg = res.msg ? res.msg : '收录成功';
                if (res.code == 1) {
                    that.text('已收录').removeClass('btn-outline-primary').addClass('btn-primary');
                    that.removeAttr('data-action');
                }

                layer.msg(msg);
            });
        }
    },

    Dropdown: {
        // 下拉菜单功能绑定
        bind_dropdown_list: function (selector, type) {
            // 搜索框聚焦时不再直接弹出空白浮层：只有输入关键词后才由
            // get_dropdown_list 显示联想结果，避免大块空白遮挡页面题图。
            $(selector).on('compositionstart', function (e) {
                e.target.composing = true
            })

            $(selector).on('compositionend', function (e) {
                e.target.composing = false
                trigger(e.target, 'input')
            });

            function trigger(el, type) {
                var e = document.createEvent('HTMLEvents')
                e.initEvent(type, true, false)
                el.dispatchEvent(e)
            }

            $(selector).on('input', function (e) {
                if (e.target.composing) {
                    return
                }

                if (type == 'search') {
                    $(selector).parent().find('.search').show().children('a').text($(selector).val());
                }

                if ($(selector).val().length >= 1) {
                    if (e.which != 38 && e.which != 40 && e.which != 188 && e.which != 13) {
                        Dask.Dropdown.get_dropdown_list($(this), type, $(selector).val());
                    }
                }
                else {
                    $(selector).parent().find('.dask-ui-dropdown').hide();
                }

                if (type == 'topic') {
                    // 逗号或回车提交
                    if (e.which == 188) {
                        if ($('.dask-edit-topic-box #dask_edit_topic_title').val() != ',') {
                            $('.dask-edit-topic-box #dask_edit_topic_title').val($('.dask-edit-topic-box #dask_edit_topic_title').val().substring(0, $('.dask-edit-topic-box #dask_edit_topic_title').val().length - 1));
                            $('.dask-edit-topic-box .dask-ui-dropdown').hide();
                            $('.dask-edit-topic-box .add').click();
                        }
                        return false;
                    }

                    // 回车提交
                    if (e.which == 13) {
                        $('.dask-edit-topic-box .dask-ui-dropdown').hide();
                        $('.dask-edit-topic-box .add').click();
                        return false;
                    }

                    var lis = $(selector).parent().find('.dask-ui-dropdown-list li');

                    //键盘往下
                    if (e.which == 40 && Dask.util.isVisible(lis)) {
                        var _index;
                        if (!lis.hasClass('active')) {
                            lis.eq(0).addClass('active');
                        }
                        else {
                            $.each(lis, function (i, e) {
                                if ($(this).hasClass('active')) {
                                    $(this).removeClass('active');
                                    if ($(this).index() == lis.length - 1) {
                                        _index = 0;
                                    }
                                    else {
                                        _index = $(this).index() + 1;
                                    }
                                }
                            });
                            lis.eq(_index).addClass('active');
                            $(selector).val(lis.eq(_index).text());
                        }
                    }

                    //键盘往上
                    if (e.which == 38 && Dask.util.isVisible(lis)) {
                        var _index;
                        if (!lis.hasClass('active')) {
                            lis.eq(lis.length - 1).addClass('active');
                        }
                        else {
                            $.each(lis, function (i, e) {
                                if ($(this).hasClass('active')) {
                                    $(this).removeClass('active');
                                    if ($(this).index() == 0) {
                                        _index = lis.length - 1;
                                    }
                                    else {
                                        _index = $(this).index() - 1;
                                    }
                                }
                            });
                            lis.eq(_index).addClass('active');
                            $(selector).val(lis.eq(_index).text());
                        }
                    }
                }
            });

            $(selector).blur(function () {
                $(selector).parent().find('.dask-ui-dropdown').delay(500).fadeOut(300);
            });
        },

        // 插入下拉菜单
        set_dropdown_list: function (selector, type) {
            switch (type) {
                case 'invite':
                case 'inbox':
                    $(selector).parent().find('.dask-ui-dropdown .dask-ui-dropdown-list li a').click(function () {
                        var text = $(this).text();
                        $(selector).val(text);
                    });
                    break;
            }
        },

        /* 下拉菜单数据获取 */
        get_dropdown_list: function (selector, type, data) {
            if (Dask.config.dropdown_list_xhr != '') {
                Dask.config.dropdown_list_xhr.abort(); // 中止上一次ajax请求
            }
            var url;
            switch (type) {
                case 'search':
                    url = baseUrl + '/ajax.search/ajax/?q=' + encodeURIComponent(data) + '&limit=15';
                    break;
                case 'invite':
                case 'inbox':
                    url = baseUrl + '/ajax.search/search_result/?type=users&q=' + encodeURIComponent(data) + '&limit=10';
                    break;
                case 'publish':
                    url = baseUrl + '/ajax.search/search_result/?type=question&q=' + encodeURIComponent(data) + '&limit=10';
                    break;
            }

            Dask.config.dropdown_list_xhr = $.get(url, function (result) {
                if (result.length != 0 && Dask.config.dropdown_list_xhr != undefined) {
                    $(selector).parent().find('.dask-ui-dropdown-list').html(''); // 清空内容
                    switch (type) {
                        case 'search':
                            $(selector).parent().find('.dask-ui-dropdown-list').html(result);
                            break;
                        case 'inbox':
                        case 'invite':
                            var html = '<ul>';
                            $.each(result.data.list, function (i, a) {
                                html += '<li class="user py-1"><a data-url="' + a.url + '" data-id="' + a.uid + '" style="cursor: pointer"><img class="img" src="' + a.avatar + '" style="width: 16px;height: 16px"/> ' + a.nick_name + '</a></li>'
                            });
                            html += '</ul>';
                            $(selector).parent().find('.dask-ui-dropdown-list').html(html);
                            $(selector).parent().find('.dask-ui-dropdown .dask-ui-dropdown-list li a').click(function () {
                                var text = $(this).text();
                                $(selector).val(text);
                            });
                            break;

                        case 'publish':
                            if (result.data.list) {
                                $(selector).parent().find('.dask-ui-dropdown').show();
                                var html = '<ul>';
                                $.each(result.data.list, function (i, a) {
                                    html += '<li class="user py-1"><a href="' + a.url + '" style="cursor: pointer">' + a.title + '</a></li>'
                                });
                                html += '</ul>';
                                $(selector).parent().find('.dask-ui-dropdown-list').html(html);
                            } else {
                                $(selector).parent().find('.dask-ui-dropdown').hide();
                            }
                            break;
                    }
                    $(selector).parent().find('.dask-ui-dropdown, .dask-ui-dropdown-list').show().children().show();
                    $(selector).parent().find('.title').hide();
                } else {
                    $(selector).parent().find('.dask-ui-dropdown').show().end().find('.title').html('没有找到相关结果').show();
                }
            }, 'json');
        },
    },

    /* 动作按钮窄屏收口：把「图标 + 数字 + 文字」中的文字包成 label、数字包成 count。
       样式层在窄屏只隐藏 label，图标与数字保留；title/aria-label 补回完整语义。 */
    compactActions: function () {
        var selector = [
            '.dask-list-item__actions label',
            '.dask-list-item__actions > a',
            '.dask-list-item__actions label > a',
            '.answer-btn-actions label > a',
            '.answer-btn-actions > a',
            '.dask-content-info .actions.dask-actions label > a',
            '.dask-content-info .actions.dask-actions > a',
            '.dask-content-info .actions.dask-actions .dropdown > a[data-bs-toggle="dropdown"]'
        ].join(',');
        var nodes = Array.prototype.slice.call(document.querySelectorAll(selector));
        nodes.forEach(function (node) {
            if (!node || node.dataset.daskCompact === '1') return;
            if (!node.querySelector('i, svg, [class*="icon-"]')) return;
            var full = (node.textContent || '').replace(/\s+/g, ' ').trim();
            if (!full) return;
            // 日期/时间不能被拆成数字段，否则 flex gap 会把 2025-10-17 13:03 撑成 2025 - 10 - 17 13 : 03
            if (/\d{4}[-\/]\d{1,2}[-\/]\d{1,2}/.test(full) || /\d{1,2}:\d{2}/.test(full)) return;
            if (!node.getAttribute('title')) node.setAttribute('title', full);
            node.setAttribute('aria-label', full);
            node.classList.add('dask-action-compact');

            Array.prototype.slice.call(node.childNodes).forEach(function (child) {
                if (child.nodeType !== 3) return;
                var raw = child.nodeValue || '';
                if (!raw.trim()) return;
                var frag = document.createDocumentFragment();
                (raw.match(/\d+|[^\d]+/g) || []).forEach(function (part) {
                    var txt = part.trim();
                    if (!txt) return;
                    var span = document.createElement('span');
                    span.className = /^\d+$/.test(txt) ? 'dask-action-count' : 'dask-action-label';
                    span.textContent = txt;
                    frag.appendChild(span);
                });
                node.replaceChild(frag, child);
            });

            Array.prototype.slice.call(node.querySelectorAll('span')).forEach(function (span) {
                if (span.classList.contains('badge') || span.classList.contains('dask-action-label') || span.classList.contains('dask-action-count')) return;
                var txt = (span.textContent || '').trim();
                if (!txt) return;
                span.classList.add(/^\d+$/.test(txt) ? 'dask-action-count' : 'dask-action-label');
            });
            node.dataset.daskCompact = '1';
        });
    },

    init: function () {
        $(function () {
            var compactTimer;
            var scheduleCompact = function () {
                clearTimeout(compactTimer);
                compactTimer = setTimeout(Dask.compactActions, 80);
            };
            Dask.compactActions();
            document.addEventListener('turbo:load', scheduleCompact);
            document.addEventListener('dask:page:rendered', scheduleCompact);
            $(document).ajaxComplete(scheduleCompact);
            //ajax获取
            $(document).on('click', '.dask-ajax-get,.ajax-get', function (e) {
                e.preventDefault();
                e.target.blur();
                let that = this;
                let options = $.extend({}, $(that).data() || {});
                if (typeof options.url === 'undefined' && $(that).attr("data-url")) {
                    options.url = $(that).attr("data-url");
                }
                let success = typeof options.success === 'function' ? options.success : null;
                let error = typeof options.error === 'function' ? options.error : null;
                delete options.success;
                delete options.error;

                if (options.login && !userId) {
                    layer.msg('您还未登录,请登录后再操作!');
                    return false;
                }

                if (options.confirm) {
                    layer.confirm(options.confirm, {
                        btn: ['确认', '取消']
                    }, function () {
                        Dask.api.ajax(options.url, success, error);
                    }, function () {
                        if (typeof options.cancel === 'undefined') {
                            layer.closeAll();
                        } else {
                            Dask.api.ajax(options.cancel, success, error);
                        }
                    });
                } else {
                    Dask.api.ajax(options.url, success, error);
                }
            });

            //弹窗点击
            $(document).on('click', '.ajax-open,.dask-ajax-open', function (e) {
                let that = this;
                let options = $(that).data();
                let url = $(that).data("url") ? $(that).data("url") : $(that).attr('href');
                let title = $(that).attr("title") || $(that).data("title") || $(that).data('original-title');
                if (options.login && !userId) {
                    layer.msg('您还未登录,请登录后再操作!');
                    return false;
                }

                if (typeof options.confirm !== 'undefined') {
                    layer.confirm(options.confirm, function (index) {
                        Dask.api.open(url, title, options);
                        layer.close(index);
                    });
                } else {
                    Dask.api.open(url, title, options);
                }
                return false;
            });

            //post弹窗点击
            $(document).on('click', '.ajax-post-open', function (e) {
                let that = this;
                let options = $.extend({}, $(that).data() || {});
                let url = $(that).data("url") ? $(that).data("url") : $(that).attr('href');
                let title = $(that).attr("title") || $(that).data("title") || $(that).data('original-title');
                let data = $(that).data('data') || {};

                if (typeof options.confirm !== 'undefined') {
                    layer.confirm(options.confirm, function (index) {
                        Dask.api.postOpen(url, title, data, options);
                        layer.close(index);
                    });
                } else {
                    window[$(that).data("window") || 'self'].Dask.api.postOpen(url, title, data, options);
                }
                return false;
            });

            //ajax表单提交带验证
            $(document).on('click', '.ajax-form', function (e) {
                let that = this;
                let options = $.extend({}, $(that).data() || {});
                let form = $($(that).parents('form')[0]);
                let success = typeof options.success === 'function' ? options.success : null;

                if (!verification(form.serializeArray())) {
                    return false;
                }
                delete options.success;
                delete options.error;
                $(that).attr('type', 'button');
                $.ajax({
                    url: form.attr('action'),
                    dataType: 'json',
                    type: 'post',
                    data: form.serialize(),
                                        beforeSend: function () {
                        $(".ajax-form").attr({ disabled: "disabled" });
                    },
                    success: function (result) {
                        if (typeof success !== 'function') {
                            let msg = result.msg ? result.msg : '操作成功';
                            if (result.code > 0) {
                                Dask.api.success(msg, result.url)
                            } else {
                                Dask.api.error(msg, result.url)
                            }
                        } else {
                            success || success(result);
                        }
                    },
                    complete: function () {
                        //移除禁用
                        $(".ajax-form").removeAttr("disabled");
                    },
                    error: function (error) {
                        if (daskTrim(error.responseText) !== '') {
                            layer.closeAll();
                            Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
                        }
                    }
                });
            });

            //ajax表单提交不带验证
            $(document).on('click', '.dask-ajax-form', function (e) {
                let that = this;
                let options = $.extend({}, $(that).data() || {});
                let form = $($(that).parents('form')[0]);
                $(that).attr('disabled', true);
                let success = typeof options.success === 'function' ? options.success : null;
                delete options.success;
                delete options.error;
                $.ajax({
                    url: form.attr('action'),
                    dataType: 'json',
                    type: 'post',
                    data: form.serialize(),
                                        success: function (result) {
                        if (typeof success !== 'function') {
                            let msg = result.msg ? result.msg : '操作成功';
                            if (result.code > 0) {
                                Dask.api.success(msg, result.url)
                            } else {
                                $(that).attr('disabled', false);
                                if (msg) {
                                    layer.msg(msg, {}, function () {
                                        if (typeof url !== 'undefined' && url) {
                                            window.location.href = url;
                                        }
                                    })
                                } else {
                                    if (typeof url !== 'undefined' && url) {
                                        window.location.href = url;
                                    }
                                }
                            }
                        } else {
                            success || success(result);
                        }
                    },
                    error: function (error) {
                        if (daskTrim(error.responseText) !== '') {
                            layer.closeAll();
                            Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
                        }
                    }
                });
            });

            //ajax 通用POST提交
            $(document).on('click', '.ajax-post,.dask-ajax-post', function (e) {
                let target, query, form;
                let target_form = $(this).attr('data-target-form');
                let that = this;
                let need_confirm = false;
                if (($(this).attr('type') == 'submit') || (target = $(this).attr('href')) || (target = $(this).attr('data-url'))) {
                    form = $('.' + target_form);

                    if ($(this).attr('hide-data') === 'true') { //无数据时也可以使用的功能
                        form = $('.hide-data');
                        query = form.serialize();
                    } else if (form.get(0) == undefined) {
                        return false;
                    } else if (form.get(0).nodeName == 'FORM') {
                        if ($(this).hasClass('confirm')) {
                            if (!confirm('确认要执行该操作吗?')) {
                                return false;
                            }
                        }
                        if ($(this).attr('url') !== undefined) {
                            target = $(this).attr('url');
                        } else {
                            target = form.get(0).action;
                        }
                        query = form.serialize();
                    } else if (form.get(0).nodeName == 'INPUT' || form.get(0).nodeName == 'SELECT' || form.get(0).nodeName == 'TEXTAREA') {
                        form.each(function (k, v) {
                            if (v.type === 'checkbox' && v.checked === true) {
                                need_confirm = true;
                            }
                        });
                        if (need_confirm && $(this).hasClass('confirm')) {
                            if (!confirm('确认要执行该操作吗?')) {
                                return false;
                            }
                        }
                        query = form.serialize();

                    } else {
                        if ($(this).hasClass('confirm')) {
                            if (!confirm('确认要执行该操作吗?')) {
                                return false;
                            }
                        }
                        query = form.find('input,select,textarea').serialize();
                    }

                    $(that).addClass('disabled').attr('autocomplete', 'off').prop('disabled', true);

                    $.post(target, query).success(function (data) {
                        if (data.code === 1) {
                            Dask.api.success(data.msg);
                            setTimeout(function () {
                                $(that).removeClass('disabled').prop('disabled', false);
                                if (data.url) {
                                    location.href = data.url;
                                } else if ($(that).hasClass('no-refresh')) {
                                    $('#top-alert').find('button').click();
                                } else {
                                    location.reload();
                                }
                            }, 1500);
                        } else {
                            Dask.api.error(data.msg);
                            setTimeout(function () {
                                $(that).removeClass('disabled').prop('disabled', false);
                            }, 1500);
                        }
                    });
                }
                return false;
            });

            $(document).on('click', '[data-delete]', function (t) {
                let url = this.dataset.delete
                let that = $(this);
                layer.confirm('确定要删除吗？', {}, function () {
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        success: function (res) {
                            if (res.code) {
                                layer.closeAll();
                                Dask.api.success(res.msg, res.url);
                                that.parents('.post-comments-single').remove();
                            } else {
                                Dask.api.error(res.msg);
                            }
                        },
                    });
                });
            });

            $(document).on('click', '[data-lock]', function (t) {
                let url = this.dataset.lock
                let that = $(this);
                $.ajax({
                    url: url,
                    dataType: 'json',
                    success: function (res) {
                        if (res.code) {
                            Dask.api.success(res.msg, res.url);
                        } else {
                            Dask.api.error(res.msg);

                        }
                    },
                })
            });
        })
    },

    upload: {
        /**
         * 上传组件
         * @param listContainer 多文件内容器
         * @param filePicker 文件选择按钮
         * @param preview 图片预览显示容器
         * @param field 字段名称
         * @param more 多选上传
         * @param type 上传类型
         * @param path 上传路径
         */
        webUpload: function (filePicker, preview, field, path, type, more, listContainer) {
            type = type || 'img';
            path = path || 'common';
            let upload_allowExt, size;
            if (type == 'img') {
                upload_allowExt = upload_image_ext.replace(/\|/g, ",");
            } else {
                upload_allowExt = upload_file_ext.replace(/\|/g, ",");
            }

            if (type == 'img') {
                size = upload_image_size * 1024;
            } else {
                size = upload_file_size * 1024;
            }

            var $list = $("#" + listContainer + "");
            var GUID = DaskUploader.Base.guid();                            // 一个GUID
            // 单图替换时记录上一张上传附件，新图成功后立即回收旧图，避免反复换封面时文件累积。
            var lastSingleUpload = null;
            var $scope = $("#" + filePicker).closest('form');
            var $accessKey = $scope.find('input[name="access_key"]').first();
            if (!$accessKey.length) {
                $accessKey = $('<input type="hidden" name="access_key">').val(DaskUploader.Base.guid());
                if ($scope.length) {
                    $scope.append($accessKey);
                } else {
                    $('body').append($accessKey);
                }
            }
            var accessKey = $accessKey.val() || DaskUploader.Base.guid();
            $accessKey.val(accessKey);
            var uploader = DaskUploader.create({
                auto: true,                                                // 选完文件后，是否自动上传。
                server: baseUrl + '/upload/index?upload_type=' + type + '&path=' + encodeURIComponent(path) + '&access_key=' + encodeURIComponent(accessKey), // 文件接收服务端
                pick: '#' + filePicker,                              // 选择文件的按钮。可选。
                resize: false,                                             // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
                chunked: false,                                             // 是否分片
                chunkSize: size,                                // 分片大小
                threads: 1,                                                // 上传并发数
                formData: {
                    // 由于Http的无状态特征，在往服务器发送数据过程传递一个进入当前页面是生成的GUID作为标示
                    GUID: GUID,                                            // 自定义参数
                    access_key: accessKey,                                 // 绑定单图/封面到表单批次
                },
                compress: false,
                fileSingleSizeLimit: size,                                 // 限制大小200M，单文件
                //fileSizeLimit: allMaxSize*1024*1024,                     // 限制大小10M，所有被选文件，超出选择不上
                accept: {
                    title: '上传图片/文件',
                    extensions: upload_allowExt,                           // 允许上传的类型 'gif,jpg,jpeg,bmp,png'
                    mimeTypes: '*',                                        // 默认全部文件，为兼容上传文件功能，如只上传图片可写成img/*
                }
            });

            // 文件上传过程中创建进度条实时显示。
            uploader.on('uploadProgress', function (file, percentage) {
                var $li = $list,
                    $percent = $li.find('.progress .progress-bar');
                // 避免重复创建
                if (!$percent.length) {
                    $percent = $('<div class="progress progress-striped active">' +
                        '<div class="progress-bar" role="progressbar" style="width: 0%">' +
                        '</div>' +
                        '</div>').appendTo($li).find('.progress-bar');
                }
                $percent.css('width', percentage * 100 + '%');
            });

            uploader.on('uploadSuccess', function (file, response) {
                if (response.code == 0) {
                    layer.msg(response.msg);
                }
                let url = response.url;
                if (more == true) {
                    var images = '<div class="row"><div class="col-6"><input type="text" name="' + field + '[]" value="' + url + '" class="form-control"/></div> <div class="col-3"><input class="form-control input-sm" type="text" name="' + field + '_title[]" value="' + file.name + '" ></div> <div class="col-3"><button type="button" class="btn w-100 btn-warning remove_images">移除</button></div></div>';
                    var images_list = $('#more_images_' + field).html();

                    $('#more_images_' + field).html(images + images_list);

                } else {
                    // 只回收本表单上一次上传的附件：remove_attach 会校验 access_key，
                    // 且服务端仍会做内容引用检查，不会误删已发布内容里的图片。
                    if (lastSingleUpload && lastSingleUpload.id
                        && String(lastSingleUpload.id) !== String(response.attach_id || '')) {
                        $.ajax({
                            url: baseUrl + '/upload/remove_attach',
                            type: 'post',
                            dataType: 'json',
                            data: { id: lastSingleUpload.id, access_key: lastSingleUpload.access_key },
                        });
                    }
                    lastSingleUpload = (response.attach_id && Number(response.attach_id) > 0)
                        ? {id: response.attach_id, access_key: response.access_key || accessKey}
                        : null;
                    $("input[name='" + field + "']").val(url);
                    // 服务端可能重新签发批次凭证，必须同步回表单，否则发布后附件无法绑定。
                    if (response.access_key) {
                        accessKey = response.access_key;
                        $scope.find('input[name="access_key"]').first().val(accessKey);
                    }
                    $("#" + preview).attr('src', url);
                    $("#" + preview).parent("a").attr('href', url);
                }
            });
            uploader.on('uploadComplete', function (file) {
                $list.find('.progress').fadeOut();
            });
            // 错误提示
            uploader.on("error", function (type) {
                if (type == "Q_TYPE_DENIED") {
                    layer.msg('请上传' + upload_allowExt + '格式的文件！');
                } else if (type == "F_EXCEED_SIZE") {
                    layer.msg('单个文件大小不能超过' + size / 1024 + 'kb！');
                } else if (type == "F_DUPLICATE") {
                    layer.msg('请不要重复选择文件');
                } else {
                    layer.msg('上传出错！请检查后重新上传！错误代码' + type);
                }
            });
        },

        /**
         * 通用上传方法
         * @param options
         * @param success
         * @param complete
         * @param error
         */
        commonUpload: function (options, success, complete, error) {
            options.path = options.path || 'common';
            let upload_allowExt, size, url;
            url = options.url || baseUrl + '/upload/index?upload_type=' + options.type + '&path=' + options.path + '&access_key=' + options.access_key;
            options = $.extend({
                filePicker: '',//文件选择触发器
                preview: '.upload-image-preview',//单图容器
                field: 'dask-file',//上传字段
                path: 'common',//上传路径
                type: 'img',
                more: false,
                progressContainer: '.upload-progress-container',//上传进度条容器
                imagesList: '.upload-images-container',//多图容器
                uploadBtnTitle: '上传图片',
                url: url,
                access_key: ''
            }, options);

            if (options.type == 'img') {
                upload_allowExt = upload_image_ext.replace(/\|/g, ",");
            } else {
                upload_allowExt = upload_file_ext.replace(/\|/g, ",");
            }

            if (options.type == 'img') {
                size = upload_image_size * 1024;
            } else {
                size = upload_file_size * 1024;
            }

            var $list = $(options.progressContainer);
            var GUID = DaskUploader.Base.guid();// 一个GUID
            var uploader = DaskUploader.create({
                auto: true,                                        // 选完文件后，是否自动上传。
                server: options.url, // 文件接收服务端
                pick: options.filePicker,                              // 选择文件的按钮。可选。
                resize: false,                                             // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
                chunked: false,                                             // 是否分片
                chunkSize: size,                                // 分片大小
                threads: 1,                                                // 上传并发数
                formData: {
                    // 由于Http的无状态特征，在往服务器发送数据过程传递一个进入当前页面是生成的GUID作为标示
                    GUID: GUID,                                            // 自定义参数
                },
                compress: false,
                fileSingleSizeLimit: size,                                 // 限制大小200M，单文件
                accept: {
                    title: options.uploadBtnTitle,
                    extensions: upload_allowExt,                           // 允许上传的类型 'gif,jpg,jpeg,bmp,png'
                    mimeTypes: '*',                                        // 默认全部文件，为兼容上传文件功能，如只上传图片可写成img/*
                }
            });
            // 文件上传过程中创建进度条实时显示。
            uploader.on('uploadProgress', function (file, percentage) {
                var $li = $list, $percent = $li.find('.progress .progress-bar');
                // 避免重复创建
                if (!$percent.length) {
                    $percent = $('<div class="progress progress-striped active">' +
                        '<div class="progress-bar" role="progressbar" style="width: 0%">' +
                        '</div>' +
                        '</div>').appendTo($li).find('.progress-bar');
                }
                $percent.css('width', percentage * 100 + '%');
            });
            uploader.on('uploadSuccess', function (file, response) {
                if (typeof success === 'function') {
                    success(file, response);
                } else {
                    if (response.code === 0) {
                        layer.msg(response.msg);
                    }
                    let url = response.url;
                    if (options.more === true) {
                        var images = '<div class="row"><div class="col-6"><input type="text" name="' + options.field + '[]" value="' + url + '" class="form-control"/></div> <div class="col-3"><input class="form-control input-sm" type="text" name="' + field + '_title[]" value="' + file.name + '" ></div> <div class="col-3"><button type="button" class="btn w-100 btn-warning remove_images">移除</button></div></div>';
                        var images_list = $(options.imagesList).html();
                        $(options.imagesList).html(images + images_list);
                    } else {
                        $("input[name='" + options.field + "']").val(url);
                        $(options.preview).attr('src', url);
                        $(options.preview).parent("a").attr('href', url);
                    }
                }
            });
            uploader.on('uploadComplete', function (file) {
                if (typeof complete === 'function') {
                    complete(file);
                } else {
                    $list.find('.progress').fadeOut();
                }
            });
            // 错误提示
            uploader.on("error", function (type) {
                if (typeof error === 'function') {
                    error(type);
                } else {
                    if (type == "Q_TYPE_DENIED") {
                        layer.msg('请上传' + upload_allowExt + '格式的文件！');
                    } else if (type == "F_EXCEED_SIZE") {
                        layer.msg('单个文件大小不能超过' + size / 1024 + 'kb！');
                    } else if (type == "F_DUPLICATE") {
                        layer.msg('请不要重复选择文件');
                    } else {
                        layer.msg('上传出错！请检查后重新上传！错误代码' + type);
                    }
                }
            });
        },

        /**
         * 删除附件
         * @param obj
         * @param attachId
         * @param access_key
         * @returns {boolean}
         */
        removeAttach: function (obj, attachId, access_key) {
            Dask.api.post(baseUrl + '/upload/remove_attach', { id: attachId, access_key: access_key }, function (res) {
                if (res.code) {
                    $(obj).parents('tr').detach();
                }
                layer.msg(res.msg);
            });

            return false;
        },

        /**
         * 附件上传
         * @param elem
         * @param bindAction
         * @param uploadList
         * @param path
         * @param access_key
         * @param id
         */
        attachUpload: function (elem, bindAction, uploadList, path, access_key, id) {
            if (!id) {
                $(uploadList).hide();
                $(bindAction).hide();
            }
            var uploadListIns = layui.upload.render({
                elem: elem
                , elemList: $(uploadList).find('tbody')
                , url: baseUrl + "/upload/index?path=" + path + '&access_key=' + access_key
                , accept: 'file'
                , multiple: true
                , number: 3
                , auto: false
                , bindAction: bindAction
                , choose: function (obj) {
                    var that = this;
                    var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
                    //读取本地文件
                    obj.preview(function (index, file, result) {
                        $(uploadList).show();
                        $(bindAction).show();
                        var tr = $(['<tr id="upload-' + index + '">'
                            , '<td>' + file.name + '</td>'
                            , '<td>' + (file.size / 1014).toFixed(1) + 'kb</td>'
                            , '<td><div class="layui-progress" lay-filter="progress-demo-' + index + '"><div class="layui-progress-bar" lay-percent=""></div></div></td>'
                            , '<td>'
                            , '<button class="layui-btn layui-btn-xs demo-reload layui-hide">重传</button>'
                            , '<button class="layui-btn layui-btn-xs layui-btn-danger demo-delete">删除</button>'
                            , '</td>'
                            , '</tr>'].join(''));

                        //单个重传
                        tr.find('.demo-reload').on('click', function () {
                            obj.upload(index, file);
                        });

                        //删除
                        tr.find('.demo-delete').on('click', function () {
                            delete files[index]; //删除对应的文件
                            tr.remove();
                            uploadListIns.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                        });

                        that.elemList.append(tr);
                        layui.element.render('progress'); //渲染新加的进度条组件
                    });
                }
                , done: function (res, index, upload) { //成功的回调
                    var that = this;
                    if (res.code === 1) { //上传成功
                        var tr = that.elemList.find('tr#upload-' + index), tds = tr.children();
                        tds.eq(3).html('上传成功'); //清空操作
                        delete this.files[index]; //删除文件队列已经上传成功的文件
                        return;
                    }
                    this.error(index, upload);
                }
                , allDone: function (obj) { //多文件上传完毕后的状态回调
                }
                , error: function (index, upload) { //错误回调
                    var that = this;
                    var tr = that.elemList.find('tr#upload-' + index)
                        , tds = tr.children();
                    tds.eq(3).find('.demo-reload').removeClass('layui-hide'); //显示重传
                }
                , progress: function (n, elem, e, index) {
                    layui.element.progress('progress-demo-' + index, n + '%');
                }
            });
        },

        upload: function (list, filePicker_image, image_preview, image, more, upload_allowext, size, type, path) {
            if (upload_allowext) {
                upload_allowext = upload_allowext.replace(/\|/g, ",");
            }
            if (size) {
                size = size * 1024;
            } else {
                size = 10240 * 1024 * 1024;
            }
            type = type || 'img';
            path = path || 'common';
            var $list = $("#" + list + "");
            var GUID = DaskUploader.Base.guid();                            // 一个GUID
            var uploader = DaskUploader.create({
                auto: true,                                                // 选完文件后，是否自动上传。
                server: '/upload/index' + '?upload_type=' + type + '&path=' + path, // 文件接收服务端
                pick: '#' + filePicker_image,                              // 选择文件的按钮。可选。
                resize: false,                                             // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
                chunked: false,                                             // 是否分片
                chunkSize: size,                                // 分片大小
                threads: 1,                                                // 上传并发数
                formData: {
                    // 由于Http的无状态特征，在往服务器发送数据过程传递一个进入当前页面是生成的GUID作为标示
                    GUID: GUID,                                            // 自定义参数
                },
                compress: false,
                fileSingleSizeLimit: size,                                 // 限制大小200M，单文件
                //fileSizeLimit: allMaxSize*1024*1024,                     // 限制大小10M，所有被选文件，超出选择不上
                accept: {
                    title: '上传图片/文件',
                    extensions: upload_allowext,                           // 允许上传的类型 'gif,jpg,jpeg,bmp,png'
                    mimeTypes: '*',                                        // 默认全部文件，为兼容上传文件功能，如只上传图片可写成img/*
                }
            });

            // 文件上传过程中创建进度条实时显示。
            uploader.on('uploadProgress', function (file, percentage) {
                var $li = $list,
                    $percent = $li.find('.progress .progress-bar');
                // 避免重复创建
                if (!$percent.length) {
                    $percent = $('<div class="progress progress-striped active">' +
                        '<div class="progress-bar" role="progressbar" style="width: 0%">' +
                        '</div>' +
                        '</div>').appendTo($li).find('.progress-bar');
                }
                //$li.find('p.state').text('上传中');
                $percent.css('width', percentage * 100 + '%');
            });
            uploader.on('uploadSuccess', function (file, response) {
                if (response.code === 0) {
                    Dask.api.error(response.msg);
                }
                var url = response.url;
                if (more === true) {
                    var images = '<div class="row"><div class="col-6"><input type="text" name="' + image + '[]" value="' + url + '" class="form-control"/></div> <div class="col-3"><input class="form-control input-sm" type="text" name="' + image + '_title[]" value="' + file.name + '" ></div> <div class="col-3"><button type="button" class="btn w-100 btn-warning remove_images">移除</button></div></div>';
                    var images_list = $('#more_images_' + image).html();

                    $('#more_images_' + image).html(images + images_list);

                } else {
                    $("input[name='" + image + "']").val(url);
                    $("#" + image_preview).attr('src', url);
                    $("#" + image_preview).parent("a").attr('href', url);
                }
            });
            uploader.on('uploadComplete', function (file) {
                $list.find('.progress').fadeOut();
            });
            // 错误提示
            uploader.on("error", function (type) {
                if (type === "Q_TYPE_DENIED") {
                    layer.msg('请上传' + upload_allowext + '格式的文件！', { time: 500 });
                } else if (type === "F_EXCEED_SIZE") {
                    layer.msg('单个文件大小不能超过' + size / 1024 + 'kb！', { time: 500 });
                } else if (type === "F_DUPLICATE", { time: 500 }) {
                    layer.msg('请不要重复选择文件', { time: 500 });
                } else {
                    layer.msg('上传出错！请检查后重新上传！错误代码' + type, { time: 500 });
                }
            });
        }
    },

    // 通用方法封装处理
    common: {
        openNewWindow: function (url) {
            var a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('target', '_blank');
            var id = Math.random(10000, 99999);
            a.setAttribute('id', id);
            // 防止反复添加
            if (!document.getElementById(id)) {
                document.body.appendChild(a);
            }
            a.click();
        },
        // 判断字符串是否为空
        isEmpty: function (value) {
            return value == null || this.trim(value) === "";

        },
        // 判断一个字符串是否为非空串
        isNotEmpty: function (value) {
            return !Dask.common.isEmpty(value);
        },
        // 空格截取
        trim: function (value) {
            if (value == null) {
                return "";
            }
            return value.toString().replace(/(^\s*)|(\s*$)|\r|\n/g, "");
        },
        // 比较两个字符串（大小写敏感）
        equals: function (str, that) {
            return str === that;
        },
        // 比较两个字符串（大小写不敏感）
        equalsIgnoreCase: function (str, that) {
            return String(str).toUpperCase() === String(that).toUpperCase();
        },
        // 将字符串按指定字符分割
        split: function (str, sep, maxLen) {
            if (Dask.common.isEmpty(str)) {
                return null;
            }
            var value = String(str).split(sep);
            return maxLen ? value.slice(0, maxLen - 1) : value;
        },
        // 字符串格式化(%s )
        sprintf: function (str) {
            var args = arguments, flag = true, i = 1;
            str = str.replace(/%s/g, function () {
                var arg = args[i++];
                if (typeof arg === 'undefined') {
                    flag = false;
                    return '';
                }
                return arg;
            });
            return flag ? str : '';
        },
        // 数组去重
        uniqueFn: function (array) {
            var result = [];
            var hashObj = {};
            for (var i = 0; i < array.length; i++) {
                if (!hashObj[array[i]]) {
                    hashObj[array[i]] = true;
                    result.push(array[i]);
                }
            }
            return result;
        },
        // 获取form下所有的字段并转换为json对象
        formToJSON: function (formId) {
            var json = {};
            $.each($("#" + formId).serializeArray(), function (i, field) {
                json[field.name] = field.value;
            });
            return json;
        },
        // 跳转页（统一走 Dask.util.navigate：Turbo 优先，回退普通跳转）
        jump: function (url) {
            Dask.util.navigate(url);
        },
        // 序列化表单，不含空元素
        serializeRemoveNull: function (serStr) {
            return serStr.split("&").filter(function (item) {
                var itemArr = item.split('=');
                if (itemArr[1]) {
                    return item;
                }
            }
            ).join("&");
        },
        isMobile: function () {
            let userAgentInfo = navigator.userAgent;
            let mobileAgents = ["Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod"];
            let mobile_flag = false;
            //根据userAgent判断是否是手机
            for (let v = 0; v < mobileAgents.length; v++) {
                if (userAgentInfo.indexOf(mobileAgents[v]) > 0) {
                    mobile_flag = true;
                    break;
                }
            }
            let screen_width = window.screen.width;
            let screen_height = window.screen.height;
            //根据屏幕分辨率判断是否是手机
            if (screen_width < 500 && screen_height < 800) {
                mobile_flag = true;
            }
            return mobile_flag;
        },
        isPhoneNumber: function (phone) {
            return /^1[3-9]\d{9}$/.test(String(phone));
        },
        isEmail: function (email) {
            var flag = false;
            var message = "";
            var myReg = /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
            if (email === '') {
                message = "邮箱不能为空！";
            } else if (!myReg.test(email)) {
                message = "请输入有效的邮箱地址！";
            } else {
                flag = true;
            }
            return flag;
        },
        browser: function () {
            var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
            var isOpera = userAgent.indexOf("Opera") > -1;
            if (isOpera) {
                return "Opera"
            }
            //判断是否Opera浏览器
            if (userAgent.indexOf("Firefox") > -1) {
                return "FF";
            }
            //判断是否Firefox浏览器
            if (userAgent.indexOf("Chrome") > -1) {
                return "Chrome";
            }
            if (userAgent.indexOf("Safari") > -1) {
                return "Safari";
            }
            //判断是否Safari浏览器
            var u = window.navigator.userAgent.toLocaleLowerCase(),
                ie11 = /(trident)\/([\d.]+)/,
                b = u.match(ie11);
            if ((userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera) || b) {
                var IE10 = false;
                var reIE = new RegExp("MSIE (\\d+\\.\\d+);");
                reIE.test(userAgent);
                var fIEVersion = parseFloat(RegExp["$1"]);
                IE10 = fIEVersion == 10.0;
                if (fIEVersion > 10 || fIEVersion == 10 || b) {
                    return "IE10";
                } else {
                    return "IE";
                }
            }
            return "no";
        },
        // 复制文本
        copyText: function (text) {
            let input = document.getElementById('copy-input')
            if (input == null) {
                input = document.createElement('input');
                input.setAttribute('id', 'copy-input')
                input.setAttribute('type', 'text')
                input.setAttribute('style', 'position: absolute;top: 0;left: 0;opacity: 0;z-index: -100;')
                document.body.appendChild(input)
                input = document.getElementById('copy-input')
            }

            input.value = text;
            input.select()
            document.execCommand("copy")
            layer.msg('复制成功', { type: 1 })
        },

        //设置cookie
        setCookie: function (name, value, liveMinutes) {
            if (liveMinutes == undefined || liveMinutes == null) {
                liveMinutes = 60 * 2;
            }
            if (typeof (liveMinutes) != 'number') {
                liveMinutes = 60 * 2;//默认120分钟
            }
            var minutes = liveMinutes * 60 * 1000;
            var exp = new Date();
            exp.setTime(exp.getTime() + minutes + 8 * 3600 * 1000);
            //path=/表示全站有效，而不是当前页
            document.cookie = name + "=" + value + ";path=/;expires=" + exp.toUTCString();
        },

        //获取cookie
        getCookie: function (cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ')
                    c = c.substring(1);
                if (c.indexOf(name) != -1)
                    return c.substring(name.length, c.length);
            }
            return "";
        },

        //删除cookies
        delCookie: function (name) {
            Dask.common.setCookie(name, 1, -1);
        }
    },

    dowmload: function (url, uid) {
        $.ajax({
            url: baseUrl + '/upload/download_file/',
            type: 'post',
            dataType: 'json',
            data: { url: url, uid: uid },
            success: function (res) {
                if (res.code == 1) {
                    location.href = res.data.file;
                } else {
                    layer.msg(res.msg);
                }
            }
        });
    },
};
Dask.util = window.DaskUtil || Dask.util;
Dask.init();
window.Dask = Dask;
