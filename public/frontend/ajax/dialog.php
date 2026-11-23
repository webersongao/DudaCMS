{if isset($list)}
{volist name="list" id="v"}
<div class="dask-im-item {if $user_id==$v['uid']}me{/if}" data-total="{$total}">
    <a href="{$v['user']['url']}" class="flex-shrink-0">
        <img class="dask-im-avatar" src="{$v['user']['avatar']}" alt="{$v['user']['name']}">
    </a>
    <div class="dask-im-msg">
        <div class="dask-im-bubble">{:render_inbox_message($v.message)}</div>
        <div class="dask-im-meta">
            {:date('m-d H:i',$v['send_time'])}
            {if $user_id==$v['uid'] && !$v.read_time}
            <span class="unread">· {:L('未读')}</span>
            {/if}
        </div>
    </div>
</div>
{/volist}
{/if}
