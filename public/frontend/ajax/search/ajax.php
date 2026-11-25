{if $list}
{volist name="list" id="v"}
{if $v['search_type']=="question" }
<div class="dask-list-item">
    <div class="dask-list-item__meta mb-1">
        {if $v.is_anonymous}
        <a href="javascript:;" class="dask-username">
            <img src="{:user_avatar_url('')}" class="dask-user-img circle" alt="{:L('匿名用户')}">{:L('匿名用户')}
        </a>
        {else/}
        <a href="{$v['user_info']['url']}" data-pjax="WrapBody" class="dask-user-name" data-id="{$v['user_info']['uid']}">
            <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" class="circle" alt="{$v['user_info']['name']}">{$v['user_info']['name']}
        </a>
        {/if}
        <i>{:L('发起了提问')}</i>
        <em class="time">{:date_friendly($v['create_time'])}</em>
    </div>
    <div class="dask-list-item__body">
        <div class="n-title">
            <span class="tip-s1 badge text-bg-secondary">{:L('问答')}</span>
            {if $v.set_top}
            <span class="tip-d badge text-bg-secondary">{:L('顶')}</span>
            {/if}
            <a href="{:url('question/detail',['id'=>$v['id']])}">{$v.title|raw}</a>
        </div>
        <div class="pcon mt-1">
            <div class="dask-two-line">
                {$v.detail|raw}
            </div>
        </div>
    </div>
</div>
{/if}

{if $v['search_type']=="article" }
<div class="dask-list-item">
    <div class="dask-list-item__body">
        <div class="n-title">
            <span class="tip-s2 badge text-bg-secondary">{:L('文章')}</span>
            
            {if $v.set_top}
            <span class="tip-d badge text-bg-secondary">{:L('顶')}</span>
            {/if}
            <a href="{:url('article/detail',['id'=>$v['id']])}">{$v['title']|raw}</a>
        </div>
        <div class="pcon mt-1">
            <div class="dask-three-line">
                {$v.message|raw}
            </div>
        </div>
    </div>
</div>
{/if}

{if $v['search_type']=="users" }
<div class="d-flex position-relative dask-list-item">
    <div class="flex-fill me-3 mb-0" style="width: 54px;height: 54px">
        <a href="{$v.url}" class="rounded d-block"><img src="{:user_avatar_url($v['avatar'] ?? '')}" alt="{$v.name}" style="width: 54px;height: 54px"></a>
        <span class="{$v['is_online'] ? 'online-dot' : 'offline-dot'}"></span>
    </div>
    <div class="flex-fill w-100 mb-0">
        <h3 class="font-11 mb-1"><a href="{$v.url}">{$v.name|raw}</a></h3>
        <p class="dask-one-line">{$v['signature']|default=L('这家伙还没有留下自我介绍～')}</p>
    </div>
    {if $user_id && $user_id!=$v.uid}
    <div class="position-absolute" style="right: 0;top:10px">
        <a href="javascript:;" class="me-3 text-primary {$v['has_focus'] ? 'active ygz' : ''}" data-action="user-focus" data-type="user" data-id="{$v.uid}">
            {$v['has_focus'] ? '<span><i class="icon-minus-circle text-light"></i> '.L('已关注').'</span>' : '<span><i class="icon-plus-circle text-primary"></i> '.L('关注').'</span>'}
        </a>
        <a href="javascript:;" class="dask-send-inbox text-smooth">{:L('私信')}</a>
    </div>
    {/if}
</div>
{/if}

{if $v['search_type']=="topic" }
<div class="topic position-relative clearfix d-flex dask-list-item">
    <div class="flex-fill" style="max-width: 60px;margin-right: 15px">
        <a href="{:url('topic/detail',['id'=>$v['id']])}">
            <img src="{$v['pic']|default='/static/common/image/topic.svg'}" class="rounded" style="width: 60px;height: 60px">
        </a>
    </div>
    <div class="flex-fill" style="width: calc(100% - 75px);">
        <a href="{:url('topic/detail',['id'=>$v['id']])}" class="fw-bold"># {$v.title|raw}</a>
        <div class="mb-0 font-8 dask-one-line">{$v.description|raw}</div>
        <p class="font-8 text-muted">
            <span class="me-3">{:L('讨论')}：{$v.discuss}</span>
            <span class="me-3"><span class="dask-global-focus-count">{:L('关注')}：{$v.focus}</span></span>
            {if $user_id}
            <a href="javascript:;" class="cursor-pointer {$v['has_focus'] ? 'active ygz' : 'gz'}" data-action="user-focus" data-type="topic" data-id="{$v.id}" >{$v['has_focus'] ? '<span><i class="icon-minus-circle text-light"></i> '.L('已关注').'</span>' : '<span><i class="icon-plus-circle text-primary"></i> '.L('关注').'</span>'}</a>
            {/if}
        </p>
    </div>
</div>
{/if}

{if $v['search_type']=="answer" }
<div class="dask-list-item">
    <div class="dask-list-item__meta text-muted">

        {if $v['answer_info']['is_anonymous']}
        <a href="javascript:;" class="dask-username" >
            <img src="{:user_avatar_url('')}" class="dask-user-img circle" alt="{:L('匿名用户')}">{:L('匿名用户')}
        </a>
        {else/}
        <a href="{$v['answer_info']['user_info']['url']}" data-id="{$v['answer_info']['uid']}" class="dask-user-name" data-pjax="WrapBody">
            <img src="{:user_avatar_url($v['answer_info']['user_info']['avatar'] ?? '')}" class="circle" alt="{$v['answer_info']['user_info']['name']}">{$v['answer_info']['user_info']['name']}
        </a>
        {/if}
        <i>{:L('回复了问题')}（{:L('回复')}{$v['answer_count']}）</i>
        <em class="time">{:date_friendly($v['answer_info']['create_time'])}</em>
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
            {if $v.set_top}
            <span class="tip-d badge text-bg-secondary">{:L('顶')}</span>
            {/if}
            <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}">{$v.title|raw}</a>
        </div>
        <div class="pcon {if $v['img_list'] && get_theme_setting('common.list_show_image')=='Y'}row{/if}">
            {if $v['img_list'] && get_theme_setting('common.list_show_image')=='Y'}
            <div class="col-md-12 t-imglist row">
                {volist name="$v['img_list']" id="img" key="k"}
                {if($k<4)}
                <div class="col-md-4 dask-list-img">
                    <img src="{$img|default='/static/common/image/default-cover.svg'}" class="rounded w-100 dask-cut-img" style="margin-bottom: 5px;" >
                </div>
                {/if}
                {/volist}
            </div>
            <div class="ov-3 col-md-12">
                <div class="dask-two-line">
                    {$v.detail|raw}
                </div>
            </div>
            {else/}
            <div class="dask-two-line">
                {$v.detail|raw}
            </div>
            {/if}
        </div>
    </div>
    <div class="dask-list-item__actions dask-actions">
        <label class="dz">
            <a type="button" class="btn btn-primary btn-sm dask-ajax-agree  {$v['answer_info']['vote_value']==1 ? 'active' : ''}" title="{:L('点赞回答')}" data-action="user-agree" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont">&#xe870;</i>{:L('赞同')}{if ($v['answer_info']['agree_count'] > 0)}<span class="badge">{$v['answer_info']['agree_count']}</span>{/if}</a>
            <a type="button" class="btn btn-primary btn-sm dask-ajax-against  {$v['answer_info']['vote_value']==-1 ? 'active' : ''}" title="{:L('反对回答')}" data-action="user-against" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont" style="font-size: 12px;">&#xe68f;</i>{:L('反对')}<span class="badge"></span></a>
        </label>
        <label class="ms-3 me-3"><i class="iconfont">&#xe625;</i>{:L('%s 感谢', $v['answer_info']['thanks_count'])}</label>
        <label class="me-3"><i class="iconfont">&#xe601;</i>{:L('评论')}{$v['answer_info']['comment_count']}</label>
    </div>
</div>
{/if}



{/volist}
{/if}