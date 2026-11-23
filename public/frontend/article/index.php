{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2 dask-page">
    {if $setting.enable_category=='Y'}
    {:widget('common/category',['type'=>'article','category'=>$category,'show_type'=>'list'])}
    {/if}
    <div class="container">
        <div class="row justify-content-between dask-grid">
            <div class="dask-left radius col-md-9 bg-white mb-2 dask-main dask-card">
                <div class="nav nav-tabs dask-pjax-a px-4 dask-card__head" role="tablist">
                    <a class="nav-item nav-link {if $sort=='recommend'}active{/if}" data-pjax="wrapMain" href="{:url('article/index',['sort'=>'recommend','category_id'=>$category])}">{:L('推荐')}</a>
                    <a class="nav-item nav-link {if $sort=='new'}active{/if}" data-pjax="wrapMain" href="{:url('article/index',['sort'=>'new','category_id'=>$category])}">{:L('最新')}</a>
                    <a class="nav-item nav-link {if $sort=='hot'}active{/if}" data-pjax="wrapMain" href="{:url('article/index',['sort'=>'hot','category_id'=>$category])}">{:L('热门')}</a>
                </div>

                <div id="tabMain" class="tab-content" >
                    <div class="tab-pane fade show active">
                        <div class="dask-common-list">
                            <div data-dask-list-items>
                            {we:article sort="$sort" category_id="$category"}
                            <!--自定义内容列表页拓展钩子,可自定义内容页插入内如，如每多少条内容显示一条广告-->
                            
                            <div class="dask-list-item articleItem dask-article-row">
                                <div class="dask-list-item__body dask-article-row__body">
                                    <div class="n-title dask-article-row__title">
                                        <em class="time dask-article-row__time">{:date_friendly($v['create_time'])}</em>
                                        <span class="tip-s2 badge text-bg-secondary">{:L('文章')}</span>
                                        
                                        {if $v.set_top}
                                        <span class="tip-d badge text-bg-secondary">{:L('顶')}</span>
                                        {/if}
                                        <a href="{:url('article/detail',['id'=>$v['id']])}" target="_blank">{$v['title']|raw}</a>
                                        
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
                                        <a type="button" href="javascript:;" class="btn btn-primary btn-sm" data-action="user-agree" data-type="article" data-id="{$v['id']}"><i class="iconfont">&#xe870;</i>{:L('赞同')}{if ($v['agree_count'] > 0)}<span class="badge">{$v['agree_count']}</span>{/if}</a>
                                    </label>
                                    <label class="ms-2 me-2"><i class="iconfont">&#xe640;</i> {:L('%s 浏览',$v.view_count)}</label>
                                    <label class="me-2"><i class="iconfont">&#xe601;</i> {:L('%s 评论',$v.comment_count)}</label>
                                    <label class="me-2"><i class="iconfont">&#xe699;</i><a href="{$v['user_info']['url']}" class="dask-username text-muted name" data-id="{$v['user_info']['uid']}">{$v['user_info']['name']}</a></label>
                                    {if isset($v['topics']) && !empty($v['topics'])}
                                    <div class="dask-list-topics ms-auto">
                                        {volist name="$v['topics']" id="topic" length="3"}
                                        <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank"><em class="tag">{$topic.title}</em></a>
                                        {/volist}
                                    </div>
                                    {/if}
                                </div>
                            </div>
                            {/we:article}
                            </div>
                            <div class="dask-ui-pager dask-card__foot">{$page|raw}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dask-right radius col-md-3 px-xs-0 dask-side dask-card">

                <!--侧边栏顶部钩子-->
                
                
                {if $theme_config['article']['sidebar_show_items'] && in_array('write_nav',$theme_config['article']['sidebar_show_items'])}
                {:widget('sidebar/writeNav')}
                {/if}
                
                {if $theme_config['article']['sidebar_show_items'] && in_array('announce',$theme_config['article']['sidebar_show_items'])}
                {:widget('sidebar/announce')}
                {/if}

                {if $theme_config['article']['sidebar_show_items'] && in_array('focus_topic',$theme_config['article']['sidebar_show_items'])}
                {:widget('sidebar/focusTopic',['uid'=>$user_id])}
                {/if}

                {if $theme_config['article']['sidebar_show_items'] && in_array('hot_topic',$theme_config['article']['sidebar_show_items'])}
                {:widget('sidebar/hotTopic',['uid'=>$user_id])}
                {/if}

                {if $theme_config['article']['sidebar_show_items'] && in_array('column',$theme_config['article']['sidebar_show_items'])}
                {:widget('sidebar/hotColumn',['uid'=>$user_id,'sort'=>'hot'])}
                {/if}

                {if $theme_config['article']['sidebar_show_items'] && in_array('hot_users',$theme_config['article']['sidebar_show_items'])}
                {:widget('sidebar/hotUsers',['uid'=>$user_id])}
                {/if}

                {if in_array('diy_content',$theme_config['article']['sidebar_show_items'])}
                {$theme_config['article']['sidebar_diy_content']|raw|htmlspecialchars_decode}
                {/if}

                <!--侧边栏底部钩子-->
                
            </div>
        </div>
    </div>
</div>
{/block}