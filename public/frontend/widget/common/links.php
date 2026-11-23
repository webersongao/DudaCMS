{if $links}
<!--友情链接-->
{if get_theme_setting('home.links_show_type')=='image'}
<div class="dask-links mt-2 bg-white py-3">
    <div class="container">
        <h6 class="dask-links__title mb-3">{:L('友情链接')}：</h6>
        <ul class="row g-3">
            {volist name="links" id="v"}
            {if $v.logo}
            <li class="col-6 col-md-2">
                <a href="{$v.url}" target="_blank" rel="noopener noreferrer"><img src="{$v.logo}" class="rounded w-100" style="max-height: 40px" alt="{$v.name}"></a>
            </li>
            {/if}
            {/volist}
        </ul>
    </div>
</div>
{else/}
<div class="dask-links mt-2 bg-white py-3">
    <div class="container">
        <h6 class="dask-links__title mb-3">{:L('友情链接')}：</h6>
        <div class="dask-link-list">
            {volist name="links" id="v"}
            <a class="dask-link-chip" href="{$v.url}" target="_blank" rel="noopener noreferrer">{$v.name}</a>
            {/volist}
        </div>
    </div>
</div>
{/if}
{/if}