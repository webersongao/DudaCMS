{if !empty($list)}
{volist name="list" id="v"}
<div class="answerCommentItem py-3">
    <div class="dask-comment-head pt-0 pb-2 position-relative">
        <a href="{:get_user_url($v['uid'])}" class="dask-username" data-id="{$v.uid}">
            <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" alt="{$v['user_info']['name']}" style="width: 18px;height: 18px" class="me-1 rounded"><span>{$v['user_info']['name']}</span>
        </a>
        <a href="javascript:;" class="dask-comment-head__time">{:front_time($v['create_time'], 'Y-m-d H:i')}</a>
        {if $user_id}
        <div class="dask-comment-head__actions">
            <!--<label class="me-3 reply" data-info='{:json_encode(["uid"=>$v.uid,"user_name"=>$v.user_info.name])}'>
                <a href="javascript:;" class="font-8" data-type="reply"> <i class="icon-reply"></i>回复</a>
            </label>-->
            {if ($v['uid']==$user_id || $user_info['group_id']==1 || $user_info['group_id']==2)}
            <label class="text-muted">
                <a href="javascript:;"  data-confirm="{:L('是否删除该评论')}?" class="dask-ajax-get" title="{:L('删除评论')}" data-url="{:url('comment/delete_answer_comment?id='.$v['id'])}"> <i class="fa fa-trash-alt font-8"></i></a>
            </label>
            {/if}
        </div>
        {/if}
    </div>
    <div class="dask-comment-text">
        <div class="post-comment-text-inner">
            <p class="text-muted font-8">
                {$v.message|raw}
            </p>
        </div>
    </div>
</div>
{/volist}
{/if}
