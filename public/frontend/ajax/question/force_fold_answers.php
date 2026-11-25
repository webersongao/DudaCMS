{if $list}
{foreach $list as $k=>$v}
<div class="dask-answer-item p-3 mb-1 bg-white" data-uninterested_count="{$v.uninterested_count}" id="question-answer-{$v.id}" data-answer-id="{$v.id}">
    <div class="user-details-card pt-0 pb-2 position-relative clearfix">
        <div class="user-details-card-avatar float-start" style="position: relative">
            {if $v.is_anonymous}
            <a href="javascript:;">
                <img src="{:user_avatar_url('')}" class="rounded" alt="{:L('匿名用户')}" title="{:L('匿名用户')}" style="width: 40px;height: 40px">
            </a>
            {else/}
            <a href="{$v['user_info']['url']}" class="dask-username dask-avatar-wrap" data-id="{$v.uid}" title="{$v['user_info']['name']}">
                <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" class="rounded" alt="{$v['user_info']['name']}" style="width: 40px;height: 40px">
                {if $v['user_info']['verified']}
                <span class="dask-verified-badge" title="{$v['user_info']['verified_name']|default='已认证'}" aria-label="{$v['user_info']['verified_name']|default='已认证'}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg></span>
                {/if}
            </a>
            {/if}
        </div>
        <div class="user-details-card-name float-start ms-2">
            {if $v.is_anonymous}<a href="javascript:;" title="{:L('匿名用户')}">{:L('匿名用户')}</a>{else/}<a href="{$v['user_info']['url']}" data-id="{$v.uid}" class="dask-username" title="{$v['user_info']['name']}">{$v['user_info']['name']}</a> <span class="badge text-bg-success">{$v['user_info']['group_name']|default=''}</span> {/if} {if $setting.show_answer_user_ip=='Y' && $v.answer_user_local}<span class="ms-2 text-muted font-8">{:L('用户来自于')}: {$v.answer_user_local}</span>{/if} <br><span class="ms-0"> {:date_friendly($v['create_time'])} </span>
        </div>

        {if $v['is_best']}
        <div class="dask-answer-best">
            <i class="fa fa-award " title="{:L('最佳回答')}"></i>
        </div>
        {/if}
    </div>

    <div class="dask-content">
        <div class="dask-answer-content overflow-hidden">
            {:html_entity_decode($v.content)}
        </div>
        {if $v.content}
        <div class="dask-answer-show dask-alpha-hidden" style="display: none">
            <span style="cursor: pointer;"><i class="icon-chevrons-down"></i> {:L('阅读全文')}</span>
        </div>
        <div class="dask-answer-hide dask-alpha-hidden mt-3" style="display: none;background:none;position: inherit;height: auto">
            <span style="position: unset;float: center;cursor: pointer"><i class="icon-chevrons-up"></i> {:L('收起全文')}</span>
        </div>
        {/if}
    </div>

    <!--回答操作-->
    <div class="answer-btn-actions mt-3">
        <label class="me-1">
            <a href="javascript:;" class="dask-ajax-agree {if $v['vote_value']==1}active{/if}"  data-action="user-agree" data-type="answer" data-id="{$v.id}">
                <i class="icon-thumb_up"></i> {:L('赞同')} {if ($v.agree_count > 0)}<span> {$v.agree_count}</span>{/if}
            </a>
        </label>

        <label class="me-3 ">
            <a href="javascript:;" class="dask-ajax-against {if $v['vote_value']==-1}active{/if}"  data-action="user-against" data-type="answer" data-id="{$v.id}">
                <i class="icon-thumb_down"></i>
            </a>
        </label>

        <label class="me-3">
            <a href="javascript:;" class="answerCommentBtn" data-url="{:url('comment/answer',['id'=>$v.id])}" data-id="{$v.id}">
                <i class="icon-chat"></i> {:L('评论')}{if ($v.comment_count > 0)}<span class="answer-comment-count{$v.id}">{$v.comment_count}</span>{/if}
            </a>
        </label>

        {if $user_id}
        <label class="me-3">
            <a href="javascript:;" {if !$v.checkReport} data-action="user-report" data-type="answer" data-id="{$v.id}"{/if} ><i class="icon-warning"></i>{$v.checkReport ? L('已举报') : L('举报')}</a>
        </label>

        <label class="me-3">
            <a href="javascript:;" data-action="user-favorite" data-type="answer" data-id="{$v.id}"><i class="icon-turned_in"></i>{if $v.checkFavorite} {:L('已收藏')}{else}{:L('收藏')}{/if}</a>
        </label>

        {if $user_id!=$v['uid']}
        <label class="me-3">
            <a href="javascript:;"  {if $v.has_thanks} class="active" {else/}data-action="user-thanks" data-id="{$v.id}"{/if}>
            <i class="icon-favorite"></i> <span>{$v.has_thanks ? L('已感谢') : L('感谢')}</span>
            </a>
        </label>
        {/if}

        {if !$v.has_uninterested}
        <label class="me-3">
            <a href="javascript:;" data-action="user-uninterested" data-type="answer" data-id="{$v.id}">
                <i class="icon-report"></i> {:L('不感兴趣')}
            </a>
        </label>
        {/if}

        {if isSuperAdmin() || isNormalAdmin() || (get_setting('uninterested_fold')<=$v.uninterested_count && $user_id==$question_info['uid'])}
        <label class="me-3">
            <a href="javascript:;" data-url="{:url('answer/force')}" data-action="user-force-fold-answer" data-id="{$v.id}">
                <i class="fa fa-cut "></i> <span>{:L($v.force_fold?'取消折叠':'折叠')}</span>
            </a>
        </label>
        {/if}

        {/if}

        <div class="dropdown d-inline-block me-3">
            <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-fw fa-share-alt font-9 d-sm-inline-block"></i> {:L('分享')}
            </a>
            <div class="dropdown-menu p-0 border-0 font-size-sm">

                <div class="text-center d-block py-2" style="min-width: 100px">
<a href="javascript:;"  class="dropdown-item dask-clipboard" data-clipboard-text="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}"><i class="icon-link"></i>
                        {:L('复制链接')}</a>
                    <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}" data-type="qzone" class="dropdown-item "><i class="fab fa-qq text-primary"></i>
                        {:L('QQ空间')}</a>
                    <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}" data-type="weibo" class="dropdown-item "><i class="fab fa-weibo text-warning"></i>
                        {:L('新浪微博')}</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}" data-type="twitter" class="dropdown-item"><i class="fab fa-twitter" style="color:#1DA1F2"></i> Twitter/X</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}" data-type="telegram" class="dropdown-item"><i class="fab fa-telegram" style="color:#229ED9"></i> Telegram</a>
                    
                    <div class="dask-qrcode-container" data-share="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}">
                        <a href="javascript:;" class="dropdown-item "><i class="fab fa-weixin text-success"></i>
                            {:L('微信扫一扫')}</a>
                        <div class="dask-qrcode text-center py-2"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--问题回答操作栏钩子-->
        

        <div class="dask-share clearfix d-inline-block">
            <div class="social-share" data-disabled="google,twitter,facebook,linkedin,douban"></div>
        </div>
        {if $user_id}
        <div class="dropdown d-inline-block">
            <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-ellipsis-h d-none d-sm-inline-block"></i>
            </a>
            <div class="dropdown-menu p-0 border-0 font-size-sm">
                <div class="text-center d-block py-2" style="min-width: 100px">
                    {if $v.comment_count < 1 && ($user_info['uid']==$v['uid'] || get_user_permission('modify_answer')=='Y')}
                    <a href="javascript:;"  class="dropdown-item daskAnswerEditor" data-question-id="{$v.question_id}" data-answer-id="{$v['id']}">{:L('编辑')}</a>
                    {/if}
                    {if $v.comment_count < 1 && ($user_info['uid']==$v['uid'] || get_user_permission('remove_answer')=='Y')}
                    <a href="javascript:;" title="{:L('删除回答')}" class="dropdown-item dask-ajax-get" data-confirm="{:L('是否删除该回答')}?" data-url="{:url('answer/delete_answer?answer_id='.$v['id'])}">{:L('删除')}</a>
                    {/if}
                </div>
            </div>
        </div>
        {/if}
    </div>

    <!--评论框动态显示-->
    <div class="answerCommentBox mt-2 border" id="answerCommentBox-{$v.id}" style="display: none;margin-left: 45px">
        <div class="answerCommentHeader clearfix px-3 pt-3">
            <h6 class="font-10 float-start mb-1">{:L('评论')}{if ($v.comment_count > 0)}<span class="answer-comment-count{$v.id}">{$v.comment_count}</span>{/if}</h6>
        </div>
        <div class="answerCommentList px-3"></div>
        <div class="pageElement"></div>
        <div class="commentForm clearfix rounded dask-replay-box"></div>
    </div>
</div>
{/foreach}
{/if}