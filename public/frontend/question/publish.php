{extend name="$theme_block" /}
{block name="style"}
<link rel="stylesheet" href="{$assetBase}vendor/select2/css/select2.min.css?v={:asset_version('vendor/select2/css/select2.min.css')}">
<link rel="stylesheet" href="{$assetBase}common/css/dask-publish.css?v={:asset_version('common/css/dask-publish.css')}">
<script src="{$assetBase}vendor/select2/js/select2.full.min.js?v={:asset_version('vendor/select2/js/select2.full.min.js')}"></script>
<script src="{$assetBase}vendor/select2/js/i18n/zh-CN.js?v={:asset_version('vendor/select2/js/i18n/zh-CN.js')}"></script>
{/block}
{block name="main"}
<div class="container mt-2">
    <div class="row justify-content-between">
        <div class="dask-left radius col-md-9 mb-2">
            <div class="card border-0">
                <div class="card-body">
                    <form id="question_form"  method="post" action="{:url('question/publish')}">
                        {:csrf_field()}
                        <input type="hidden" id="captcha">
                        <input type="hidden" name="access_key" value="{$access_key}">
                        <input type="hidden" name="id" value="{$question_info['id']|default=0}">
                        <div class="mb-3 d-flex gap-2 dask-publish-top-row">
                            <div class="flex-fill">
                                <input id="title" name="title" value="{$question_info.title|default=''}" class="dask-form-control" type="text" placeholder="{:L('问题标题')}">
                                <div class="dask-ui-dropdown mt-2 border" style="display: none ;border-radius: 5px" >
                                    <h6 class="px-3 pt-3 text-muted">{:L('标题尽量“信达雅”，不要暴露智商')}</h6>
                                    <div class="dask-ui-dropdown-list dask-common-list dask-overflow-auto text-start px-3 pb-3"></div>
                                </div>
                            </div>
                            {if !empty($category_list) && $setting.enable_category=='Y'}
                            <div class="flex-fill" style="max-width: 150px">
                                <select class="dask-form-control" name="category_id" title="{:L('请选择一项分类')}" required>
                                    <option value="0">{:L('选择分类')}</option>
                                    {volist name="category_list" id="v"}
                                    <option value="{$v.id}" {if isset($question_info['category_id']) && $question_info['category_id']==$v['id']}selected {/if}>{$v.title}</option>
                                    {/volist}
                                </select>
                            </div>
                            {/if}
                        </div>

                        <div class="mb-3 dask-content">
                            <label class="mb-3">{:L('问题详情')}</label>
                            {:editor_render(['name'=>'detail','cat'=>'question','value'=>isset($question_info['detail']) ? $question_info['detail'] : '','access_key'=>$access_key])}
                        </div>

                        <div class="mb-3">
                            <label class="mb-3">{:L('添加话题')}</label>
                            <div class="topic">
                                <div class="tip-list">
                                    <label for="topics" class="d-block w-100">
                                        <select class="select2 form-control" value="{$question_info['topics']|default=''}" id="topics" name="topics[]" multiple>
                                            <option></option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {if(get_setting('attach_enable')=='Y')}
                        <div class="dask-attach-upload mb-3" data-path="question_attach">
                            <a  class="text-primary cursor-pointer fw-bold" id="testList" style="cursor: pointer"><i class="fas fa-cloud-upload-alt"></i>{:L('选择附件')}</a>
                            <input class="layui-upload-file" type="file" accept="" name="file" multiple="">
                            <span class="text-muted font-8">({:L('允许上传文件类型')}:{:get_setting('upload_file_ext')})</span>
                            <a class="cursor-pointer ms-3 fw-bold text-white float-end btn btn-primary px-3 btn-sm" id="uploadListAction" style="cursor: pointer">{:L('开始上传')}</a>
                            <div class="attach-upload-list mt-3">
                                <table class="layui-table">
                                    <thead>
                                    <tr>
                                        <th>{:L('文件名')}</th>
                                        <th>{:L('大小')}</th>
                                        <th>{:L('上传进度')}</th>
                                        <th>{:L('操作')}</th>
                                    </tr>
                                    </thead>
                                    <tbody id="attachList">
                                    {if $attach_list && isset($question_info['id'])}
                                    {volist name="$attach_list" id="v"}
                                    <tr>
                                        <td>{$v.name}</td>
                                        <td>{:formatBytes($v.size)}</td>
                                        <td>
                                            <span class="text-success">{:L('上传成功')}</span>
                                        </td>
                                        <td><button type="button" data-id="{$v.id}" data-key="{$v.access_key}" class="layui-btn layui-btn-xs layui-btn-danger dask-attach-delete">
                                            {:L('删除')}</button></td>
                                    </tr>
                                    {/volist}
                                    {/if}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {/if}
                        <!--发布附加钩子-->
                        
                        <!--发布附加钩子-->

                        <div class="mt-4 clearfix">
                            {if !isset($question_info['id']) && $setting.enable_anonymous=='Y'}
                            <label class="mb-0 text-muted me-3">
                                <input value="1" name="is_anonymous" type="checkbox"  {$question_info.is_anonymous ? 'checked' : ''}>
                                {:L('匿名')}
                            </label>
                            {/if}
                            <a href="{:url('page/credit')}" target="_blank" ><i class="fa fa-database"></i> {:L(get_setting("credit_unit"))}{:L('规则')}</a>
                            {if $setting.auto_save_draft=='Y'}
                            <script>
                                //自动保存时间间隔（内置配置，单位毫秒；30 秒 = 30000）
                                var AutoSaveTime = {$draft_interval_ms|default=30000};
                                //设置自动保存
                                setInterval(function (item_type,itemId){
                                    var formData = Dask.common.formToJSON('question_form');
                                    $.ajax({
                                        url:baseUrl + '/ajax/save_draft',
                                        dataType: 'json',
                                        type:'post',
                                        data:{
                                            data:formData,
                                            item_id:'{$question_info.id|default=0}',
                                            item_type:'question'
                                        },
                                        success: function (result)
                                        {

                                        },
                                        error:  function (error) {
                                            if (daskTrim(error.responseText) !== '') {
                                                layer.closeAll();
                                                Dask.api.error("{:L('发生错误, 返回的信息')}:" + ' ' + error.responseText);
                                            }
                                        }
                                    });
                                }, AutoSaveTime);
                            </script>
                            {/if}
                            {if $user_id && !$user_info['is_valid_email'] && $user_info['email'] && $setting['register_valid_type']=='email'}
                            <a href="/account/send_valid_mail.html" class="btn btn-primary px-3 btn-sm float-end">
                                {:L('验证邮箱')}</a>
                            {else/}
                            <button type="button" class="btn btn-primary px-3 btn-sm dask-question-form float-end">
                                {:L('发表问题')}</button>
                            {/if}
                            <button type="button" data-action="user-draft" data-type="question" data-id="{$question_info.id|default=0}" class="btn btn-outline-primary px-3 btn-sm dask-save-draft me-3 float-end">
                                {:L('存草稿')}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="dask-right radius col-md-3 px-xs-0">
            <div class="r-box mb-2">
                <div class="r-title">
                    <h4>{:L('发布说明')}</h4>
                </div>
                <div class="pb-2">
                    <dl class="text-muted font-9">
                        <dt>{:L('问题标题')}：</dt>
                        <dd class="mt-2">{:L('请用准确的语言描述您发布的问题思想')}</dd>
                    </dl>
                    <dl class="text-muted font-9">
                        <dt>{:L('问题补充')}：</dt>
                        <dd class="mt-2">{:L('详细补充您的问题内容,并提供一些相关的素材以供参与者更多的了解您所要问题的主题思想')}</dd>
                    </dl>
                    <dl class="text-muted font-9">
                        <dt>{:L('选择话题')}：</dt>
                        <dd class="mt-2">{:L('选择一个或者多个合适的话题,让您发布的文章得到更多有相同兴趣的人参与,所有人可以在您发布文章之后添加和编辑该文章所属的话题')}</dd>
                    </dl>
                    <dl class="text-muted font-9">
                        <dt>{:L('关于')}{:L($setting.credit_unit)}：</dt>
                        <dd class="mt-2">{:L('发起一个问题会消耗您')} {$credit_rule.NEW_QUESTION}{:L($setting.credit_unit)},
                            {:L('每多一个回复你将获得')} {$credit_rule.QUESTION_ANSWER} {:L($setting.credit_unit)}{:L('的奖励')} ,{:L('为了您的利益')},
                            {:L('在发起问题的时候希望能够更好的描述您的问题以及多使用站内搜索功能')}.</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let ACCESS_KEY = "{$access_key}";
    let ATTACH_LEN = parseInt('{:count($attach_list)}');
    let ITEM_ID = parseInt("{$question_info && isset($question_info['id']) ? $question_info['id'] : 0}");
    let SYS_ATTACH = "0";
    Dask.Dropdown.bind_dropdown_list('#title', 'publish');
</script>
{/block}

{block name="script"}
<script>
    let createEnable = parseInt("{if $user_info['permission']['create_topic_enable']=='Y' || isNormalAdmin() || isSuperAdmin()}1{else/}0{/if}");
    let topicBox = $('#topics');
    $(function () {
        // 启用ajax分页查询
        var  option = {
            placeholder: "添加话题（最多{:get_setting('max_topic_select')}个，{$setting.topic_enable=='Y'?'必填':'选填'}）",
            language: "zh-CN",
            allowClear: false,
            tags: createEnable === 1,
            createTag: function(params) {
                var term = daskTrim(params.term);
                if (term === '') {
                    return null;
                }
                var match = false;
                // 判断输入的内容是否已经存在选项中
                $.each(this.$element.find('option'), function() {
                    if (daskTrim($(this).text()) === term) {
                        match = true;
                        return false;
                    }
                });
                if (match) {
                    return null;
                }
                // 创建新的选项
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            },
            maximumSelectionLength: Number("{:get_setting('max_topic_select')}"),
            ajax: {
                type:'POST',
                delay: 250, // 限速请求
                url: "{:url('ajax.Topic/get_topics')}",   //  请求地址
                dataType: 'json',
                data: function (params) {
                    return {
                        keyWord: params.term || '',    //搜索参数
                        page: params.page || 1,        //分页参数
                        rows: 10,   //每次查询10条记录,
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data.list,
                        pagination: {
                            more: (params.page) < data.data.total
                        }
                    };
                },
                cache: true
            }
        };
        // 默认值设置
        var defaultValue = topicBox.attr("value");
        if (defaultValue) {
            $.ajax({
                type: "POST",
                url: "{:url('ajax.Topic/get_topics')}",
                data: {value:defaultValue},
                dataType: "json",
                success: function(data){
                    var html = '';
                    $.each(data.data.list,function(index,value){
                        html +="<option selected value='" + value.id + "'>" +value.text + "</option>"
                    })
                    topicBox.append(html);
                }
            });
        }

        $('#topics').select2(option).on('select2:select', function(e) {
            // 如果选择了新创建的选项，则需要把它添加到select2选项中
            if (e.params.data.newTag) {
                var $option = $('', {
                    value: e.params.data.id,
                    text: e.params.data.text,
                    selected: true
                });
                $(this).append($option).trigger('change');
            }
        });
    })
    $('.dask-question-form').click(function (){
        var that = this;
        {if $captcha_enable}
        $('#captcha').captcha({
            callback: function () {
                publishQuestion(that)
            }
        });
        {else/}
            publishQuestion(that)
            {/if}
            })
</script>
{/block}
