{if get_setting('enable_multilingual','N')=='Y'}
<div class="dask-lang-switch dask-actions" role="group" aria-label="{:L('语言')}" style="align-items:center;gap:2px 6px;max-width:100%;">
    <svg class="dask-lang-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width:18px;height:18px;flex:0 0 auto;">
        <path d="M2.5 6.5h12"/><path d="m11.5 4.2 3 2.3-3 2.3"/>
        <path d="M17.5 13.5h-12"/><path d="m8.5 11.2-3 2.3 3 2.3"/>
    </svg>
    {php}$langs = available_langs();{/php}
    {foreach $langs as $i=>$lg}
    {if $i>0}<span class="dask-lang-sep" aria-hidden="true">/</span>{/if}
    <a class="dask-lang-option{$lg.cls} dask-dropdown__trigger d-inline-flex align-items-center" href="{$lg.url}" rel="nofollow" style="min-height:44px;padding:8px 6px;white-space:nowrap;line-height:1.2;">{$lg.label}</a>
    {/foreach}
</div>
{/if}
