{extend name="$theme_block" /}
{block name="main"}
<div class="p-3" id="tabMain">
    {if !empty($list)}
    {volist name="list" id="v"}
    {if $v['item_type']=="question" || $v['relation_type']=='question' }
    <div class="dask-list-item">
        <div>
            <span class="text-muted">{$v.remark|raw}</span>
        </div>
        <div class="mt-2">
            <div class="n-title">
                <span class="tip-s1 badge text-bg-secondary">{:L('问答')}</span>
                <a href="{:url('question/detail',['id'=>$v['id']])}" target="_blank">{$v.title}</a>
            </div>
        </div>
    </div>
    {/if}

    {if $v['item_type']=="article" || $v['relation_type']=='article' }
    <div class="dask-list-item article">
        <div>
            <span class="text-muted">{$v.remark|raw}</span>
        </div>
        <div class="mt-2">
            <div class="n-title">
                <span class="tip-s2 badge text-bg-secondary">{:L('文章')}</span>
                
                <a href="{:url('article/detail',['id'=>$v['id']])}"  target="_blank">{$v['title']}</a>
            </div>
        </div>
    </div>
    {/if}

    {if $v['item_type']=="answer" || $v['relation_type']=='answer' }
    <div class="dask-list-item">
        <div class="mb-2">
            <span class="text-muted">{$v.remark|raw}</span>
        </div>
        <div class="n-title mt-2">
            <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}"  target="_blank">{$v.title}</a>
        </div>
    </div>
    {/if}

    {if $v['item_type']=='topic' && $v['relation_type']==''}
    <div class="dask-list-item">
        <div class="mb-2">
            <span class="text-muted">{$v.remark|raw}</span>
        </div>
    </div>
    {/if}
    {/volist}
    {$page|raw}
    {else/}
    <p class="text-center py-3 text-muted">
        <img src="{$assetBase}common/image/empty.svg">
        <span class="d-block">{:L('暂无内容')}</span>
    </p>
    {/if}
</div>
{/block}