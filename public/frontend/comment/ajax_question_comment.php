<div class="answerCommentItem py-2">
    <div class="dask-comment-head pt-0 pb-2 position-relative">
        <a href="{:get_user_url($comment['uid'])}" class="dask-username" data-id="{$comment.uid}">
            <img src="{:user_avatar_url($comment['user_info']['avatar'] ?? '')}" alt="{$comment['user_info']['name']}" style="width: 18px;height: 18px" class="me-1 rounded"><span>{$comment['user_info']['name']}</span>
        </a>
        <a href="javascript:;" class="dask-comment-head__time">{:front_time($comment['create_time'], 'Y-m-d H:i')}</a>
        {if $user_id}
        <div class="dask-comment-head__actions">
            <!--<label class="me-3 reply" data-info='{:json_encode(["uid"=>$comment.uid,"user_name"=>$comment.user_info.name])}'>
                <a href="javascript:;" class="font-8" data-type="reply"> <i class="icon-reply"></i>回复</a>
            </label>-->
            {if ($comment['uid']==$user_id || $user_info['group_id']==1 || $user_info['group_id']==2)}
            <label>
                <a href="javascript:;"  data-confirm="{:L('是否删除该评论')}?" class="dask-ajax-get" title="{:L('删除评论')}" data-url="{:url('comment/delete_comment?id='.$comment['id'])}"> <i class="icon-delete"></i></a>
            </label>
            {/if}
        </div>
        {/if}
    </div>
    <div class="dask-comment-text">
        <div class="post-comment-text-inner">
            <p class="text-muted font-8">
                {$comment.message}
            </p>
        </div>
    </div>
</div>
