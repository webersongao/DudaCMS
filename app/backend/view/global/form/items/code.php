<div class="mb-3 {$form[type].extra_class|default=''}" id="form_group_{$form[type].name}">
    <label class="control-label" for="{$form[type].name}">{$form[type].title|htmlspecialchars}:{notempty name="form[type].required"}(<span class="text-danger ">*</span>){/notempty}</label>
    <textarea id="{$form[type].name}" class="{notempty name="form[type].required"}required{/notempty}" rows="3" name="{$form[type].name}" data-dask-field="code" data-cm-mode="{$form[type].mode|default='text/plain'}" {$form[type].extra_attr|raw}>{$form[type].value}</textarea>
    {notempty name="form[type].tips"}
    <div class="mt-1" style="font-size:0.9rem;display: block;color: #dc3545;">
        {$form[type].tips|raw}
    </div>
    {/notempty}
</div>
