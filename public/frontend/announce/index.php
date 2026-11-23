{extend name="$theme_block" /}
{block name="main"}
<div class="container dask-page mt-2">
    <div class="row dask-grid justify-content-between">
        <div class="dask-left dask-main radius col-md-9 mb-2">
            <div class="bg-white dask-card rounded">
                <div class="dask-card__head px-3 py-3">
                    <h1 class="h5 mb-0">{:L('站点公告')}</h1>
                </div>
                {if $list && count($list)>0}
                <div class="dask-common-list">
                    {volist name="list" id="v"}
                    <a class="dask-list-item dask-hcard px-3 py-3" href="{:url('announce/detail',['id'=>$v.id])}">
                        <div class="dask-hcard__body">
                            <h2 class="h6 mb-2">
                                {if $v.set_top}<span class="badge text-bg-primary me-1">{:L('置顶')}</span>{/if}
                                {$v.title|htmlspecialchars}
                            </h2>
                            <p class="text-muted mb-0">{:date_friendly($v.create_time)}</p>
                        </div>
                        <div class="dask-hcard__extra text-muted"><i class="fa fa-angle-right"></i></div>
                    </a>
                    {/volist}
                </div>
                {else/}
                <div class="dask-empty">
                    <img src="{$assetBase}common/image/empty.svg" alt="" width="84" height="84">
                    <div class="dask-empty__title">{:L('暂无内容')}</div>
                    <div class="dask-empty__desc">{:L('站点公告会在这里出现。')}</div>
                </div>
                {/if}
            </div>
        </div>
        <div class="dask-right dask-side radius col-md-3 px-xs-0">
            <div class="bg-white dask-card rounded p-3">
                <h2 class="h6 mb-2">{:L('站点公告')}</h2>
                <p class="text-muted mb-0">{:L('站点规则与重要变更会在这里发布。')}</p>
            </div>
        </div>
    </div>
</div>
{/block}
