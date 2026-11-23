{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2 dask-page" data-page-module="questions">
    {if $setting.enable_category=='Y'}
    {:widget('common/category',['type'=>'question','category'=>$category,'show_type'=>'list'])}
    {/if}
    <div class="container">
        <div class="row justify-content-between dask-grid">
            <div class="dask-left radius col-md-9 bg-white mb-2 dask-main dask-card">
                <div class="nav nav-tabs dask-pjax-a px-4 dask-card__head" role="tablist">
                    <a class="nav-item nav-link {if $sort=='recommend'}active{/if}" data-pjax="wrapMain" href="{:url('question/index',['sort'=>'recommend','category_id'=>$category])}">{:L('推荐')}</a>
                    <a class="nav-item nav-link {if $sort=='new'}active{/if}" data-pjax="wrapMain" href="{:url('question/index',['sort'=>'new','category_id'=>$category])}">{:L('最新')}</a>
                    <a class="nav-item nav-link {if $sort=='hot'}active{/if}" data-pjax="wrapMain" href="{:url('question/index',['sort'=>'hot','category_id'=>$category])}">{:L('热门')}</a>
                    <a class="nav-item nav-link {if $sort=='unresponsive'}active{/if}" data-pjax="wrapMain" href="{:url('question/index',['sort'=>'unresponsive','category_id'=>$category])}">{:L('待回答')}</a>
                </div>

                <div id="tabMain" class="tab-content">
                    <div class="tab-pane fade show active">
                        <div class="dask-common-list">
                            <div data-dask-list-items>
                                {we:question sort="$sort" category_id="$category"}
                                
                            <div class="dask-list-item questionItem">
                                <div class="dask-list-item__meta text-muted">
                                        {if (!$v['answer_info'])}
                                        {if $v.is_anonymous}
                                        <a href="javascript:;" class="dask-username">
                                            <img src="/static/common/image/default-avatar.svg" class="dask-user-img circle" alt="{:L('匿名用户')}">{:L('匿名用户')}
                                        </a>
                                        {else/}
                                        <a href="{$v['user_info']['url']}" class="dask-user-name" data-id="{$v['user_info']['uid']}" target="_blank">
                                            <img src="{$v['user_info']['avatar']}" onerror="this.src='/static/common/image/default-avatar.svg'" class="circle" alt="{$v['user_info']['name']}">{$v['user_info']['name']}
                                        </a>
                                        {/if}
                                        <i>{:L('发起了提问')}</i>
                                        <em class="time">{:date_friendly($v['create_time'])}</em>
                                        {else/}
                                        {if $v['answer_info']['is_anonymous']}
                                        <a href="javascript:;" class="dask-username">
                                            <img src="/static/common/image/default-avatar.svg" class="dask-user-img circle" alt="{:L('匿名用户')}">{:L('匿名用户')}
                                        </a>
                                        {else/}
                                        <a href="{$v['answer_info']['user_info']['url']}" data-id="{$v['answer_info']['uid']}" class="dask-user-name" target="_blank">
                                            <img src="{$v['answer_info']['user_info']['avatar']}" onerror="this.src='/static/common/image/default-avatar.svg'" class="circle" alt="{$v['answer_info']['user_info']['name']}">{$v['answer_info']['user_info']['name']}
                                        </a>
                                        {/if}
                                        <i>{:L('回复了问题')}</i>
                                        <em class="time">{:date_friendly($v['answer_info']['create_time'])}</em>
                                        {/if}
                                        {if isset($v['topics']) && !empty($v['topics'])}
                                        <div class="tag d-inline-block mt-2">
                                            {volist name="$v['topics']" id="topic"}
                                            <a href="{:url('topic/detail',['id'=>$topic['id']])}" class="dask-topic" data-id="{$topic.id}" target="_blank"><em class="tag">{$topic.title}</em></a>
                                            {/volist}
                                        </div>
                                        {/if}
                                    </div>
                                    <div class="dask-list-item__body dask-question-row__body">
                                        <div class="n-title">
                                            <span class="tip-s1 badge text-bg-secondary">{:L('问答')}</span>
                                            {if $v.set_top}
                                            <span class="tip-d badge text-bg-secondary">{:L('顶')}</span>
                                            {/if}
                                            <a href="{:url('question/detail',['id'=>$v['id']])}" target="_blank">{$v.title|raw}</a>
                                            
                                        </div>
                                        <div class="pcon {if $v['img_list'] && get_theme_setting('common.list_show_image')=='Y'}row{/if}">
                                            {if $v['img_list'] && get_theme_setting('common.list_show_image')=='Y'}
                                            <div class="col-md-12 t-imglist row">
                                                {volist name="$v['img_list']" id="img" key="k"}
                                                {if($k<4)}
                                                    <div class="col-md-4 dask-list-img">
                                                    <img src="{$img|default='/static/common/image/default-cover.svg'}?w=100&h=100" class="rounded w-100 dask-cut-img" style="margin-bottom: 5px;">
                                            </div>
                                            {/if}
                                            {/volist}
                                        </div>
                                        <div class="ov-3 col-md-12">
                                            <div class="dask-two-line">
                                                {if !$v['answer_info']}
                                                <a href="{:url('question/detail',['id'=>$v['id']])}" target="_blank">{$v.detail|raw}</a>:
                                                {else/}
                                                {if $v['answer_info']['is_anonymous']}
                                                <a href="javascript:;" class="dask-username">匿名用户</a>:
                                                {else/}
                                                <a href="{$v['answer_info']['user_info']['url']}" class="dask-username fw-bold">{$v['answer_info']['user_info']['name']}</a>:
                                                {/if}
                                                <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}" target="_blank">{$v.detail|raw}</a>
                                                {/if}
                                            </div>
                                        </div>
                                        {else/}
                                        <div class="dask-two-line">
                                            {if !$v['answer_info']}
                                            <a href="{:url('question/detail',['id'=>$v['id']])}" target="_blank">{$v.detail|raw}</a>:
                                            {else/}
                                            {if $v['answer_info']['is_anonymous']}
                                            <a href="javascript:;" class="dask-username">匿名用户</a>:
                                            {else/}
                                            <a href="{$v['answer_info']['user_info']['url']}" class="dask-username">{$v['answer_info']['user_info']['name']}</a>:
                                            {/if}
                                            <a href="{:url('question/detail',['id'=>$v['id'],'answer'=>$v['answer_info']['id']])}" target="_blank">{$v.answer_info.content|raw}</a>
                                            {/if}
                                        </div>
                                        {/if}
                            </div>
                            </div>
                            <div class="dask-list-item__actions dask-actions">
                                {if (!$v['answer_info'])}
                                <label>
                                    <a type="button" href="javascript:;" class="{$v['has_focus'] ? 'ygz' : 'gz'} btn btn-primary btn-sm" data-action="user-focus" data-type="question" data-id="{$v.id}">{$v['has_focus'] ? L('已关注') : L('关注问题')} {if ($v.focus_count > 0)}<span class="badge focus-count">{$v.focus_count}</span>{/if}</a>
                                </label>
                                <label class="ms-3 me-3"><i class="iconfont">&#xe870;</i> {:L('点赞')}{$v.agree_count}</label>
                                <label class="me-3"><i class="iconfont">&#xe601;</i> {:L('%s 评论',$v['comment_count'])}</label>
                                {else/}
                                <label class="dz">
                                    <a type="button" href="javascript:;" class="btn btn-primary btn-sm dask-ajax-agree  {$v['answer_info']['vote_value']==1 ? 'active' : ''}" title="{:L('点赞回答')}" data-action="user-agree" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont">&#xe870;</i>{:L('赞同')}{if ($v['answer_info']['agree_count'] > 0)}<span class="badge">{$v['answer_info']['agree_count']}</span>{/if}</a>
                                    <a type="button" href="javascript:;" class="btn btn-primary btn-sm dask-ajax-against  {$v['answer_info']['vote_value']==-1 ? 'active' : ''}" title="{:L('反对回答')}" data-action="user-against" data-type="answer" data-id="{$v['answer_info']['id']}"><i class="iconfont" style="font-size: 12px;">&#xe68f;</i>{:L('反对')}<span class="badge"></span></a>
                                </label>
                                <label class="ms-3 me-3"><i class="iconfont">&#xe625;</i>{:L('%s 感谢',$v['answer_info']['thanks_count'])}</label>
                                <label class="me-3"><i class="iconfont">&#xe601;</i>{:L('%s 评论',$v['answer_info']['comment_count'])}</label>
                                <label class="me-3"><i class="fa fa-comment-alt"></i>{:L('%s 回复',$v['answer_count'])}</label>
                                {/if}
                            </div>
                            </div>
                            {/we:question}
                        </div>
                        <div class="dask-ui-pager dask-card__foot">{$page|raw}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dask-right radius col-md-3 px-xs-0 dask-side dask-card">
            <!--侧边栏顶部钩子-->
            

            {if $theme_config['question']['sidebar_show_items'] && in_array('write_nav',$theme_config['question']['sidebar_show_items'])}
            {:widget('sidebar/writeNav')}
            {/if}

            {if $theme_config['question']['sidebar_show_items'] && in_array('announce',$theme_config['question']['sidebar_show_items'])}
            {:widget('sidebar/announce')}
            {/if}

            {if $theme_config['question']['sidebar_show_items'] && in_array('focus_topic',$theme_config['question']['sidebar_show_items'])}
            {:widget('sidebar/focusTopic',['uid'=>$user_id])}
            {/if}

            {if $theme_config['question']['sidebar_show_items'] && in_array('hot_topic',$theme_config['question']['sidebar_show_items'])}
            {:widget('sidebar/hotTopic',['uid'=>$user_id])}
            {/if}

            {if $theme_config['question']['sidebar_show_items'] && in_array('column',$theme_config['question']['sidebar_show_items'])}
            {:widget('sidebar/hotColumn',['uid'=>$user_id,'sort'=>'hot'])}
            {/if}

            {if $theme_config['question']['sidebar_show_items'] && in_array('hot_users',$theme_config['question']['sidebar_show_items'])}
            {:widget('sidebar/hotUsers',['uid'=>$user_id])}
            {/if}

            {if $theme_config['question']['sidebar_show_items'] && in_array('diy_content',$theme_config['question']['sidebar_show_items'])}
            {$theme_config['question']['sidebar_diy_content']|raw|htmlspecialchars_decode}
            {/if}

            <!--侧边栏底部钩子-->
            
        </div>
    </div>
</div>
</div>
{/block}