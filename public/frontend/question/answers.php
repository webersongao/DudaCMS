{if !empty($list)}
{volist name="list" id="v"}
<div class="dask-answer-item p-3 mb-1 bg-white" id="question-answer-{$v.id}" data-answer-id="{$v.id}">
    <div class="user-details-card pt-0 pb-2 position-relative clearfix">
        <div class="user-details-card-avatar float-start" style="position: relative">
            {if $v.is_anonymous}
            <a href="javascript:;">
                <img src="/static/common/image/default-avatar.svg" alt="{:L('匿名用户')}" title="{:L('匿名用户')}" style="width: 40px;height: 40px">
            </a>
            {else/}
            <a href="{$v['user_info']['url']}" class="dask-username" data-id="{$v.uid}" title="{$v['user_info']['name']}">
                <img src="{$v['user_info']['avatar']}" onerror="this.src='/static/common/image/default-avatar.svg'" alt="{$v['user_info']['name']}" style="width: 40px;height: 40px">
            </a>
            {/if}
        </div>
        <div class="user-details-card-name float-start ms-2">
            {if $v.is_anonymous}<a href="javascript:;" title="{:L('匿名用户')}">{:L('匿名用户')}</a>{else/}<a href="{$v['user_info']['url']}" data-id="{$v.uid}" class="dask-username" title="{$v['user_info']['name']}">{$v['user_info']['name']}</a>  <span class="badge text-bg-success">{$v['user_info']['group_name']}</span>{/if} {if $setting.show_answer_user_ip=='Y' && $v.answer_user_local}<span class="ms-2 text-muted font-8">{:L('用户来自于')}: {$v.answer_user_local}</span>{/if} <br><span class="ms-0"> {:date_friendly($v['create_time'])} </span>
        </div>

        {if $v['is_best']}
        <div class="dask-answer-best">
            <i class="iconfont" title="{:L('最佳回答')}">&#xe6f7;</i>
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
    <div class="answer-btn-actions mt-3">
        <label class="me-1">
            <a href="javascript:;" class="dask-ajax-agree {if $v['vote_value']==1}active{/if}"  data-action="user-agree" data-type="answer" data-id="{$v.id}">
                <i class="icon-thumb_up"></i> {:L('赞同')} {if ($v.agree_count > 0)}<span> {$v.agree_count}</span>{/if}
            </a>
        </label>

        <label class="me-3 ">
            <a href="javascript:;" class="dask-ajax-against {if $v['vote_value']==-1}active{/if}"  data-action="user-against" data-type="answer" data-id="{{$v.id}}">
                <i class="icon-thumb_down"></i>
            </a>
        </label>

        <label class="me-3">
            <a href="javascript:;" class="dask-ajax-open" data-title="评论回答" data-url="{:url('comment/answer?answer_id='.$v['id'])}">
                <i class="icon-chat"></i> {:L('评论')}{if ($v.comment_count > 0)}<span class="answer-comment-count{$v.id}">{$v.comment_count}</span>{/if}
            </a>
        </label>

        {if $user_id}
        <label class="me-3">
            <a href="javascript:;" {if !$v.checkReport} data-action="user-report" data-type="answer" data-id="{$v.id}"{/if} ><i class="icon-warning"></i>{$v.checkReport ? L('已举报') : L('举报')}</a>
        </label>

        <label class="me-3">
            <a href="javascript:;" data-action="user-favorite" data-type="answer" data-id="{$v.id}"><i class="icon-turned_in"></i> {if $v.checkFavorite} {:L('已收藏')}{else}{:L('收藏')}{/if}</a>
        </label>

        <label class="me-3">
            <a href="javascript:;"  {if $v.has_thanks} class="active" {else/}data-action="user-thanks" data-id="{$v.id}"{/if}>
            <i class="icon-favorite"></i> <span>{$v.has_thanks ? L('已感谢') : L('感谢')}</span>
            </a>
        </label>

        {if !$v.has_uninterested}
        <label class="me-3">
            <a href="javascript:;" data-action="user-uninterested" data-type="answer" data-id="{$v.id}">
                <i class="icon-report"></i> {:L('不感兴趣')}
            </a>
        </label>
        {/if}


        {/if}
        {if ($user_id && ($v['uid']==$user_id || $user_info['group_id']==1 || $user_info['group_id']==2)  && !$v['is_best'] && !$best_answer_count)}
        <label class="me-3">
            <a href="javascript:;"  class="dask-ajax-get" data-confirm="{:L('是否把该回答设为最佳')}？" data-url="{:url('question/set_answer_best?answer_id='.$v['id'])}">
                <i class="fa fa-award "></i> {:L('最佳')}
            </a>
        </label>
        {/if}
        <div class="dropdown d-inline-block me-3">
            <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-fw fa-share-alt font-9 d-none d-sm-inline-block"></i> {:L('分享')}
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
        {if isset($user_info) && ($user_info['group_id']==1 || $user_info['group_id']==2 || $user_info['uid']==$v['uid'])}
        <div class="dropdown d-inline-block">
            <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-ellipsis-h d-none d-sm-inline-block"></i>
            </a>
            <div class="dropdown-menu p-0 border-0 font-size-sm">
                <div class="text-center d-block py-2" style="min-width: 100px">
                    <a href="javascript:;"  class="dropdown-item dask-answer-editor" data-question-id="{$v.question_id}" data-answer-id="{$v['id']}">{:L('编辑')}</a>
                    <a href="javascript:;" title="{:L('删除回答')}" class="dropdown-item dask-ajax-get" data-confirm="{:L('是否删除该回答')}？" data-url="{:url('question/delete_answer?answer_id='.$v['id'])}">{:L('删除')}</a>
                </div>
            </div>
        </div>
        {/if}
    </div>
</div>
{/volist}
{else/}
<p class="text-center text-muted p-3 bg-white">
    <img src="{$assetBase}common/image/empty.svg">
    <span class="d-block">{:L('暂无回答')}</span>
</p>
{/if}
