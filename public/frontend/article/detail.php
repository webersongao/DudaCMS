{extend name="$theme_block" /}
{block name="meta_script"}
{if $theme_config['common']['enable_mathjax']=='Y'}
<style>
    .MathJax{outline:0;text-align: unset !important;}
</style>
<script async src="{$assetBase}common/js/tex-mml-chtml.js"></script>
{/if}
{/block}
{block name="main"}
<div class="dask-wrap dask-page mt-2">
    <div class="container">
        <div class="row dask-grid">
            <div class="col-md-1 text-center d-xs-none actions">
                <label class="px-1 py-2 bg-white rounded d-block mb-2">
                    <a href="javascript:;" class="dask-ajax-agree {$article_info['vote_value']==1 ? 'active' : ''}" data-action="user-agree" data-type="article" data-id="{$article_info.id}">
                        <i class="icon-thumb_up font-12"></i>
                        {if ($article_info['agree_count'] > 0)}<span class="d-block">{$article_info['agree_count']}</span>{/if}
                    </a>
                </label>
                {if $user_id}
                {if $user_id!=$article_info['uid']}
                <label class="px-1 py-2 bg-white rounded d-block">
                    <a href="javascript:;"  style="color: #76839b" class="fw-bold dask-ajax-against {$article_info['vote_value']==-1 ? 'active' : ''}" data-action="user-against" data-type="article" data-id="{$article_info.id}">
                        <i class="icon-thumb_down font-12"></i>
                        <span class="d-block">{:L('反对')}</span>
                    </a>
                </label>
                <label class="px-1 py-2 bg-white rounded d-block"  data-action="user-report" data-type="article" data-id="{$article_info.id}">
                    <a href="javascript:;"  style="color: #76839b" class="fw-bold">
                        <i class="icon-warning font-12"></i>
                        <span class="d-block">{if $article_info['is_report']}{:L('已举报')}{else/}{:L('举报')}{/if}</span>
                    </a>
                </label>
                {/if}
                <label class="px-1 py-2 bg-white rounded d-block" data-action="user-favorite" data-type="article" data-id="{$article_info.id}">
                    <a href="javascript:;"  style="color: #76839b" class="fw-bold">
                        <i class="icon-star-outlined font-12"></i>
                        <span class="d-block">{if $article_info['is_favorite']}{:L('已收藏')}{else/}{:L('收藏')}{/if}</span>
                    </a>
                </label>
                {/if}

                <div class="dropdown px-1 py-2 bg-white rounded d-block mb-2">
                    <a href="javascript:;" style="color: #76839b" class="fw-bold" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-share"></i>
                        <span class="d-block">{:L('分享')}</span>
                    </a>
                    <div class="dropdown-menu p-0 border-0 font-size-sm">

                        <div class="text-center d-block py-2" style="min-width: 100px">
<a href="javascript:;"  class="dropdown-item dask-clipboard" data-clipboard-text="{:url('article/detail',['id'=>$article_info.id],true,true)}"><i class="icon-link"></i>
                                {:L('复制链接')}</a>
                            <a href="javascript:;" data-action="user-share" data-title="{$article_info.title}" data-url="{:url('article/detail',['id'=>$article_info.id],true,true)}" data-type="qzone" class="dropdown-item "><i class="fab fa-qq text-primary"></i>
                                {:L('QQ空间')}</a>
                            <a href="javascript:;" data-action="user-share" data-title="{$article_info.title}" data-url="{:url('article/detail',['id'=>$article_info.id],true,true)}" data-type="weibo" class="dropdown-item "><i class="fab fa-weibo text-warning"></i>
                                {:L('新浪微博')}</a>
<a href="javascript:;" data-action="user-share" data-title="{$article_info.title}" data-url="{:url('article/detail',['id'=>$article_info.id],true,true)}" data-type="twitter" class="dropdown-item"><i class="fab fa-twitter" style="color:#1DA1F2"></i> Twitter/X</a>
<a href="javascript:;" data-action="user-share" data-title="{$article_info.title}" data-url="{:url('article/detail',['id'=>$article_info.id],true,true)}" data-type="telegram" class="dropdown-item"><i class="fab fa-telegram" style="color:#229ED9"></i> Telegram</a>
                            
                            <div class="dask-qrcode-container" data-share="{:url('article/detail',['id'=>$article_info.id],true,true)}">
                                <a href="javascript:;" class="dropdown-item "><i class="fab fa-weixin text-success"></i>
                                    {:L('微信扫一扫')}</a>
                                <div class="dask-qrcode text-center py-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {if $user_id && ($user_id==$article_info['uid'] || isSuperAdmin() || isNormalAdmin())}
                <div class="dropdown px-1 py-2 bg-white rounded d-block mb-2">
                    <a href="javascript:;" class="fw-bold" style="color: #76839b" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-more-horizontal"></i>
                        <span class="d-block">{:L('更多')}</span>
                    </a>
                    <div class="dropdown-menu p-0 border-0 font-size-sm">
                        <div class="text-center d-block py-2" style="min-width: 100px">
                            {if get_user_permission('recommend_post')=='Y'}
                            <a href="javascript:;" class="ajax-get py-1 text-muted dropdown-item" data-url="{:url('ajax.article/action',['type'=>'recommend','is_recommend'=>$article_info['is_recommend'],'article_id'=>$article_info['id']])}">
                                <span>{$article_info['is_recommend'] ? L('取消推荐') : L('推荐文章')}</span>
                            </a>
                            {/if}
                            {if get_user_permission('set_top_post')=='Y'}
                            <a href="javascript:;" class="ajax-get py-1 text-muted dropdown-item" data-url="{:url('ajax.article/action',['type'=>'set_top','set_top'=>$article_info['set_top'],'article_id'=>$article_info['id']])}">
                                <span>{$article_info['set_top'] ? L('取消置顶') : L('置顶文章')}</span>
                            </a>
                            {/if}

                            {if $user_id && (get_user_permission('modify_article')=='Y' || $user_info['uid']==$article_info['uid'])}
                            <a href="{:url('article/publish',['id'=>$article_info['id']])}" class="py-1 text-muted dropdown-item" target="_blank">
                                <span>{:L('编辑文章')}</span>
                            </a>
                            {/if}
                            {if ($article_info.comment_count < 1) && $user_id && (get_user_permission('remove_article')=='Y' || $user_info['uid']==$article_info['uid'])}
                            <a href="javascript:;" class="ajax-get py-1 text-muted dropdown-item" data-confirm="{:L('确定要删除吗')}？" data-url="{:url('ajax.article/remove_article',['id'=>$article_info['id']])}">
                                <span>{:L('删除文章')}</span>
                            </a>

                            {if !$article_info['column_id'] && ($user_info['uid']==$article_info['uid'] || isNormalAdmin() || isSuperAdmin())}
                            <a href="javascript:;" class="dask-ajax-open py-1 text-muted dropdown-item" data-title="{:L('收录至专栏')}" data-url="{:url('ajax.column/collect',['id'=>$article_info['id']])}">
                                <span>{:L('收录至专栏')}</span>
                            </a>
                            {/if}

                            {if isSuperAdmin() || isNormalAdmin()}
                            <a class="dask-ajax-open py-1 text-muted dropdown-item" data-title="{:L('添加到帮助')}" href="javascript:;" data-url="{:url('ajax.Help/select_chapter',['item_id'=>$article_info['id'],'item_type'=>'article'])}">
                                <span>{:L('添加到帮助')}</span>
                            </a>
                            {/if}

                            {/if}
                        </div>
                    </div>
                </div>
                {/if}

                <!--自定义文章页左侧拓展钩子-->
                

            </div>
            <div class="col-md-8 dask-main px-0 col-sm-12 mb-1">
                <div class="bg-white p-3 dask-article-wrap rounded">
                    <!--文章内容主页面 详情顶部钩子-->
                    
                    
                    <article class="dask-article">
                        <h2 class="font-14 mb-3">{if $article_info.set_top}
                            <i class="iconfont icon-zhiding text-warning font-14"></i>{/if}{:htmlspecialchars_decode($article_info.title)}
                            
                        </h2>
                        <div class="dask-author-info mb-3">
                            <div class="dask-user">
                                <div class="dask-hcard">
                                    <div class="dask-hcard__media">
                                        <a href="{$article_info['user_info']['url']}" class="dask-username" data-id="{$article_info.uid}">
                                            <img alt="{$article_info['user_info']['nick_name']}" src="{:user_avatar_url($article_info['user_info']['avatar'] ?? '')}" class="circle" style="width: 45px;height: 45px">
                                        </a>
                                    </div>
                                    <div class="dask-hcard__body">
                                        <h6 class="mb-0">
                                            <a href="{$article_info['user_info']['url']}" class="dask-username" data-id="{$article_info.uid}">{$article_info['user_info']['nick_name']}</a>
                                            <small class="text-muted d-block mt-2">{$article_info['user_info']['signature']|default=L('这家伙很懒，还没有设置简介')}</small>
                                        </h6>
                                    </div>
                                    <div class="dask-hcard__extra text-muted"><span>{$article_info.agree_count}</span>&nbsp;{:L('人点赞')} · {:L('%s 次浏览',$article_info.view_count)}</div>
                                </div>
                            </div>
                        </div>
                        <div class="dask-content mt-3">
                            {$article_info.message|raw}
                        </div>
                    </article>

                    {if $attach_list}
                    <div class="bg-info-light dask-attach-list my-3 p-3">
                        {volist name="attach_list" id="v"}
                        <div class="dask-hcard py-1">
                            <div class="dask-hcard__body">
                                <p class="mb-1">{$v.name}</p>
                                <p class="text-muted font-8 mb-0">{:formatBytes($v.size)}</p>
                            </div>
                            <div class="dask-hcard__extra">
                                <a href="javascript:;" class="btn btn-primary btn-sm attach-download" data-name="{$v.auth_key}">{:L('下载')}</a>
                            </div>
                        </div>
                        {/volist}
                    </div>
                    {/if}

                    <!--文章内容主页面 详情底部钩子-->
                    

                    

                    <div class="dask-article-bottom overflow-hidden mt-2 mb-2 text-muted py-1">
                        <p class="float-start publish-info ">{:L('发布于')} {:date_friendly($article_info['create_time'])}</p>
                        <div class="float-end">
                            <ul class="page-detail-topic d-inline" id="awTopicList">
                                {if !empty($article_info['topics'])}
                                {volist name="article_info['topics']" id="v"}
                                <li class="d-inline-block dask-tag"><a href="{:url('topic/detail',['id'=>$v['id']])}" class="dask-topic" data-id="{$v.id}"><em class="tag">{$v.title}</em></a></li>
                                {/volist}
                                {/if}
                            </ul>
                            {if $user_id && (isSuperAdmin() || isNormalAdmin() || get_user_permission('edit_content_topic')=='Y' || $user_id == $article_info.uid)}
                            <a href="javascript:;" data-width="600px" class="dask-ajax-open d-inline" data-url="{:url('topic/select',['item_type'=>'article','item_id'=>$article_info['id']])}" data-title="{:L('编辑话题')}"><i class="icon-edit1"> </i></a>
                            {/if}
                        </div>
                    </div>
                    <div class="actions dask-actions d-sm-none">
                        <label class="me-3 mb-0">
                            <a href="javascript:;" class="{$article_info['vote_value']==1 ? 'active' : ''} dask-ajax-agree" data-action="user-agree" data-type="article" data-id="{$article_info.id}">
                                <i class="icon-thumb_up"></i> {:L('赞同')} <span class="agree-count">{$article_info['agree_count'] ? $article_info['agree_count'] : ''}</span>
                            </a>
                        </label>

                        <div class="dropdown d-inline-block me-3">
                            <a href="javascript:;"  class="fw-bold" style="color: #76839b" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-share"></i> {:L('分享')}
                            </a>
                            <div class="dropdown-menu p-0 border-0 font-size-sm">

                                <div class="text-start d-block py-2" style="min-width: 100px">
<a href="javascript:;"  class="dropdown-item dask-clipboard" data-clipboard-text="{:url('article/detail',['id'=>$article_info.id],true,true)}"><i class="icon-link"></i>
                                        {:L('复制链接')}</a>
                                    <a href="javascript:;" data-action="user-share" data-title="{$article_info.title}" data-url="{:url('article/detail',['id'=>$article_info.id],true,true)}" data-type="qzone" class="dropdown-item "><i class="fab fa-qq text-primary"></i>
                                        {:L('QQ空间')}</a>
                                    <a href="javascript:;" data-action="user-share" data-title="{$article_info.title}" data-url="{:url('article/detail',['id'=>$article_info.id],true,true)}" data-type="weibo" class="dropdown-item "><i class="fab fa-weibo text-warning"></i>
                                        {:L('新浪微博')}</a>
<a href="javascript:;" data-action="user-share" data-title="{$article_info.title}" data-url="{:url('article/detail',['id'=>$article_info.id],true,true)}" data-type="twitter" class="dropdown-item"><i class="fab fa-twitter" style="color:#1DA1F2"></i> Twitter/X</a>
<a href="javascript:;" data-action="user-share" data-title="{$article_info.title}" data-url="{:url('article/detail',['id'=>$article_info.id],true,true)}" data-type="telegram" class="dropdown-item"><i class="fab fa-telegram" style="color:#229ED9"></i> Telegram</a>
                                    
                                    <div class="dask-qrcode-container" data-share="{:url('article/detail',['id'=>$article_info.id],true,true)}">
                                        <a href="javascript:;" class="dropdown-item "><i class="fab fa-weixin text-success"></i>
                                            {:L('微信扫一扫')}</a>
                                        <div class="dask-qrcode text-center py-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {if $user_id}
                        {if $user_id!=$article_info['uid']}
                        <label class="me-3 mb-0">
                            <a href="javascript:;" class="dask-ajax-against {$article_info['vote_value']==-1 ? 'active' : ''}" data-action="user-against" data-type="article" data-id="{$article_info.id}">
                                <i class="icon-thumb_down"></i> <span>{:L('反对')}</span>
                            </a>
                        </label>
                        <label class="me-3 mb-0"  data-action="user-report" data-type="article" data-id="{$article_info.id}">
                            <a href="javascript:;">
                                <i class="icon-warning"></i> <span>{if $article_info['is_report']}{:L('已举报')}{else/}{:L('举报')}{/if}</span>
                            </a>
                        </label>
                        {/if}
                        <label class="me-3 mb-0" data-action="user-favorite" data-type="article" data-id="{$article_info.id}">
                            <a href="javascript:;">
                                <i class="icon-star-outlined"></i> <span>{if $article_info['is_favorite']}{:L('已收藏')}{else/}{:L('收藏')}{/if}</span>
                            </a>
                        </label>
                        {/if}
                        {if $user_id && ($user_info['uid']==$article_info['uid'] || isSuperAdmin() || isNormalAdmin())}
                        <div class="dropdown px-1 py-2 bg-white rounded d-inline-block mb-2">
                            <a href="javascript:;" class="fw-bold" style="color: #76839b" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-more-horizontal"></i> <span class="d-inline-block">{:L('更多')}</span>
                            </a>
                            <div class="dropdown-menu p-0 border-0 font-size-sm">
                                <div class="text-center d-block py-2" style="min-width: 100px">
                                    {if get_user_permission('recommend_post')=='Y'}
                                    <a href="javascript:;" class="ajax-get py-1 text-muted dropdown-item" data-url="{:url('ajax.article/action',['type'=>'recommend','is_recommend'=>$article_info['is_recommend'],'article_id'=>$article_info['id']])}">
                                        <span>{$article_info['is_recommend'] ? L('取消推荐') : L('推荐文章')}</span>
                                    </a>
                                    {/if}
                                    {if get_user_permission('set_top_post')=='Y'}
                                    <a href="javascript:;" class="ajax-get py-1 text-muted dropdown-item" data-url="{:url('ajax.article/action',['type'=>'set_top','set_top'=>$article_info['set_top'],'article_id'=>$article_info['id']])}">
                                        <span>{$article_info['set_top'] ? L('取消置顶') : L('置顶文章')}</span>
                                    </a>
                                    {/if}

                                    {if $user_id && (get_user_permission('modify_article') || $user_info['uid']==$article_info['uid'])}
                                    <a href="{:url('article/publish',['id'=>$article_info['id']])}" class=" py-1 text-muted dropdown-item" target="_blank">
                                        <span>{:L('编辑文章')}</span>
                                    </a>
                                    {/if}
                                    {if ($article_info.comment_count < 1) && $user_id && (get_user_permission('remove_article') || $user_info['uid']==$article_info['uid'])}
                                    <a href="javascript:;" class="ajax-get py-1 text-muted dropdown-item" data-confirm="{:L('确定要删除吗')}?" data-url="{:url('ajax.article/remove_article',['id'=>$article_info['id']])}">
                                        <span>{:L('删除文章')}</span>
                                    </a>
                                    {/if}

                                    {if !$article_info['column_id'] && ($user_info['uid']==$article_info['uid'] || isNormalAdmin() || isSuperAdmin())}
                                    <a href="javascript:;" class="dask-ajax-open py-1 text-muted dropdown-item" data-title="{:L('收录至专栏')}" data-url="{:url('ajax.column/collect',['id'=>$article_info['id']])}">
                                        <span>{:L('收录至专栏')}</span>
                                    </a>
                                    {/if}

                                    {if isSuperAdmin() || isNormalAdmin()}
                                    <a class="dask-ajax-open py-1 text-muted dropdown-item" data-title="{:L('添加到帮助')}" href="javascript:;" data-url="{:url('ajax.Help/select_chapter',['item_id'=>$article_info['id'],'item_type'=>'article'])}">
                                        <span>{:L('添加到帮助')}</span>
                                    </a>
                                    {/if}
                                </div>
                            </div>
                        </div>
                        {/if}
                    </div>
                    <div class="font-9 mt-2 p-3 bg-light">
                        <h6 class="text-danger fw-bold font-11">{:L('免责声明')}:</h6>
                        <p class="text-muted">{:L('本文由')} <a href="{$article_info['user_info']['url']}">{$article_info['user_info']['nick_name']}</a>
                            {:L('原创发布于')} <a href="{$baseUrl}">{$setting['site_name']}</a> ，{:L('著作权归作者所有')}。</p>
                    </div>
                </div>
                <div class="bg-white mt-2 p-3 dask-article-comment-editor rounded">
                    {if $user_id}
                    <form method="post" action="{:url('comment/save_article_comment')}">
                        <input type="hidden" name="article_id" value="{$article_info.id}">
                        <input type="hidden" name="token" value="{:csrf_token()}" />
                        <input type="hidden" name="at_info" value="">
                        <input type="hidden" name="pid" value="0">
                        <textarea type="text" name="message" class="form-control commentMessage" rows="6" placeholder="{:L('写下您的评论吧')}..."></textarea>
                        <div class="overflow-hidden mt-3">
                            <div class="float-start dask-username" data-id="{$user_info.uid}">
                                <img src="{:user_avatar_url($user_info['avatar'] ?? '')}" alt="{$user_info['nick_name']}" class="rounded" style="width: 16px;height: 16px">
                                <span>{$user_info['nick_name']}</span>
                            </div>
                            <div class="float-end">
                                <button type="button" class="dask-article-comment-submit btn btn-primary btn-sm">{:L('发布')}</button>
                            </div>
                        </div>
                    </form>
                    {else/}
                    <p class="text-center">{:L('贡献精彩回答，参与评论互动，更多精彩内容等你去发现')}</p>
                    <p class="text-center mt-2">{:L('去')} <a href="javascript:;" data-action="user-login" class="me-1 text-primary">{:L('登录')}</a>!
                        {:L('还没有账号?')}{:L('去')}<a <a href="javascript:;" data-action="user-login" class="text-primary">{:L('注册')}</a></p>
                    {/if}
                </div>
                <div id="comment-container" class="rounded">
                    <div class="dask-mod bg-white px-3 mt-2 pt-3">
                        <div class="dask-mod-head mb-0 border-bottom clearfix pb-3">
                            <p class="float-start mod-head-title">{:L('全部')} <span class="dask-comment-count">{$article_info.comment_count}</span>{:L('条评论')}</p>
                            <div class="dropdown float-end">
                                <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-fw fa-angle-down d-none d-sm-inline-block"></i><span>{$sort=='hot' ? L('热门排序'):L('默认排序')}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm">
                                    <div class="text-center d-block py-2 dask-nav dask-ui-dropdown-nav text-center dask-answer-sort" style="min-width: 100px">
                                        <div class="{$sort=='new' ? 'active':''} py-1"><a href="{:url('article/detail',['id'=>$article_info['id'],'sort'=>'new'])}" data-pjax="comment-container">{:L('默认排序')}</a> </div>
                                        <div class="{$sort=='hot' ? 'active':''} py-1"><a href="{:url('article/detail',['id'=>$article_info['id'],'sort'=>'hot'])}" data-pjax="comment-container">{:L('热门排序')} </a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="post-comments post" style="padding: 0;box-shadow: none">
                        <div id="article-comment-list" class="bg-white">
                            {if $comment_list}
                            {volist name="comment_list" id="v"}
                            <div class="p-3 mb-1 dask-article-comment-item" id="article-comment-{$v.id}">
                                <div class="clearfix pt-0 pb-2">
                                    <div class="user-details-card-avatar float-start" style="position: relative">
                                        <a href="{:get_user_url($v['uid'])}">
                                            <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" alt="{$v['user_info']['name']}" style="width: 40px;height: 40px">
                                        </a>
                                    </div>
                                    <div class="user-details-card-name float-start ms-2">
                                        <a href="{$v['user_info']['url']}">{$v['user_info']['name']}</a> <br><span class="ms-0"> {:date_friendly($v['create_time'])} </span>
                                    </div>
                                </div>
                                <div class="articleCommentContent" style="padding-left: 45px;">
                                    <div class="dask-content">{$v.message|raw}</div>
                                    <div class="actions">
                                        <div class="font-8 mt-2 text-muted">
                                            <a href="javascript:;" class="text-muted dask-ajax-agree me-3 {if $v['vote_value']==1}active{/if}" data-action="user-agree" data-type="article_comment" data-id="{$v.id}"><i class="icon-thumb_up"></i>&nbsp;{:L('点赞')} <span>{$v.agree_count}</span></a>
                                            {if $user_id}
                                            <a href="javascript:;" class="me-3 text-muted article-comment-reply"  data-comment-id="{$v.id}" data-username="{$v['user_info']['nick_name']}" data-uid="{$v.uid}"> <i class="icon-reply"></i>&nbsp;{:L('回复')}</a>
                                            {if $user_id==$v['uid'] || $user_info['group_id']==1 || $user_info['group_id']==2}
                                            <a href="javascript:;" class="text-muted dask-ajax-get" data-confirm="{:L('确定要删除吗')}?" data-url="{:url('comment/remove_article_comment',['id'=>$v.id])}"> <i class="icon-delete me-1"></i>{:L('删除')} </a>
                                            {/if}
                                            {/if}
                                        </div>
                                    </div>
                                    <div class="replay-editor mt-2" style="display: none"></div>
                                    {if isset($v['childs']) && $v['childs']}
                                    <div class="article-comment-child px-3 bg-light rounded mt-2">
                                        {volist name="$v['childs']" id="v1"}
                                        <div class="p-3 mb-1 dask-article-comment-item border-bottom" style="border-color: #eee !important;" id="article-comment-{$v1.id}">
                                            <div class="user-details-card pt-0 pb-2">
                                                <div class="user-details-card-avatar" style="position: relative">
                                                    <a href="{:get_user_url($v1['uid'])}">
                                                        <img src="{:user_avatar_url($v1['user_info']['avatar'] ?? '')}" alt="{$v1['user_info']['nick_name']}" style="width: 40px;height: 40px">
                                                    </a>
                                                </div>
                                                <div class="user-details-card-name">
                                                    <a href="{$v1['user_info']['url']}">{$v1['user_info']['nick_name']}</a> <span class="ms-0"> {:front_time($v['create_time'], 'Y-m-d H:i')} </span>
                                                </div>
                                            </div>
                                            <p>{$v1.message|raw}</p>
                                            <div class="actions">
                                                <div class="font-9 mt-2">
                                                    <a href="javascript:;" class="text-muted dask-ajax-agree me-3 {if $v1['vote_value']==1}active{/if}" data-action="user-agree" data-type="article_comment" data-id="{$v1.id}"><i class="icon-thumb_up"></i>&nbsp;{:L('点赞')} {if ($v1.agree_count)}<span>{$v1.agree_count}</span>{/if}</a>
                                                    {if $user_id}
                                                    <a href="javascript:;" class="me-3 text-muted article-comment-reply" data-username="{$v1['user_info']['user_name']}" data-comment-id="{$v1.id}" data-info='{:json_encode(["uid"=>$v1["uid"],"user_name"=>$v1["user_info"]["user_name"]])}'> <i class="icon-reply"></i>&nbsp;{:L('回复')} </a>
                                                    {if $user_id==$v1['uid'] || $user_info['group_id']==1 || $user_info['group_id']==2}
                                                    <a href="javascript:;" class="text-muted dask-ajax-get" data-confirm="{:L('确定要删除吗')}?" data-url="{:url('comment/remove_article_comment',['id'=>$v1.id])}"> <i class="icon-delete me-1"></i>{:L('删除')} </a>
                                                    {/if}
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="replay-editor mt-2" style="display: none"></div>
                                        </div>
                                        {/volist}
                                    </div>
                                    {/if}
                                </div>
                            </div>
                            {/volist}
                            {if $page_render}
                            <div class="p-3 dask-ui-pager">{$page_render|raw}</div>
                            {/if}
                            {else/}
                            <p class="text-center py-3 text-muted ">
                                <img src="{$assetBase}common/image/empty.svg">
                                <span class="d-block  ">{:L('暂无评论')}</span>
                            </p>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 dask-side col-sm-12 px-xs-0">
                
                
                {if get_theme_setting('article_detail.sidebar_show_items') && in_array('write_nav',get_theme_setting('article_detail.sidebar_show_items'))}
                {:widget('sidebar/writeNav')}
                {/if}

                {if get_theme_setting('article_detail.sidebar_show_items') && in_array('announce',get_theme_setting('article_detail.sidebar_show_items'))}
                {:widget('sidebar/announce')}
                {/if}
                
                {if $article_info['column_id'] && $article_info['column_info']}
                <div class="r-box dask-card mb-2">
                    <div class="r-title dask-card__head">
                        <h4>{:L('已收录至专栏')}</h4>
                    </div>
                    <div class="block-content dask-card__body">
                        <div class="dask-hcard pb-2 border-bottom">
                            <div class="dask-hcard__media">
                                <a href="{:url('column/detail',['id'=>$article_info['column_id']])}" data-pjax="pageMain">
                                    <img src="{:safe_url($article_info['column_info']['cover'] ?? '', '/static/common/image/default-cover.svg')}" class="rounded" width="40" height="40">
                                </a>
                            </div>
                            <div class="dask-hcard__body">
                                <a href="{:url('column/detail',['id'=>$article_info['column_id']])}" class="d-block dask-one-line" target="_blank">
                                    <strong>{$article_info['column_info']['name']}</strong>
                                </a>
                                <p class="mb-0 font-8 text-muted dask-one-line mt-2">{$article_info['description']['name']|default=L("暂无专栏介绍")}</p>
                            </div>
                        </div>
                        <div class="d-flex text-center pt-3 pb-3 text-muted">
                            <a href="javascript:;" class="flex-fill mb-0">
                                <dl class="mb-0">
                                    <dt>{$article_info['column_info']['post_count']}</dt>
                                    <dd>{:L('文章')}</dd>
                                </dl>
                            </a>
                            <a href="javascript:;" class="flex-fill mb-0">
                                <dl class="mb-0">
                                    <dt>{$article_info['column_info']['view_count']}</dt>
                                    <dd>{:L('浏览')}</dd>
                                </dl>
                            </a>
                            <a href="javascript:;" class="flex-fill mb-0">
                                <dl class="mb-0">
                                    <dt>{$article_info['column_info']['focus_count']}</dt>
                                    <dd>{:L('关注')}</dd>
                                </dl>
                            </a>
                        </div>
                    </div>
                </div>
                {/if}

                {if $relation_article && $theme_config['article_detail']['sidebar_show_relation_article']=='Y'}
                <div class="r-box dask-card mb-2 hot-topic">
                    <div class="r-title dask-card__head">
                        <h4>{:L('相关文章')}</h4>
                        <a href="{:url('article/index',['sort'=>'hot'])}" target="_blank" class="dask-r-title-more" title="{:L('查看更多')}">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6.5 12.5 11 8l-4.5-4.5"/><path d="M10.5 8H3.5"/>
                            </svg>
                        </a>
                    </div>
                    <div class="dask-card__body">
                        {volist name="relation_article" id="v"}
                        <div class="dask-side-list mb-0 py-2">
                            <div class="dask-side-list__title d-block dask-one-line fw-normal">
                                <a href="{:url('article/detail',['id'=>$v['id']])}">{$v.title}</a>
                            </div>
                            <div class="dask-side-list__meta mt-2 font-9 text-color-info mb-0">
                                <label class="me-2 mb-0">{:L('%s 浏览',$v.view_count)}</label>
                                <label class="me-2 mb-0">{:L('%s 评论',$v['comment_count'])}</label>
                            </div>
                        </div>
                        {/volist}
                    </div>
                </div>
                {/if}

                {if $recommend_post && $theme_config['article_detail']['sidebar_show_recommend_post']=='Y'}
                <div class="r-box dask-card mb-2 hot-topic">
                    <div class="r-title dask-card__head">
                        <h4>{:L('推荐内容')}</h4>
                    </div>
                    <div class="dask-card__body">
                        {volist name="recommend_post" id="v"}
                        {if $v['item_type']=='article'}
                        <div class="dask-side-list mb-0 py-2 border-bottom">
                            <div class="dask-side-list__title d-block dask-one-line fw-normal font-9">
                                <span class="bg-primary text-white font-8 d-inline-block text-center rounded" style="width: 18px;height: 18px">{:L('文')}</span> <a href="{:url('article/detail',['id'=>$v['id']])}">{$v.title}</a>
                            </div>
                            <div class="dask-side-list__meta mt-2 font-9 text-color-info mb-0">
                                <label class="me-2 mb-0">{:L('%s 浏览',$v.view_count)}</label>
                                <label class="me-2 mb-0">{:L('%s 评论',$v['comment_count'])}</label>
                            </div>
                        </div>
                        {/if}
                        {if $v['item_type']=='question'}
                        <div class="dask-side-list mb-0 py-2 border-bottom">
                            <div class="dask-side-list__title d-block dask-one-line fw-normal font-9">
                                <span class="bg-warning text-white font-8 d-inline-block text-center rounded" style="width: 18px;height: 18px">{:L('问')}</span> <a href="{:url('question/detail',['id'=>$v['id']])}">{$v.title}</a>
                            </div>
                            <div class="dask-side-list__meta mt-2 font-9 text-color-info mb-0">
                                <label class="me-2 mb-0">{:L('%s 浏览',$v.view_count)}</label>
                                <label class="me-2 mb-0">{:L('%s 关注',$v.focus_count)}</label>
                                <label class="me-2 mb-0">{:L('%s 评论',$v['comment_count'])}</label>
                            </div>
                        </div>
                        {/if}
                        {/volist}
                    </div>
                </div>
                {/if}

                {if get_theme_setting('article_detail.sidebar_show_items') && in_array('focus_topic',get_theme_setting('article_detail.sidebar_show_items'))}
                {:widget('sidebar/focusTopic',['uid'=>$user_id])}
                {/if}

                {if get_theme_setting('article_detail.sidebar_show_items') && in_array('hot_topic',get_theme_setting('article_detail.sidebar_show_items'))}
                {:widget('sidebar/hotTopic',['uid'=>$user_id])}
                {/if}

                {if get_theme_setting('article_detail.sidebar_show_items') && in_array('column',get_theme_setting('article_detail.sidebar_show_items'))}
                {:widget('sidebar/hotColumn',['uid'=>$user_id,'sort'=>'hot'])}
                {/if}

                {if get_theme_setting('article_detail.sidebar_show_items') && in_array('hot_users',get_theme_setting('article_detail.sidebar_show_items'))}
                {:widget('sidebar/hotUsers',['uid'=>$user_id])}
                {/if}

                {if get_theme_setting('article_detail.sidebar_show_items') && in_array('diy_content',get_theme_setting('article_detail.sidebar_show_items'))}
                {$theme_config['home']['sidebar_diy_content']|raw|htmlspecialchars_decode}
                {/if}
                
            </div>
        </div>
    </div>
</div>
{/block}