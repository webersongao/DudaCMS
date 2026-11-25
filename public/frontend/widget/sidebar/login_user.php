{if $user_id}
<div class="dask-ui-card dask-ui-card-default dask-ui-card-small dask-padding-small mb-3">
    <div class="sidebar-user pt-0">
        <div class="dask-hcard">
            <div class="dask-hcard__media">
                <a href="{$user_info.url}"><img src="{:user_avatar_url($user_info['avatar'] ?? '')}" alt="{$user_info.name}" class="circle" style="width: 48px;height: 48px"></a>
            </div>
            <div class="dask-hcard__body">
                <a href="{$user_info.url}">{$user_info.name}</a>
                <p class="dask-text-meta">{$user_info.signature|default=L("还没有完善签名哦")}</p>
            </div>
        </div>
    </div>
    <ul class="sidebar-user-list">
        <li>
            <a href="{:build_user_url($user_info['url_token'],$user_info['user_name'],['type'=>'question'])}">
                <p><i class="icon-help-with-circle"></i>{:L('我的提问')}</p>
                <em>{$user_info.question_count}</em>
            </a>
        </li>
        <li>
            <a href="{:build_user_url($user_info['url_token'],$user_info['user_name'],['type'=>'article'])}">
                <p><i class="icon-assignment"></i>{:L('我的文章')}</p>
                <em>{$user_info.article_count}</em>
            </a>
        </li>
        <li>
            <a href="{:build_user_url($user_info['url_token'],$user_info['user_name'],['type'=>'answer'])}">
                <p><i class="icon-insert_comment"></i>{:L('我的回答')}</p>
                <em>{$user_info.answer_count}</em>
            </a>
        </li>
    </ul>
</div>
{else/}
<div class="dask-ui-card dask-ui-card-default dask-ui-card-small dask-ui-card-body mb-3 rounded" data-dask-sticky="offset:70 ; media : @m">
    <h3 class="mb-3">{:L('账号登录')}</h3>
    <p>{:get_setting('seo_description')}</p>
    <a href="{:url('account/login')}" class="button primary dask-display-block mt-3">{:L('登录')}</a>
    <a class="mt-3 button default dask-display-block" href="{:url('account/register')}">{:L('注册')}</a>
</div>
{/if}