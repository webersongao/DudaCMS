/*二维码生成：仅页面存在分享二维码容器时加载 qrcode 插件*/
function loadQrcodePlugin() {
    if (window.jQuery && jQuery.fn && jQuery.fn.qrcode) return Promise.resolve(true);
    const asset = window.DaskUtil && window.DaskUtil.asset;
    if (!asset) return Promise.resolve(false);
    return asset.script('common/js/jquery.qrcode.min.js', 'qrcode-js', function () {
        return !!(window.jQuery && jQuery.fn && jQuery.fn.qrcode);
    });
}

$(document).ready(function () {
    const qc = $('.dask-qrcode-container')
    if (!qc.length) return;

    loadQrcodePlugin().then(function (ok) {
        if (!ok) return;
        qc.each(function () {
            const that = $(this);
            const url = that.data('share');
            that.find('.dask-qrcode').qrcode({
                render: 'canvas',
                width: 90,
                height: 90,
                text: url,
            });
            const myCanvas = that.find('.dask-qrcode').find('canvas')[0];
            if (!myCanvas) return;
            const img = new Image();
            img.src = myCanvas.toDataURL('image/png');
            that.find('.dask-qrcode').append(img);
            that.find('.dask-qrcode canvas').hide();
        });
    });
})

$.fn.extend({
    insertAtCaret: function (textFieldValue) {
        var textObj = $(this).get(0);
        if (document.all && textObj.createTextRange && textObj.caretPos) {
            var caretPos = textObj.caretPos;
            caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) === '' ?
                textFieldValue + '' : textFieldValue;
        }
        else if (textObj.setSelectionRange) {
            var rangeStart = textObj.selectionStart,
                rangeEnd = textObj.selectionEnd,
                tempStr1 = textObj.value.substring(0, rangeStart),
                tempStr2 = textObj.value.substring(rangeEnd);
            textObj.value = tempStr1 + textFieldValue + tempStr2;
            textObj.focus();
            var len = textFieldValue.length;
            textObj.setSelectionRange(rangeStart + len, rangeStart + len);
            textObj.blur();
        }
        else {
            textObj.value += textFieldValue;
        }
    },

    highText: function (searchWords, htmlTag, tagClass) {
        return this.each(function () {
            $(this).html(function high(replaced, search, htmlTag, tagClass) {
                var pattarn = search.replace(/\b(\w+)\b/g, "($1)").replace(/\s+/g, "|");
                return replaced.replace(new RegExp(pattarn, "ig"), function (keyword) {
                    return $("<" + htmlTag + " class=" + tagClass + ">" + keyword + "</" + htmlTag + ">").outerHTML();
                });
                //$(this).text()换成$(this).html() 防止js标签转为字符
            }($(this).html(), searchWords, htmlTag, tagClass));
        });
    },

    outerHTML: function (s) {
        return (s) ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
    }
});

$(document).on('click', '.dask-pjax-a a', function () {
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
});

$(document).on('click', '.dask-pjax-buttons a', function () {
    $(this).addClass('btn-primary text-white').removeClass('btn-outline-primary text-primary');
    $(this).siblings().removeClass('btn-primary text-white').addClass('btn-outline-primary text-primary');
});

//pjax标签导航点击
$(document).on('click', '.dask-pjax-tabs li', function () {
    $(this).parent('.dask-pjax-tabs').find('li a').removeClass('active');
    $(this).find('a').addClass('active');
});

//发送短信
$(document).on('click', '[data-sms]', function (t) {
    let that = this;
    let mobile = $($(that).data('sms')).val();
    if (!mobile) {
        AWS_MOBILE.api.error('请输入手机号');
        return;
    }

    if ($.cookie("sms")) {
        let count = $.cookie("sms");
        $(that).prop('disabled', true);
        $(that).text(count + '秒');
        let resend = setInterval(function () {
            count--;
            if (count > 0) {
                $(that).text(count + '秒');
                $.cookie("captcha", count, { path: '/', expires: (1 / 86400) * count });
                $(that).addClass('sending');
            } else {
                $(that).prop('disabled', false);
                clearInterval(resend);
                $(that).text("获取验证码")
                $(that).removeClass('sending');
            }
        }, 1000);
    }

    if ($(that).hasClass('sending')) {
        return false;
    }

    $.ajax({
        url: baseUrl + '/ajax/sms/',
        type: "post",
        dataType: "json",
        data: {
            mobile: mobile,
        },
        success: function (result) {
            if (!result.code) {
                layer.msg(result.msg);
                return false;
            }
            var count = 60;
            var inl = setInterval(function () {
                $(that).prop('disabled', true);
                count -= 1;
                var text = count + ' 秒后重试';
                $.cookie("sms", count, { path: '/', expires: (1 / 86400) * count });
                $(that).addClass('sending');
                $(that).text(text);
                if (count <= 0) {
                    clearInterval(inl);
                    $(that).prop('disabled', false);
                    $(that).text('获取短信验证码');
                    $(that).removeClass('sending');
                }
            }, 1000);
            return layer.msg(result.msg);
        }
    });
});

//自定义拓展
(function ($) {
    $.extend(
        {
            // 滚动到指定位置
            scrollTo: function (type, duration, options) {
                if (typeof type == 'object') { $(type).offset().top }
                $('html, body').animate({ scrollTop: type }, {
                    duration: duration,
                    queue: options.queue
                });
            }
        })
})(jQuery);

function cutImg(obj) {
    let image = new Image();
    image.src = obj.src;
    let $this = $(obj);
    let iWidth = $this.width();
    let iHeight = $this.height();
    if (image.width * iHeight !== 1 * iWidth * image.height) {
        //原始图片的尺寸与CSS里固定的图片尺寸比例不一致，则进行处理
        if (image.width / image.height >= iWidth / iHeight) {
            $this.height(iHeight + 'px');
            $this.width((image.width * iHeight) / image.height + 'px');
        } else {
            $this.width(iWidth + 'px');
            $this.height((image.height * iWidth) / image.width + 'px');
        }
        //用cite装起来，做出裁剪效果
        if (!$this.parent().is('cite')) {
            $this.wrap('<cite />');
        }
    }
}

$(document).ready(function () {

    // BS5：carousel/popover 由 data-bs-* 属性自动初始化；站点当前无实际使用，移除旧的 jQuery 插件式调用。

    //图片裁切
    let aci = $('.dask-cut-img')
    if (aci.length) {
        aci.each(function () {
            let image = new Image();
            image.src = this.src;
            if (image.complete) {
                cutImg(this);
            } else {
                this.onload = function () {
                    cutImg(this);
                };
            }
        });
    }

    //灯箱效果
    let acimg = $(".dask-content img")
    if (acimg.length) {
        acimg.each(function (i) {
            if (!this.parentNode.href) {
                $(this).wrap("<a href='" + this.src + "' data-fancybox='fancybox' data-fancybox-group='dask-thumb' data-caption='" + this.alt + "'></a>")
            }
        })
    }

    /*用户头像提示box*/
    Dask.User.showCard('.dask-username, .dask-user-img, .dask-user-name', 'user');

    //话题小卡片
    Dask.User.showCard('.dask-topic , .dask-topic-img', 'topic');

    //小卡片mouseover
    $(document).on('mouseover', '#dask-ui-card-tips', function () {
        clearTimeout(Dask.config.card_box_hide_timer);
        $(this).show();
    });

    //私信：同一套下拉在桌面/窄屏各渲染一次，用 data 属性统一委派，避免重复 ID。
    $(document).on('click', '[data-header-inbox]', function () {
        var target = $(window).width() > 990 ? $('#topInboxBox') : $('#topInboxBox_Toggler');
        if (!target.length) return;
        $.ajax({
            url: baseUrl + '/inbox/index?header=1',
            type: "GET",
            dataType: "html",
            success: function (ret) { target.html(ret); }
        });
    });

    //通知：与私信同理，统一走 data-header-notify。
    $(document).on('click', '[data-header-notify]', function () {
        var target = $(window).width() > 990 ? $('#topNotifyBox') : $('#topNotifyBox_Toggler');
        if (!target.length) return;
        $.ajax({
            url: baseUrl + '/notify/index?header=1',
            type: "GET",
            dataType: "html",
            success: function (ret) { target.html(ret); }
        });
    });

    //小卡片mouseout
    $(document).on('mouseout', '#dask-ui-card-tips', function () {
        $(this).hide();
    });

    //用户小卡片关注更新缓存
    $(document).on('click', '.dask-ui-card-tips-user .follow', function () {
        let uid = $(this).parents('.dask-ui-card-tips').find('.name').attr('data-id');
        $.each(Dask.config.cacheUserData, function (i, a) {
            if (a.match('data-id="' + uid + '"')) {
                if (Dask.config.cacheUserData.length === 1) {
                    Dask.config.cacheUserData = [];
                }
                else {
                    Dask.config.cacheUserData[i] = '';
                }
            }
        });
    });

    //搜索下拉
    Dask.Dropdown.bind_dropdown_list('#globalSearchInput', 'search');

    //返回顶部
    if ($('.dask-back-top').length) {
        $(window).scroll(function () {
            if ($(window).scrollTop() > ($(window).height() / 2)) {
                $('.dask-back-top').fadeIn();
            }
            else {
                $('.dask-back-top').fadeOut();
            }
        });
    }

    // 图片灯箱由 dask-lightbox.js（PhotoSwipe 5）基于 data-fancybox 标记统一委托处理。

    // 复制链接：使用原生 Clipboard API，支持动态渲染的节点
    $(document).on('click', '.dask-clipboard', async function () {
        let text = $(this).data('clipboard-text') || window.location.href
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text)
            } else {
                let temp = $('<textarea>').val(text).css({position: 'fixed', opacity: 0}).appendTo('body')
                temp[0].select()
                let ok = document.execCommand('copy')
                temp.remove()
                if (!ok) throw new Error('copy failed')
            }
            layer.msg('复制成功')
        } catch (e) {
            layer.msg('复制失败')
        }
    })
});

// 附件下载
$(document).on('click', '.attach-download', function () {
    $('#attach-name').val($(this).data('name'))
    $('#attach-download-form').submit();
})

//附件删除

// 附件下载
$(document).on('click', '.dask-attach-delete', function () {
    var that = $(this);
    var attachId = $(this).data('id');
    var attachKey = $(this).data('key');
    Dask.api.post(baseUrl + '/upload/remove_attach', { id: attachId, access_key: attachKey }, function (res) {
        if (res.code > 0) {
            that.parents('tr').detach();
        } else {
            layer.msg('附件删除失败');
        }
    })
})
