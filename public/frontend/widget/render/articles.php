{if !empty($list)}
{foreach $list as $key=>$v}
<!--自定义内容列表页拓展钩子,可自定义内容页插入内如，如每多少条内容显示一条广告-->

<div class="articleItem dask-list-item dask-article-row">
    <div class="dask-list-item__body dask-article-row__body">
            <div class="n-title dask-article-row__title">
                <em class="time dask-article-row__time">{:date_friendly($v['create_time'])}</em>
                
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
            {if $v['topics']}
            <div class="dask-list-topics ms-auto">
                {volist name="$v['topics']" id="topic" length="3"}
                <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank"><em class="tag">{$topic.title}</em></a>
                {/volist}
            </div>
            {/if}
    </div>
</div>
{/foreach}
{$page|raw}
{else/}
<p class="text-center text-muted dask-card__body">
    <img src="{$assetBase}common/image/empty.svg">
    <span class="d-block">{:L('暂无内容')}</span>
</p>
{/if}