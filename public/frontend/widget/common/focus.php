{if !empty($list)}
{volist name="list" id="v"}
{if ($v['item_type']=="question" || $v['relation_type']=='question') && $v['item_type']!='answer' }
<div class="dask-list-item">
    <div class="dask-list-item__meta">
        <span class="text-muted">{$v.remark|raw}</span>
        {if isset($v['topics']) && !empty($v['topics'])}
        <div class="tag d-inline-block mt-2">
            {volist name="$v['topics']" id="topic"}
            <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank"><em class="tag">{$topic.title}</em></a>
            {/volist}
        </div>
        {/if}
    </div>
    <div class="dask-list-item__body">
        <div class="n-title">
            <span class="tip-s1 badge text-bg-secondary">{:L('问答')}</span>
            <a href="{:url('question/detail',['id'=>$v['id']])}">{$v.title}</a>
        </div>
        <div class="pcon dask-two-line">
            {if !$v['answer_info']}
            <a href="{:url('question/detail',['id'=>$v['id']])}" target="_blank">{$v.detail|raw}</a>:
            {else/}
            {if $v['answer_info']['is_anonymous']}
            <a href="javascript:;" class="dask-username" >匿名用户</a>:
            {else/}
            <a href="{$v['answer_info']['user_info']['url']}" class="dask-username fw-bold" >{$v['answer_info']['user_info']['name']}</a>:
            {/if}
            <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}" target="_blank">{$v.answer_info.content|raw}</a>
            {/if}
        </div>
    </div>
    <div class="dask-list-item__actions dask-actions">
        <label>
            <a type="button" class="{$v['has_focus'] ? 'ygz' : 'gz'} btn btn-primary btn-sm" data-action="user-focus" data-type="question" data-id="{$v.id}">{$v['has_focus'] ? L('已关注') : L('关注问题')} {if ($v.focus_count > 0)}<span class="badge">{$v.focus_count}</span>{/if}</a>
        </label>
        <label class="ms-3 me-3"><i class="iconfont">&#xe870;</i> {:L('点赞')}{$v.agree_count}</label>
        <label class="me-3"><i class="iconfont">&#xe601;</i> {:L('评论')}{$v['comment_count']}</label>
    </div>
</div>
{/if}

{if $v['item_type']=="article" || $v['relation_type']=='article' }
<div class="dask-list-item article">
    <div class="dask-list-item__meta">
        <span class="text-muted">{$v.remark|raw}</span>
        {if isset($v['topics']) && !empty($v['topics'])}
        <div class="tag d-inline-block mt-2">
            {volist name="$v['topics']" id="topic"}
            <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank"><em class="tag">{$topic.title|raw}</em></a>
            {/volist}
        </div>
        {/if}
    </div>
    <div class="dask-list-item__body">
        <div class="n-title">
            <span class="tip-s2 badge text-bg-secondary">{:L('文章')}</span>
            
            {if $v.set_top}
            <span class="tip-d badge text-bg-secondary">{:L('顶')}</span>
            {/if}
            <a href="{:url('article/detail',['id'=>$v['id']])}">{$v['title']}</a>
        </div>
        <div class="pcon">
            {$v.message|raw}
        </div>
    </div>
    <div class="dask-list-item__actions dask-actions">
        <label class="dz">
            <a type="button" class="btn btn-primary btn-sm" data-action="user-agree" data-type="article" data-id="{$v['id']}"><i class="iconfont">&#xe870;</i>{:L('赞同')}{if ($v['agree_count'] > 0)}<span class="badge">{$v['agree_count']}</span>{/if}</a>
        </label>
        <label class="ms-2 me-2"><i class="iconfont">&#xe640;</i> {:L('浏览')}{$v['view_count']?:''}</label>
        <label class="me-2"><i class="iconfont">&#xe601;</i> {:L('评论')}{$v['comment_count']}</label>
        <label class="me-2"><i class="iconfont">&#xe699;</i><a href="{$v['user_info']['url']}" class="dask-username text-muted name" data-id="{$v['user_info']['uid']}">{$v['user_info']['name']}</a></label>
        <label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label>
    </div>
</div>
{/if}

{if $v['item_type']=="answer" && isset($v['answer_info'])}
<div class="dask-list-item">
    <div class="dask-list-item__meta mb-2">
        <span class="text-muted">{$v.remark|raw}</span>
        {if isset($v['topics']) && !empty($v['topics'])}
        <div class="tag d-inline-block mt-2">
            {volist name="$v['topics']" id="topic"}
            <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank"><em class="tag">{$topic.title}</em></a>
            {/volist}
        </div>
        {/if}
    </div>
    <div class="dask-list-item__body n-title">
        <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}">{$v.title}</a>
    </div>
    <div class="dask-list-item__body dask-two-line pcon">
        {if !$v['answer_info']}
        <a href="{:url('question/detail',['id'=>$v['id']])}" target="_blank">{$v.detail|raw}</a>:
        {else/}
        {if $v['answer_info']['is_anonymous']}
        <a href="javascript:;" class="dask-username" >匿名用户</a>:
        {else/}
        <a href="{$v['answer_info']['user_info']['url']}" class="dask-username fw-bold" >{$v['answer_info']['user_info']['name']}</a>:
        {/if}
        <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}" target="_blank">{$v.answer_info.content|raw}</a>
        {/if}
    </div>
    <div class="dask-list-item__actions dask-actions">
        <label class="dz">
            <a type="button" class="btn btn-primary btn-sm dask-ajax-agree {$v['vote_value']==1 ? 'active' : ''}" title="{:L('点赞回答')}" data-action="user-agree" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont">&#xe870;</i>{:L('赞同')}{if ($v['answer_info']['agree_count'] > 0)}<span class="badge">{$v['answer_info']['agree_count']}</span>{/if}</a>
            <a type="button" class="btn btn-primary btn-sm dask-ajax-against {$v['vote_value']==-1 ? 'active' : ''}" title="{:L('反对回答')}" data-action="user-against" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont" style="font-size: 12px;">&#xe68f;</i>{:L('反对')}<span class="badge"></span></a>
        </label>
        <label class="ms-3 me-3"><i class="iconfont">&#xe625;</i>{:L('%s 感谢', $v['answer_info']['thanks_count'])}</label>
        <label class="me-3"><i class="iconfont">&#xe601;</i>{:L('评论')}{$v['answer_info']['comment_count']}</label>
    </div>
</div>
{/if}
{/volist}
{$page|raw}
{else/}
<p class="text-center py-3 text-muted">
    <img src="{$assetBase}common/image/empty.svg">
    <span class="d-block">{:L('暂无内容')}</span>
</p>
{/if}