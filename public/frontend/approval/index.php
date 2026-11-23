{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>$user_id])}
            <div class="col-md-10">
                <div class="bg-white">
                    <div class="dask-ui-nav-container">
                        <ul class="nav nav-tabs nav-tabs-block js-tabs-enabled dask-pjax-tabs">
                            <li class="nav-item"><a class="nav-link {if $status==0}active{/if}" data-pjax="tabMain" href="{:url('approval/index',['status'=>0])}">{:L('待审核')}</a></li>
                            <li class="nav-item"><a class="nav-link {if $status==1}active{/if}" data-pjax="tabMain" href="{:url('approval/index',['status'=>1])}">{:L('已审核')}</a></li>
                            <li class="nav-item"><a class="nav-link {if $status==2}active{/if}" data-pjax="tabMain" href="{:url('approval/index',['status'=>2])}">{:L('已拒绝')}</a></li>
                        </ul>
                    </div>
                    <div id="tabMain">
                        <div class="dask-common-list">
                            {if !empty($list)}
                            {foreach $list as $key=>$v}

                            {if $v.type=='question'}
                            <div class="dask-list-item approval-item">
                                <div class="dask-list-item__body">
                                    {if $v.item_id && $v.status==1}
                                    <div class="n-title"><a href="{:url('question/detail',['id'=>$v.item_id])}" target="_blank"><span class="badge text-bg-primary">{:L('提问')}</span>{$v.data.title}</a></div>
                                    {else/}
                                    <div class="n-title"><a href="{:url('approval/preview',['id'=>$v.id])}" target="_blank"><span class="badge text-bg-primary">{:L('提问')}</span>{$v.data.title}</a></div>
                                    {/if}
                                    <div class="dask-content dask-two-line">{:str_cut(strip_tags(htmlspecialchars_decode($v['data']['detail'])),0,200)}</div>
                                </div>
                                <div class="dask-list-item__actions"><label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label></div>
                            </div>
                            {/if}

                            {if $v.type=='modify_question'}
                            <div class="dask-list-item approval-item">
                                <div class="dask-list-item__body">
                                    {if $v.item_id && $v.status==1}
                                    <div class="n-title"><a href="{:url('question/detail',['id'=>$v.item_id])}" target="_blank"><span class="badge text-bg-primary">{:L('修改提问')}</span> {$v.data.title}</a></div>
                                    {else/}
                                    <div class="n-title"><a href="{:url('approval/preview',['id'=>$v.id])}" target="_blank"><span class="badge text-bg-primary">{:L('修改提问')}</span> {$v.data.title}</a></div>
                                    {/if}
                                    <div class="dask-content dask-two-line">{:str_cut(strip_tags(htmlspecialchars_decode($v['data']['detail'])),0,200)}</div>
                                </div>
                                <div class="dask-list-item__actions"><label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label></div>
                            </div>
                            {/if}

                            {if $v.type=='article'}
                            <div class="dask-list-item approval-item">
                                <div class="dask-list-item__body">
                                    {if $v.item_id && $v.status==1}
                                    <div class="n-title"><a href="{:url('article/detail',['id'=>$v.item_id])}" target="_blank"><span class="badge text-bg-success">{:L('文章')}</span> {$v.data.title}</a></div>
                                    {else/}
                                    <div class="n-title"><a href="{:url('approval/preview',['id'=>$v.id])}" target="_blank"><span class="badge text-bg-success">{:L('文章')}</span> {$v.data.title}</a></div>
                                    {/if}
                                    <div class="dask-content dask-two-line">{:str_cut(strip_tags(htmlspecialchars_decode($v['data']['message'])),0,200)}</div>
                                </div>
                                <div class="dask-list-item__actions"><label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label></div>
                            </div>
                            {/if}

                            {if $v.type=='modify_article'}
                            <div class="dask-list-item approval-item">
                                <div class="dask-list-item__body">
                                    {if $v.item_id && $v.status==1}
                                    <div class="n-title"><a href="{:url('article/detail',['id'=>$v.item_id])}" target="_blank"><span class="badge text-bg-success">{:L('修改文章')}</span> {$v.data.title}</a></div>
                                    {else/}
                                    <div class="n-title"><a href="{:url('approval/preview',['id'=>$v.id])}" target="_blank"><span class="badge text-bg-success">{:L('修改文章')}</span> {$v.data.title}</a></div>
                                    {/if}
                                    <div class="dask-content dask-two-line">{:str_cut(strip_tags(htmlspecialchars_decode($v['data']['message'])),0,200)}</div>
                                </div>
                                <div class="dask-list-item__actions"><label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label></div>
                            </div>
                            {/if}

                            {if $v.type=='answer'}
                            <div class="dask-list-item approval-item">
                                <div class="dask-list-item__body">
                                    {if $v.item_id && $v.status==1}
                                    <div class="n-title"><a href="{:url('question/detail',['id'=>$v.data.question_id,'answer'=>$v.item_id])}" target="_blank"><span class="badge text-bg-warning text-white">{:L('回答')}</span> {$v.data.title}</a></div>
                                    {else/}
                                    <div class="n-title"><a href="{:url('approval/preview',['id'=>$v.id])}" target="_blank"><span class="badge text-bg-warning text-white">{:L('回答')}</span> {$v.data.title}</a></div>
                                    {/if}
                                    <div class="dask-content dask-two-line"><span class="text-danger fw-bolder">{:L('回答内容')}：</span>{:str_cut(strip_tags(htmlspecialchars_decode($v['data']['content'])),0,200)}</div>
                                </div>
                                <div class="dask-list-item__actions"><label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label></div>
                            </div>
                            {/if}

                            {if $v.type=='modify_answer'}
                            <div class="dask-list-item approval-item">
                                <div class="dask-list-item__body">
                                    {if $v.item_id && $v.status==1}
                                    <div class="n-title"><a href="{:url('question/detail',['id'=>$v.data.question_id,'answer'=>$v.item_id])}" target="_blank"><span class="badge text-bg-warning text-white">{:L('修改回答')}</span> {$v.data.title}</a></div>
                                    {else/}
                                    <div class="n-title"><a href="{:url('approval/preview',['id'=>$v.id])}" target="_blank"><span class="badge text-bg-warning text-white">{:L('修改回答')}</span> {$v.data.title}</a></div>
                                    {/if}
                                    <div class="dask-content dask-two-line"><span class="text-danger fw-bolder">{:L('回答内容')}：</span>{:str_cut(strip_tags(htmlspecialchars_decode($v['data']['content'])),0,200)}</div>
                                </div>
                                <div class="dask-list-item__actions"><label><i class="iconfont">&#xe68a;</i>{:date_friendly($v['create_time'])}</label></div>
                            </div>
                            {/if}

                            {/foreach}
                            {$page|raw}
                            {else/}
                            <p class="text-center py-3 text-muted">
                                <img src="{$assetBase}common/image/empty.svg">
                                <span class="d-block">{:L('暂无内容')}</span>
                            </p>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}