{extend name="$theme_block" /}
{block name="main"}
<div class="container dask-page mt-2">
    <div class="row">
        <div class="col-md-12 mb-2 bg-white dask-card rounded dask-profile-head">
            <div class="dask-profile-head__avatar">
                <div class="authorAvatar dask-avatar-wrap">
                    <img src="{:user_avatar_url($user['avatar'] ?? '')}" alt="{$user.name}" onerror="this.onerror=null;this.src='/static/common/image/default-avatar.svg'">
                    {if $user.verified_icon}
                    <span class="dask-verified-badge" title="{$user.verified_name|default='已认证'}" aria-label="{$user.verified_name|default='已认证'}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg>
                    </span>
                    {/if}
                </div>
            </div>
            <div class="dask-profile-head__info">
                <div class="dask-profile-head__top">
                    <div class="dask-profile-head__id">
                        <h2 class="dask-profile-head__name mb-1">
                            <a href="{$user.url}">{$user.name}</a>
                            <span class="badge dask-badge-group">{$user.group_name}</span>
                            {if $user.status==3}<span class="badge text-bg-danger">{:L('已封禁')}</span>{/if}
                        </h2>
                        <div class="dask-two-line dask-profile-head__sign">{$user.signature|default=L('这家伙还没有留下自我介绍')}</div>
                        <div class="dask-profile-head__sex">{$user.sex==1 ? L('男') : ($user.sex==2 ? L('女') : L('保密'))}</div>
                    </div>
                    {if $user_id}
                    <div class="dask-home-user-btn dask-actions">
                        {if $user_id==$user.uid}
                        <a class="btn btn-primary btn-sm px-4" href="{:url('setting/profile')}">{:L('编辑资料')}</a>
                        {else/}
                        <a class="btn btn-primary btn-sm px-4 {if $user['has_focus']}active{/if} {$user['has_focus'] ? 'ygz' : 'gz'}" href="javascript:;"
                           data-action="user-focus" data-type="user" data-id="{$user.uid}">{$user['has_focus'] ? L('已关注') : L('关注')}</a>
                        <a class="btn btn-outline-primary btn-sm px-4" href="javascript:;" data-action="user-inbox" data-name="{$user.nick_name}">{:L('私信')}</a>
                        {/if}
                    </div>
                    {/if}
                </div>
                <div class="dask-stat-grid dask-profile-stats">
                    <div class="dask-profile-stat"><span class="dask-stat-value">{$user.friend_count}</span><span class="dask-stat-label">{:L('关注')}</span></div>
                    <div class="dask-profile-stat"><span class="dask-stat-value">{$user.fans_count}</span><span class="dask-stat-label">{:L('粉丝')}</span></div>
                    <div class="dask-profile-stat"><span class="dask-stat-value">{$user.agree_count}</span><span class="dask-stat-label">{:L('获赞')}</span></div>
                    <div class="dask-profile-stat"><span class="dask-stat-value">{$user.credit}</span><span class="dask-stat-label">{:L($setting.credit_unit)}</span></div>
                    <div class="dask-profile-stat"><span class="dask-stat-value">{$answer_count}</span><span class="dask-stat-label">{:L('回答')}</span></div>
                    <div class="dask-profile-stat"><span class="dask-stat-value">{$article_count}</span><span class="dask-stat-label">{:L('文章')}</span></div>
                    <div class="dask-profile-stat"><span class="dask-stat-value">{$question_count}</span><span class="dask-stat-label">{:L('提问')}</span></div>
                    <div class="dask-profile-stat"><span class="dask-stat-value">{$user.views_count}</span><span class="dask-stat-label">{:L('访问')}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row dask-grid">
        <div class="col-md-9 dask-left dask-main mb-2 center-main">
            <div class="dask-profile-tabs bg-white">
                <ul class="dask-pjax-tabs nav nav-tabs nav-tabs-block px-3 bg-white dask-profile-tabs-list">
                    <li class="nav-item {if $type=='dynamic'}i-active{/if}">
                        <a class="nav-link {if $type=='dynamic'}active{/if}" data-pjax="tabMain" href="{:build_user_url($user['url_token'],$user['user_name'],['type'=>'dynamic'])}">{:L('动态')}</a>
                    </li>
                    <li class="nav-item {if $type=='question'}i-active{/if}">
                        <a class="nav-link {if $type=='question'}active{/if}" data-pjax="tabMain" href="{:build_user_url($user['url_token'],$user['user_name'],['type'=>'question'])}">{:L('提问')} {$question_count}</a>
                    </li>
                    <li class="nav-item {if $type=='answer'}i-active{/if}">
                        <a class="nav-link {if $type=='answer'}active{/if}" data-pjax="tabMain" href="{:build_user_url($user['url_token'],$user['user_name'],['type'=>'answer'])}">{:L('回答')} {$answer_count}</a>
                    </li>
                    <li class="nav-item {if $type=='article'}i-active{/if}">
                        <a class="nav-link {if $type=='article'}active{/if}" data-pjax="tabMain" href="{:build_user_url($user['url_token'],$user['user_name'],['type'=>'article'])}">{:L('文章')} {$article_count}</a>
                    </li>
                    <li class="nav-item {if $type=='friend'}i-active{/if}">
                        <a class="nav-link {if $type=='friend'}active{/if}" href="{:build_user_url($user['url_token'],$user['user_name'],['type'=>'friend'])}" data-pjax="tabMain">{:L('关注的人')}</a>
                    </li>
                    <li class="nav-item {if $type=='fans'}i-active{/if}">
                        <a class="nav-link {if $type=='fans'}active{/if}" href="{:build_user_url($user['url_token'],$user['user_name'],['type'=>'fans'])}" data-pjax="tabMain">{:L('关注TA的')}</a>
                    </li>
                    <li class="nav-item {if $type=='column'}i-active{/if}">
                        <a class="nav-link {if $type=='column'}active{/if}" href="{:build_user_url($user['url_token'],$user['user_name'],['type'=>'column'])}" data-pjax="tabMain">{:L('关注的专栏')}</a>
                    </li>
                    <li class="nav-item {if $type=='topic'}i-active{/if}">
                        <a class="nav-link {if $type=='topic'}active{/if}" href="{:build_user_url($user['url_token'],$user['user_name'],['type'=>'topic'])}" data-pjax="tabMain">{:L('关注的话题')}</a>
                    </li>
                    
                </ul>
            </div>
            <div class="tab-content bg-white bg-white" id="tabMain">
                <div class="tab-pane fade show active">
                    <div class="dask-common-list">
                        {:widget('member/getUserPost', ['uid' => $user['uid'], 'type' => $type])}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 dask-right dask-side px-xs-0">
            <!--侧边栏顶部钩子-->
            

            <div class="dask-card dask-achievement-card">
                <div class="dask-card__head"><h6 class="mb-0 fw-bold">{:L('个人成就')}</h6></div>
                <div class="dask-card__body">
                    <div class="dask-achievement-list">
                        <div class="dask-achievement-item">
                            <span class="dask-achievement-ico dask-achievement-ico--blue"><i class="fa fa-thumbs-up"></i></span>
                            <span class="dask-achievement-text">{:L('获得 %s 次赞同',$user.agree_count)}</span>
                        </div>
                        <div class="dask-achievement-item">
                            <span class="dask-achievement-ico dask-achievement-ico--green"><i class="fa fa-user-plus"></i></span>
                            <span class="dask-achievement-text">{:L('被 %s 人关注了',$user.fans_count)}</span>
                        </div>
                        <div class="dask-achievement-item">
                            <span class="dask-achievement-ico dask-achievement-ico--orange"><i class="fa fa-users"></i></span>
                            <span class="dask-achievement-text">{:L('关注了 %s 人',$user.friend_count)}</span>
                        </div>
                        <div class="dask-achievement-item">
                            <span class="dask-achievement-ico dask-achievement-ico--purple"><i class="fa fa-pencil-alt"></i></span>
                            <span class="dask-achievement-text">{:L('贡献了 %s 个回答',$answer_count)}</span>
                        </div>
                        <div class="dask-achievement-item">
                            <span class="dask-achievement-ico dask-achievement-ico--cyan"><i class="fa fa-file-alt"></i></span>
                            <span class="dask-achievement-text">{:L('发布了 %s 篇文章',$article_count)}</span>
                        </div>
                        <div class="dask-achievement-item">
                            <span class="dask-achievement-ico dask-achievement-ico--gray"><i class="fa fa-eye"></i></span>
                            <span class="dask-achievement-text">{:L('主页被访问 %s 次',$user.views_count)}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!--侧边栏底部钩子-->
            
        </div>
    </div>
</div>
{/block}