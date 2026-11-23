<form action="{:url('invitation/operate')}" method="post" class="dask-form" id="invitation-form">
    <input type="hidden" name="active_type" value="{$type}">
    <input type="hidden" name="invitation_code" value="{$invitation_code}">
    {if ($type == 'link')}
    <div class="mb-3 row">
        <label class="col-md-1">{:L('链接')}</label>
        <div class="col-md-10">
            <input type="text" name="link" value="{$link}" class="form-control" readonly>
        </div>
    </div>
    {else/}
    <div class="mb-3 row">
        <label class="col-md-1">{:L('邮箱')}</label>
        <div class="col-md-10">
            <input type="email" name="invitation_email" value="" placeholder="{:L('请输入被邀请者邮箱')}" class="form-control">
        </div>
    </div>
    {/if}
</form>