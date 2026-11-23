{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>$user_id])}
            <div class="col-md-10">
                <div class="block new-block mb-0">
                    <div class="block-header dask-inbox-head">
                        <b>{:L('对话列表')}</b>
                        <div class="float-end notefun">
                            <a href="javascript:;" data-action="user-inbox" class="text-muted me-3">{:L('新私信')}</a>
                            <a href="{:url('setting/notify')}" class="text-muted"><i class="icon-settings"></i> {:L('私信设置')}</a>
                        </div>
                    </div>
                </div>
                <div class="bg-white mb-2 mt-1 rounded p-3">
                    {if isset($list)}
                    <div data-dask-list-items>
                    {volist name="list" id="v"}
                    <div class="dask-hcard py-3 border-bottom position-relative">
                        <div class="dask-hcard__media">
                            <a href="{$v['user']['url']}">
                                <img src="{$v['user']['avatar']}" alt="" class="rounded" style="width: 46px;height: 46px">
                            </a>
                            <span class="dask-online-status {$v['unread']  ? 'unread' : 'read'}"></span>
                        </div>
                        <div class="dask-hcard__body">
                            <p class="text-muted font-8">{$v['user']['name']} · {:date_friendly($v['update_time'])}</p>
                            <p class="dask-one-line cursor-pointer {$v['unread']  ? 'text-primary' : 'text-muted'} mt-2" data-action="user-inbox" data-name="{$v.user.nick_name}">{:get_username($v['last_message_uid'])}:{:inbox_message_text($v['last_message'])}</p>
                        </div>
                        <div class="font-8 position-absolute" style="top: 1rem;right: 0">
                            <a href="javascript:;" class="text-primary" data-action="user-inbox" data-name="{$v.user.nick_name}">{:L('共 %s 条对话',$v.count)}</a>
                       </div>
                    </div>
                    {/volist}
                    </div>
                    <div class="dask-ui-pager">{$page}</div>
                    {else/}
                    <p class="text-center mt-4 text-meta">
                        <img src="{$assetBase}common/image/empty.svg" alt="{:L('暂无私信记录')}">
                        <span class="mt-3 d-block ">{:L('暂无私信记录')}</span>
                    </p>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>
{/block}