<div class="bg-white p-3 mb-1 dask-article-comment-item" id="article-comment-{$comment_info.id}">
    <div class="dask-comment-head pt-0 pb-2">
        <div class="user-details-card-avatar dask-comment-head__avatar" style="position: relative">
            <a href="{:url('index/index',['uid'=>$comment_info['uid']])}">
                <img src="{$comment_info['user_info']['avatar']}" alt="{$comment_info['user_info']['name']}" style="width: 40px;height: 40px">
            </a>
        </div>
        <div class="user-details-card-name dask-comment-head__name">
            <a href="{$comment_info['user_info']['url']}">{$comment_info['user_info']['name']}</a><br> <span class="ms-0"> {:date_friendly($comment_info['create_time'])} </span>
        </div>
    </div>
    <div class="articleCommentContent dask-comment-body">
        <div class="dask-content">{$comment_info.message|raw}</div>
        <div class="actions">
            <div class="font-9 mt-2">
                <a href="javascript:;" class="text-muted dask-ajax-agree me-3 {if $comment_info['vote_value']==1}active{/if}" data-action="user-agree" data-type="article_comment" data-id="{$comment_info.id}"><i class="icon-thumb_up"></i>&nbsp;{:L('点赞')} <span>{$comment_info.agree_count}</span></a>
                {if $user_id}
                <a href="javascript:;" class="me-3 text-muted article-comment-reply" data-username="{$comment_info['user_info']['user_name']}" data-info='{:json_encode(["uid"=>$comment_info["uid"],"user_name"=>$comment_info["user_info"]["user_name"]])}'> <i class="icon-reply"></i>&nbsp;{:L('回复')} </a>
                {if $user_id==$comment_info['uid'] || $user_info['group_id']==1 || $user_info['group_id']==2}
                <a href="javascript:;" class="text-muted dask-ajax-get" data-confirm="{:L('确定要删除吗')}？" data-url="{:url('comment/remove_article_comment',['id'=>$comment_info.id])}"> <i class="icon-delete me-1"></i>{:L('删除')} </a>
                {/if}
                {/if}
            </div>
        </div>
        <div class="replay-editor mt-2" style="display: none"></div>
    </div>
</div>