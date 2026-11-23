{if $user_id && !empty($topic_list)}
<div class="r-box mb-2 hot-topic focus-topic-box">
    <div class="r-title">
        <h4>{:L('关注话题')}</h4>
        <a href="{:url('focus/index',['type'=>'topic'])}" target="_blank" class="dask-r-title-more" title="{:L('查看更多')}">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 8h9m0 0-3.5-3.5M12 8 8.5 11.5"/>
            </svg>
        </a>
    </div>
    <div class="hot-list hot-yh-list pb-2">
        <div class="sidebarFocusTopic dask-tag">
            {foreach $topic_list as $key=>$v}
            <a href="{:url('topic/detail',['id'=>$v['id']])}" data-id="{$v['id']}"  class="topic-btn d-inline-block mb-2"><em class="tag">{$v.title}</em></a>
            {/foreach}
        </div>
        {foreach $topic_list as $key=>$v}
        <div class="bg-light rounded p-2 sidebarTopicHover" id="topic{$v.id}" style="{if $key!=0}display: none {/if}">
            <div class="dask-hcard align-items-center">
                <a href="{:url('topic/detail',['id'=>$v['id']])}" class="text-primary dask-hcard__body">{$v.title}</a>
                <a href="{:url('topic/detail',['id'=>$v['id']])}" class="icon-arrow-right dask-hcard__extra" aria-label="{:L('查看更多')}"></a>
            </div>
            <p class="mt-1 mb-0 bh-sidebar-topic-list">
                <b>{$v.question_count}</b> <span>{:L('提问')}</span>
                <b>{$v.article_count}</b> <span>{:L('文章')}</span>
            </p>
        </div>
        {/foreach}
    </div>
</div>
<script>
    $('.sidebarFocusTopic a').mousemove(function(){
        var topic = $(this).attr('data-id');
        $('.sidebarTopicHover').hide();
        $('#topic'+topic).show();
    });
</script>
{/if}
