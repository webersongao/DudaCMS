{volist name="comment_list" id="v"}
<div class="bg-white p-3 mb-1 dask-article-comment-item" data-total="{$total}" id="article-comment-{$v.id}">
    <div class="user-details-card pt-0 pb-2 clearfix">
        <div class="user-details-card-avatar float-start" style="position: relative">
            <a href="{:url('index/index',['uid'=>$v['uid']])}">
                <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" alt="{$v['user_info']['name']}" style="width: 40px;height: 40px">
            </a>
        </div>
        <div class="user-details-card-name float-start ms-2">
            <a href="{$v['user_info']['url']}">{$v['user_info']['name']}</a><br><span class="ms-0"> {:front_time($v['create_time'], 'Y-m-d H:i')} </span>
        </div>
    </div>
    <p>{$v.message|raw}</p>
	<div class="actions">
		<div class="font-9 mt-2">
            <a href="javascript:;" class="text-muted dask-ajax-agree me-3 {if $v['vote_value']==1}active{/if}" data-action="user-agree" data-type="article_comment" data-id="{$v.id}"><i class="icon-thumb_up"></i>&nbsp;{:L('点赞')} <span>{$v.agree_count}</span></a>
			{if $user_id}
            <a href="javascript:;" class="me-3 text-muted article-comment-reply" data-username="{$v['user_info']['user_name']}" data-info='{:json_encode(["uid"=>$v["uid"],"user_name"=>$v["user_info"]["user_name"]])}'> <i class="icon-reply"></i>&nbsp;{:L('回复')} </a>
            {if $user_id==$v['uid'] || $user_info['group_id']==1 || $user_info['group_id']==2}
			<a href="javascript:;" class="text-muted dask-ajax-get" data-confirm="{:L('确定要删除吗')}？" data-url="{:url('comment/remove_article_comment',['id'=>$v.id])}"> <i class="icon-delete me-1"></i>{:L('删除')} </a>
            {/if}
            {/if}
		</div>
	</div>
    <div class="replay-editor mt-2" style="display: none"></div>
</div>
{/volist}