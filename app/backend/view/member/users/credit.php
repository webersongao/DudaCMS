{extend name="block" /}
{block name="main"}
<div class="p-3">
    <div class="mb-2">
        <form action="{:url('credit')}">
            <input type="hidden" name="uid" value="{$uid}">
            <div class="d-flex">
                <input type="text" class="form-control d-flex me-1" name="credit" value="">
                <button type="button" class="flex-fill btn btn-primary dask-ajax-form" style="width: 120px;">操作成长值</button>
            </div>
            <div class="mt-1" style="font-size:0.9rem;display: block;color: #dc3545;">
                填写您需要操作的成长值；如 100 或 -100
            </div>
        </form>
    </div>
    {if $list}
    <table class="table table-divider">
        <thead>
        <tr>
            <th>成长值描述</th>
            <th>成长值变化</th>
            <th>当前余额</th>
            <th>变动时间</th>
        </tr>
        </thead>
        <tbody>
        {volist name="list" id="v"}
        <tr>
            <td class="text-muted">{$v.remark}</td>
            <td>{$v.credit}</td>
            <td>{$v.balance}</td>
            <td>{:date('Y-m-d H:i:s',$v['create_time'])}</td>
        </tr>
        {/volist}
        </tbody>
    </table>
    {$page|raw}
    {/if}
</div>
{/block}