{extend name="$theme_block" /}
{block name="main"}
<div class="favorite-tag-list overflow-auto">
    <div class="favorite-body">
        {volist name="list" id="v"}
        <div class="favorite-item dask-hcard align-items-center p-3 bg-white mb-2">
            <div class="dask-hcard__body favorite-item-inner">
                <h4 class="favorite-item-name">{$v.title}</h4>
                <div class="mt-2 text-muted"><span class="favorite-post-count">{$v.post_count}</span> {:L('条内容')}</div>
            </div>
            {if $v['is_favorite']}
            <button class="dask-hcard__extra favorite-ajax-get btn btn-primary btn-sm px-3 active" data-url="{:url('favorite/dialog',['item_id'=>$item_id,'item_type'=>$item_type,'tag_id'=>$v['id']])}">{:L('取消收藏')}</button>
            {else/}
            <button class="dask-hcard__extra favorite-ajax-get btn btn-primary btn-sm px-3" data-url="{:url('favorite/dialog',['item_id'=>$item_id,'item_type'=>$item_type,'tag_id'=>$v['id']])}">{:L('收藏')}</button>
            {/if}
        </div>
        {/volist}
    </div>
    <div class="text-center p-3 listCreate">
        {:L('去')} <a href="javascript:;" class="text-primary create-favorite">{:L('创建收藏夹')}</a>
    </div>
</div>

<div class="p-3 bg-white createFavoriteBox" style="{if $list}display:none;{/if}">
    <div class="no-info text-center ">
        <p>
            <img src="{$assetBase}common/image/empty.svg">
        </p>
        <p class="dask-text-meta mt-4">
            <span class="mt-3 me-2">{:L('暂无收藏夹')}</span>
            {:L('去')} <a href="javascript:;" class="text-primary create-favorite">{:L('创建收藏夹')}</a>
        </p>
    </div>
    <div class="favorite-tag-add" style="display: none">
        <form action="{:url('favorite/save_favorite')}" method="post">
            <div class="mb-3">
                <input type="text" name="title" class="dask-form-control" placeholder="{:L('标签名字')}">
            </div>
            <!--<div class="mb-3">
                <input type="checkbox" name="is_public" > 公开收藏夹
            </div>-->
            <div class="mb-3">
                <button type="button" class="btn btn-outline-primary  px-3 btn-sm cancel-create">{:L('取消')}</button>
                <button type="button" class="btn btn-primary btn-sm px-3 save-favorite-tag">{:L('确认创建')}</button>
            </div>
        </form>
    </div>
</div>
{/block}
