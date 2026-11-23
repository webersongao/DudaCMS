{if !empty($announce_list)}
<div class="r-box mb-2">
    <div class="r-title">
        <h4>{:L('站点公告')}</h4>
    </div>
    <div class="hot-list hot-yh-list pb-2">
        {volist name="announce_list" id="v"}
        <div class="border-bottom">
            <div class="mb-2 w-100" style="height: auto">
                <a href="{:url('announce/detail',['id'=>$v.id])} " class="text-danger dask-two-line">{$v.title}</a>
            </div>
            <div class="pt-1 px-0 text-muted dask-four-line pstyle">
                {$v.message|raw}
            </div>
        </div>
        {/volist}
    </div>
</div>
{/if}
