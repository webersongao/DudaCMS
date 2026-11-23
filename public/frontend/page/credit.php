{extend name="$theme_block" /}
{block name="main"}
<div class="{if !$_ajax_open}dask-wrap mt-2{/if}">
    <div class="{if !$_ajax_open}container{/if}">
        <div class="bg-white dask-score-rule p-3">
            <h1 class="text-center mb-3 font-15">{$setting.site_name}{:L($setting.credit_unit)}{:L('规则')}</h1>
            {volist name="list" id="v"}
            <dl class="dask-kv-row overflow-auto">
                <dt>{$v.title}</dt>
                <dd>
                    <span class="{$v.credit >0 ? 'text-success' : 'text-danger'}">{$v.credit} {:L($setting.credit_unit)}</span>
                    <span class="ms-4 text-danger">
                        {if $v['cycle_type']=='day' && $v['cycle']}
                        {:L('每')} <span class="fw-bold badge text-bg-danger"> {$v['cycle']} </span> {:L('天可获得')} <span class="fw-bold badge text-bg-primary">{$v.max>0 ? $v.max : L('不限')}</span> {:L('次')}
                        {/if}

                        {if $v['cycle_type']=='week' && $v['cycle']}
                        {:L('每')} <span class="fw-bold badge text-bg-danger"> {$v['cycle']} </span> {:L('周可获得')} <span class="fw-bold badge text-bg-primary">{$v.max>0 ? $v.max : L('不限')}</span> {:L('次')}
                        {/if}

                        {if $v['cycle_type']=='month' && $v['cycle']}
                        {:L('每')} <span class="fw-bold badge text-bg-danger"> {$v['cycle']} </span> {:L('月可获得')} <span class="fw-bold badge text-bg-primary">{$v.max>0 ? $v.max : L('不限')}</span> {:L('次')}
                        {/if}

                        {if $v['cycle_type']=='hour' && $v['cycle']}
                        {:L('每')} <span class="fw-bold badge text-bg-danger"> {$v['cycle']} </span> {:L('小时可获得')} <span class="fw-bold badge text-bg-primary">{$v.max>0 ? $v.max : L('不限')}</span> {:L('次')}
                        {/if}

                        {if $v['cycle_type']=='minute' && $v['cycle']}
                        {:L('每')} <span class="fw-bold badge text-bg-danger"> {$v['cycle']} </span> {:L('分钟可获得')} <span class="fw-bold badge text-bg-primary">{$v.max>0 ? $v.max : L('不限')}</span> {:L('次')}
                        {/if}

                        {if $v['cycle_type']=='second' && $v['cycle']}
                        {:L('每')} <span class="fw-bold badge text-bg-danger"> {$v['cycle']} </span> {:L('秒可获得')} <span class="fw-bold badge text-bg-primary">{$v.max>0 ? $v.max : L('不限')}</span> {:L('次')}
                        {/if}

                        {if !$v['cycle']}
                        <span class="fw-bold">{:L('不限次数')}</span>
                        {/if}
                    </span>
                </dd>
            </dl>
            {/volist}
        </div>
    </div>
</div>
{/block}