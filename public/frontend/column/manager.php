{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            <div class="dask-left col-md-9 col-sm-12 px-0">
                <div class="dask-ui-nav-container clearfix bg-white px-3">
                    <ul class="dask-pjax-tabs nav nav-tabs nav-tabs-block">
                        <li class="nav-item"><a class="nav-link {if $type=='recommend'}active{/if}" data-pjax="tabMain" href="{:url('column/manager',['type'=>'recommend','column_id'=>$column_id])}">{:L('推荐收录')}</a></li>
                        <!--<li class="nav-item"><a class="nav-link {if $type=='users'}active{/if}" data-pjax="tabMain" href="{:url('column/manager',['type'=>'users','column_id'=>$column_id])}">{:L('签约用户')}</a></li>-->
                    </ul>
                </div>
                <div id="tabMain" class="bg-white">
                    {if $type=='recommend'}
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
                                    <a href="javascript:;" class="btn btn-outline-primary btn-sm px-3" data-action="column-recommend-save" data-id="{$v.id}">{:L('收录')}</a>

                                    <a href="javascript:;" class="btn btn-outline-danger btn-sm px-3 ms-2" data-action="column-recommend-unsave" data-id="{$v.id}">{:L('拒绝')}</a>
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
                    {/if}

                    {if $type=='users'}

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
                                <p class="mb-0 font-8 text-muted dask-one-line mt-2">{$user_info['signature']}</p>
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
<script>
    function saveColumnRecommendArticle(articleId)
    {
        layer.confirm("{:L('是否收录该文章')}",{
            btn: ["{:L('确认')}", "{:L('取消')}"]
        }, function () {
            Dask.api.post("{:url('ajax.column/collect_recommend')}",{
                article_id:articleId,
                column_id:"{$column_id}",
                status:1
            });
        }, function(){
            layer.closeAll();
        });

    }

    function unSaveColumnRecommendArticle(articleId)
    {
        layer.confirm("{:L('是否拒绝收录该文章')}",{
            btn: ["{:L('确认')}", "{:L('取消')}"]
        }, function () {
            Dask.api.post("{:url('ajax.column/collect_recommend')}",{
                article_id:articleId,
                column_id:"{$column_id}",
                status:2
            });
        }, function(){
            layer.closeAll();
        });

    }
</script>
{/block}
