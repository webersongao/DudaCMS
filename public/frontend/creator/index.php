{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>$user_id])}
            <div class="col-md-10">
                    <div class="card border-0 dask-creator-profile">
                    <div class="card-body d-flex align-items-center">
                        <div class="dask-creator-avatar">
                            <a href="{:url('setting/profile')}" class="d-block">
                                <img src="{:user_avatar_url($user_info['avatar'] ?? '')}" alt="{$user_info.nick_name}">
                            </a>
                        </div>
                        <div class="flex-fill min-w-0">
                            <h5 class="mb-1 name">{$user_info.nick_name}</h5>
                            <p class="mb-3"><span class="badge dask-badge-group">{$user_info.group_name}</span></p>
                            <div class="dask-creator-stats">
                                <div class="dask-creator-stat"><span class="dask-stat-label">{:L('获赞')}</span><span class="dask-stat-value">{$user_info.agree_count}</span></div>
                                <div class="dask-creator-stat"><span class="dask-stat-label">{:L('问题')}</span><span class="dask-stat-value">{$question_count}</span></div>
                                <div class="dask-creator-stat"><span class="dask-stat-label">{:L('文章')}</span><span class="dask-stat-value">{$article_count}</span></div>
                                <div class="dask-creator-stat"><span class="dask-stat-label">{:L('回答')}</span><span class="dask-stat-value">{$answer_count}</span></div>
                                <div class="dask-creator-stat"><span class="dask-stat-label">{$setting.credit_unit}</span><span class="dask-stat-value">{$user_info.credit}</span></div>
                                <div class="dask-creator-stat"><span class="dask-stat-label">{:L('粉丝')}</span><span class="dask-stat-value">{$user_info.fans_count}</span></div>
                                <div class="dask-creator-stat"><span class="dask-stat-label">{:L('好友')}</span><span class="dask-stat-value">{$user_info.friend_count}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--用户中心主页钩子-->
                
            </div>
        </div>
    </div>
</div>
{/block}