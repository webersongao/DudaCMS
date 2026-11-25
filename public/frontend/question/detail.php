{extend name="$theme_block" /}
{block name="meta_script"}
{if $theme_config['common']['enable_mathjax']=='Y'}
<style>
    .MathJax{outline:0;text-align: unset !important;}
</style>
<script async src="{$assetBase}common/js/tex-mml-chtml.js"></script>
{/if}
{/block}

{block name="main"}
<div class="dask-wrap dask-page">
    <div class="container mt-2">
        {if $redirect_message}
        <div class="row">
            <div class="col-sm-12 ps-0">
                {foreach $redirect_message as $key => $message}
                <div class="alert alert-danger">
                    {$message|raw}
                </div>
                {/foreach}
            </div>
        </div>
        {/if}

        {if $question_info['is_lock']}
        <div class="row">
            <div class="col-sm-12 ps-0">
                <div class="alert alert-danger"><i class="icon icon-info"></i> {:L('该问题已被锁定')}！</div>
            </div>
        </div>
        {/if}
        <div class="row dask-grid">
            <div class="dask-left dask-main col-md-9 px-xs-0 mb-1">
                <div class="dask-question-container bg-white pt-3 pb-3 mb-2">
                    <div class="container position-relative">
                        <div class="extend-info position-absolute d-xs-none">
                            <div class="d-flex text-center text-muted">
                                <dl class="flex-fill mb-0 me-4">
                                    <dt class="fw-bold">{$question_info['focus_count']}</dt>
                                    <dd>{:L('关注')}</dd>
                                </dl>
                                <dl class="flex-fill mb-0">
                                    <dt>{$question_info['view_count']}</dt>
                                    <dd>{:L('浏览')}</dd>
                                </dl>
                            </div>
                        </div>
                        <div style="min-height: 70px">
                            <h2 class="mb-3 title font-13 fw-bold">
                                {if $question_info.set_top}
                                <i class="iconfont icon-zhiding text-warning font-14"></i>
                                {/if}
                                {:htmlspecialchars_decode($question_info.title)}

                                
                            </h2>
                            {if !empty($question_info['topics']) || get_user_permission('edit_content_topic')=='Y'}
                            <div class="page-detail-topic mb-2">
                                {if !empty($question_info['topics'])}
                                <ul id="awTopicList" class="d-inline p-0">
                                    {volist name="question_info['topics']" id="v"}
                                    <li class="d-inline-block dask-tag"><a href="{:url('topic/detail',['id'=>$v['id']])}" class="dask-topic" data-id="{$v.id}"><em class="tag">{$v.title}</em></a></li>
                                    {/volist}
                                </ul>
                                {/if}
                                {if $user_id && (get_user_permission('edit_content_topic')=='Y' || $user_id == $question_info.uid || isSuperAdmin() || isNormalAdmin())}
                                <a href="javascript:;" class="dask-ajax-open d-inline" data-url="{:url('topic/select',['item_type'=>'question','item_id'=>$question_info['id']])}"><i class="icon-edit1"> </i></a>
                                {/if}
                            </div>
                            {/if}
                        </div>
                        <div class="dask-content-info">
                            

                            <div class="dask-content position-relative" id="question-content">
                                <div id="show-all" >{$question_info.detail|raw}</div>
                                {if $question_info.detail}
                                <div class="dask-question-show dask-alpha-hidden" style="display: none">
                                    <span style="cursor: pointer;"><i class="icon-chevrons-down"></i> {:L('阅读全文')}</span>
                                </div>
                                <div class="dask-question-hide dask-alpha-hidden" style="display: none;background:none;position: inherit;height: auto">
                                    <span style="position: unset;float: center;cursor: pointer"><i class="icon-chevrons-up"></i> {:L('收起全文')}</span>
                                </div>
                                {/if}
                            </div>

                            
                            {if !$log}
                            <div class="actions dask-actions">
                                {if $isMobile}
                                <label class="me-3">
                                    <button data-action="user-focus" data-type="question" data-id="{$question_info.id}" class="btn btn-primary btn-sm px-3 {if $question_info['has_focus']}active ygz{/if}">{$question_info['has_focus'] ? L('已关注') : L('关注')}</button>
                                </label>
                                <label class="me-3">
                                    <button class="btn btn-outline-primary btn-sm px-3 daskAnswerEditor" data-enable="{$user_id && $user_info['permission']['publish_answer_enable']=='N' ? 1 : 0}" data-question-id="{$question_info['id']}" data-answer-id="0">
                                        {:L('回复')}</button>
                                </label>
                                <label class="me-3">
                                    <a href="javascript:;" class="btn btn-outline-secondary btn-sm {$question_info['vote_value']==1 ? 'active' : ''}" data-action="user-agree" data-type="question" data-id="{$question_info.id}" title="{:L('这是个好问题')}"><i class="fa fa-thumbs-up"></i>
                                        {:L('好问题')}</a>
                                </label>
                                <div class="dropdown d-inline-block float-end">
                                    <a href="javascript:;" data-bs-toggle="dropdown" class="btn btn-default btn-sm" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h d-sm-inline-block"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm">
                                        <div class="text-center d-block py-2" style="min-width: 100px">

                                            <a href="javascript:;" class="dropdown-item">{:L('评论')}<span class="question-comment-count">{$question_info['comment_count']}</span></a>
                                            <a href="javascript:;" class="dropdown-item" data-action="user-favorite" data-type="question" data-id="{$question_info.id}">{if $checkFavorite} {:L('已收藏')}{else}{:L('收藏')}{/if} </a>
                                            <a href="javascript:;" class="dropdown-item" {if !$checkReport} data-action="user-report" data-type="question" data-id="{$question_info.id}" {/if} >{if $checkReport}{:L('已举报')}{else}{:L('举报')}{/if}</a>
                                            <a href="javascript:;" class="dropdown-item" data-action="user-invite" data-id="{$question_info.id}">
                                                <span>{:L('邀请回答')}</span>
                                            </a>
                                            <a class="dropdown-item" href="{:url('question/detail',['id'=>$question_info['id'],'log'=>1])}">
                                                <span>{:L('修改记录')}</span>
                                            </a>
                                            {if isSuperAdmin() || isNormalAdmin() || ($question_info.answer_count < 1 && ($question_info['uid']==$user_id || get_user_permission('modify_question')=='Y'))}
                                            <a href="{:url('question/publish?id='.$question_info['id'])}" class="dropdown-item"><span>{:L('编辑问题')}</span></a>
                                            {/if}
                                            {if isSuperAdmin() || isNormalAdmin() || ($question_info.answer_count < 1 && ($question_info['uid']==$user_id || get_user_permission('remove_question')=='Y'))}
                                            <a class="dask-ajax-get dropdown-item"  href="javascript:;" data-confirm="{:L('是否删除该问题')}?" data-url="{:url('ajax.Question/remove_question',['id'=>$question_info['id']])}">
                                                <span>{:L('删除问题')}</span>
                                            </a>
                                            {/if}
                                            {if isSuperAdmin() || isNormalAdmin() || get_user_permission('recommend_post')=='Y'}
                                            <a href="javascript:;" class="dask-ajax-get dropdown-item" data-url="{:url('ajax.Question/manager',['id'=>$question_info['id'],'type'=>$question_info['is_recommend'] ? 'un_recommend' : 'recommend'])}">
                                                <span>{$question_info['is_recommend'] ? L('取消推荐') : L('推荐问题')}</span>
                                            </a>
                                            {/if}
                                            {if isSuperAdmin() || isNormalAdmin() || get_user_permission('set_top_post')=='Y'}
                                            <a  href="javascript:;" class="dask-ajax-get dropdown-item" data-url="{:url('ajax.Question/manager',['id'=>$question_info['id'],'type'=> $question_info['set_top'] ? 'unset_top' : 'set_top'])}">
                                                <span>{$question_info['set_top'] ? L('取消置顶') : L('置顶问题')}</span>
                                            </a>
                                            {/if}

                                            {if (isSuperAdmin() || isNormalAdmin() || get_user_permission('lock_question')=='Y') && !$question_info['is_lock']}
                                            <a class="dask-ajax-get dropdown-item" data-title="锁定问题" href="javascript:;" data-url="{:url('ajax.Question/lock_question',['question_id'=>$question_info['id']])}">
                                                <span>{:L('锁定问题')}</span>
                                            </a>
                                            {/if}

                                            {if isSuperAdmin() || isNormalAdmin() || get_user_permission('redirect_question')=='Y'}
                                            <a class="dask-ajax-open dropdown-item" data-title="问题重定向" href="javascript:;" data-url="{:url('ajax.Question/redirect_content',['item_id'=>$question_info['id'],'item_type'=>'question'])}">
                                                <span>{:L('问题重定向')}</span>
                                            </a>
                                            {/if}

                                            {if isSuperAdmin() || isNormalAdmin()}
                                            <a class="dask-ajax-open dropdown-item" data-title="添加到帮助" href="javascript:;" data-url="{:url('ajax.Help/select_chapter',['item_id'=>$question_info['id'],'item_type'=>'question'])}">
                                                <span>{:L('添加到帮助')}</span>
                                            </a>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                {else/}
                                <label class="me-3">
                                    <button data-action="user-focus" data-type="question" data-id="{$question_info.id}" class="btn btn-primary btn-sm px-3 {if $question_info['has_focus']}active ygz{/if}">{$question_info['has_focus'] ? L('已关注') : L('关注问题')}</button>
                                </label>
                                <label class="me-3">
                                    <button class="btn btn-outline-primary btn-sm px-3 daskAnswerEditor" data-enable="{$user_id && $user_info['permission']['publish_answer_enable']=='N' ? 1 : 0}" data-question-id="{$question_info['id']}" data-answer-id="0">
                                        {:L('回答问题')}</button>
                                </label>
                                <label class="me-4">
                                    <button class="btn btn-outline-secondary btn-sm px-3" data-action="user-invite" data-id="{$question_info.id}">
                                        {:L('邀请回答')}</button>
                                </label>
                                <label class="me-3">
                                    <a href="javascript:;" class="{$question_info['vote_value']==1 ? 'active' : ''}" data-action="user-agree" data-type="question" data-id="{$question_info.id}" title="{:L('这是个好问题')}"><i class="fa fa-thumbs-up"></i>
                                        {:L('好问题')}</a>
                                </label>
                                <label class="me-3 questionCommentBtn" data-id="{$question_info['id']}">
                                    <a href="javascript:;"><i class="fa fa-keyboard"></i> {:L('评论')}<span class="question-comment-count">{$question_info['comment_count']}</span></a>
                                </label>
                                <label class="me-3">
                                    <a href="javascript:;" data-action="user-favorite" data-type="question" data-id="{$question_info.id}"><i class="fa fa-star"></i>{if $checkFavorite} {:L('已收藏')}{else}{:L('收藏')}{/if} </a>
                                </label>
                                <label class="me-3">
                                    <a href="javascript:;" {if !$checkReport} data-action="user-report" data-type="question" data-id="{$question_info.id}" {/if} ><i class="fa fa-info-circle"></i> {if $checkReport}{:L('已举报')}{else}{:L('举报')}{/if}</a>
                                </label>
                                <div class="dropdown d-inline-block me-3">
                                    <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-fw fa-share-alt font-9 d-none d-sm-inline-block"></i>{:L('分享')}
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm">

                                        <div class="text-center d-block py-2" style="min-width: 100px">
<a href="javascript:;"  class="dropdown-item dask-clipboard" data-clipboard-text="{:url('question/detail',['id'=>$question_info.id],true,true)}"><i class="icon-link"></i>
                                                {:L('复制链接')}</a>
                                            <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['id'=>$question_info.id],true,true)}" data-type="qzone" class="dropdown-item "><i class="fab fa-qq text-primary"></i>
                                                {:L('QQ空间')}</a>
                                            <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['id'=>$question_info.id],true,true)}" data-type="weibo" class="dropdown-item "><i class="fab fa-weibo text-warning"></i>
                                                {:L('新浪微博')}</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['id'=>$question_info.id],true,true)}" data-type="twitter" class="dropdown-item"><i class="fab fa-twitter" style="color:#1DA1F2"></i> Twitter/X</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['id'=>$question_info.id],true,true)}" data-type="telegram" class="dropdown-item"><i class="fab fa-telegram" style="color:#229ED9"></i> Telegram</a>
                                            
                                            <div class="dask-qrcode-container" data-share="{:url('question/detail',['id'=>$question_info.id],true,true)}">
                                                <a href="javascript:;" class="dropdown-item "><i class="fab fa-weixin text-success"></i>
                                                    {:L('微信扫一扫')}</a>
                                                <div class="dask-qrcode text-center py-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {if $user_id}
                                <div class="dropdown d-inline-block">
                                    <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h d-none d-sm-inline-block"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm">
                                        <div class="text-center d-block py-2" style="min-width: 100px">
                                            <a class="dropdown-item" href="{:url('question/detail',['id'=>$question_info['id'],'log'=>1])}">
                                                <span>{:L('修改记录')}</span>
                                            </a>
                                            {if isSuperAdmin() || isNormalAdmin() || ($question_info.answer_count < 1 && ($question_info['uid']==$user_id || get_user_permission('modify_question')=='Y'))}
                                            <a href="{:url('question/publish?id='.$question_info['id'])}" class="dropdown-item"><span>{:L('编辑问题')}</span></a>
                                            {/if}
                                            {if isSuperAdmin() || isNormalAdmin() || ($question_info.answer_count < 1 && ($question_info['uid']==$user_id || get_user_permission('remove_question')=='Y'))}
                                            <a class="dask-ajax-get dropdown-item"  href="javascript:;" data-confirm="{:L('是否删除该问题')}?" data-url="{:url('ajax.Question/remove_question',['id'=>$question_info['id']])}">
                                                <span>{:L('删除问题')}</span>
                                            </a>
                                            {/if}
                                            {if isSuperAdmin() || isNormalAdmin() || get_user_permission('recommend_post')=='Y'}
                                            <a href="javascript:;" class="dask-ajax-get dropdown-item" data-url="{:url('ajax.Question/manager',['id'=>$question_info['id'],'type'=>$question_info['is_recommend'] ? 'un_recommend' : 'recommend'])}">
                                                <span>{$question_info['is_recommend'] ? L('取消推荐') : L('推荐问题')}</span>
                                            </a>
                                            {/if}
                                            {if isSuperAdmin() || isNormalAdmin() || get_user_permission('set_top_post')=='Y'}
                                            <a  href="javascript:;" class="dask-ajax-get dropdown-item" data-url="{:url('ajax.Question/manager',['id'=>$question_info['id'],'type'=> $question_info['set_top'] ? 'unset_top' : 'set_top'])}">
                                                <span>{$question_info['set_top'] ? L('取消置顶') : L('置顶问题')}</span>
                                            </a>
                                            {/if}
                                            {if (isSuperAdmin() || isNormalAdmin() || get_user_permission('lock_question')=='Y') && !$question_info['is_lock']}
                                            <a class="dask-ajax-get dropdown-item" data-title="锁定问题" href="javascript:;" data-url="{:url('ajax.Question/lock_question',['question_id'=>$question_info['id']])}">
                                                <span>{:L('锁定问题')}</span>
                                            </a>
                                            {/if}
                                            {if isSuperAdmin() || isNormalAdmin() || get_user_permission('redirect_question')=='Y'}
                                            <a class="dask-ajax-open dropdown-item" data-title="问题重定向" href="javascript:;" data-url="{:url('ajax.Question/redirect_content',['item_id'=>$question_info['id']])}">
                                                <span>{:L('问题重定向')}</span>
                                            </a>
                                            {/if}

                                            {if isSuperAdmin() || isNormalAdmin()}
                                            <a class="dask-ajax-open dropdown-item" data-title="添加到帮助" href="javascript:;" data-url="{:url('ajax.Help/select_chapter',['item_id'=>$question_info['id'],'item_type'=>'question'])}">
                                                <span>{:L('添加到帮助')}</span>
                                            </a>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                {/if}
                                {/if}
                            </div>

                            <!--评论框动态显示-->
                            <div class="answerCommentBox mt-2 border" id="questionCommentBox-{$question_info.id}" style="display: none">
                                <div class="answerCommentHeader clearfix px-3 pt-3">
                                    <h6 class="font-10 float-start mb-1">{:L('评论')}<span class="question-comment-count">{$question_info.comment_count}</span></h6>
                                </div>
                                <div class="questionCommentList p-3"></div>
                                <div class="pageElement"></div>
                                <div class="commentForm clearfix rounded"></div>
                            </div>
                            {/if}
                        </div>
                    </div>
                </div>

                {if !$log}

                {if $user_id}
                <div id="answerEditor" class="mb-2" style="display: none"></div>
                {/if}
                {if $attach_list}
                <div class="card mb-2 border-0">
                    <div class="card-header border-0 bg-white"><b>{:L('附件列表')}</b></div>
                    <div class="card-body pt-0">
                        {volist name="attach_list" id="v"}
                        <div class="dask-hcard py-1">
                            <div class="dask-hcard__body">
                                <p class="mb-1">{$v.name}</p>
                                <p class="text-muted font-8 mb-0">{:formatBytes($v.size)}</p>
                            </div>
                            <div class="dask-hcard__extra">
                                <a href="javascript:;" class="btn btn-primary btn-sm attach-download" data-name="{$v.auth_key}">{:L('下载')}</a>
                            </div>
                        </div>
                        {/volist}
                    </div>
                </div>
                {/if}
                <div id="answer-container">
                    {if $answer_id}
                    <p class="dask-view-all-answer bg-white p-3 text-center mb-2"><a  data-pjax="answer-container" href="{:url('question/detail',['id'=>$question_info['id'],'answer'=>0])}">{:L('查看全部')} <span class="dask-answer-count">{$question_info.answer_count}</span> 个回答</a></p>
                    {else/}
                    {if get_setting('visitor_view_answer_count',0) && !$user_id}
                    <div class="alert alert-danger mt-3">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <p class="text-center mb-2">{:L('您还未登录!暂时最多只可查看 %s 条回答',get_setting('visitor_view_answer_count',0))}</p>
                        <p class="text-center mt-2">{:L('去')} <a href="javascript:;" data-action="user-login" class="me-1 text-primary">{:L('登录')}</a>!
                            {:L('还没有账号?')}{:L('去')}<a <a href="javascript:;" data-action="user-login" class="text-primary">{:L('注册')}</a></p>
                    </div>
                    {else/}
                    <div class="{$force_fold?'':'d-none'} clearfix py-2 px-3 my-2 bg-primary rounded" id="load_force_fold_answers">
                        <span class="float-end text-light" style="cursor: pointer" data-action="layer-alert" data-message="{:L('被折叠的回复是被你或者被大多数用户不感兴趣的回复')}">{:L('为什么被折叠')}？</span>
                        <a href="javascript:;" class="text-center text-white">{:L('%s 个回复被折叠', $force_fold)}</a>
                    </div>
                    <div class="d-none" id="force_fold_answers_list"></div>
                    {/if}
                    <div class="dask-mod bg-white px-3 pt-3">
                        <div class="dask-mod-head mb-0 border-bottom clearfix pb-3">
                            <p class="float-start me-5 fw-bold">
                                <span class="dask-answer-count">{$question_info.answer_count}</span> {:L('回答')}
                            </p>

                            <div class="dropdown float-end">
                                <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-fw fa-angle-down d-sm-inline-block"></i><span>{$sort_texts[$sort]}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0 border-0 font-size-sm">
                                    <div class="text-center d-block py-2 dask-nav dask-ui-dropdown-nav text-center dask-answer-sort" style="min-width: 100px">
                                        <div class="{$sort=='new' ? 'active':''} py-1"><a href="{:url('question/detail',['id'=>$question_info['id'],'answer'=>0,'sort'=>'new'])}"  data-pjax="answer-container">{:L('最新排序')}</a> </div>
                                        <div class="{$sort=='hot' ? 'active':''} py-1"><a href="{:url('question/detail',['id'=>$question_info['id'],'answer'=>0,'sort'=>'hot'])}"  data-pjax="answer-container">{:L('热门排序')} </a></div>
                                        <div class="{$sort=='publish' ? 'active':''} py-1"><a href="{:url('question/detail',['id'=>$question_info['id'],'answer'=>0,'sort'=>'publish'])}"  data-pjax="answer-container">{:L('只看楼主')} </a></div>
                                        {if $user_id}
                                        <div class="{$sort=='focus' ? 'active':''} py-1"><a href="{:url('question/detail',['id'=>$question_info['id'],'answer'=>0,'sort'=>'focus'])}"  data-pjax="answer-container">{:L('关注的人')} </a></div>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {/if}
                    <div class="dask-answer-body dask-answer-list" id="dask-answer-list" data-id="{$question_info.id}" data-aid="{$answer_id}">
                        {if !empty($data)}
                        <div data-dask-list-items>
                        {volist name="data" id="v"}
                        <div class="dask-answer-item p-3 mb-1 bg-white" data-uninterested_count="{$v.uninterested_count}" id="question-answer-{$v.id}" data-answer-id="{$v.id}">
                            <div class="user-details-card pt-0 pb-2 position-relative clearfix">
                                <div class="user-details-card-avatar float-start" style="position: relative">
                                    {if $v.is_anonymous}
                                    <a href="javascript:;">
                                        <img src="{:user_avatar_url('')}" class="rounded" alt="{:L('匿名用户')}" title="{:L('匿名用户')}" style="width: 40px;height: 40px">
                                    </a>
                                    {else/}
                                    <a href="{$v['user_info']['url']}" class="dask-username dask-avatar-wrap" data-id="{$v.uid}" title="{$v['user_info']['name']}">
                                        <img src="{:user_avatar_url($v['user_info']['avatar'] ?? '')}" class="rounded" alt="{$v['user_info']['name']}" style="width: 40px;height: 40px">
                                        {if $v['user_info']['verified']}
                                        <span class="dask-verified-badge" title="{$v['user_info']['verified_name']|default='已认证'}" aria-label="{$v['user_info']['verified_name']|default='已认证'}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg></span>
                                        {/if}
                                    </a>
                                    {/if}
                                </div>
                                <div class="user-details-card-name float-start ms-2">
                                    {if $v.is_anonymous}<a href="javascript:;" title="{:L('匿名用户')}">{:L('匿名用户')}</a>{else/}<a href="{$v['user_info']['url']}" data-id="{$v.uid}" class="dask-username" title="{$v['user_info']['name']}">{$v['user_info']['name']}</a> <span class="badge text-bg-success">{$v['user_info']['group_name']|default=''}</span> {/if} {if $setting.show_answer_user_ip=='Y' && $v.answer_user_local}<span class="ms-2 text-muted font-8">{:L('用户来自于')}: {$v.answer_user_local}</span>{/if} <br><div class="ms-0 pt-1"> {:date_friendly($v['create_time'])} </div>
                                </div>

                                {if $v['is_best']}
                                <div class="dask-answer-best">
                                    <i class="fa fa-award " title="{:L('最佳回答')}"></i>
                                </div>
                                {/if}
                            </div>

                            <div class="dask-content">
                                <div class="dask-answer-content overflow-hidden">
                                    {:html_entity_decode($v.content)}
                                </div>
                                {if $v.content}
                                <div class="dask-answer-show dask-alpha-hidden" style="display: none">
                                    <span style="cursor: pointer;"><i class="icon-chevrons-down"></i> {:L('阅读全文')}</span>
                                </div>
                                <div class="dask-answer-hide dask-alpha-hidden mt-3" style="display: none;background:none;position: inherit;height: auto">
                                    <span style="position: unset;float: center;cursor: pointer"><i class="icon-chevrons-up"></i> {:L('收起全文')}</span>
                                </div>
                                {/if}
                            </div>

                            <!--回答操作-->
                            <div class="answer-btn-actions dask-actions mt-3">
                                <label class="me-1">
                                    <a href="javascript:;" class="dask-ajax-agree {if $v['vote_value']==1}active{/if}"  data-action="user-agree" data-type="answer" data-id="{$v.id}">
                                        <i class="icon-thumb_up"></i> {:L('赞同')} {if ($v.agree_count > 0)}<span> {$v.agree_count}</span>{/if}
                                    </a>
                                </label>

                                <label class="me-3 ">
                                    <a href="javascript:;" class="dask-ajax-against {if $v['vote_value']==-1}active{/if}"  data-action="user-against" data-type="answer" data-id="{$v.id}">
                                        <i class="icon-thumb_down"></i>
                                    </a>
                                </label>

                                <label class="me-3">
                                    <a href="javascript:;" class="answerCommentBtn" data-url="{:url('comment/answer',['id'=>$v.id])}" data-id="{$v.id}">
                                        <i class="icon-chat"></i> {:L('评论')}{if ($v.comment_count > 0)}<span class="answer-comment-count{$v.id}">{$v.comment_count}</span>{/if}
                                    </a>
                                </label>

                                {if $user_id}
                                <label class="me-3">
                                    <a href="javascript:;" {if !$v.checkReport} data-action="user-report" data-type="answer" data-id="{$v.id}"{/if} ><i class="icon-warning"></i>{$v.checkReport ? L('已举报') : L('举报')}</a>
                                </label>

                                <label class="me-3">
                                    <a href="javascript:;" data-action="user-favorite" data-type="answer" data-id="{$v.id}"><i class="icon-turned_in"></i>{if $v.checkFavorite} {:L('已收藏')}{else}{:L('收藏')}{/if}</a>
                                </label>

                                {if $user_id!=$v['uid']}
                                <label class="me-3">
                                    <a href="javascript:;"  {if $v.has_thanks} class="active" {else/}data-action="user-thanks" data-id="{$v.id}"{/if}>
                                    <i class="icon-favorite"></i> <span>{$v.has_thanks ? L('已感谢') : L('感谢')}</span>
                                    </a>
                                </label>
                                {/if}

                                {if !$v.has_uninterested}
                                <label class="me-3">
                                    <a href="javascript:;" data-action="user-uninterested" data-type="answer" data-id="{$v.id}">
                                        <i class="icon-report"></i> {:L('不感兴趣')}
                                    </a>
                                </label>
                                {/if}

                                {if isSuperAdmin() || isNormalAdmin()}
                                <label class="me-3">
                                    <a href="javascript:;" data-url="{:url('answer/force')}" data-action="user-force-fold-answer" data-id="{$v.id}">
                                        <i class="fa fa-cut "></i> <span>{:L($v.force_fold?'取消折叠':'折叠')}</span>
                                    </a>
                                </label>
                                {/if}

                                {/if}
                                {if ($user_id && get_user_permission('set_best_answer')=='Y' && !$v['is_best'] && !$best_answer_count)}
                                <label class="me-3">
                                    <a href="javascript:;"  class="dask-ajax-get" data-confirm="{:L('是否把该回答设为最佳')}？" data-url="{:url('answer/set_answer_best?answer_id='.$v['id'])}">
                                        <i class="fa fa-award "></i> {:L('最佳')}
                                    </a>
                                </label>
                                {/if}
                                <div class="dropdown d-inline-block me-3">
                                    <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-fw fa-share-alt font-9 d-sm-inline-block"></i> {:L('分享')}
                                    </a>
                                    <div class="dropdown-menu p-0 border-0 font-size-sm">

                                        <div class="text-center d-block py-2" style="min-width: 100px">
<a href="javascript:;"  class="dropdown-item dask-clipboard" data-clipboard-text="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}"><i class="icon-link"></i>
                                                {:L('复制链接')}</a>
                                            <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}" data-type="qzone" class="dropdown-item "><i class="fab fa-qq text-primary"></i>
                                                {:L('QQ空间')}</a>
                                            <a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}" data-type="weibo" class="dropdown-item "><i class="fab fa-weibo text-warning"></i>
                                                {:L('新浪微博')}</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}" data-type="twitter" class="dropdown-item"><i class="fab fa-twitter" style="color:#1DA1F2"></i> Twitter/X</a>
<a href="javascript:;" data-action="user-share" data-title="{$question_info.title}" data-url="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}" data-type="telegram" class="dropdown-item"><i class="fab fa-telegram" style="color:#229ED9"></i> Telegram</a>
                                            
                                            <div class="dask-qrcode-container" data-share="{:url('question/detail',['answer'=>$v.id,'id'=>$question_info.id],true,true)}">
                                                <a href="javascript:;" class="dropdown-item "><i class="fab fa-weixin text-success"></i>
                                                    {:L('微信扫一扫')}</a>
                                                <div class="dask-qrcode text-center py-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--问题回答操作栏钩子-->
                                

                                <div class="dask-share clearfix d-inline-block">
                                    <div class="social-share" data-disabled="google,twitter,facebook,linkedin,douban"></div>
                                </div>
                                {if $user_id && $v.comment_count < 1 && ($user_info['uid']==$v['uid'] || get_user_permission('modify_answer')=='Y' || get_user_permission('remove_answer')=='Y')}
                                <div class="dropdown d-inline-block">
                                    <a href="javascript:;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h d-none d-sm-inline-block"></i>
                                    </a>
                                    <div class="dropdown-menu p-0 border-0 font-size-sm">
                                        <div class="text-center d-block py-2" style="min-width: 100px">
                                            {if $user_info['uid']==$v['uid'] || get_user_permission('modify_answer')=='Y'}
                                            <a href="javascript:;"  class="dropdown-item daskAnswerEditor" data-question-id="{$v.question_id}" data-answer-id="{$v['id']}">{:L('编辑')}</a>
                                            {/if}
                                            {if $user_info['uid']==$v['uid'] || get_user_permission('remove_answer')=='Y'}
                                            <a href="javascript:;" title="{:L('删除回答')}" class="dropdown-item dask-ajax-get" data-confirm="{:L('是否删除该回答')}?" data-url="{:url('answer/delete_answer?answer_id='.$v['id'])}">{:L('删除')}</a>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                {/if}
                            </div>

                            <!--评论框动态显示-->
                            <div class="answerCommentBox mt-2 border" id="answerCommentBox-{$v.id}" style="display: none;margin-left: 45px">
                                <div class="answerCommentHeader clearfix px-3 pt-3">
                                    <h6 class="font-10 float-start mb-1">{:L('评论')}{if ($v.comment_count > 0)}<span class="answer-comment-count{$v.id}">{$v.comment_count}</span>{/if}</h6>
                                </div>
                                <div class="answerCommentList px-3"></div>
                                <div class="pageElement"></div>
                                <div class="commentForm clearfix rounded dask-replay-box"></div>
                            </div>
                        </div>
                        {/volist}
                        </div>
                        {if $page_render}
                        <div class="bg-white p-3 dask-ui-pager">
                            {$page_render|raw}
                        </div>
                        {/if}
                        {else/}
                        <p class="text-center text-muted p-3 bg-white">
                            <img src="{$assetBase}common/image/empty.svg">
                            <span class="d-block">{:L('暂无回答')}</span>
                        </p>
                        {/if}
                    </div>
                </div>
                {else/}
                <div class="dask-mod bg-white px-3 pt-3">
                    <div class="dask-mod-head mb-0 border-bottom clearfix pb-3">
                        <p class="float-start me-5 fw-bold">
                            <span class="dask-answer-count">{:L('修改记录')}
                        </p>
                        <a href="{:url('question/detail',['id'=>$question_info.id])}" class="float-end text-muted font-8" data-pjax="wrapMain">{:L('返回问题')} »</a>
                    </div>

                    <div class="dask-mod-body py-3 dask-modify-log">
                        {foreach $logs as $k=>$v}
                        <dl class="border-bottom">
                            <dt>{$v.label|raw}</dt>
                            <dd class="mt-2">{$v.content|raw}</dd>
                            <dd>{:front_time($v['create_time'] ?? 0)}</dd>
                        </dl>
                        {/foreach}
                    </div>
                </div>
                {/if}
            </div>
            <div class="dask-right dask-side col-md-3 px-xs-0">
                {if get_theme_setting('question_detail.sidebar_show_items') && in_array('write_nav',get_theme_setting('question_detail.sidebar_show_items'))}
                {:widget('sidebar/writeNav')}
                {/if}

                

                {if !$question_info['is_anonymous']}
                <div class="r-box dask-card mb-1">
                    <div class="r-title dask-card__head">
                        <h4>{:L('关于作者')}</h4>
                    </div>
                    <div class="block-content dask-card__body">
                        <div class="dask-hcard pb-2 border-bottom">
                            <div class="dask-hcard__media">
                                <a href="{$question_info['user_info']['url']}" data-pjax="pageMain" class="dask-username dask-avatar-wrap" data-id="{$question_info.uid}">
                                    <img src="{:user_avatar_url($question_info['user_info']['avatar'] ?? '')}" class="rounded" width="45" height="45">
                                    {if $question_info['user_info']['verified']}
                                    <span class="dask-verified-badge" title="{$question_info['user_info']['verified_name']|default='已认证'}" aria-label="{$question_info['user_info']['verified_name']|default='已认证'}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.2 4.2L19 7"/></svg></span>
                                    {/if}
                                </a>
                            </div>
                            <div class="dask-hcard__body">
                                <a href="{$question_info['user_info']['url']}" class="d-block dask-one-line dask-username" data-id="{$question_info.uid}" target="_blank">
                                    <strong>{$question_info['user_info']['name']}</strong> <span class="badge text-bg-success">{$question_info['user_info']['group_name']|default=''}</span>
                                </a>
                                <p class="mb-0 font-8 text-muted dask-one-line mt-2">{$question_info['user_info']['signature']|default=L('这家伙很懒，还没有设置简介')}</p>
                            </div>
                        </div>
                        <div class="d-flex text-center pt-3 pb-3 text-muted">
                            <a href="{:get_user_url($question_info['uid'],['type'=>'answer'])}" target="_blank" class="flex-fill mb-0">
                                <dl class="mb-0">
                                    <dt>{$publish_answer_count}</dt>
                                    <dd>{:L('回答')}</dd>
                                </dl>
                            </a>
                            <a href="{:get_user_url($question_info['uid'],['type'=>'article'])}" target="_blank" class="flex-fill mb-0">
                                <dl class="mb-0">
                                    <dt>{$publish_article_count}</dt>
                                    <dd>{:L('文章')}</dd>
                                </dl>
                            </a>
                            <a href="{:get_user_url($question_info['uid'],['type'=>'question'])}" target="_blank" class="flex-fill mb-0">
                                <dl class="mb-0">
                                    <dt>{$publish_question_count}</dt>
                                    <dd>{:L('问题')}</dd>
                                </dl>
                            </a>
                        </div>
                        {if $user_id && $user_id!=$question_info['uid']}
                        <div class="user-btn-group d-flex text-center dask-actions">
                            <div class="me-2 flex-fill">
                                <button class="btn btn-primary btn-sm w-100 {$question_info['user_focus'] ? 'active ygz' : ''}" data-action="user-focus" data-type="user" data-id="{$question_info.uid}">{$question_info['user_focus'] ? L('已关注') : L('关注TA')}</button>
                            </div>
                            <div class="ms-2 flex-fill">
                                <button data-action="user-inbox" data-name="{$question_info['user_info']['nick_name']}" class="btn btn-outline-secondary btn-sm w-100">
                                    {:L('发私信')}</button>
                            </div>
                        </div>
                        {/if}
                    </div>
                </div>
                {/if}

                <div class="r-box dask-card mb-2">
                    <div class="r-title dask-card__head">
                        <h4>{:L('问题动态')}</h4>
                    </div>
                    <div class="block-content2 dask-card__body pb-2">
                        <div class="text-center text-muted">
                            <dl class="dask-kv-row mb-1">
                                <dt>{:L('发布时间')}</dt>
                                <dd class="dask-kv-time" title="{:front_time($question_info['create_time'], 'Y-m-d H:i')}">{:date_compact($question_info['create_time'])}</dd>
                            </dl>
                            <dl class="dask-kv-row mb-1">
                                <dt>{:L('更新时间')}</dt>
                                <dd class="dask-kv-time" title="{:front_time($question_info['update_time'], 'Y-m-d H:i')}">{:date_compact($question_info['update_time'])}</dd>
                            </dl>
                            <dl class="dask-kv-row mb-1">
                                <dt>{:L('关注')}</dt>
                                <dd>{:L('%s 人关注',$question_info['focus_count'])}</dd>
                            </dl>
                        </div>
                        <div>
                            <ul id="questionFocusUsers">
                                {foreach $question_focus_users as $k=>$v}
                                <li class="px-1 d-inline-block">
                                    <a href="{$v.url}" class="dask-username" data-id="{$v.uid}" title="{$v.nick_name}" >
                                        <img src="{:user_avatar_url($v['avatar'] ?? '')}" width="26" height="26" style="border-radius: 50%" alt="{$v.nick_name}">
                                    </a>
                                </li>
                                {/foreach}
                            </ul>
                        </div>

                    </div>
                </div>

                {if get_theme_setting('question_detail.sidebar_show_items') && in_array('announce',get_theme_setting('question_detail.sidebar_show_items'))}
                {:widget('sidebar/announce')}
                {/if}
                
                {if $relation_question && get_theme_setting('question_detail.sidebar_show_relation_question')=='Y'}
                <div class="r-box dask-card mb-2">
                    <div class="r-title dask-card__head">
                        <h4>{:L('相关问题')}</h4>
                    </div>
                    <div class="aboutanswer dask-card__body">
                        {volist name="relation_question" id="v"}
                        <div class="dask-side-list mb-0 py-2">
                            <div class="dask-side-list__title d-block dask-one-line fw-normal font-9">
                                <a href="{:url('question/detail',['id'=>$v['id']])}">{$v.title}</a>
                            </div>
                            <div class="dask-side-list__meta mt-2 text-color-info mb-0">
                                <label class="mb-0" title="{:L('浏览')}"><i class="fas fa-eye"></i>{$v.view_count}</label>
                                <label class="mb-0" title="{:L('关注')}"><i class="fas fa-user-friends"></i>{$v.focus_count}</label>
                                <label class="mb-0" title="{:L('回答')}"><i class="fas fa-comment-dots"></i>{$v.answer_count}</label>
                                <label class="mb-0" title="{:L('评论')}"><i class="fas fa-comment"></i>{$v.comment_count}</label>
                            </div>
                        </div>
                        {/volist}
                    </div>
                </div>
                {/if}

                {if $recommend_post && get_theme_setting('question_detail.sidebar_show_recommend_post')=='Y'}
                <div class="r-box dask-card mb-2">
                    <div class="r-title dask-card__head">
                        <h4>{:L('推荐内容')}</h4>
                    </div>
                    <div class="aboutanswer dask-card__body">
                        {volist name="recommend_post" id="v"}
                        {if $v['item_type']=='article'}
                        <div class="dask-side-list mb-0 py-2 border-bottom">
                            <div class="dask-side-list__title d-block dask-one-line fw-normal font-9">
                                <span class="bg-primary text-white font-8 d-inline-block text-center rounded" style="width: 18px;height: 18px">文</span> <a href="{:url('article/detail',['id'=>$v['id']])}">{$v.title}</a>
                            </div>
                            <div class="dask-side-list__meta mt-2 font-9 text-color-info mb-0">
                                <label class="mb-0" title="{:L('浏览')}"><i class="fas fa-eye"></i>{$v.view_count}</label>
                                <label class="mb-0" title="{:L('评论')}"><i class="fas fa-comment"></i>{$v.comment_count}</label>
                            </div>
                        </div>
                        {/if}
                        {if $v['item_type']=='question'}
                        <div class="dask-side-list mb-0 py-2 border-bottom">
                            <div class="dask-side-list__title d-block dask-one-line fw-normal font-9">
                                <span class="bg-warning text-white font-8 d-inline-block text-center rounded" style="width: 18px;height: 18px">问</span> <a href="{:url('question/detail',['id'=>$v['id']])}">{$v.title}</a>
                            </div>
                            <div class="dask-side-list__meta mt-2 font-9 text-color-info mb-0">
                                <label class="mb-0" title="{:L('浏览')}"><i class="fas fa-eye"></i>{$v.view_count}</label>
                                <label class="mb-0" title="{:L('关注')}"><i class="fas fa-user-friends"></i>{$v.focus_count}</label>
                                <label class="mb-0" title="{:L('回答')}"><i class="fas fa-comment-dots"></i>{$v.answer_count}</label>
                                <label class="mb-0" title="{:L('评论')}"><i class="fas fa-comment"></i>{$v.comment_count}</label>
                            </div>
                        </div>
                        {/if}
                        {/volist}
                    </div>
                </div>
                {/if}

                {if get_theme_setting('question_detail.sidebar_show_items') && in_array('focus_topic',get_theme_setting('question_detail.sidebar_show_items'))}
                {:widget('sidebar/focusTopic',['uid'=>$user_id])}
                {/if}

                {if get_theme_setting('question_detail.sidebar_show_items') && in_array('hot_topic',get_theme_setting('question_detail.sidebar_show_items'))}
                {:widget('sidebar/hotTopic',['uid'=>$user_id])}
                {/if}

                {if get_theme_setting('question_detail.sidebar_show_items') && in_array('column',get_theme_setting('question_detail.sidebar_show_items'))}
                {:widget('sidebar/hotColumn',['uid'=>$user_id,'sort'=>'hot'])}
                {/if}

                {if get_theme_setting('question_detail.sidebar_show_items') && in_array('hot_users',get_theme_setting('question_detail.sidebar_show_items'))}
                {:widget('sidebar/hotUsers',['uid'=>$user_id])}
                {/if}

                {if get_theme_setting('question_detail.sidebar_show_items') && in_array('diy_content',get_theme_setting('question_detail.sidebar_show_items'))}
                {$theme_config['home']['sidebar_diy_content']|raw|htmlspecialchars_decode}
                {/if}

                
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script src="{$static_url}js/question/detail.js?v={:asset_version('frontend/js/question/detail.js')}"></script>
<script>
    $(document).on('click', '#load_force_fold_answers a', function(event) {
        if($('#force_fold_answers_list').hasClass('d-none'))
        {
            $.ajax({
                type: 'GET',
                url: "{:url('ajax.Question/force_fold_answers',['question_id'=>$question_info['id']])}",
                dataType:'json',
                success: function (ret) {
                    $('#force_fold_answers_list').html(ret.data.html).removeClass('d-none');
                },
                error: function (xhr) {
                    let ret = {code: xhr.status, msg: xhr.statusText, data: null};
                    Dask.events.onAjaxError(ret, error);
                }
            });
        }else{
            $('#force_fold_answers_list').html('').addClass('d-none');
        }
    })

    $(document).ready(function ()
    {
        var openEnable="{$setting.open_content_enable}";
        var answerId = parseInt("{$answer_id ? $answer_id : 0}");
        var showAll = $('#show-all');
        if(openEnable==='Y')
        {
            $('.dask-question-show').click();
            $('.dask-answer-show').click();
            if(showAll.height() >= 120)
            {
                $('.dask-question-hide').show();
            }
        }else{
            if(showAll.height() >= 120)
            {
                showAll.show().css('height','120px');
                $('.dask-question-show').show();
            }
        }
        $(document).on('click', '.dask-question-show', function (e) {
            $('.dask-question-show').hide();
            showAll.show().css('height','auto');
            $('.dask-question-hide').show();
        });
        $(document).on('click', '.dask-question-hide', function (e) {
            $('.dask-question-hide').hide();
            showAll.show().css('height','120px');
            $('.dask-question-show').show();
        });
        $('.dask-answer-item .dask-answer-content').each(function(){
            if($(this).height() >= 200)
            {
                if(openEnable==='Y')
                {
                    $(this).css('height','auto');
                    $(this).parents('.dask-answer-item').find('.dask-answer-hide').show();
                }else{
                    if(answerId)
                    {
                        $(this).css('height','auto');
                        $(this).parents('.dask-answer-item').find('.dask-answer-hide').show();
                    }else{
                        $(this).css('height','200px');
                        $(this).parents('.dask-answer-item').find('.dask-answer-show').show();
                    }
                }
            }
        });

        $(document).on('click', '.dask-answer-show', function (e) {
            $(this).hide();
            $(this).parents('.dask-answer-item').find('.dask-answer-content').show().css('height','auto');
            $(this).parents('.dask-answer-item').find('.dask-answer-hide').show();
        });

        $(document).on('click', '.dask-answer-hide', function (e) {
            $(this).hide();
            $(this).parents('.dask-answer-item').find('.dask-answer-content').show().css('height','200px');
            $(this).parents('.dask-answer-item').find('.dask-answer-show').show();
        });
    });
</script>
{/block}