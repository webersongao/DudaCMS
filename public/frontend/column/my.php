{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            <div class="dask-left col-md-9 col-sm-12 px-0">
                <div class="dask-ui-nav-container clearfix bg-white px-3">
                    <ul class="dask-pjax-tabs nav nav-tabs nav-tabs-block">
                        <li class="nav-item"><a class="nav-link {if $verify==1}active{/if}" data-pjax="tabMain" href="{:url('column/my',['verify'=>1])}">{:L('已审核')}</a></li>
                        <li class="nav-item"><a class="nav-link {if $verify==0}active{/if}" data-pjax="tabMain" href="{:url('column/my',['verify'=>0])}">{:L('待审核')}</a></li>
                        <li class="nav-item"><a class="nav-link {if $verify==2}active{/if}" data-pjax="tabMain" href="{:url('column/my',['verify'=>2])}">{:L('已拒绝')}</a></li>
                    </ul>
                </div>
                <div id="tabMain" class="p-3 bg-white">
                    {if !empty($list)}
                    <div class="my-column-list">
                        {volist name="list" id="v"}
                        <div class="column-item border-bottom">
                            <div class="dask-hcard py-2">
                                <div class="dask-hcard__media">
                                    <a href="{:url('column/detail',['id'=>$v['id']])}">
                                        <img src="{$v.cover}" alt="" width="70" height="70" class="rounded">
                                    </a>
                                </div>
                                <div class="dask-hcard__body">
                                    <h3 class="dask-one-line font-10 mb-0"> <a href="{:url('column/detail',['id'=>$v['id']])}">{$v.name}</a></h3>
                                    <p class="text-color-info my-1 font-9 dask-one-line">{$v.description}</p>
                                    <div class="d-flex justify-content-between align-items-center font-9">
                                        <div>
                                            <a href="javascript:;" class="text-color-info"> {$v.post_count|num2string}
                                                {:L('内容')} </a><span class="text-color-info"> | </span>
                                            <a href="javascript:;" class="text-color-info"> {$v.focus_count|num2string}
                                                {:L('关注')} </a>
                                        </div>

                                        <div class="font-9">
                                            <div class="dropdown show d-inline-block">
                                                <a href="javascript:;" class="d-none-arrow text-color-info" data-bs-toggle="dropdown" ><i class="icon-more-horizontal"></i></a>
                                                <div class="dropdown-menu detail-more-dropdown text-center">
                                                    <span class="arrow"></span>
                                                    {if $verify==1}
                                                    <a href="{:url('article/publish',['column_id'=>$v.id])}" target="_blank" class="dropdown-item">{:L('发文')}</a>
                                                    {/if}
                                                    <a href="{:url('column/apply',['id'=>$v.id])}" class="dropdown-item">{:L('编辑')}</a>
                                                    <a href="{:url('column/manager',['column_id'=>$v.id])}" class="dropdown-item">{:L('管理')}</a>
                                                    <a href="javascript:;" data-url="{:url('ajax.column/delete',['id'=>$v.id])}" class="dropdown-item">{:L('删除')}</a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/volist}
                    </div>
                    {$page|raw}
                    {else/}
                    <p class="text-center p-3 text-color-info">
                        <img src="{$assetBase}common/image/empty.svg">
                        <span class="mt-3 d-block ">{:L('暂无记录')}</span>
                    </p>
                    {/if}
                </div>
            </div>
            <div class="dask-right col-md-3 col-sm-12">
                <!--侧边栏顶部钩子-->
                

                <div class="dask-mod mb-2">
                    <div class="dask-mod-body">
                        <div class="dask-hcard border-bottom bg-white p-3">
                            <div class="dask-hcard__media">
                                <a href="{$user_info['url']}">
                                    <img src="{:user_avatar_url($user_info['avatar'] ?? '')}" width="45" height="45">
                                </a>
                            </div>
                            <div class="dask-hcard__body">
                                <a href="{$user_info['url']}" class="d-block">
                                    <strong>{$user_info['name']}</strong>
                                </a>
                                <p class="mb-0 font-8 text-muted dask-one-line mt-2">{$user_info['signature']|default=L('这家伙还没有留下自我介绍～')}</p>
                            </div>
                        </div>
                        <div class="d-flex text-center bg-white p-3 text-muted">
                            <dl class="flex-fill mb-0">
                                <dt>{$user_info['answer_count']}</dt>
                                <dd>{:L('回答')}</dd>
                            </dl>
                            <dl class="flex-fill mb-0">
                                <dt>{$user_info['article_count']}</dt>
                                <dd>{:L('文章')}</dd>
                            </dl>
                            <dl class="flex-fill mb-0">
                                <dt>{$user_info['question_count']}</dt>
                                <dd>{:L('问题')}</dd>
                            </dl>
                        </div>
                        <div class="d-flex bg-white p-3 border-top dask-btn-pair">
                            <a href="{:url('column/apply')}" class="btn btn-sm btn-primary">{:L('申请专栏')}</a>
                            <a href="{:url('column/my')}" class="btn btn-sm btn-outline-primary">{:L('我的专栏')}</a>
                        </div>
                    </div>
                </div>
                {:widget('sidebar/hotColumn',['uid'=>$user_id,'sort'=>'hot'])}
                <!--侧边栏底部钩子-->
                
            </div>
        </div>
    </div>
</div>
{/block}
