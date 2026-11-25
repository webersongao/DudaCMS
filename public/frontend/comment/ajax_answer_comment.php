<div class="answerCommentItem py-2">
    <div class="dask-comment-head pt-0 pb-2 position-relative">
        <a href="{:get_user_url($uid)}" class="dask-username" data-id="{$uid}">
            <img src="{:user_avatar_url($user_info['avatar'] ?? '')}" alt="{$user_info['name']}"  style="width: 18px;height: 18px" class="me-1 rounded"><span>{$user_info['name']}</span>
        </a>
        <a href="javascript:;" class="dask-comment-head__time">{:front_time($create_time, 'Y-m-d H:i')}</a>
        <div class="dask-comment-head__actions">
            <!--<label class="me-3 reply" data-info='{:json_encode(["uid"=>$uid,"user_name"=>$user_info.name])}'>
                <a href="javascript:;" class="font-8" data-type="reply"> <i class="icon-reply"></i>回复</a>
            </label>-->
            <label>
                <a href="javascript:;"  data-confirm="{:L('是否删除该评论')}?" class="dask-ajax-get" title="{:L('删除评论')}" data-url="{:url('question/delete_answer_comment?id='.$id)}"> <i class="icon-delete"></i></a>
            </label>
        </div>
    </div>
    <div class="dask-comment-text">
        <div class="post-comment-text-inner">
            <p class="text-muted font-8">
                {$message|raw}
            </p>
        </div>
    </div>
</div>