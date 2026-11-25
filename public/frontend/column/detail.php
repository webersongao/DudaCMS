{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="bg-white pt-4 pb-3 mb-2">
        <div class="container htinfo">
            <div>
                <h4 class="mb-2">{$column_info.name}</h4>
                <p class="bh-text-gray">{$column_info.description|RAW}</p>
                <div class="columnInfo bh-text-muted mt-3">
                    <label>
                        <a href="{$column_info['user_info']['url']}"  class="dask-username avatar" data-id="{$column_info['user_info']['uid']}">
                            <img src="{:user_avatar_url($column_info['user_info']['avatar'] ?? '')}" alt="" class="rounded dask-user-img" style="width: 18px;height: 18px">
                        </a>
                        <a href="{$column_info['user_info']['url']}" class="dask-username name" data-id="{$column_info['user_info']['uid']}">{$column_info['user_info']['name']}</a>
                    </label>
                    .
                    <label>{:L('%s 篇文章',$column_info['post_count'])}</label>
                    .
                    <label>{:L('%s 人关注',$column_info['focus_count'])}</label>
                </div>
            </div>
            <div class="htfun mt-2">
                {if $user_id && $user_id!=$column_info.uid}
                <label>
                    <a data-action="user-focus" data-type="column" data-id="{$column_info.id}" href="javascript:;" class="px-4 btn btn-sm btn-primary {if !$focus}active ygz{/if} me-2">{if !$focus}{:L('已关注')}{else}+{:L('关注')}{/if}</a>
                </label>
                <label>
                    <a data-action="user-inbox" data-name="{$column_info['user_info']['nick_name']}" href="javascript:;" class="px-4 btn btn-outline-primary btn-sm">{:L('私信')}</a>
                </label>
                {/if}
                {if $user_id && $user_id==$column_info.uid}
                <label>
                    <a href="{:url('article/publish',['column_id'=>$column_info.id])}" target="_blank" class="px-4 btn btn-success btn-sm" >{:L('发布文章')}</a>
                </label>
                {/if}
                {if $user_id && $user_id!=$column_info.uid}
                <label class="float-end">
                    <a href="javascript:;" data-url="{:url('ajax.column/recommend_article',['column_id'=>$column_info.id])}" class="px-4 btn btn-danger btn-sm dask-ajax-open" >{:L('推荐文章')}</a>
                </label>
                {/if}

                {if $user_id && $user_id==$column_info.uid}
                <div class="dropdown d-inline-block ms-3">
                    <a href="javascript:;" data-bs-toggle="dropdown" class="btn btn-default btn-sm" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-h d-sm-inline-block text-muted"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm">
                        <div class="text-center d-block py-2" style="min-width: 100px">
                            <a href="{:url('column/manager',['column_id'=>$column_info.id])}" target="_blank" class="dropdown-item">{:L('管理专栏')}</a>
                        </div>
                    </div>
                </div>
                {/if}
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-between">
            <div class="dask-left radius col-md-9 bg-white mb-2">
                <ul class="dask-pjax-tabs nav nav-tabs nav-tabs-block text-align px-3">
                    <li class="nav-item"><a class="nav-link {if $sort=='column'}active{/if}" data-pjax="tabMain"  href="{:url('column/detail',['id'=>$column_info['id']])}">{:L('专栏文章')}</a></li>
                    <li class="nav-item"><a class="nav-link {if $sort=='other'}active{/if}" data-pjax="tabMain"  href="{:url('column/detail',['id'=>$column_info['id'],'sort'=>'other'])}">{:L('其他文章')}</a></li>
                </ul>
                <div class="tab-content bg-white" id="tabMain">
                    <div class="tab-pane fade show active">
                        <div class="dask-common-list">
                            {if !empty($list)}
                            {foreach $list as $key=>$v}
                            <div class="dask-list-item">
                                <div class="dask-list-item__body">
                                    <div class="n-title">
                                        
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
                                            {if $v['topics']}
                                            <div class="tags">
                                                {volist name="$v['topics']" id="topic"}
                                                <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank"><em class="tag">{$topic.title}</em></a>
                                                {/volist}
                                            </div>
                                            {/if}
                                        </div>
                                        {else/}
                                        <div class="dask-three-line">
                                            {$v.message|raw}
                                        </div>
                                        {if $v['topics']}
                                        <div class="tags">
                                            {volist name="$v['topics']" id="topic"}
                                            <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank"><em class="tag">{$topic.title}</em></a>
                                            {/volist}
                                        </div>
                                        {/if}
                                        {/if}
                                    </div>
                                </div>
                                <div class="dask-list-item__actions">
                                    <div>
                                        <label class="dz">
                                            <a type="button" class="btn btn-primary btn-sm" data-action="user-agree" data-type="article" data-id="{$v['id']}"><i class="iconfont">&#xe870;</i>{:L('赞同')}{if ($v['agree_count'] > 0)}<span class="badge">{$v['agree_count']}</span>{/if}</a>
                                        </label>
                                        <label class="ms-2 me-2"><i class="iconfont">&#xe640;</i> {:L('浏览')}{$v['view_count']?:''}</label>
                                        <label class="me-2"><i class="iconfont">&#xe601;</i> {:L('评论')}{$v['comment_count']}</label>
                                        <label class="me-2"><i class="iconfont">&#xe699;</i><a href="{$v['user_info']['url']}" class="dask-username text-muted name" data-id="{$v['user_info']['uid']}">{$v['user_info']['name']}</a></label>
                                        <label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label>
                                    </div>

                                    {if $user_id && !$v['column_id'] && (isNormalAdmin() || isSuperAdmin() || $user_id==$column_info['uid'])}
                                    <div class="ms-auto">
                                        <a href="javascript:;" class="btn btn-outline-primary btn-sm px-3" data-action="user-save-column-article" data-article-id="{$v.id}" data-column-id="{$column_info.id}">{:L('收录至专栏')}</a>
                                    </div>
                                    {/if}

                                    {if $v['column_id'] && $sort=='other'}
                                    <div class="ms-auto">
                                        <a href="javascript:;" class="btn btn-outline-primary btn-sm px-3">{:L('已收录其他专栏')}</a>
                                    </div>
                                    {/if}
                                </div>
                            </div>
                            {/foreach}
                            {$page|raw}
                            {else/}
                            <p class="text-center py-3 text-muted">
                                <img src="{$assetBase}common/image/empty.svg">
                                <span class="d-block">{:L('暂无内容')}</span>
                            </p>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>

            <div class="dask-right radius col-md-3 px-xs-0">
                <!--侧边栏顶部钩子-->
                

                {:widget('sidebar/hotColumn',['uid'=>$user_id,'sort'=>'hot'])}

                <!--侧边栏底部钩子-->
                
            </div>
        </div>
    </div>
</div>
{/block}