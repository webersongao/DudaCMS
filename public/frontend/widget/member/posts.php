{if $list}
{foreach $list as $key=>$v}
{if $v['item_type']=="question" }
<div class="dask-list-item">
    <div class="dask-list-item__meta">
        <span class="text-muted text-muted2">{$v.remark|raw}</span>
        {if isset($v['topics']) && $v['topics']}
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
        <div class="dask-two-line pcon">
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
        <label class="me-3"><i class="iconfont">&#xe601;</i> {:L('%s 评论',$v['comment_count'])}</label>
    </div>
</div>
{/if}

{if $v['item_type']=="article" }
<div class="dask-list-item">
    <div class="dask-list-item__meta"><span class="text-muted text-muted2">{$v.remark|raw}</span></div>
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
        <label class="ms-2 me-2"><i class="iconfont">&#xe640;</i> {:L('%s 浏览',$v['view_count']?:'')}</label>
        <label class="me-2"><i class="iconfont">&#xe601;</i> {:L('%s 评论',$v['comment_count'])}</label>
        <label class="me-2"><i class="iconfont">&#xe699;</i><a href="{$v['user_info']['url']}" class="dask-username text-muted name" data-id="{$v['user_info']['uid']}">{$v['user_info']['name']}</a></label>
        <label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label>
    </div>
</div>
{/if}

{if $v['item_type']=="answer" && isset($v['answer_info'])}
<div class="dask-list-item">
    <div class="dask-list-item__meta mb-2">
        <span class="text-muted text-muted2">{$v.remark|raw}</span>
        {if isset($v['topics']) && $v['topics']}
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
        <a href="{$v['answer_info']['user_info']['url']}" class="dask-username" >{$v['answer_info']['user_info']['name']}</a>:
        {/if}
        <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}" target="_blank">{$v.answer_info.content|raw}</a>
        {/if}
    </div>
    <div class="dask-list-item__actions dask-actions">
        <label class="dz">
            <a type="button" class="btn btn-primary btn-sm dask-ajax-agree {$v['vote_value']==1 ? 'active' : ''}" title="{:L('点赞回答')}" data-action="user-agree" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont">&#xe870;</i>{:L('赞同')}{if ($v['answer_info']['agree_count'] > 0)}<span class="badge">{$v['answer_info']['agree_count']}</span>{/if}</a>
            <a type="button" class="btn btn-primary btn-sm dask-ajax-against {$v['vote_value']==-1 ? 'active' : ''}" title="{:L('反对回答')}" data-action="user-against" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont" style="font-size: 12px;">&#xe68f;</i>{:L('反对')}<span class="badge"></span></a>
        </label>
        <label class="ms-3 me-3"><i class="iconfont">&#xe625;</i>{:L('%s 感谢',$v['answer_info']['thanks_count'])}</label>
        <label class="me-3"><i class="iconfont">&#xe601;</i>{:L('%s 评论',$v['answer_info']['comment_count'])}</label>
        <label class="me-3"><i class="fa fa-comment-alt"></i>{:L('%s 回复',$v['answer_count'])}</label>
    </div>
</div>
{/if}

{if $type=='friend' && $v.user_info}
<div class="dask-user-row py-3 bg-white mb-1 border-bottom">
    <div class="dask-hcard dask-user-hcard position-relative">
        <div class="dask-hcard__media">
            <a href="{$v.user_info.url}" class="dask-username rounded d-block">
                <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" alt="{$v.user_info.name}" width="80" height="80">
            </a>
            {if $v['user_info']['is_online']}
            <span class="online-dot"></span>
            {else/}
            <span class="offline-dot"></span>
            {/if}
        </div>

        <div class="dask-hcard__body">
            <h3 class="mb-1">
                <a href="{$v.user_info.url}" class="dask-username font-12">{$v.user_info.name}</a>
            </h3>
            <p class="text-muted font-9 dask-one-line">{$v['user_info']['signature']|default=L('这家伙还没有留下自我介绍～')}</p>
            <div class="text-muted mt-1">
                <label>{:get_setting("credit_unit")}:{$v.user_info.credit}</label>
            </div>
        </div>

        {if $user_id && $v['user_info']['uid']!=$user_id}
        <div class="dask-hcard__extra">
            <button class="btn btn-primary btn-sm px-3 {if $v.has_focus}active ygz{/if} me-3" data-action="user-focus" data-type="user" data-id="{$v.user_info.uid}">{if $v.has_focus}{:L('已关注')}{else}{:L('关注')}{/if}</button>
            <button class="btn btn-outline-primary px-3 btn-sm" data-action="user-inbox" data-name="{$v.user_info.nick_name}">{:L('私信')}</button>
        </div>
        {/if}
    </div>
</div>
{/if}

{if $type=='fans' && $v.user_info}
<div class="dask-user-row py-3 bg-white mb-1 border-bottom">
    <div class="dask-hcard dask-user-hcard position-relative">
        <div class="dask-hcard__media">
            <a href="{$v.user_info.url}" class="dask-username rounded d-block">
                <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" alt="{$v.user_info.name}" width="80" height="80">
            </a>
            {if $v['user_info']['is_online']}
            <span class="online-dot"></span>
            {else/}
            <span class="offline-dot"></span>
            {/if}
        </div>

        <div class="dask-hcard__body">
            <h3 class="mb-1">
                <a href="{$v.user_info.url}" class="dask-username font-12">{$v.user_info.name}</a>
            </h3>
            <p class="text-muted font-9 dask-one-line">{$v['user_info']['signature']|default=L('这家伙还没有留下自我介绍～')}</p>
            <div class="text-muted mt-1">
                <label>{:get_setting("credit_unit")}:{$v.user_info.credit}</label>
            </div>
        </div>

        {if $user_id && $v['user_info']['uid']!=$user_id}
        <div class="dask-hcard__extra">
            <button class="btn btn-primary btn-sm px-3 {if $v.has_focus}active ygz{/if} me-3" data-action="user-focus" data-type="user" data-id="{$v.user_info.uid}">{if $v.has_focus}{:L('已关注')}{else}{:L('关注')}{/if}</button>
            <button class="btn btn-outline-primary px-3 btn-sm" data-action="user-inbox" data-name="{$v.user_info.nick_name}">{:L('私信')}</button>
        </div>
        {/if}
    </div>
</div>
{/if}

{if $type=='column'}
<div class="bg-white py-2 border-bottom">
    <div class="mt-1">
        <h3 class="dask-one-line font-12"><a href="{:url('column/detail',['id'=>$v['id']])}">{$v.name}</a></h3>
        <p class="text-muted my-1 dask-two-line">{$v.description}</p>
        <a href="javascript:;" class="text-color-info me-2 font-9">{$v.join_count|num2string} {:L('用户')}</a>
        <a href="javascript:;" class="text-color-info me-2 font-9">{$v.post_count|num2string} {:L('内容')} </a>
        <a href="javascript:;" class="text-color-info font-9">{$v.focus_count|num2string} {:L('关注')} </a>
    </div>
</div>
{/if}

{if $type=='topic'}
<div class="dask-topic-row py-2 rounded topic-item">
    <div class="dask-list-item dask-hcard position-relative">
        <div class="dask-hcard__media topic-thumb">
            <a href="{:url('topic/detail',['id'=>$v['id']])}">
                <img src="{$v['pic']|default='/static/common/image/topic.svg'}" class="rounded">
            </a>
        </div>
        <div class="dask-hcard__body topic-info position-relative">
            <a href="{:url('topic/detail',['id'=>$v['id']])}" class="dask-topic-row__title">{$v.title|raw}</a>
            <p class="dask-topic-row__desc">{$v.description|raw}</p>
            <p class="dask-topic-row__meta">
                <span>{:L('讨论')}：{$v.discuss}</span>
                <span><span class="dask-global-focus-count">{:L('关注')}：{$v.focus}</span></span>
            </p>
        </div>
    </div>
</div>
{/if}

{/foreach}
{$page|raw}
{else/}
<p class="text-center py-3 text-muted">
    <img src="{$assetBase}common/image/empty.svg">
    <span class="d-block">{:L('暂无内容')}</span>
</p>
{/if}
