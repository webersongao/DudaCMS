{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>input('uid')])}
            <div class="col-md-10">
                <div class="bg-white">
                    <div class="dask-ui-nav-container">
                        <ul class="dask-pjax-tabs nav nav-tabs nav-tabs-block px-3">
                            <li class="nav-item"><a class="nav-link {$type=='log'?'active':''}" data-pjax="tabMain" href="{:url('credit/index',['type'=>'log'])}">{:L('成长记录')}</a></li>
                                        </ul>
                    </div>
                    <div id="tabMain" class="p-3 table-responsive">
                        {if $list}
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>{:L('成长值描述')}</th>
                                <th>{:L('成长值变化')}</th>
                                <th>{:L('当前余额')}</th>
                                <th>{:L('变动时间')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {volist name="list" id="v"}
                            <tr>
                                <td class="text-muted">{$v.remark}</td>
                                <td>{$v.credit}</td>
                                <td>{$v.balance}</td>
                                <td>{:front_time($v['create_time'], 'Y-m-d H:i:s')}</td>
                            </tr>
                            {/volist}
                            </tbody>
                        </table>
                        {$page|raw}
                        {else/}
                        <p class="text-center mt-4 text-meta">
                            <img src="{$assetBase}common/image/empty.svg" alt="{:L('暂无记录')}">
                            <span class="mt-3 d-block ">{:L('暂无成长值记录')}</span>
                        </p>
                        {/if}
                    </div>
                </div>
			</div>
		</div>
	</div>
</div>
{/block}