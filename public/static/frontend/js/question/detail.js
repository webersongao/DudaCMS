$(window).scroll(function () {
    let questionHeight = $('.dask-question-container').height();
    if (questionHeight > $(window).height() && $(window).scrollTop() + ($(window).height() / 2) < questionHeight) {
        $('.dask-question-container .actions').addClass('fixed-bottom').addClass('bg-white').addClass('container').addClass('p-3');
    } else {
        $('.dask-question-container .actions').removeClass('fixed-bottom').removeClass('bg-white').removeClass('container').removeClass('p-3');
    }
});

/**
 * 回答编辑器
 */
$(document).on('click', '.daskAnswerEditor', function (e) {
    if (!userId) {
        return Dask.User.login();
    }

    if ($(this).data('enable') == 1) {
        return layer.msg('您所在用户组没有回答问题的权限');
    }
    var questionId = $(this).data('question-id');
    var answerId = $(this).data('answer-id');
    var answerEditorContainer = $('#answerEditor');
    answerEditorContainer.toggle();
    $.ajax({
        url: baseUrl + '/ajax/editor?_ajax=1',
        dataType: '',
        type: 'post',
        data: {
            question_id: questionId,
            answer_id: answerId,
        },
        success: function (result) {
            answerEditorContainer.html(result);
            if (window.DaskUtil && window.DaskUtil.isVisible(answerEditorContainer)) {
                let offset = answerEditorContainer.offset();
                $('body,html').animate({
                    scrollTop: offset.top
                })
            }
        },
    });
});

/*保存问题评论：全局单次委托。
  历史实现把该处理器写在 questionCommentBtn 的点击回调里，每展开一次评论框
  就多注册一个 document 委托，第 N 次点发布会发 N 次请求且闭包持有旧的 answerId。
  现在只在此处注册一次，answerId / content 从当前按钮所属表单实时推导。*/
$(document).on('click', '.saveQuestionComment', function (e) {
    var $form = $(this).closest('form');
    var answerId = $form.find('input[name="question_id"]').val();
    var boxElement = $('#questionCommentBox-' + answerId);
    var content = boxElement.find('div.questionCommentList');
    $.ajax({
        url: baseUrl + '/comment/save_question_comment',
        dataType: 'json',
        type: 'post',
        data: $form.serialize(),
        success: function (result) {
            if (result.code > 0) {
                layer.msg(result.msg);
                let c = $('.question-comment-count')
                c.text(1 + parseInt(c.eq(0).text()))
                content.prepend(result.data.html);
                $form.find('input.comment-input').val('');
            } else {
                layer.msg(result.msg);
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

/*保存回答评论：全局单次委托，原因同上。*/
$(document).on('click', '.saveAnswerComment', function (e) {
    var $form = $(this).closest('form');
    var answerId = $form.find('input[name="answer_id"]').val();
    var boxElement = $('#answerCommentBox-' + answerId);
    var content = boxElement.find('div.answerCommentList');
    $.ajax({
        url: baseUrl + '/comment/save_answer_comment',
        dataType: 'json',
        type: 'post',
        data: $form.serialize(),
        success: function (result) {
            if (result.code > 0) {
                layer.msg(result.msg);
                let c = $('.answer-comment-count' + answerId)
                c.text(1 + parseInt(c.eq(0).text()))
                content.prepend(result.data.html);
                $form.find('input.comment-input').val('');
            } else {
                layer.msg(result.msg);
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

/*问题评论*/
$(document).on('click', '.questionCommentBtn', function (e) {
    var answerId = $(this).data('id');
    var boxElement = $('#questionCommentBox-' + answerId);
    var content = boxElement.find('div.questionCommentList');
    var pageElement = boxElement.find('div.pageElement');
    var ajaxUrl = baseUrl + '/comment/question?id=' + answerId;
    var formBox = boxElement.find('div.commentForm');
    boxElement.toggle();
    if (window.DaskUtil && window.DaskUtil.isHidden(boxElement)) {
        return true;
    }

    if (userId) {
        var formString = '' +
            '<form class="p-3" method="post" id="questionForm-' + answerId + '" style="background-color: #fafafa;">' +
            '   <input type="hidden" name="question_id" value="' + answerId + '">' +
            '   <div class="clearfix">' +
            '     <input type="text" name="message" class="form-control comment-input float-start" placeholder="写下您的评论..." style="width: calc(100% - 85px)">' +
            '     <button type="button" class="btn btn-primary px-3 saveQuestionComment float-end" >发布</button>' +
            '   </div>' +
            '</form>';
    } else {
        formString = '<p class="text-center text-muted pb-3">' +
            '    <span class="d-block">您还未登录,点击 <a href="javascript:;" onclick="Dask.User.login()" style="color: #3182f7;">登录</a> 发表你的评论吧!</span>' +
            '</p>';
    }
    formBox.html(formString);
    ajaxPage(content, pageElement, ajaxUrl);
});

/*回答评论*/
$(document).on('click', '.answerCommentBtn', function (e) {
    var answerId = $(this).data('id');
    var boxElement = $('#answerCommentBox-' + answerId);
    var content = boxElement.find('div.answerCommentList');
    var pageElement = boxElement.find('div.pageElement');
    var ajaxUrl = $(this).data('url');
    var formBox = boxElement.find('div.commentForm');
    boxElement.toggle();

    if (window.DaskUtil && window.DaskUtil.isHidden(boxElement)) {
        return true;
    }
    if (userId) {
        var formString = '' +
            '<form class="p-3"  method="post" id="commentForm-' + answerId + '" style="background-color: #fafafa;">' +
            '   <input type="hidden" name="answer_id" value="' + answerId + '">' +
            '   <div class="clearfix">' +
            '     <input type="text" name="message" class="form-control comment-input float-start comment-form-input" placeholder="写下您的评论..." style="width: calc(100% - 85px)">' +
            '     <button type="button" class="btn btn-primary px-3 saveAnswerComment float-end" >发布</button>' +
            '   </div>' +
            '</form>';
    } else {
        formString = '<p class="text-center text-muted pb-3">' +
            '    <span class="d-block">您还未登录,点击 <a href="javascript:;" onclick="Dask.User.login()" style="color: #3182f7;">登录</a> 发表你的评论吧!</span>' +
            '</p>';
    }
    formBox.html(formString);
    //编辑器@人
    Dask.User.at_user_lists('#commentForm-' + answerId + ' .comment-form-input', 5);
    ajaxPage(content, pageElement, ajaxUrl);
});

/*ajax分页*/
function ajaxPage(content, pageElement, ajaxUrl) {
    var curPage = 1; //当前页码
    var total, pageSize, totalPage, string; //总记录数，每页显示数，总页数,内容
    getData(1);
    // 用命名空间先解绑再绑定，避免每次展开评论框都叠加一个 document/pageElement 委托
    $(pageElement).off('click.daskAjaxPage').on('click.daskAjaxPage', 'span a', function () {
        var rel = $(this).attr("rel");
        if (rel) {
            getData(rel);
        }
    });

    function getData(page) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                'page': page
            },
            success: function (json) {
                total = json.total; //总记录数
                pageSize = json.per_page; //每页显示条数
                curPage = page; //当前页
                totalPage = json.last_page; //总页数
                string = json.html;
                $(content).find('*').remove();
                if (totalPage > 1 && string) {
                    getPageBar();
                }

                if (!userId) {
                    string = string ? string : '<p class="text-center text-muted">' +
                        '    <span class="d-block">暂无评论,点击 <a href="javascript:;" onclick="Dask.User.login()" style="color: #3182f7;">登录</a> 发表你的评论吧!</span>' +
                        '</p>';
                } else {
                    string = string ? string : '<p class="text-center text-muted">' +
                        '    <span class="d-block">暂无评论,快来发表你的评论吧!</span>' +
                        '</p>';
                }
                $(content).append(string);
            },
            error: function () {
                layer.msg("数据加载失败");
            }
        });
    }

    //获取分页条
    function getPageBar() {
        $(pageElement).find('*').remove();
        //页码大于最大页数
        if (curPage > totalPage) curPage = totalPage;
        //页码小于1
        if (curPage < 1) curPage = 1;
        pageStr = "<span>共" + total + "条</span><span>" + curPage + "/" + totalPage + "</span>";
        //如果是第一页
        if (curPage == 1) {
            pageStr += "<span>首页</span><span>上一页</span>";
        } else {
            pageStr += "<span><a href='javascript:void(0)' rel='1'>首页</a></span><span><a href='javascript:void(0)' rel='" + (curPage - 1) + "'>上一页</a></span>";
        }

        //如果是最后页
        if (curPage >= totalPage) {
            pageStr += "<span>下一页</span><span>尾页</span>";
        } else {
            pageStr += "<span><a href='javascript:void(0)' rel='" + (parseInt(curPage) + 1) + "'>下一页</a></span><span><a href='javascript:void(0)' rel='" + totalPage + "'>尾页</a></span>";
        }

        $(pageElement).addClass('px-3 pb-3').append(pageStr);
    }
}
