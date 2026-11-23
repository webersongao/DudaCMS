{extend name="$theme_block" /}
{block name="main"}
<style>
    .swiper-slide {width: auto!important}
    .nav-pills .nav-link {
        background: #eee;
        padding: 8px 14px;
        margin-right: 10px;
        font-size: 14px;
    }
</style>
<div class="container mt-2 dask-page">
    <div class="row justify-content-between dask-grid">
        <div class="dask-left radius col-md-9 bg-white mb-2 dask-main dask-card">
            <nav>
                <div class="nav nav-tabs ps-4 pe-4 dask-pjax-a dask-card__head" role="tablist">
                    <a class="nav-item nav-link {if !$type || $type=='new'}active{/if}" data-pjax="pageMain" href="{:url('topic/index',['type'=>'new','pid'=>$pid])}">
                        {:L('最新')} </a>
                    <a class="nav-item nav-link {if $type=='focus'}active{/if}" data-pjax="pageMain" href="{:url('topic/index',['type'=>'focus','pid'=>$pid])}" >
                        {:L('关注最多')} </a>
                    <a class="nav-item nav-link {if $type=='discuss'}active{/if}" data-pjax="pageMain" href="{:url('topic/index',['type'=>'discuss','pid'=>$pid])}">
                        {:L('讨论最多')} </a>
                </div>
            </nav>
            <div id="tabMain">
                <div class="swiper swiper-container mx-3 my-3" style="margin: 0">
                    <ul class="nav nav-pills n-nav swiper-wrapper" style="flex-wrap: nowrap;">
                        <li class="nav-item swiper-slide">
                            <a class="nav-link mb-0 {if !$pid}active c-active{/if}" data-k="0" data-pjax="pageMain" href="{:url('topic/index',['type'=>$type,'pid'=>0])}">{:L('全部话题')}</a>
                        </li>
                        {foreach $parent_list as $k => $v}
                        <li class="nav-item swiper-slide">
                            <a class="nav-link {if $pid==$v.id} active c-active{/if}" data-k="{$k}" data-pjax="pageMain" href="{:url('topic/index',['type'=>$type,'pid'=>$v.id])}">{$v.title}</a>
                        </li>
                        {/foreach}
                    </ul>
                </div>

                <div class="dask-overflow-hidden rounded">
                    <div class="dask-mod dask-topic-list">
                        <div class="row" data-dask-list-items>
                            {volist name="list" id="v"}
                            <div class="col-md-4 mb-3">
                                <div class="rounded topic-item dask-card dask-card__body dask-list-item">
                                    <div class="topic-head">
                                        <a href="{:url('topic/detail',['id'=>$v['id']])}" class="topic-cover-link">
                                            <span class="topic-cover rounded" style='background-image:url("{$v.pic|default='/static/common/image/topic.svg'}")'></span>
                                        </a>
                                        <a href="{:url('topic/detail',['id'=>$v['id']])}" class="topic-title fw-bold">#{:ltrim(trim(strip_tags($v['title'])), '# ')}</a>
                                        {if $user_id}
                                        <a href="javascript:;" class="topic-focus cursor-pointer {$v['has_focus'] ? 'active ygz' : 'gz'}" data-action="user-focus" data-type="topic" data-id="{$v.id}" title="{$v['has_focus'] ? L('已关注') : L('关注')}"><i class="{$v['has_focus'] ? 'fa fa-check' : 'fa fa-plus'}"></i></a>
                                        {/if}
                                    </div>
                                    <p class="topic-desc mb-0">{$v.description|raw}</p>
                                    <div class="topic-meta dask-stat-grid">
                                        <span>{:L('讨论')}：{$v.discuss}</span>
                                        <span class="dask-global-focus-count">{:L('关注')}：{$v.focus}</span>
                                        <span class="topic-time">{:date_friendly($v.create_time)}</span>
                                    </div>
                                </div>
                            </div>
                            {/volist}
                        </div>
                    </div>
                </div>
                <div class="dask-ui-pager dask-card__foot">{$page|raw}</div>
            </div>
        </div>
        <div class="dask-right radius col-md-3 px-xs-0 dask-side dask-card">
            {:widget('sidebar/writeNav')}
            {:widget('sidebar/focusTopic',['uid'=>$user_id])}
            {:widget('sidebar/hotTopic',['uid'=>$user_id])}
        </div>
    </div>
</div>
<script>
    let c_a = $('.c-active'),
        k = parseInt(c_a.data('k'))
    let navSwiper = new Swiper('.swiper.swiper-container', {
        speed: 600,
        grabCursor: true,
        slidesPerView: "auto",
        initialSlide: k,
        slidesPerGroup: 3
    })
</script>
{/block}