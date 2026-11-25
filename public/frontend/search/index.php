{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2 dask-page">
    <div class="container" >
        <div class="row dask-grid">
            <div class="dask-left col-md-9 col-sm-12 px-0 mb-1 dask-main dask-card" id="SearchResultMain">
                {if !empty($empty_keyword)}
                <div class="bg-white p-4 text-center">
                    <p class="h5 mb-2">{:L('想搜点什么？先给个关键词呗')}</p>
                    <p class="text-muted mb-3">{:L('在上方输入内容，或者先从热门搜索里挑一个')}。</p>
                    {if $search_list}
                    <div class="dask-tag mt-3">
                        {foreach $search_list as $hot}
                        <a href="{:url('search/index',['q'=>$hot['keyword']])}" class="topic-btn d-inline-block mb-2"><em class="tag">{$hot.keyword|htmlspecialchars}</em></a>
                        {/foreach}
                    </div>
                    {/if}
                </div>
                {else}
                <div class="position-relative dask-card__head">
                    <ul class="dask-pjax-tabs nav nav-tabs nav-tabs-block searchTypeTab px-3">
                        <li class="nav-item"><a class="nav-link {if $type=='all' || !$type}active{/if}" data-pjax="SearchResultMain" href="{:url('search/index',['q'=>$keywords,'sort'=>$sort,'type'=>'all'])}">{:L('全部')}</a></li>
                        {volist name="tab_list" id="v"}
                        <li class="nav-item"><a class="nav-link {if $type==$v.name}active{/if}" data-pjax="SearchResultMain" href="{:url('search/index',['q'=>$keywords,'sort'=>$sort,'type'=>$v.name])}">{:L($v.title)}</a></li>
                        {/volist}
                    </ul>
                </div>
                <div class="bg-white">
                    <div class="search-detail-list bg-white" id="tabMain">
                        <div class="search-detail-info mb-2 clearfix px-3 pt-3">
                            <div class="search-discuss-info">
                                "<em style="font-style: normal;color: red">{:htmlspecialchars(urldecode((string)$keywords), ENT_QUOTES, 'UTF-8')}</em>" {:L('搜索结果')}：{:L('为您找到约')} {$total} {:L('条结果')}
                            </div>
                        </div>
                        {if !empty($list)}
                        <div class="dask-common-list">
                            <div data-dask-list-items>
                            {volist name="$list" id="v"}
                            {if $v['search_type']=="question" }
                            <div class="dask-list-item">
                                <div class="dask-list-item__meta text-muted">
                                    {if $v.is_anonymous}
                                    <a href="javascript:;" class="dask-username">
                                        <img src="{:user_avatar_url('')}"   class="dask-user-img circle" alt="{:L('匿名用户')}">{:L('匿名用户')}
                                    </a>
                                    {else/}
                                    <a href="{$v['user_info']['url']}" class="dask-user-name" data-id="{$v['user_info']['uid']}">
                                        <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}"   class="circle" alt="{$v['user_info']['name']}">{$v['user_info']['name']}
                                    </a>
                                    {/if}
                                    <i>{:L('发起了提问')}</i>
                                    <em class="time">{:date_friendly($v['create_time'])}</em>
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
                                        <a href="{:url('question/detail',['id'=>$v['id']])}">{$v.title|raw}</a>
                                    </div>
                                    <div class="pcon {if $v['img_list'] && get_theme_setting('common.list_show_image')=='Y'}row{/if}">
                                        {if $v['img_list'] && get_theme_setting('common.list_show_image')=='Y'}
                                        <div class="col-md-12 t-imglist row">
                                            {volist name="$v['img_list']" id="img" key="k"}
                                            {if($k<4)}
                                            <div class="col-md-4 dask-list-img">
                                                <img src="{$img|default='/static/common/image/default-cover.svg'}" class="rounded w-100 dask-cut-img" style="margin-bottom: 5px;" >
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
                                    <label>
                                        <a type="button" class="{$v['has_focus'] ? 'ygz' : 'gz'} btn btn-primary btn-sm" data-action="user-focus" data-type="question" data-id="{$v.id}">{$v['has_focus'] ? L('已关注') : L('关注问题')} {if ($v.focus_count > 0)}<span class="badge focus-count">{$v.focus_count}</span>{/if}</a>
                                    </label>
                                    <label class="ms-3 me-3"><i class="iconfont">&#xe870;</i> {:L('点赞')}{$v.agree_count}</label>
                                    <label class="me-3"><i class="iconfont">&#xe601;</i> {:L('评论')}{$v['comment_count']}</label>
                                </div>
                            </div>
                            {/if}

                            {if $v['search_type']=="article" }
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
                            {/if}

                            {if $v['search_type']=="users" }
                            <div class="users d-flex position-relative dask-list-item">
                                <div class="flex-fill me-3" style="width: 54px;height: 54px">
                                    <a href="{$v.url}" class="rounded d-block"><img src="{:user_avatar_url($v['avatar'] ?? '')}"   alt="{$v.name}" style="width: 54px;height: 54px"></a>
                                    <span class="{$v['is_online'] ? 'online-dot' : 'offline-dot'}"></span>
                                </div>
                                <div class="flex-fill w-100 mb-0">
                                    <h3 class="font-11 mb-2"><a href="{$v.url}">{$v.name|raw}</a></h3>
                                    <p class="dask-two-line">{$v['signature']|default=L('这家伙还没有留下自我介绍～')|raw}</p>
                                </div>
                                {if $user_id && $user_id!=$v.uid}
                                <div class="position-absolute" style="right: 0;top:10px">
                                    <a href="javascript:;" class="me-3 text-primary {$v['has_focus'] ? 'active ygz' : ''}" data-action="user-focus" data-type="user" data-id="{$v.uid}">
                                        {$v['has_focus'] ? '<span><i class="icon-minus-circle text-light"></i>'. L('已关注').'</span>' : '<span><i class="icon-plus-circle text-primary"></i> '.L('关注').'</span>'}
                                    </a>
                                    <a href="javascript:;" class="dask-send-inbox text-smooth">{:L('私信')}</a>
                                </div>
                                {/if}
                            </div>
                            {/if}

                            {if $v['search_type']=="topic" }
                            <div class="topic position-relative dask-list-item dask-hcard">
                                <div class="dask-hcard__media" style="max-width: 60px">
                                    <a href="{:url('topic/detail',['id'=>$v['id']])}">
                                        <img src="{$v['pic']|default='/static/common/image/topic.svg'}" onerror="this.src='/static/common/image/topic.svg'" class="rounded" style="width: 60px;height: 60px">
                                    </a>
                                </div>
                                <div class="dask-hcard__body">
                                    <a href="{:url('topic/detail',['id'=>$v['id']])}" class="fw-bold"># {$v.title|raw}</a>
                                    <div class="mb-0 font-8 dask-one-line">{$v.description|raw}</div>
                                    <p class="font-8 text-muted">
                                        <span class="me-3">{:L('讨论')}：{$v.discuss}</span>
                                        <span class="me-3"><span class="dask-global-focus-count">{:L('关注')}：{$v.focus}</span></span>
                                        {if $user_id}
                                        <a href="javascript:;" class="cursor-pointer {$v['has_focus'] ? 'active ygz' : 'gz'}" data-action="user-focus" data-type="topic" data-id="{$v.id}" >{$v['has_focus'] ? '<span><i class="icon-minus-circle text-light"></i> '.L('已关注').'</span>' : '<span><i class="icon-plus-circle text-primary"></i> '.L('关注').'</span>'}</a>
                                        {/if}
                                    </p>
                                </div>
                            </div>
                            {/if}

                            {if $v['search_type']=="answer" }
                            <div class="dask-list-item">
                                <div class="dask-list-item__meta text-muted">
                                    {if (!$v['answer_info'])}
                                    {if $v.is_anonymous}
                                    <a href="javascript:;" class="dask-username">
                                        <img src="{:user_avatar_url('')}" class="dask-user-img circle" alt="{:L('匿名用户')}">{:L('匿名用户')}
                                    </a>
                                    {else/}
                                    <a href="{$v['user_info']['url']}" class="dask-user-name" data-id="{$v['user_info']['uid']}">
                                        <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" class="circle" alt="{$v['user_info']['name']}">{$v['user_info']['name']}
                                    </a>
                                    {/if}
                                    <i>{:L('发起了提问')}</i>
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
                                                <img src="{$img|default='/static/common/image/default-cover.svg'}" class="rounded w-100 dask-cut-img" style="margin-bottom: 5px;" >
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
                                    <label class="ms-3 me-3"><i class="iconfont">&#xe625;</i>{:L('%s 感谢', $v['answer_info']['thanks_count'])}</label>
                                    <label class="me-3"><i class="iconfont">&#xe601;</i>{:L('评论')}{$v['answer_info']['comment_count']}</label>
                                    {/if}
                                </div>
                            </div>
                            {/if}

                            

                            {/volist}
                            </div>
                            <div class="dask-ui-pager dask-card__foot">{$page|raw}</div>
                        </div>
                        {else/}
                        <div class="searchNotFound p-3">
                            <p>{:L('找不到和')} <em style="font-style: normal;color: red">{$keywords}</em> {:L('相符的内容或信息')}。</p>
                        </div>
                        <div class="searchNotFound p-3">
                            <p>{:L('建议您')}：</p>
                            <br>
                            <ul class="list-unstyled text-muted">
                                <li>1.{:L('请检查输入字词有无错误')}。</li>
                                <li>2.{:L('请换用另外的查询字词')}。</li>
                                <li>3.{:L('请改用较短')}、{:L('较为常见的字词')}。</li>
                            </ul>
                        </div>
                        {/if}
                    </div>
                </div>
                {/if}
            </div>
            <div class="dask-right col-md-3 col-sm-12 px-xs-0 dask-side dask-card">
                <!--侧边栏顶部钩子-->
                
                {if $search_list}
                <div class="r-box mb-2 hot-topic">
                    <div class="r-title">
                        <h4>{:L('热门搜索')}</h4>
                    </div>
                    <div class="hot-list hot-yh-list pb-2">
                        <div class="sidebarFocusTopic dask-tag">
                            {foreach $search_list as $key=>$v}
                            <a href="{:url('search/index',['q'=>$v['keyword']])}" class="topic-btn d-inline-block mb-2"><em class="tag">{$v.keyword|htmlspecialchars}</em></a>
                            {/foreach}
                        </div>
                    </div>
                </div>
                {/if}

                <!--侧边栏底部钩子-->
                
            </div>
        </div>
    </div>
</div>
<script>
    $(function (){
        $(document).on('click', '.searchSortTab li a', function()
        {
            $('.searchSortTab li').removeClass('active');
            $(this).parent('li').addClass('active');
            $('.searchSortTabText span').text($(this).text());
        });
    });
</script>
{/block}