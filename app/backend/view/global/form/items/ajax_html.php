<div class="mb-3" id="form_group_{$form[type].name}" data-dask-field="ajax-html" data-ah-url="{$form[type].url}" data-ah-trigger="{$form[type].trigger|default=''}" data-ah-value="{$form[type].value|default=''}">
    <div id="ajaxHtml"></div>
    {notempty name="form[type].tips"}
    <div class="mt-1" style="font-size:0.9rem;display: block;color: #dc3545;">
        {$form[type].tips|raw}
    </div>
    {/notempty}
</div>
