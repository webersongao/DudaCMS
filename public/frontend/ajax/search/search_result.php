{if !empty($list)}
<div class="dask-common-list">
    {volist name="$list" id="v"}
    {switch name="$v['search_type']"}
    {case value="question" }
    <div class="dask-list-item">
        <div class="dask-list-item__meta text-muted">
            {if (!$v['answer_info'])}
            {if $v.is_anonymous}
            <a href="javascript:;" class="dask-username">
                <img src="{:user_avatar_url('')}" class="dask-user-img circle" alt="{:L('匿名用户')}">{:L('匿名用户')}
            </a>
            {else/}
            <a href="{$v['user_info']['url']}" data-pjax="WrapBody" class="dask-user-name" data-id="{$v['user_info']['uid']}">
                <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" class="circle" alt="{$v['user_info']['name']}">{$v['user_info']['name']}
            </a>
            {/if}
            <i>发起了提问</i>
            <em class="time">{:date_friendly($v['create_time'])}</em>
            {else/}
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
            {/if}
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
                {if (!$v['answer_info'])}
                <a href="{:url('question/detail',['id'=>$v['id']])}">{$v.title|raw}</a>
                {else/}
                <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}">{$v.title|raw}</a>
                {/if}
            </div>
            <div class="pcon {if $v['img_list'] && get_theme_setting('common.list_show_image')=='Y'}row{/if}">
                {if $v['img_list'] && get_theme_setting('common.list_show_image')=='Y'}
                <div class="col-md-12 t-imglist row">
                    {volist name="$v['img_list']" id="img" key="k"}
                    {if($k<4)}
                    <div class="col-md-4 dask-list-img">
                        <img src="{$img|default='static/common/image/default-cover.svg'}" class="rounded w-100 dask-cut-img" style="margin-bottom: 5px;" >
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
            {if (!$v['answer_info'])}
            <label>
                <a type="button" class="{$v['has_focus'] ? 'ygz' : 'gz'} btn btn-primary btn-sm" data-action="user-focus" data-type="question" data-id="{$v.id}">{$v['has_focus'] ? L('已关注') : L('关注问题')} {if ($v.focus_count > 0)}<span class="badge focus-count">{$v.focus_count}</span>{/if}</a>
            </label>
            <label class="ms-3 me-3"><i class="iconfont">&#xe870;</i> {:L('点赞')}{$v.agree_count}</label>
            <label class="me-3"><i class="iconfont">&#xe601;</i> {:L('评论')}{$v['comment_count']}</label>
            {else/}
            <label class="dz">
                <a type="button" class="btn btn-primary btn-sm dask-ajax-agree  {$v['answer_info']['vote_value']==1 ? 'active' : ''}" title="{:L('点赞回答')}" data-action="user-agree" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont">&#xe870;</i>{:L('赞同')}{if ($v['answer_info']['agree_count'] > 0)}<span class="badge">{$v['answer_info']['agree_count']}</span>{/if}</a>
                <a type="button" class="btn btn-primary btn-sm dask-ajax-against  {$v['answer_info']['vote_value']==-1 ? 'active' : ''}" title="{:L('反对回答')}" data-action="user-against" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont" style="font-size: 12px;">&#xe68f;</i>{:L('反对')}<span class="badge"></span></a>
            </label>
            <label class="ms-3 me-3"><i class="iconfont">&#xe625;</i>}{:L('%s 感谢', $v['answer_info']['thanks_count'])}</label>
            <label class="me-3"><i class="iconfont">&#xe601;</i>{:L('评论')}{$v['answer_info']['comment_count']}</label>
            {/if}
        </div>
    </div>
    {/case}

    {case value="article" }
    <div class="dask-list-item">
        <div class="dask-list-item__body">
            <div class="n-title">
                <span class="tip-s2 badge text-bg-secondary">{:L('文章')}</span>
                
                {if $v.set_top}
                <span class="tip-d badge text-bg-secondary">{:L('顶')}</span>
                {/if}
                <a href="{:url('article/detail',['id'=>$v['id']])}">{$v['title']|raw}</a>
            </div>
            <div class="pcon {if ($v['img_list'] || $v.cover) && get_theme_setting('common.list_show_image')=='Y'}row{/if}">
                {if ($v['img_list'] || $v.cover) && get_theme_setting('common.list_show_image')=='Y'}
                <div class="col-md-3 dask-list-img"><img src="{$v.cover|default=$v['img_list'][0]}" class="rounded dask-cut-img" alt="{$v['title']}" width="100%"></div>
                <div class="ov-3 col-md-9">
                    <div class="dask-three-line">
                        {$v.message|raw}
                    </div>
                </div>
                {else/}
                <div class="dask-three-line">
                    {$v.message|raw}
                </div>
                {/if}
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
    {/case}

    {default /}
    {/switch}
    {/volist}
</div>
{/if}