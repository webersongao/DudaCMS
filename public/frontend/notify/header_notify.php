{if !empty($list)}
{volist name="list" id="v"}
<div class="mb-0 py-2 px-3 header-inbox-item overflow-hidden position-relative cursor-pointer text-start">
    <p class="font-9 mb-1">{$v['subject']}</p>
    <p class="text-color-info font-9 mt-1 dask-two-line" >
        <span class="bh-content-notify">{$v.content|raw} </span>
    </p>
    <p class="font-8 mt-2">
        <a href="javascript:;" data-action="user-delete-notify" data-id="{$v.id}" class="text-muted"><i class="fa fa-trash-alt"></i> {:L('删除')}</a>
        {if !$v['read_flag']}
        <a href="javascript:;" data-action="user-read-notify" data-id="{$v.id}" class="ms-2 text-mute float-end"><i class="fa fa-check-circle"></i> {:L('标记已读')}</a>
        {/if}
    </p>
</div>
{/volist}
{else/}
<p class="text-center text-muted py-3 font-9">{:L('暂无最新通知')}</p>
{/if}