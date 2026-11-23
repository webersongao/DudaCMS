{if isset($list)}
{volist name="list" id="v"}
<div class="dask-hcard p-2 header-inbox-item position-relative cursor-pointer">
    <div class="dask-hcard__media">
        <a href="{$v['user']['url']}">
            <img src="{$v['user']['avatar']}" alt="" class="rounded" style="width: 46px;height: 46px">
        </a>
    </div>
    <div class="dask-hcard__body">
        <p class="text-muted text-start">{$v['user']['name']}</p>
        <p class="dask-one-line font-9 cursor-pointer text-start text-muted" data-action="user-inbox" data-name="{$v.nick.name}">{:get_username($v['last_message_uid'])}:{$v['last_message']}</p>
    </div>
</div>
{/volist}
{else/}
<p class="text-center text-muted py-3 font-9">{:L('暂无最新私信')}</p>
{/if}