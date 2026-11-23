{extend name="$theme_block" /}
{block name="main"}
<div class="container dask-page mt-2">
    <div class="row dask-grid justify-content-between">
        <div class="dask-left dask-main radius col-md-9 mb-2">
            <div class="nav nav-tabs ps-4 pe-4 dask-pjax-a bg-white" role="tablist">
                <a class="nav-item nav-link {if $sort=='credit'}active{/if}" data-pjax="tabMain" href="{:url('people/lists',['sort'=>'credit'])}">{:L('活跃榜')} </a>
            </div>
            <div id="tabMain" class="mt-2">
                <div class="plist people-grid">
                    {volist name="list" id="v"}
                    <div class="people-list">
                        <div class="dask-hcard bg-white dask-card">
                            <div class="dask-hcard__media mt-2" style="width: 80px">
                                <a href="{$v.url}" class="dask-username rounded d-block" data-id="{$v.uid}">
                                    <img src="{$v.avatar}" onerror="this.src='/static/common/image/default-avatar.svg'" alt="{$v.name}" width="80" height="80" style="border-radius: 50%">
                                </a>
                                {if $v['is_online']}
                                <span class="online-dot"></span>
                                {else/}
                                <span class="offline-dot"></span>
                                {/if}
                            </div>
                            <div class="dask-hcard__body">
                                <h3 class="mb-1">
                                    <a href="{$v.url}" class="dask-username font-12" data-id="{$v.uid}">{$v.name}</a>
                                </h3>
                                <p class="text-color-info font-9 mt-2 people-signature">{$v['signature']|default=L('这家伙还没有留下自我介绍～')}</p>
                                <div class="text-color-info mt-2 font-9 dask-stat-grid">
                                    <label class="text-muted">{:L($setting.credit_unit)}: {$v.credit}</label>
                                    <label class="text-muted">{:L('获赞')}: {$v.agree_count}</label>
                                </div>

                                <div class="dask-actions" style="right: 1rem;bottom: .7rem">
                                    {if $user_id && $v['uid']!=$user_id}
                                    <a class="{if $v.has_focus}active ygz{/if} px-4 btn btn-sm btn-primary" href="javascript:;" data-action="user-focus" data-type="user" data-id="{$v.uid}">{$v.has_focus ? L('已关注'): L('关注')}</a>
                                    <a class="px-4 btn btn-sm btn-outline-primary" href="javascript:;" data-action="user-inbox" data-name="{$v.nick_name}">{:L('私信')}</a>
                                    {else/}
                                    <button type="button" class="px-4 btn btn-sm btn-primary" disabled>{$v.has_focus ? L('已关注'): L('关注')}</button>
                                    <button type="button" class="px-4 btn btn-sm btn-outline-primary" disabled>{:L('私信')}</button>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                    {/volist}
                </div>
                {if $page}
                <div class="dask-ui-pager">{$page|raw}</div>
                {/if}
            </div>
        </div>
        <div class="dask-right dask-side radius col-md-3 px-xs-0">
            <!--侧边栏顶部钩子-->
            

            {if in_array('write_nav',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/writeNav')}
            {/if}

            {if in_array('focus_topic',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/focusTopic',['uid'=>$user_id])}
            {/if}

            {if in_array('hot_topic',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/hotTopic',['uid'=>$user_id])}
            {/if}

            {if in_array('column',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/hotColumn',['uid'=>$user_id,'sort'=>'hot'])}
            {/if}

            {if in_array('hot_users',get_theme_setting('home.sidebar_show_items'))}
            {:widget('sidebar/hotUsers',['uid'=>$user_id])}
            {/if}

            {if in_array('diy_content',get_theme_setting('home.sidebar_show_items'))}
            {$theme_config['home']['sidebar_diy_content']|raw|htmlspecialchars_decode}
            {/if}

            <!--侧边栏底部钩子-->
            
        </div>
    </div>
</div>
{/block}