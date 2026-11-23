{if $update}
<div class="user-details-card pt-0 pb-2 position-relative clearfix">
    <div class="user-details-card-avatar float-start" style="position: relative">
        {if $info.is_anonymous}
        <a href="javascript:;">
            <img src="static/common/image/default-avatar.svg" alt="{:L('匿名用户')}" title="{:L('匿名用户')}" style="width: 40px;height: 40px">
        </a>
        {else/}
        <a href="{$info['user_info']['url']}" class="dask-username" data-id="{$info.uid}" title="{$info['user_info']['name']}">
            <img src="{$info['user_info']['avatar']}" alt="{$info['user_info']['name']}" style="width: 40px;height: 40px">
        </a>
        {/if}
    </div>
    <div class="user-details-card-name float-start ms-2">
        {if $info.is_anonymous}<a href="javascript:;" title="{:L('匿名用户')}">{:L('匿名用户')}</a>{else/}<a href="{$info['user_info']['url']}" data-id="{$info.uid}" class="dask-username" title="{$info['user_info']['name']}">{$info['user_info']['name']}</a>  <span class="badge text-bg-success">{$info['user_info']['group_name']}</span>{/if} {if $setting.show_answer_user_ip=='Y' && $info.answer_user_local}<span class="ms-2 text-muted font-8">{:L('用户来自于')}: {$info.answer_user_local}</span>{/if}<br><span class="ms-0"> {:date_friendly($info['create_time'])} </span>
    </div>

    {if $info['is_best']}
    <div class="dask-answer-best">
        <i class="iconfont" title="{:L('最佳回答')}">&#xe6f7;</i>
    </div>
    {/if}
</div>
<div class="dask-content">
    <div class="dask-answer-content overflow-hidden">
        {:html_entity_decode($info.content)}
    </div>
    {if $info.content}
    <div class="dask-answer-show dask-alpha-hidden" style="display: none">
        <span style="cursor: pointer;"><i class="icon-chevrons-down"></i> {:L('阅读全文')}</span>
    </div>
    <div class="dask-answer-hide dask-alpha-hidden mt-3" style="display: none;background:none;position: inherit;height: auto">
        <span style="position: unset;float: center;cursor: pointer"><i class="icon-chevrons-up"></i> {:L('收起全文')}</span>
    </div>
    {/if}
</div>
<div class="answer-btn-actions mt-3">
    <label class="me-1">
        <a href="javascript:;" class="dask-ajax-agree {if $info['vote_value']==1}active{/if}"  data-action="user-agree" data-type="answer" data-id="{$info.id}">
            <i class="icon-thumb_up"></i> {:L('赞同')} <span> {$info.agree_count}</span>
        </a>
    </label>

    <label class="me-3 ">
        <a href="javascript:;" class="dask-ajax-against {if $info['vote_value']==-1}active{/if}"  data-action="user-against" data-type="answer" data-id="{{$info.id}}">
            <i class="icon-thumb_down"></i>
        </a>
    </label>

    <label class="me-3">
        <a href="javascript:;" data-title="评论回答" class="answerCommentBtn" data-id="{$info.id}">
            <i class="icon-chat"></i> <span class="answer-comment-count{$info.id}">{$info.comment_count}</span>{:L('评论')}
        </a>
    </label>

    {if $user_id}
    <label class="me-3">
        <a href="javascript:;" data-action="user-report" data-type="answer" data-id="{$info.id}" ><i class="icon-warning"></i> {:L('举报')}</a>
    </label>

    <label class="me-3">
        <a href="javascript:;" data-action="user-favorite" data-type="answer" data-id="{$info.id}"><i class="icon-turned_in"></i> {:L('收藏')}</a>
    </label>

    <label class="me-3">
        <a href="javascript:;"  {if $info.has_thanks} class="active" {else/}data-action="user-thanks" data-id="{$info.id}"{/if}>
        <i class="icon-favorite"></i> <span>{:L($info.has_thanks ? '已喜欢' : '喜欢')}</span>
        </a>
    </label>

    {if !$info.has_uninterested}
    <label class="me-3">
        <a href="javascript:;" data-action="user-uninterested" data-type="answer" data-id="{$info.id}">
            <i class="icon-report"></i> {:L('不感兴趣')}
        </a>
    </label>
    {/if}
    {/if}

    {if ($user_id && get_user_permission('set_best_answer')=='Y' && !$info['is_best'] && !$best_answer_count)}
    <label class="me-3">
        <a href="javascript:;"  class="dask-ajax-get" data-confirm="{:L('是否把该回答设为最佳')}?" data-url="{:url('question/set_answer_best?answer_id='.$info['id'])}">
            <i class="fa fa-award "></i> {:L('最佳')}
        </a>
    </label>
    {/if}

    <div class="dropdown d-inline-block me-3">
        <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-fw fa-share-alt font-9 d-none d-sm-inline-block"></i>{:L('分享')}
        </a>
        <div class="dropdown-menu p-0 border-0 font-size-sm">

            <div class="text-center d-block py-2" style="min-width: 100px">
<a href="javascript:;"  class="dropdown-item dask-clipboard" data-clipboard-text="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}"><i class="icon-link"></i> {:L('复制链接')}</a>
                <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}" data-type="qzone" class="dropdown-item "><i class="fab fa-qq text-primary"></i> {:L('QQ空间')}</a>
                <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}" data-type="weibo" class="dropdown-item "><i class="fab fa-weibo text-warning"></i> {:L('新浪微博')}</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}" data-type="twitter" class="dropdown-item"><i class="fab fa-twitter" style="color:#1DA1F2"></i> Twitter/X</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}" data-type="telegram" class="dropdown-item"><i class="fab fa-telegram" style="color:#229ED9"></i> Telegram</a>
                
                <div class="dask-qrcode-container" data-share="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}">
                    <a href="javascript:;" class="dropdown-item "><i class="fab fa-weixin text-success"></i> {:L('微信扫一扫')}</a>
                    <div class="dask-qrcode text-center py-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!--问题回答操作栏钩子-->
    

    {if $user_id && (isNormalAdmin() || isSuperAdmin() || $user_id==$info['uid'])}
    <div class="dropdown d-inline-block">
        <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-ellipsis-h d-none d-sm-inline-block"></i>
        </a>
        <div class="dropdown-menu p-0 border-0 font-size-sm">
            <div class="text-center d-block py-2" style="min-width: 100px">
                <a href="javascript:;"  class="dropdown-item dask-answer-editor" data-question-id="{$info.question_id}" data-answer-id="{$info['id']}">{:L('编辑')}</a>
                <a href="javascript:;" title="{:L('删除回答')}" class="dropdown-item dask-ajax-get" data-confirm="{:L('是否删除该回答')}?" data-url="{:url('question/delete_answer?answer_id='.$info['id'])}">{:L('删除')}</a>
            </div>
        </div>
    </div>
    {/if}
</div>
{else/}
<div class="dask-answer-item p-3 mb-1 bg-white" id="question-answer-{$info.id}" data-answer-id="{$info.id}">
    <div class="user-details-card pt-0 pb-2 position-relative clearfix">
        <div class="user-details-card-avatar float-start" style="position: relative">
            {if $info.is_anonymous}
            <a href="javascript:;">
                <img src="static/common/image/default-avatar.svg" alt="{:L('匿名用户')}" title="{:L('匿名用户')}" style="width: 40px;height: 40px">
            </a>
            {else/}
            <a href="{$info['user_info']['url']}" class="dask-username" data-id="{$info.uid}" title="{$info['user_info']['name']}">
                <img src="{$info['user_info']['avatar']}" alt="{$info['user_info']['name']}" style="width: 40px;height: 40px">
            </a>
            {/if}
        </div>
        <div class="user-details-card-name float-start ms-2">
            {if $info.is_anonymous}<a href="javascript:;" title="{:L('匿名用户')}">{:L('匿名用户')}</a>{else/}<a href="{$info['user_info']['url']}" data-id="{$info.uid}" class="dask-username" title="{$info['user_info']['name']}">{$info['user_info']['name']}</a>{/if}<br><span class="ms-0"> {:date_friendly($info['create_time'])} </span>
        </div>
        {if $info['is_best']}
        <div class="dask-answer-best">
            <i class="iconfont" title="{:L('最佳回答')}">&#xe6f7;</i>
        </div>
        {/if}
    </div>
    <div class="dask-content">
        <div class="dask-answer-content overflow-hidden">
            {:html_entity_decode($info.content)}
        </div>
        {if $info.content}
        <div class="dask-answer-show dask-alpha-hidden" style="display: none">
            <span style="cursor: pointer;"><i class="icon-chevrons-down"></i> {:L('阅读全文')}</span>
        </div>
        <div class="dask-answer-hide dask-alpha-hidden mt-3" style="display: none;background:none;position: inherit;height: auto">
            <span style="position: unset;float: center;cursor: pointer"><i class="icon-chevrons-up"></i> {:L('收起全文')}</span>
        </div>
        {/if}
    </div>
    <div class="answer-btn-actions mt-3">
        <label class="me-1">
            <a href="javascript:;" class="dask-ajax-agree {if $info['vote_value']==1}active{/if}"  data-action="user-agree" data-type="answer" data-id="{$info.id}">
                <i class="icon-thumb_up"></i> {:L('赞同')} <span> {$info.agree_count}</span>
            </a>
        </label>

        <label class="me-3 ">
            <a href="javascript:;" class="dask-ajax-against {if $info['vote_value']==-1}active{/if}"  data-action="user-against" data-type="answer" data-id="{{$info.id}}">
                <i class="icon-thumb_down"></i>
            </a>
        </label>

        <label class="me-3">
            <a href="javascript:;" data-title="评论回答" class="answerCommentBtn" data-id="{$info.id}">
                <i class="icon-chat"></i> <span class="answer-comment-count{$info.id}">{$info.comment_count}</span>{:L('评论')}
            </a>
        </label>

        {if $user_id}
        <label class="me-3">
            <a href="javascript:;" data-action="user-report" data-type="answer" data-id="{$info.id}" ><i class="icon-warning"></i> {:L('举报')}</a>
        </label>

        <label class="me-3">
            <a href="javascript:;" data-action="user-favorite" data-type="answer" data-id="{$info.id}"><i class="icon-turned_in"></i> {:L('收藏')}</a>
        </label>

        <label class="me-3">
            <a href="javascript:;"  {if $info.has_thanks} class="active" {else/}data-action="user-thanks" data-id="{$info.id}"{/if}>
            <i class="icon-favorite"></i> <span>{:L($info.has_thanks ? '已喜欢' : '喜欢')}</span>
            </a>
        </label>

        {if !$info.has_uninterested}
        <label class="me-3">
            <a href="javascript:;" data-action="user-uninterested" data-type="answer" data-id="{$info.id}">
                <i class="icon-report"></i> {:L('不感兴趣')}
            </a>
        </label>
        {/if}
        {/if}

        {if ($user_id && ($info['uid']==$user_id || $user_info['group_id']==1 || $user_info['group_id']==2)  && !$info['is_best'] && !$best_answer_count)}
        <label class="me-3">
            <a href="javascript:;"  class="dask-ajax-get" data-confirm="{:L('是否把该回答设为最佳')}?" data-url="{:url('question/set_answer_best?answer_id='.$info['id'])}">
                <i class="fa fa-award "></i> {:L('最佳')}
            </a>
        </label>
        {/if}

        <div class="dropdown d-inline-block me-3">
            <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-fw fa-share-alt font-9 d-none d-sm-inline-block"></i>{:L('分享')}
            </a>
            <div class="dropdown-menu p-0 border-0 font-size-sm">

                <div class="text-start d-block py-2" style="min-width: 100px">
<a href="javascript:;"  class="dropdown-item dask-clipboard" data-clipboard-text="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}"><i class="icon-link"></i> {:L('复制链接')}</a>
                    <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}" data-type="weibo" class="dropdown-item "><i class="fab fa-weibo text-warning"></i> {:L('新浪微博')}</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}" data-type="twitter" class="dropdown-item"><i class="fab fa-twitter" style="color:#1DA1F2"></i> Twitter/X</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}" data-type="telegram" class="dropdown-item"><i class="fab fa-telegram" style="color:#229ED9"></i> Telegram</a>
                    <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}" data-type="qzone" class="dropdown-item "><i class="fab fa-qq text-primary"></i> {:L('QQ空间')}</a>
                    
                    <div class="dask-qrcode-container" data-share="{:url('question/detail',['answer'=>$info.id,'id'=>$question_info.id],true,true)}">
                        <a href="javascript:;" class="dropdown-item "><i class="fab fa-weixin text-success"></i> {:L('微信扫一扫')}</a>
                        <div class="dask-qrcode text-center py-2"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--问题回答操作栏钩子-->
        
        <div class="dask-share clearfix d-inline-block">
            <div class="social-share" data-disabled="google,twitter,facebook,linkedin,douban"></div>
        </div>
        {if $user_id && (isNormalAdmin() || isSuperAdmin() || $user_id==$info['uid'])}
        <div class="dropdown d-inline-block">
            <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-ellipsis-h d-none d-sm-inline-block"></i>
            </a>
            <div class="dropdown-menu p-0 border-0 font-size-sm">
                <div class="text-center d-block py-2" style="min-width: 100px">
                    <a href="javascript:;"  class="dropdown-item dask-answer-editor" data-question-id="{$info.question_id}" data-answer-id="{$info['id']}">{:L('编辑')}</a>
                    <a href="javascript:;" title="{:L('删除回答')}" class="dropdown-item dask-ajax-get" data-confirm="{:L('是否删除该回答')}?" data-url="{:url('question/delete_answer?answer_id='.$info['id'])}">{:L('删除')}</a>
                </div>
            </div>
        </div>
        {/if}
        <!--评论框动态显示-->
        <div class="answerCommentBox mt-2 border" id="answerCommentBox-{$info.id}" style="display: none;margin-left: 45px">
            <div class="answerCommentHeader clearfix px-3 pt-3">
                <h6 class="font-10 float-start mb-1"><span class="answer-comment-count{$info.id}">{$info.comment_count}</span> {:L('评论')}</h6>
            </div>
            <div class="answerCommentList px-3"></div>
            <div class="pageElement"></div>
            <div class="commentForm clearfix rounded dask-replay-box"></div>
        </div>
    </div>
</div>
{/if}