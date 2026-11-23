{if $list}
{volist name="list" id="v"}
{if $type=='question'}
<div class="dask-list-item">
    <div class="dask-list-item__meta text-muted">
        {if $v.is_anonymous}
        <a href="javascript:;" class="dask-username">
            <img src="/static/common/image/default-avatar.svg" class="dask-user-img circle" alt="{:L('匿名用户')}">{:L('匿名用户')}
        </a>
        {else/}
        <a href="{$v['user_info']['url']}" class="dask-user-name" data-id="{$v['user_info']['uid']}" target="_blank">
            <img src="{$v['user_info']['avatar']}" onerror="this.src='/static/common/image/default-avatar.svg'" class="circle" alt="{$v['user_info']['name']}">{$v['user_info']['name']}
        </a>
        {/if}
        <i>{:L('发起了提问')}</i>
        <em class="time">{:date_friendly($v['create_time'])}</em>
        {if $v['topics']}
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
            <a href="{:url('question/detail',['id'=>$v['id']])}" target="_blank">{$v.title|raw}</a>
        </div>
        <div class="pcon">
            <div class="dask-two-line">{$v.detail|raw}</div>
        </div>
    </div>
    <div class="dask-list-item__actions dask-actions">
        <label>
            <a type="button" class="{$v['has_focus'] ? 'ygz' : 'gz'} btn btn-primary btn-sm" data-action="user-focus" data-type="question" data-id="{$v.id}">{$v['has_focus'] ? L('已关注') : L('关注问题')} {if ($v.focus_count > 0)}<span class="badge focus-count">{$v.focus_count}</span>{/if}</a>
        </label>
        <label class="ms-3 me-3"><i class="iconfont">&#xe625;</i>{:L('感谢')}{$v.thanks_count}</label>
        <label class="me-3"><i class="iconfont">&#xe601;</i>{:L('评论')}{$v['comment_count']}</label>
    </div>
</div>
{/if}

{if $type=='friend'}
<div class="p-3 bg-white mb-1 border-bottom">
    <div class="dask-hcard dask-user-hcard position-relative">
        <div class="dask-hcard__media">
            <a href="{$v.user_info.url}" class="dask-username rounded d-block">
                <img src="{$v.user_info.avatar}" class="rounded" onerror="this.src='/static/common/image/default-avatar.svg'" alt="{$v.user_info.name}" width="80" height="80">
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

{if $type=='fans'}
<div class="p-3 bg-white mb-1 border-bottom">
    <div class="dask-hcard dask-user-hcard position-relative">
        <div class="dask-hcard__media">
            <a href="{$v.user_info.url}" class="dask-username rounded d-block">
                <img src="{$v.user_info.avatar}" class="rounded" onerror="this.src='/static/common/image/default-avatar.svg'" alt="{$v.user_info.name}" width="80" height="80">
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
            <button class="btn btn-primary btn-sm px-3 {if $v.has_focus}active ygz{/if} me-3" data-action="user-focus" data-type="user" data-id="{$v.user_info.uid}">{if $v.has_focus}{:L('互相关注')}{else}{:L('关注')}{/if}</button>
            <button class="btn btn-outline-primary px-3 btn-sm" data-action="user-inbox" data-name="{$v.user_info.nick_name}">{:L('私信')}</button>
        </div>
        {/if}
    </div>
</div>
{/if}

{if $type=='column'}
<div class="p-3 bg-white border-bottom">
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
<div class="dask-focus-topic-item">
    <a class="dask-focus-topic-icon dask-topic" href="{:url('topic/detail',['id'=>$v['id']])}" data-id="{$v.id}">
        <img src="{$v['pic']|default='/static/common/image/topic.svg'}" onerror="this.src='/static/common/image/topic.svg'" alt="{$v.title|raw}">
    </a>
    <div class="dask-focus-topic-body">
        <a class="dask-focus-topic-title dask-topic" href="{:url('topic/detail',['id'=>$v['id']])}" data-id="{$v.id}"># {:ltrim(trim(strip_tags($v['title'])), '# ')}</a>
        <p class="dask-focus-topic-desc dask-one-line">{$v.description|raw}</p>
        <div class="dask-focus-topic-stats">
            <span>{:L('讨论')} <strong>{$v.discuss}</strong></span>
            <span>{:L('关注')} <strong class="dask-global-focus-count">{$v.focus}</strong></span>
        </div>
    </div>
    <a class="dask-focus-topic-follow btn btn-sm {$v['is_focus'] ? 'btn-light ygz' : 'btn-primary'}" href="javascript:;" data-action="user-focus" data-type="topic" data-id="{$v.id}">{$v['is_focus'] ? L('已关注') : L('关注')}</a>
</div>
{/if}
{/volist}
{/if}
