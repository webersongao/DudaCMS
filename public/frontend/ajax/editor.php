<div class="bg-white p-3">
    {if $error==''}
    {if $user_id && ($user_info['permission']['publish_answer_enable']=='Y' || isSuperAdmin() || isNormalAdmin()) }
    <form method="post" action="{:url('ajax.question/save_answer')}" id="ajaxForm">
        {:csrf_field()}
        <input type="hidden" name="question_id" value="{$question_id}">
        <input type="hidden" name="id" value="{$answer_id}">
        <input type="hidden" name="access_key" value="{$access_key}">
        <input type="hidden" id="captcha">
        <div class="mb-3 dask-content">
            {if isset($answer_info['id'])}
            {:editor_render(['name'=>'content','cat'=>'answer','value'=>$answer_info['content'],'access_key'=>$access_key])}
            {else/}
            {:editor_render(['name'=>'content','cat'=>'answer','value'=>isset($answer_info['content']) ? $answer_info['content'] : '','access_key'=>$access_key])}
            {/if}
        </div>

        <!--发布附加钩子-->
        
        <!--发布附加钩子-->

        <div class="mb-3">
            {if !$answer_id}
            {if !$is_focus}
            <label class="font-9 me-3" style="color: #76839b">
                <input class="dask-checkbox" type="checkbox" value="1" name="focus_question"> {:L('关注问题')}
            </label>
            {/if}

            {if $setting.enable_anonymous=='Y'}
            <label class="font-9 me-3" style="color: #76839b">
                <input class="dask-checkbox" type="checkbox" value="1" name="is_anonymous"> {:L('匿名')}
            </label>
            {/if}
            {/if}
            <a href="{:url('page/credit')}" class="font-9" style="color: #76839b" target="_blank"><i class="fa fa-database"></i> {:get_setting("credit_unit")}{:L('规则')}</a>
            {if $setting.auto_save_draft=='Y'}
            <script>
                //自动保存时间间隔（内置配置，单位毫秒；30 秒 = 30000）
                var AutoSaveTime = {$draft_interval_ms|default=30000};
                //设置自动保存
                setInterval(function (item_type,itemId){
                    var formData = Dask.common.formToJSON('ajaxForm');
                    $.ajax({
                        url:baseUrl + '/ajax/save_draft',
                        dataType: 'json',
                        type:'post',
                        data:{
                            data:formData,
                            item_id:'{$answer_info.id|default=0}',
                            item_type:'answer'
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
            {if $user_id && !$user_info['is_valid_email'] && $user_info['email'] && $setting['register_valid_type']=='email'}
            <a href="/account/send_valid_mail.html" class="btn btn-primary btn-sm float-end px-3">{:L('验证邮箱')}</a>
            {else/}
            <button type="button" class="btn btn-primary btn-sm float-end dask-answer-submit px-3">{:L('提交回答')}</button>
            {/if}
        </div>
    </form>
    <script>
        $(document).on('click', '.dask-answer-submit', function (e) {
            var that = this;
            {if $answer_captcha_enable}
            $('#captcha').captcha({
                callback: function () {
                    publishAnswer(that)
                }
            });
            {else/}
            publishAnswer(that)
            {/if}
        });

        function publishAnswer(obj)
        {
            let that = obj;
            var form = $($(obj).parents('form')[0]);
            $(that).attr('disabled',true);

            $.ajax({
                url: form.attr('action'),
                dataType: 'json',
                type: 'post',
                data: form.serialize(),
                success: function (result) {
                    if(result.code)
                    {
                        if(result.code===2)
                        {
                            $(".dask-answer-list").prepend(result.data.html);
                            $('.dask-answer-count').text(result.data.answer_count);
                        }

                        if(result.code===1)
                        {
                            $('#question-answer-'+result.data.id).html(result.data.html);
                        }
                        $('#answerEditor').empty().hide();
                    }else{
                        $(that).attr('disabled',false);
                        if(typeof result.msg !== 'undefined')
                        {
                            layer.msg(result.msg,{},function (){
                                if (typeof result.url !== 'undefined' && result.url) {
                                    window.location.href = result.url;
                                }
                            })
                        }else{
                            if (typeof result.url !== 'undefined' && result.url) {
                                window.location.href = result.url;
                            }
                        }
                    }
                    layer.msg(result.msg);
                }
            });
        }
    </script>
    {else/}
    <p class="text-center">{:L('抱歉')}，{:L('您所在用户组没有回答问题的权限')}！</p>
    {/if}
    {else/}
    <p class="text-center text-danger">{$error}</p>
    {/if}
</div>