<div class="navbox">
    <div class="container nav-auto">
        <div class="navbar navbar-expand-lg navbar-light">
            <a class="navbar-brand" href="{$baseUrl}">{if $setting.site_logo}<img class="logoimg-2" style="max-width: 192px;" src="{$setting.site_logo}">{else/}<span class="bh-nav-logo fw-bold">{$setting.site_name}</span>{/if}</a>
            <div class="bh-mobile-navbar-toggler d-lg-none">
            {if $user_id}
                <div class="dropdown bh-mobile d-inline-block me-4 position-relative">
                    <a href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-header-inbox>
                        <i class="iconfont bh-nav-icon">&#xe628</i>
                        {if $user_id && $user_info['inbox_unread']}
                        <span class="badge text-bg-danger rounded-pill position-absolute" data-inbox-unread style="right:-20px">{$user_info['inbox_unread']? $user_info['inbox_unread'] : '99+'}</span>                            {/if}
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" style="width: 310px !important;">
                        <div class="bg-light text-center">
                            <h5 class="dropdown-header text-uppercase py-2">{:L('私信消息')}</h5>
                        </div>
                        <div class="nav-items mb-0 dask-overflow-auto" id="topInboxBox_Toggler" data-inbox-list style="max-height: 400px"></div>                            <div class="p-2 border-top">
                            <a class="btn btn-sm btn-light w-100 text-center" href="{:url('inbox/index')}">
                                <i class="fa fa-fw fa-arrow-down me-1"></i> {:L('加载更多')}...
                            </a>
                        </div>
                    </div>
                </div>
                <div class="dropdown bh-mobile d-inline-block me-4 position-relative">
                    <a href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-header-notify>
                        <i class="iconfont bh-nav-icon">&#xe689</i>
                        {if $user_id && $user_info['notify_unread']}
                        <span class="badge text-bg-danger rounded-pill position-absolute" data-notify-unread style="right:-20px">{$user_info['notify_unread']? $user_info['notify_unread'] : '99+'}</span>
                        {/if}
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" style="width: 310px !important;">
                        <div class="bg-light clearfix py-2 px-3">
                            <h5 class="text-uppercase float-start fw-bold font-9 mb-0">{:L('通知消息')}</h5>
                            <a class="text-muted float-end font-8" href="#" role="button" data-action="user-header-notify-read-all">{:L('全部已读')}</a>
                        </div>
                        <div class="nav-items mb-0 dask-overflow-auto" id="topNotifyBox_Toggler" data-notify-list style="max-height: 400px"></div>
                           <div class="p-2 border-top">
                            <a class="btn btn-sm btn-light w-100 text-center" href="{:url('notify/index')}">
                                <i class="fa fa-fw fa-arrow-down me-1"></i> {:L('加载更多')}...
                            </a>
                        </div>
                    </div>
                </div>
            {/if}
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse newnavbar" id="navbarNavDropdown" style="z-index: 1001;justify-content: space-between">
                <div class="dask-nav-container position-relative">
                    <ul class="navbar-nav navbar-new-nav">
                        {foreach $navMenu as $k=>$v}
                        {if $k<5}
                        <li class="nav-item {$v.active ? 'cur' : ''} clearfix">
                            {if $v.child_list}
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{$v.title}</a>
                            <div class="dropdown-menu text-center bh-toggle-position" aria-labelledby="navbarDropdownMenuLink">
                                {volist name="$v['child_list']" id="v1"}
                                <a class="dropdown-item" href="{$v1.url}" data-pjax="pageMain" title="{$v1.title}">{$v1.title}</a>
                                {/volist}
                            </div>
                            {else/}
                            <a class="nav-link" href="{$v.url}" data-pjax="pageMain" title="{$v.title}">{$v.title}</a>
                            {/if}
                        </li>
                        {/if}
                        {/foreach}
                        {if count($navMenu)>5}
                        <li class="nav-item clearfix">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{:L('更多')}</a>
                            <div class="dropdown-menu text-center bh-toggle-position" aria-labelledby="navbarDropdownMenuLink">
                                {foreach $navMenu as $k=>$v1}
                                {if $k>=5}
                                <a class="dropdown-item" href="{$v1.url}" data-pjax="pageMain" title="{:L($v1.title)}">{$v1.title}</a>
                                {/if}
                                {/foreach}
                            </div>
                        </li>
                        {/if}
                    </ul>
                </div>
                <div class="nav-form position-relative" style="text-align:right;z-index: 2">
                    <form class="position-relative dask-form-search d-inline-block" action="{:url('search/index')}" method="get" id="awGlobalSearch">
                        <div class="searchbox">
                            <input id="globalSearchInput" class="dask-search-input" autocomplete="off" placeholder="{:L('请输入想搜索的内容')}" value="{:input('get.q')}"  name="q" type="text" required>
                            <label class="si si-magnifier" data-action="form-submit" data-form="#awGlobalSearch" ></label>
                            <div class="dask-ui-dropdown" style="display: none">
                                <div class="mod-body">
                                    <p class="title text-center p-3 fw-bold">{:L('请输入关键字进行搜索')}</p>
                                    <div class="dask-ui-dropdown-list dask-common-list dask-overflow-auto text-start px-3"></div>
                                    <p class="search p-3 text-start"><span>{:L('查看更多')} "</span><a href="#" role="button" data-action="form-submit" data-form="#awGlobalSearch" class="text-danger fw-bold"></a>"
                                        {:L('的搜索结果')}</p>
                                </div>
                                <div class="mod-footer px-2 py-1">
                                    <a href="{:url('question/publish')}" data-pjax="pageMain" class="btn btn-primary btn-small float-end">{:L('发起问题')}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                    {if $user_id}
                    <div class="dropdown d-inline-block me-4 position-relative">
                        <a href="#" role="button" class="btn btn-sm gradientBtn px-3 text-white" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {:L('发起')}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm">
                            <div class="text-center d-block py-2 dask-nav dask-ui-dropdown-nav text-center dask-answer-sort" style="min-width: 100px">
                                <a href="{:url('question/publish')}" class="py-1 dropdown-item" target="_blank">{:L('问题')}</a>
                                <a href="{:url('article/publish')}" class="py-1 dropdown-item" target="_blank">{:L('文章')} </a>
                                {volist name=":config('dask.publish')" id="v"}
                                <a href="{:url($v['url'])} " class="py-1 dropdown-item" target="_blank">{$v.title}</a>
                                {/volist}
                                
                            </div>
                        </div>
                    </div>
                    <div class="d-none d-lg-block">
                    <div class="dropdown bh-mobile d-inline-block me-4 position-relative">
                        <a href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-header-inbox>
                            <i class="iconfont bh-nav-icon">&#xe628</i>
                            {if $user_id && $user_info['inbox_unread']}
                            <span class="badge text-bg-danger rounded-pill position-absolute" data-inbox-unread style="right:-20px">{$user_info['inbox_unread']? $user_info['inbox_unread'] : '99+'}</span>
                            {/if}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" style="width: 310px !important;">
                            <div class="bg-light text-center">
                                <h5 class="dropdown-header text-uppercase py-2">{:L('私信消息')}</h5>
                            </div>
                            <div class="nav-items mb-0 dask-overflow-auto" id="topInboxBox" data-inbox-list style="max-height: 400px"></div>
                            <div class="p-2 border-top">
                                <a class="btn btn-sm btn-light w-100 text-center" href="{:url('inbox/index')}">
                                    <i class="fa fa-fw fa-arrow-down me-1"></i> {:L('加载更多')}...
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown bh-mobile d-inline-block me-4 position-relative">
                        <a href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-header-notify>
                            <i class="iconfont bh-nav-icon">&#xe689</i>
                            {if $user_id && $user_info['notify_unread']}
                            <span class="badge text-bg-danger rounded-pill position-absolute" data-notify-unread style="right:-20px">{$user_info['notify_unread']? $user_info['notify_unread'] : '99+'}</span>
                            {/if}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" style="width: 310px !important;">
                            <div class="bg-light clearfix py-2 px-3">
                                <h5 class="text-uppercase float-start fw-bold font-9 mb-0">{:L('通知消息')}</h5>
                                <a class="text-muted float-end font-8" href="#" role="button" data-action="user-header-notify-read-all">{:L('全部已读')}</a>
                            </div>
                            <div class="nav-items mb-0 dask-overflow-auto" id="topNotifyBox" data-notify-list style="max-height: 400px"></div>
                            <div class="p-2 border-top">
                                <a class="btn btn-sm btn-light w-100 text-center" href="{:url('notify/index')}">
                                    <i class="fa fa-fw fa-arrow-down me-1"></i> {:L('加载更多')}...
                                </a>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="dropdown bh-mobile d-inline-block position-relative">
                        <a href="#" role="button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded" src="{:user_avatar_url($user_info['avatar'] ?? '')}" style="width: 22px;height: 22px" alt="{$user_info.nick_name|default=''}">
                            <span class="d-none d-sm-inline-block ms-1">{$user_info.nick_name}</span>
                            <i class="fa fa-fw fa-angle-down d-none d-sm-inline-block"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm" aria-labelledby="page-header-user-dropdown">
                            <div class="p-3 text-center bg-primary">
                                <a href="{:url('creator/index')}">
                                    <img class="img-avatar img-avatar-thumb" style="border-radius: 50%" width="48" height="48" src="{:user_avatar_url($user_info['avatar'] ?? '')}" alt="">
                                </a>
                            </div>
                            <div class="p-2 text-center">
                                <a class="dropdown-item align-items-center" href="{$user_info.url}">
                                    <span>{:L('个人主页')}</span>
                                </a>
                                <a class="dropdown-item align-items-center" href="{:url('creator/index')}">
                                    <span>{:L('用户中心')}</span>
                                </a>
                                <a class="dropdown-item align-items-center" href="{:url('setting/profile')}">
                                    <span>{:L('个人资料')}</span>
                                </a>
                                <div role="separator" class="dropdown-divider"></div>
                                {if $user_info['group_id']==1 || $user_info['group_id']==2}
                                <a class="dropdown-item align-items-center" target="_blank" href="/{:config('app.admin')}">
                                    <span>{:L('管理后台')}</span>
                                </a>
                                {/if}
                                <a class="dropdown-item align-items-center" data-dask-confirm="{:L('确定退出登录吗？')}" data-dask-action="{:url('account/logout')}" href="#">
                                    <span>{:L('退出登录')}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    {else/}
                    <a {if $theme_config['common']['login_type']=='dialog'}href="#" role="button" data-action="user-login" {else/}href="{:url('account/login')}"{/if} class="btn btn-sm gradientBtn px-3 logon-but d-inline-block">{:L('登录')}</a>
                    {/if}
                    {include file="widget/theme_toggle" /}
                </div>
            </div>
        </div>
    </div>
</div>