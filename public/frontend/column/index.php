{extend name="$theme_block" /}
{block name="header"}
<div class="w-top-img" style="background: url('{$static_url}image/zl-timg.png') center center; background-size:auto 100%;">
    {include file="global/nav"}
    <div class="container index-search">
        <div class="row">
            <h2 class="col-12"><img src="{$static_url}image/zl-img-icon.png"></h2>
            <h3 class="col-12">{$theme_config['column']['navbar_text']|default=L('随心写作,自由表达')}</h3>
            <div class="col-12">
                <a href="{:url('article/publish')}" class="btn btn-primary px-4 btn-lg">{:L('开始装逼')}</a>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="main"}
<div class="container">
    <div class="row n-tab align-content-center">
        <ul class="dask-pjax-tabs nav nav-pills w-100">
            <li class="nav-item"><a class="nav-link {if $sort=='new'}active{/if}" data-pjax="tabMain" href="{:url('column/index')}">{:L('最新')}</a></li>
            <li class="nav-item"><a class="nav-link {if $sort=='hot'}active{/if}" data-pjax="tabMain" href="{:url('column/index',['sort'=>'hot'])}">{:L('热门')}</a></li>
            <li class="nav-item"><a class="nav-link {if $sort=='recommend'}active{/if}" data-pjax="tabMain" href="{:url('column/index',['sort'=>'recommend'])}">{:L('推荐')}</a></li>
            <li class="nav-item ms-auto float-end me-2 d-xs-none">
                <a href="{:url('column/apply')}" class="me-2 btn btn-primary btn-sm px-3">{:L('申请专栏')}</a>
                <a href="{:url('column/my')}" class=" btn btn-outline-primary btn-sm px-3">{:L('我的专栏')}</a>
            </li>
        </ul>
    </div>
</div>
<div class="container">
    <div id="tabMain">
        {if !empty($list)}
        <div class="row">
            {volist name="list" id="v"}
            <div class="z-list col-md-3">
                <div class="dask-column-card">
                    <div>
                        <h2 class="dask-one-line px-3"><a href="{:url('column/detail',['id'=>$v['id']])}">{$v.name}</a></h2>
                        <a href="{:url('column/detail',['id'=>$v['id']])}" class="d-block text-center">
                            <img src="{$v.cover}" alt="{$v.name}" style="width: 50px;height: 50px;border-radius: 50%">
                        </a>
                    </div>
                    <div class="px-3">
                        <h4 class="dask-two-line mb-2 text-muted">{$v.description|raw}</h4>
                        <span><em>{:L('%s 篇文章',num2string($v.post_count))}</em> |<em>{$v.focus_count|num2string} {:L('关注')}</em></span>
                        <a href="{:url('column/detail',['id'=>$v['id']])}" class="align-content-center">{:L('进入专栏')}</a>
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
    <!--<div class="wenz">
        <a href="{:url('column/apply')}">申请专栏</a>
    </div>-->
</div>
{/block}
