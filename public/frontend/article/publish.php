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
                    <form class="dask-form" method="post" action="{:url('article/publish')}" id="ajaxForm">
                        <input type="hidden" name="id" value="{$article_info.id|default=0}">
                        <!--<input type="hidden" name="wait_time">-->
                        <input type="hidden" name="access_key" value="{$access_key}">
                        <input type="hidden" id="captcha">
                        {:csrf_field()}
                        <div class="mb-3 d-flex gap-2 dask-publish-top-row">
                            <div class="flex-fill">
                                <input class="dask-form-control" type="text" name="title" value="{$article_info['title']|default=''}" placeholder="{:L('文章标题')}">
                            </div>
                            {if $article_category_list && $setting.enable_category=='Y'}
                            <div class="flex-fill" style="max-width: 150px">
                                <select class="dask-form-control" name="category_id">
                                    <option value="0">{:L('选择分类')}</option>
                                    {volist name="article_category_list" id="v"}
                                    <option value="{$v.id}" {if isset($article_info['category_id']) && $article_info['category_id']==$v['id']}selected {/if}>{$v.title}</option>
                                    {/volist}
                                </select>
                            </div>
                            {/if}
                            {if($column_list)}
                            <div class="flex-fill" style="max-width: 150px">
                                <select class="dask-form-control" name="column_id">
                                    <option value="0">{:L('选择专栏')}</option>
                                    {volist name="column_list" id="v"}
                                    <option value="{$v.id}" {if isset($article_info['column_id']) && $v['id']==$article_info['column_id']}selected{/if}>{$v.name}</option>
                                    {/volist}
                                </select>
                            </div>
                            {/if}
                        </div>
                        <div class="mb-3">
                            <label>{:L('文章封面')}:<span class="font-8 text-muted">（* {:L('建议图片尺寸')}178*100{:L('的倍数')}）</span></label>
                            <div class="article-cover-box">
                                <div id="fileList_cover" class="uploader-list"></div>
                                <div id="filePicker_cover" style="margin: 0 auto">
                                    <a href="{$article_info['cover']|default='/static/common/image/default-cover.svg'}" target="_blank">
                                        <img class="image_preview_info" src="{$article_info['cover']|default='/static/common/image/default-cover.svg'}" id="cover_preview" width="100" height="100">
                                    </a>
                                </div>
                                <input type="hidden" name="cover" value="{$article_info['cover']|default=''}" class="article-cover">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="mb-3">添加话题</label>
                            <div class="topic">
                                <div class="tip-list">
                                    <label for="topics" class="d-block w-100">
                                        <select class="select2 form-control" value="{$article_info['topics']|default=''}" id="topics" name="topics[]" multiple>
                                            <option></option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 dask-content">
                            <label class="mb-3">{:L('文章详情')}</label>
                            {:editor_render(['name'=>'message','cat'=>'article','value'=>isset($article_info['message']) ? $article_info['message']:'','access_key'=>$access_key])}
                        </div>
                        {if(get_setting('attach_enable')=='Y')}
                        <div class="dask-attach-upload mb-3" data-path="article_attach">
                            <a  class="text-primary cursor-pointer fw-bold" id="testList" style="cursor: pointer"><i class="fas fa-cloud-upload-alt"></i> {:L('选择附件')}</a>
                            <input class="layui-upload-file" type="file" accept="" name="file" multiple="">
                            <span class="text-muted font-8">({:L('允许上传文件类型')}:{:get_setting('upload_file_ext')})</span>
                            <a class="cursor-pointer btn btn-primary text-white px-3 btn-sm ms-3 fw-bold float-end" id="uploadListAction" style="cursor: pointer">{:L('开始上传')}</a>
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
                                    {if $attach_list && isset($article_info['id'])}
                                    {volist name="$attach_list" id="v"}
                                    <tr>
                                        <td>{$v.name}</td>
                                        <td>{:formatBytes($v.size)}</td>
                                        <td>
                                            <span class="text-success">{:L('上传成功')}</span>
                                        </td>
                                        <td><button type="button" data-id="{$v.id}" data-key="{$v.access_key}" class="layui-btn layui-btn-xs layui-btn-danger dask-attach-delete">{:L('删除')}</button></td>
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
                            <a href="{:url('page/credit')}" target="_blank" ><i class="fa fa-database"></i> {:get_setting("credit_unit")}{:L('规则')}</a>
                            {if $user_id && !$user_info['is_valid_email'] && $user_info['email'] && $setting['register_valid_type']=='email'}
                            <a href="/account/send_valid_mail.html" class="btn btn-primary btn-sm px-4 me-3 float-end">
                                {:L('验证邮箱')}</a>
                            {else/}
                            <button type="button" class="btn btn-primary btn-sm px-4 dask-article-publish me-3 float-end">{:L('发表文章')}</button>
                            {/if}
                            {if $setting.auto_save_draft=='Y'}
                            <script>
                                //自动保存时间间隔（内置配置，单位毫秒；30 秒 = 30000）
                                let AutoSaveTime = {$draft_interval_ms|default=30000};
                                //设置自动保存
                                setInterval(function (item_type,itemId){
                                    var formData = Dask.common.formToJSON('ajaxForm');
                                    $.ajax({
                                        url:baseUrl + '/ajax/save_draft',
                                        dataType: 'json',
                                        type:'post',
                                        data:{
                                            data:formData,
                                            item_id:'{$article_info.id|default=0}',
                                            item_type:'article'
                                        },
                                        success: function (result)
                                        {

                                        },
                                        error:  function (error) {
                                            if (daskTrim(error.responseText) !== '') {
                                                layer.closeAll();
                                                Dask.api.error('发生错误, 返回的信息:' + ' ' + error.responseText);
                                            }
                                        }
                                    });
                                }, AutoSaveTime);
                            </script>
                            {/if}
                            <button type="button" data-action="user-draft" data-type="article" data-id="{$article_info.id|default=0}" class="btn btn-outline-primary px-4 btn-sm me-3 float-end">{:L('存草稿')}</button>
                            <button type="button" data-url="{:url('article/preview')}" class="btn btn-outline-primary btn-sm dask-preview px-4 me-3 float-end d-none d-sm-block">{:L('预览')}</button>
                        </div>

                        {if !isset($article_info['id'])}
                        <script id="timing-publish-modal" type="text/html">
                            <div class="rounded p-3">
                                <div class="mb-3">
                                    <label for="timing"><input type="text" id="timing" placeholder="{:L('选择定时发布时间')}" class="form-control"></label>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm px-4 me-3 select-choose">{:L('确定选择')}</button>
                            </div>
                        </script>
                        {/if}
                    </form>
                </div>
            </div>
        </div>
        <div class="dask-right radius col-md-3 px-xs-0">
            
            <div class="r-box mb-2">
                <div class="r-title">
                    <h4>{:L('发布说明')}</h4>
                </div>
                <div class="pb-2 fbsm">
                    <dl class="text-muted font-9">
                        <dt>{:L('文章标题')}：</dt>
                        <dd class="mt-2">{:L('请用准确的语言描述您发布的文章思想')}</dd>
                    </dl>

                    <dl class="text-muted font-9">
                        <dt>{:L('文章补充')}：</dt>
                        <dd class="mt-2">{:L('详细补充您的文章内容')}, {:L('并提供一些相关的素材以供参与者更多的了解您所要文章的主题思想')}</dd>
                    </dl>

                    <dl class="text-muted font-9">
                        <dt>{:L('选择话题')}：</dt>
                        <dd class="mt-2">{:L('选择一个或者多个合适的话题,让您发布的文章得到更多有相同兴趣的人参与,所有人可以在您发布文章之后添加和编辑该文章所属的话题')}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let ACCESS_KEY = "{$access_key}";
    let ATTACH_LEN = parseInt('{:count($attach_list)}');
    let ITEM_ID = parseInt("{$article_info['id']??0}");
    let SYS_ATTACH = "0";
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
    $('.dask-article-publish').click(function (){
        var that = this;
        {if $captcha_enable}
        $('#captcha').captcha({
            callback: function () {
                publishArticle(that)
            }
        });
        {else/}
            publishArticle(that)
            {/if}
            })
</script>
{/block}