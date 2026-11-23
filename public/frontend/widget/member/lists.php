{if !empty($list)}
<div class="dask-common-list py-2">
	{volist name="list" id="v"}
	{switch name="$v['item_type']"}
	{case value="question" }
    <div class="dask-list-item question">
        <div class="dask-list-item__meta mb-2">
            {if (!$v['answer_info'])}
            {if $v.is_anonymous}
            <a href="javascript:;" class="dask-username"  data-id="{$v['user_info']['uid']}">
                <img src="/static/common/image/default-avatar.svg" class="dask-user-img" alt="{:L('匿名用户')}">{:L('匿名用户')}
            </a>
            {else/}
            <a href="{$v['user_info']['url']}" class="dask-user-name">
                <img src="{$v['user_info']['avatar']}" alt="{$v['user_info']['name']}">{$v['user_info']['name']}
            </a>
            {/if}
            <span>{:L('发起了提问')}</span>
            <label class="float-end">{:date_friendly($v['create_time'])}</label>
            {else/}
            <a href="{$v['answer_info']['user_info']['url']}" class="dask-user-name">
                <img src="{$v['answer_info']['user_info']['avatar']}" alt="{$v['answer_info']['user_info']['name']}">{$v['answer_info']['user_info']['name']}
            </a>
            <span>{:L('回复了问题')}（{:L('回复')}{$v['answer_count']}）</span>
            <label class="float-end">{:date_friendly($v['answer_info']['create_time'])}</label>
            {/if}
            {if $v['topics']}
            <div class="dask-tag d-inline">
                {volist name="$v['topics']" id="topic"}
                <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank">{$topic.title}</a>
                {/volist}
            </div>
            {/if}
        </div>
        <div class="dask-list-item__body title">
            <p class="dask-one-line fw-bold">
                {if $v.set_top}
                <i class="iconfont icon-zhiding text-warning font-12"></i>
                {/if}
                {if $v.question_type=='reward'}
                <i class="iconfont icon-shang text-danger font-12"></i>
                {/if}
                {if (!$v['answer_info'])}
                <a href="{:url('question/detail',['id'=>$v['id']])}">{$v.title}</a>
                {else/}
                <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}">{$v.title}</a>
                {/if}
            </p>
        </div>
        <div class="dask-list-item__body content my-2 dask-two-line">
            {$v.detail|raw}
        </div>
        {if (!$v['answer_info'])}
        <div class="dask-common-footer">
            <label class="me-2">
                <a href="javascript:;" class="btn btn-primary btn-sm px-3 me-3 {if $v['has_focus']}active ygz{/if}" title="{:L('关注问题')}" data-action="user-focus" data-type="question" data-id="{$v.id}">{:L($v['has_focus'] ? '已关注' : '关注问题')} {if ($v.focus_count > 0)}<span class="focus-count">{$v.focus_count}</span>{/if}</a>
            </label>
            <label class="me-2"><i class="icon-eye"></i> {:L('浏览')}{$v.view_count}</label>
            <label class="me-2"><i class="icon-comment"></i> {:L('评论')}{$v['comment_count']}</label>
        </div>
        {else/}
        <div class="dask-common-footer">
            <label class="me-2">
                <a href="javascript:;" class="{$v['answer_info']['vote_value']==1 ? 'active' : ''}" title="{:L('点赞回答')}" data-action="user-agree" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="icon-thumb_up"></i> {:L('赞同')} <span>{$v['answer_info']['agree_count']}</span></a>
            </label>
            <label class="me-2">
                <a href="javascript:;" class="{$v['answer_info']['vote_value']==-1 ? 'active' : ''}" title="{:L('点赞回答')}" data-action="user-against" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="icon-thumb_down"></i> {:L('反对')}</a>
            </label>
            <label class="me-2">  {:L('%s 感谢', $v['answer_info']['thanks_count'])}</label>
            <label class="me-2"><i class="icon-comment"></i> {:L('评论')}{$v['answer_info']['comment_count']}</label>
        </div>
        {/if}
    </div>
	{/case}

	{case value="article" }
    <div class="dask-list-item article">
        {if $v['cover']}
        <div class="dask-list-item__body col-sm-12 px-0">
            <img src="{$v['cover']|default='/static/common/image/default-cover.svg'}" class="rounded dask-cut-img" alt="{$v['title']}">
        </div>
        {/if}
        <div class="dask-list-item__body col-sm-12 m-0 px-0" {if !$v['cover']}style="width:100%"{/if}>
            <h2 class="dask-one-line mb-2">
                {if $v.set_top}
                <i class="iconfont icon-zhiding text-warning font-12"></i>
                {/if}
                <a href="{:url('article/detail',['id'=>$v['id']])}">{$v['title']}</a>
            </h2>
            <div class="dask-content dask-two-line">
                {$v.message|raw}
            </div>
            {if $v['topics']}
            <div class="dask-tag">
                {volist name="$v['topics']" id="topic"}
                <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank">{$topic.title}</a>
                {/volist}
            </div>
            {/if}
            <div class="dask-common-footer">
                <a href="{$v['user_info']['url']}" class="dask-username avatar" data-id="{$v['user_info']['uid']}">
                    <img src="{$v['user_info']['avatar']}" alt="" class="rounded dask-user-img" style="width: 22px;height: 22px">
                </a>
                <a href="{$v['user_info']['url']}" class="dask-username name" data-id="{$v['user_info']['uid']}">{$v['user_info']['name']}</a>
                <span> | {:date_friendly($v['create_time'])}</span>
                <div class="float-end">
                    <label><i class="icon-eye"></i> {$v['view_count']}</label>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
	{/case}

    {case value="answer" }
    <div class="dask-list-item question">
        <div class="dask-list-item__meta mb-2">
            {if (!$v['answer_info'])}
            {if $v.is_anonymous}
            <a href="javascript:;" class="dask-username"  data-id="{$v['user_info']['uid']}">
                <img src="/static/common/image/default-avatar.svg" class="dask-user-img" alt="{:L('匿名用户')}">{:L('匿名用户')}
            </a>
            {else/}
            <a href="{$v['user_info']['url']}" class="dask-user-name">
                <img src="{$v['user_info']['avatar']}" alt="{$v['user_info']['name']}">{$v['user_info']['name']}
            </a>
            {/if}
            <span>{:L('发起了提问')}</span>
            <label class="float-end">{:date_friendly($v['create_time'])}</label>
            {else/}
            <a href="{$v['answer_info']['user_info']['url']}" class="dask-user-name">
                <img src="{$v['answer_info']['user_info']['avatar']}" alt="{$v['answer_info']['user_info']['name']}">{$v['answer_info']['user_info']['name']}
            </a>
            <span>{:L('回复了问题')}（{:L('回复')}{$v['answer_count']}）</span>
            <label class="float-end">{:date_friendly($v['answer_info']['create_time'])}</label>
            {/if}
            {if $v['topics']}
            <div class="dask-tag d-inline">
                {volist name="$v['topics']" id="topic"}
                <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank">{$topic.title}</a>
                {/volist}
            </div>
            {/if}
        </div>
        <div class="dask-list-item__body title">
            <p class="dask-one-line fw-bold">
                {if $v.set_top}
                <i class="iconfont icon-zhiding text-warning font-12"></i>
                {/if}
                {if $v.question_type=='reward'}
                <i class="iconfont icon-shang text-danger font-12"></i>
                {/if}
                {if (!$v['answer_info'])}
                <a href="{:url('question/detail',['id'=>$v['id']])}">{$v.title}</a>
                {else/}
                <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}">{$v.title}</a>
                {/if}
            </p>
        </div>
        <div class="dask-list-item__body content my-2 dask-two-line">
            {$v.detail|raw}
        </div>
        {if (!$v['answer_info'])}
        <div class="dask-common-footer">
            <label class="me-2">
                <a href="javascript:;" class="btn btn-primary btn-sm px-3 me-3 {if $v['has_focus']}active ygz{/if}" title="{:L('关注问题')}" data-action="user-focus" data-type="question" data-id="{$v.id}">{:L($v['has_focus'] ? '已关注' : '关注问题')} {if ($v.focus_count > 0)}<span class="focus-count">{$v.focus_count}</span>{/if}</a>
            </label>
            <label class="me-2"><i class="icon-eye"></i> {:L('浏览')}{$v.view_count}</label>
            <label class="me-2"><i class="icon-comment"></i> {:L('评论')}{$v['comment_count']}</label>
        </div>
        {else/}
        <div class="dask-common-footer">
            <label class="me-2">
                <a href="javascript:;" class="{$v['answer_info']['vote_value']==1 ? 'active' : ''}" title="{:L('点赞回答')}" data-action="user-agree" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="icon-thumb_up"></i> {:L('赞同')} <span>{$v['answer_info']['agree_count']}</span></a>
            </label>
            <label class="me-2">
                <a href="javascript:;" class="{$v['answer_info']['vote_value']==-1 ? 'active' : ''}" title="{:L('点赞回答')}" data-action="user-against" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="icon-thumb_down"></i> {:L('反对')}</a>
            </label>
            <label class="me-2">  {:L('%s 感谢', $v['answer_info']['thanks_count'])}</label>
            <label class="me-2"><i class="icon-comment"></i> {:L('评论')}{$v['answer_info']['comment_count']}</label>
        </div>
        {/if}
    </div>
    {/case}

	{default /}
	{/switch}
	{/volist}
</div>
{$page|raw}
{else/}
<p class="text-center py-3 text-muted">
    <img src="{$assetBase}common/image/empty.svg">
    <span class="d-block">{:L('暂无内容')}</span>
</p>
{/if}
