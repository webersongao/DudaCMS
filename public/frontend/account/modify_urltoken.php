{extend name="$theme_block" /}
{block name="main"}
<form class="p-3" action="{:url('account/modify_urltoken')}">
    <div class="mb-3">
        <input type="password" class="dask-form-control" name="password" placeholder="{:L('输入用户密码')}" value="">
    </div>

    <div class="mb-3">
        <input type="text" class="dask-form-control" name="new_url_token" placeholder="{:L('输入新的个性域名')}" value="">
    </div>

    <div class="overflow-hidden">
        <button class="dask-ajax-form btn btn-primary px-4" type="button">{:L('保存')}</button>
    </div>
</form>
{/block}