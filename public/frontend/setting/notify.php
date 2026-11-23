{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>input('uid')])}
            <div class="col-md-10">
                {include file="setting/nav"}
				<div class="bg-white mt-1 px-3 py-1" id="tabMain">
                    <form method="post" class="dask-notify-form" action="{:url('setting/notify')}">
                        <div class="dask-mod bg-white px-3 pt-3">
                            <div class="dask-mod-head mb-0">
                                <p class="mod-head-title font-12 ">{:L('私信设置')}</p>
                            </div>
                            <div class="dask-mod-body">
                                <div class="dask-setting-field">
                                    <div class="text-muted mt-3 mb-2 font-9">{:L('谁可以给我发私信')}:</div>
                                    <div class="row px-0 font-9 align-items-center">
                                        <label class="col-4"><input type="radio" value="all" name="inbox_setting" {if isset($user_setting['inbox_setting']) && $user_setting['inbox_setting']=='all'} checked="checked" {/if}>
                                            {:L('所有人')}</label>
                                        <label class="col-6"><input type="radio" value="focus" name="inbox_setting" {if isset($user_setting['inbox_setting']) && $user_setting['inbox_setting']=='focus'} checked="checked" {/if}>
                                            {:L('我关注的人')}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {foreach $notify_setting as $k=>$v}
                        <div class="dask-mod bg-white px-3 pt-3">
                            <div class="dask-mod-head mb-0">
                                <p class="mod-head-title font-12">{:L($types[$k] ?? $k)}</p>
                            </div>
                            <div class="dask-mod-body">
                                <div class="dask-setting-field">
                                    <div class="text-muted my-2 font-9">{:L('哪些情况可以给我发')}:</div>
                                    <div class="row px-0 font-9 align-items-center">
                                        {foreach $v['config'] as $k1=>$v1}
                                        {if $v1.user_setting}
                                        <label class="col-4">
                                            <input name="notify_setting[{$k}][]" type="checkbox" value="{$v1.name}" {if isset($user_setting['notify_setting'][$k]) && in_array($v1.name,$user_setting['notify_setting'][$k])} checked="checked" {/if}> {$v1.title}
                                        </label>
                                        {/if}
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/foreach}
                        <button type="button" class="btn btn-primary px-4 dask-ajax-form btn-sm mb-3">{:L('提交修改')}</button>
                    </form>
				</div>
			</div>
		</div>
	</div>
</div>
{/block}