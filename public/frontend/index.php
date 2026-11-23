{extend name="$theme_block" /}
{block name="main"}
<div class="container mt-2 dask-page" data-page-module="home">
    <div class="row justify-content-between dask-grid">
        <div class="dask-left radius col-md-9 bg-white mb-2 dask-main dask-card">
            <div class="nav nav-tabs px-4 dask-pjax-a dask-card__head" role="tablist">
                <a class="nav-item nav-link {if $sort=='recommend' && !$type}active{/if}" data-pjax="tabMain" href="{:url('index/index',['sort'=>'recommend'])}">{:L('推荐')}</a>
                <a class="nav-item nav-link {if $sort=='new' && !$type}active{/if}" data-pjax="tabMain" href="{:url('index/index',['sort'=>'new'])}">{:L('最新')}</a>
                <a class="nav-item nav-link {if $sort=='hot' && !$type}active{/if}" data-pjax="tabMain" href="{:url('index/index',['sort'=>'hot'])}">{:L('热门')}</a>
                {if $user_id}
                <a class="nav-item nav-link {if $sort=='focus'}active{/if}" data-pjax="tabMain" href="{:url('index/index',['sort'=>'focus'])}">{:L('关注')}</a>
                {/if}
                <a class="nav-item nav-link {if $sort=='unresponsive' && !$type}active{/if}" data-pjax="tabMain" href="{:url('index/index',['sort'=>'unresponsive'])}">{:L('待回答')}</a>
                {volist name=":config('dask.tabs')" id="v"}
                <a class="nav-link nav-item {if $type==$key}active{/if}" href="{:url('index/index',['sort'=>'new','type'=>$key])}" data-pjax="tabMain">{$v.title}</a>
                {/volist}
            </div>
            <div class="tab-content" id="tabMain">
                <div class="tab-pane fade show active">
                    <div class="dask-common-list">
                        <div data-dask-list-items>{:widget('common/lists',['sort'=>$sort,'item_type'=>$type])}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dask-right radius col-md-3 px-xs-0 dask-side dask-card">
            <!--侧边栏顶部钩子-->
            

            {if get_theme_setting('home.sidebar_show_items') && in_array('write_nav',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/writeNav')}
            {/if}

            {if get_theme_setting('home.sidebar_show_items') && in_array('announce',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/announce')}
            {/if}

            {if get_theme_setting('home.sidebar_show_items') && in_array('focus_topic',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/focusTopic',['uid'=>$user_id])}
            {/if}

            {if get_theme_setting('home.sidebar_show_items') && in_array('hot_topic',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/hotTopic',['uid'=>$user_id])}
            {/if}

            {if get_theme_setting('home.sidebar_show_items') && in_array('column',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/hotColumn',['uid'=>$user_id,'sort'=>'hot'])}
            {/if}

            {if get_theme_setting('home.sidebar_show_items') && in_array('hot_users',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/hotUsers',['uid'=>$user_id])}
            {/if}

            {if get_theme_setting('home.sidebar_show_items') && in_array('diy_content',get_theme_setting('home.sidebar_show_items'))}
            {$theme_config['home']['sidebar_diy_content']|raw|htmlspecialchars_decode}
            {/if}

            {if get_theme_setting('home.sidebar_show_items') && in_array('side_footer',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/sideFooter')}
            {/if}

            <!--侧边栏底部钩子-->
            
        </div>
    </div>
</div>

<!--友情链接小部件-->
{:widget('common/links')}

{/block}