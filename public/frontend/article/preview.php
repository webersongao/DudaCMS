{extend name="$theme_block" /}
{block name="main"}
<div class="dask-main-wrap mt-2">
    <div class="container">
        <div class="row">
            <div class="col-md-1 text-center actions">
                <label class="px-1 py-2 bg-white rounded d-block mb-2">
                    <a href="javascript:;">
                        <i class="icon-thumb_up font-12"></i>
                        {if ($article_info['agree_count'] > 0)}<span class="d-block">{$article_info['agree_count']}</span>{/if}
                    </a>
                </label>
            </div>
            <div class="col-md-8 px-0">
                <div class="bg-white p-3">
                    <article class="dask-article">
                        <h2 class="font-14 mb-3">{$article_info.title}</h2>
                        <div class="dask-author-info mb-3">
                            <div class="dask-user dask-hcard">
                                <div class="dask-hcard__media">
                                    <a href="{$user_info['url']}">
                                        <img alt="{$user_info['name']}" src="{$user_info['avatar']}" class="rounded" style="width: 40px;height: 40px">
                                    </a>
                                </div>
                                <div class="dask-hcard__body">
                                    <h6 class="mb-0"><a href="{$user_info['url']}">{$user_info['name']}</a></h6>
                                </div>
                                <div class="dask-hcard__extra text-muted">
                                    <span>{:L('人点赞')}{$article_info.agree_count}</span> · {:L('次浏览')}{$article_info.view_count}
                                </div>
                            </div>
                        </div>
                        <div class="dask-content mt-3">
                            {$article_info.message|raw}
                        </div>
                    </article>
                    
                    <div class="dask-article-bottom overflow-hidden mt-4 text-muted py-1">
                        <p class="float-start publish-info ">{:L('发布于')} {:date_friendly($article_info['create_time'])}</p>
                        {if isset($article_info['topics']) && !empty($article_info['topics'])}
                        <div class="page-detail-topic float-end">
                            {if !empty($article_info['topics'])}
                            {volist name="article_info['topics']" id="v"}
                            <a href="{:url('topic/detail',['id'=>$v['id']])}" class="dask-topic" data-id="{$v.id}"><em class="tag">{$v.title}</em></a>
                            {/volist}
                            {/if}
                        </div>
                        {/if}
                    </div>
                    <div class="text-muted font-8 mt-2">
                        <p>
                            <i class="icon-info-with-circle"></i> {:L('本文由')} <a href="{$user_info['url']}">{$user_info['name']}</a>
                            {:L('原创发布于')} <a href="{$baseUrl}">{$setting['site_name']}</a> ，{:L('著作权归作者所有')}。
                        </p>
                    </div>
                </div>

                {if $article_info.comment_count}
                <div class="dask-mod bg-white px-3 mt-2 pt-3">
                    <div class="dask-mod-head mb-0">
                        <p class="mod-head-title">{:L('全部')} <span class="dask-answer-count">{$article_info.comment_count}</span>{:L('条评论')}</p>
                        <div class="mod-head-more dropdown show">
                            <a href="javascript:;" class="dropdown-toggle d-none-arrow dask-sort-show" data-bs-toggle="dropdown">
                                <span>{:L('默认排序')}</span> <i class="icon-select-arrows"></i>
                            </a>
                            <div class="dropdown-menu text-center dask-answer-sort-dropdown">
                                <span class="arrow"></span>
                                <div class="dask-nav dask-ui-dropdown-nav text-center dask-answer-sort">
                                    <div class="active py-1" data-type="new"><a href="JavaScript:;">{:L('默认排序')}</a> </div>
                                    <div class="py-1" data-type="hot"><a href="JavaScript:;">{:L('热门排序')} </a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="post-comments post" style="padding: 0;box-shadow: none">
                    <div id="article-comment-list"  data-url="{:url('ajax.article/get_ajax_comment',['article_id'=>$article_info['id']])}"></div>
                </div>
                {/if}
            </div>
            <div class="col-md-3">
                {:widget('sidebar/hotTopic',['uid'=>$user_id])}
            </div>
        </div>
    </div>
</div>
{/block}