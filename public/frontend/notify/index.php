{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>input('uid')])}
            <div class="col-md-10">
                <div class="block new-block mb-0 ">
                    <div class="block-header">
                        <b>{:L('通知中心')}</b>
                        <div class="float-end notefun">
                            <a class="text-muted me-4 dask-ajax-get" href="javascript:;" data-url="{:url('notify/read_all')}">{:L('全部已读')}</a>
                            <a href="{:url('setting/notify')}" class="text-muted" data-pjax="wrapMain"><i class="icon-settings"></i>
                                {:L('通知设置')}</a>
                        </div>
                    </div>
                </div>
                <div class="bg-white mt-2 ntab">
                    <ul class="dask-pjax-tabs nav nav-tabs nav-tabs-block px-3">
                        <li class="nav-item"><a class="nav-link {if $type==''}active{/if}" data-pjax="wrapMain" href="{:url('notify/index')}">{:L('全部')}</a>
                        {volist name="notify" id="v"}
                        <li class="nav-item"><a class="nav-link {if $type==$key}active{/if}" data-pjax="wrapMain" href="{:url('notify/index',['type'=>$key])}">{:L($v)}</a>
                        {/volist}
                    </ul>
                </div>
                <div class="bg-white mb-2 rounded p-3" id="tabMain">
                    {if $list}
                    <div data-dask-list-items>
                    {volist name="list" id="v"}
                    <div class="dask-hcard py-3 border-bottom position-relative">
                        <div class="dask-hcard__media">
                            {if $v['recipient_user']}
                            <a href="javascript:;" class="badge text-bg-warning text-white" style="width: 46px;height: 46px;line-height: 46px;">
                                <i class="icon-bell" style="font-size: 24px;line-height: 40px"></i>
                            </a>
                            {else/}
                            <a href="{$v['recipient_user']['url']}">
                                <img src="{:user_avatar_url($v['recipient_user']['avatar'] ?? '')}" alt="" class="rounded" style="width: 46px;height: 46px">
                            </a>
                            {/if}
                            <span class="dask-online-status dask-notify-status {$v['read_flag'] ? 'read' : 'unread'}"></span>
                        </div>
                        <div class="dask-hcard__body">
                            <p class="text-muted font-9 {if !$v['read_flag']}fw-bold{/if}">{$v['subject']|raw} · {:date_friendly($v['create_time'])}</p>
                            <p class="font-9 mt-2 text-color-info">{$v.content|raw}</p>
                        </div>
                        <div class="font-8 position-absolute" style="{$isMobile ? 'bottom: 0.5rem;right: 0' : 'top: 1rem;right: 0'}">
                            <a href="javascript:;" data-action="user-delete-notify" data-id="{$v.id}" class="text-color-info"><i class="icon-delete"></i>
                                {:L('删除')}</a>
                            {if !$v['read_flag']}
                            <a href="javascript:;" data-action="user-read-notify" data-id="{$v.id}" class="ms-2 text-color-info float-end">{:L('标记已读')}</a>
                            {/if}
                        </div>
                    </div>
                    {/volist}
                    </div>
                    <div class="dask-ui-pager">{$page|raw}</div>

                    {else/}
                    <p class="text-center mt-4 text-meta">
                        <img src="{$assetBase}common/image/empty.svg" alt="{:L('暂无消息')}">
                        <span class="mt-3 d-block ">{:L('暂无消息')}</span>
                    </p>
                    {/if}
                </div>
			</div>
		</div>
	</div>
</div>
{/block}