<div class="mb-3 {$form[type].extra_class|default=''}" id="form_group_{$form[type].name}">
    <label class="control-label" for="{$form[type].name}">{$form[type].title|htmlspecialchars}:{notempty name="form[type].required"}(<span class="text-danger ">*</span>){/notempty}</label>
    <select class="select2 form-control {notempty name="form[type].required"}required{/notempty}" id="{$form[type].name}" name="{$form[type].name}" data-value="{$form[type].value}" data-dask-field="select2" data-s2-has-options="{if $form[type].options}1{else /}0{/if}" data-s2-url="{$form[type].url|default=''}" data-s2-value="{$form[type].value}" data-s2-placeholder="{$form[type].placeholder|default=''}" data-s2-flavor="frontend" {$form[type].extra_attr|default=''}>
        <option value="">{$form[type].placeholder}</option>
        {volist name="form[type].options" id="option"}
        <option value="{$key}" {in name="key" value="$form[type].value"}selected{/in}>{$option}</option>
        {/volist}
    </select>
    {notempty name="form[type].tips"}
    <div class="mt-1" style="font-size:0.9rem;display: block;color: #dc3545;">
        {$form[type].tips|raw}
    </div>
    {/notempty}
</div>