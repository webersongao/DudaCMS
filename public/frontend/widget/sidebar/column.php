{if !empty($list)}
<div class="r-box mb-2 hot-topic">
    <div class="r-title">
        <h4>{:L('热门专栏')}</h4>
        <a href="{:url('column/index',['sort'=>'hot'])}" target="_blank" class="dask-r-title-more" title="{:L('查看更多')}">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 8h9m0 0-3.5-3.5M12 8 8.5 11.5"/>
            </svg>
        </a>
    </div>
    <div class="hot-list">
        {volist name="list" id="v"}
        <div class="dask-hcard">
            <div class="dask-hcard__media" style="width: 41px">
                <a href="{:url('column/detail',['id'=>$v['id']])}">
                    <div style='background-image:url("{$v['cover']|default='/static/common/image/topic.svg'}") ;background-size: cover;width:41px;height:41px'></div>
                </a>
            </div>
            <div class="dask-hcard__body">
                <h3><a href="{:url('column/detail',['id'=>$v['id']])}">{$v.name}</a></h3>
                <label>{:L('文章')}:<i>{$v.post_count}</i></label>
                <label>{:L('浏览')}:<i>{$v.view_count}</i></label>
            </div>
        </div>
        {/volist}
    </div>
</div>
{/if}
