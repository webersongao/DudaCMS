<div class="mb-3 {$form[type].extra_class|default=''}" id="form_group_{$form[type].name}">
    <label class="control-label" for="{$form[type].name}">{$form[type].title|htmlspecialchars}:{notempty name="form[type].required"}(<span class="text-danger ">*</span>){/notempty}</label>
    <input type="hidden" name="{$form[type].name}" class="{notempty name="form[type].required"}required{/notempty}" value="{$form[type].value|default=''}">
    <div id="{$form[type].name}" class="dask-jstree-box" data-dask-tree data-name="{$form[type].name}" data-source="{$form[type].name}Data" {$form[type].extra_attr|default=''}></div>
    {notempty name="form[type].tips"}
    <div class="dask-form-tips mt-1">
        {$form[type].tips|raw}
    </div>
    {/notempty}
</div>

<div class="dask-jstree-data" id="{$form[type].name}Data">
    {$form[type].options}
</div>

