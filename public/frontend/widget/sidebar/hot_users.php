{if !empty($people_list)}
<div class="r-box mb-2">
    <div class="r-title">
        <h4>{:L('热门用户')}</h4>
        <a href="{:url('people/lists')}" target="_blank" class="dask-r-title-more" title="{:L('查看更多')}">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 8h9m0 0-3.5-3.5M12 8 8.5 11.5"/>
            </svg>
        </a>
    </div>
    <div class="hot-list hot-yh-list">
        {volist name="people_list" id="v"}
        <div class="dask-hcard">
            <div class="dask-hcard__media">
                <a href="{$v.url}" class="dask-username" data-id="{$v.uid}">
                    <img src="{:user_avatar_url($v['avatar'] ?? '')}">
                </a>
            </div>
            <div class="dask-hcard__body">
                <h3><a href="{$v.url}" class="dask-username dask-one-line" data-id="{$v.uid}">{$v.nick_name}</a></h3>
                <label>{:L('提问')}:<i>{$v.question_count}</i></label>
                <label>{:L('获赞')}:<i>{$v.agree_count}</i></label>
            </div>
        </div>
        {/volist}
    </div>
</div>
{/if}
