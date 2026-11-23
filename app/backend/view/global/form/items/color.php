<div class="mb-3 {$form[type].extra_class|default=''}" id="form_group_{$form[type].name}">
    <label class="control-label" for="{$form[type].name}">{$form[type].title|htmlspecialchars}:{notempty name="form[type].required"}(<span class="text-danger ">*</span>){/notempty}</label>
    <div class="input-group {$form[type].name}-colorpicker" data-dask-field="color" data-color-name="{$form[type].name}">
        <input class="form-control {notempty name="form[type].required"}required{/notempty}" type="text" id="{$form[type].name}" name="{$form[type].name}" value="{$form[type].value}" placeholder="{$form[type].placeholder}" {$form[type].extra_attr|raw}>
            <button type="button" class="btn btn-outline-secondary color-pickr-trigger" aria-label="选择颜色">
                <i class="fas fa-square" {if $form[type].value}style="color: {$form[type].value}"{/if}></i>
            </button>
    </div>
    {notempty name="form[type].tips"}
    <div class="mt-1" style="font-size:0.9rem;display: block;color: #dc3545;">
        {$form[type].tips|raw}
    </div>
    {/notempty}
</div>
