{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>$user_id])}
            <div class="col-md-10">
                <div class="bg-white">
                    <div class="dask-ui-nav-container mb-2">
                        <ul class="dask-pjax-tabs nav nav-tabs nav-tabs-block px-3">
                            <li class="nav-item"><a class="nav-link active" data-pjax="tabMain" href="javascript:;">{:L('浏览记录')}</a></li>
                        </ul>
                    </div>
                    <div id="tabMain">
                        <div class="dask-common-list">
                            {if !empty($list)}
                            {foreach $list as $key=>$v}
                            <div class="record-item">
                                <div class="dask-list-item">
                                    <div class="dask-list-item__body"><a href="{$v.url}" target="_blank"><span class="badge text-bg-primary">{$v.label}</span> {$v.title}</a></div>
                                    <div class="dask-list-item__body dask-content dask-two-line">{$v.content|raw}</div>
                                    <div class="dask-list-item__body mb-0"><label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label></div>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}