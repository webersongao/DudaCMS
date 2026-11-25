<div class="mb-3 {$form[type].extra_class|default=''}" id="form_group_{$form[type].name}">
    <label class="control-label" for="{$form[type].name}">{$form[type].title|htmlspecialchars}:{notempty name="form[type].required"}(<span class="text-danger ">*</span>){/notempty}</label>
    <div class="dask-password-field">
        <input class="form-control {notempty name="form[type].required"}required{/notempty}" type="password" id="{$form[type].name}" name="{$form[type].name}" placeholder="{$form[type].placeholder}" {notempty name="form[type].required"}data-dask-validate="required"{/notempty} {$form[type].extra_attr|raw}>
        <button type="button" class="dask-password-toggle" data-action="toggle-password" aria-controls="{$form[type].name}" aria-pressed="false" aria-label="{:L('显示密码')}" data-label-show="{:L('显示')}" data-label-hide="{:L('隐藏')}"><span class="dask-password-toggle__text" aria-hidden="true">{:L('显示')}</span></button>
    </div>
    {notempty name="form[type].tips"}
    <div class="dask-form-tips mt-1">
        {$form[type].tips|raw}
    </div>
    {/notempty}
</div>

