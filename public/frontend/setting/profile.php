{extend name="$theme_block" /}
{block name="main"}
<div class="dask-wrap mt-2">
    <div class="container">
        <div class="row">
            {:widget('member/userNav',['uid'=>$user_info.uid])}
            <div class="col-md-10">
                {include file="setting/nav"}
                <div class="bg-white mt-1 px-3 py-1" id="tabMain">
                    <form class="p-3 position-relative" action="{:url('account/save_profile')}">
                        <input type="hidden" name="uid" value="{$user_id}">
                        <div class="mb-3 row">
                            <label class="col-md-1 col-form-label">{:L('头像')}</label>
                            <div class="col-md-2">
                                <div id="fileList_cover" class="uploader-list"></div>
                                <div id="filePicker_cover" class="dask-setting-avatar-wrap">
                                    <a href="{:user_avatar_url($user_info['avatar'] ?? '')}" target="_blank">
                                        <img class="image_preview_info dask-setting-avatar" src="{:user_avatar_url($user_info['avatar'] ?? '')}" id="cover_preview" alt="{$user_info.user_name}">
                                    </a>
                                </div>
                                <input type="hidden" name="avatar" value="{:user_avatar_url($user_info['avatar'] ?? '')}" class="avatar-input">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-1 col-form-label"> {:L('昵称')} </label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="nick_name" placeholder="{:L('输入用户昵称')}" value="{$user_info.nick_name}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-1 col-form-label"> {:L('签名')} </label>
                            <div class="col-md-4">
                                <textarea  class="form-control" name="signature" placeholder="{:L('输入个人签名')}">{$user_info.signature}</textarea>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-1 col-form-label"> {:L('性别')} </label>
                            <div class="col-md-4 col-form-label">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sex" id="inlineRadio1" value="1" {if $user_info.sex == 1} checked {/if}>
                                    <label class="form-check-label" for="inlineRadio1">{:L('男')}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sex" id="inlineRadio2" value="2" {if $user_info.sex == 2} checked {/if}>
                                    <label class="form-check-label" for="inlineRadio2">{:L('女')}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sex" id="inlineRadio3" value="0" {if $user_info.sex == 0} checked {/if}>
                                    <label class="form-check-label" for="inlineRadio3">{:L('保密')}</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-1 col-form-label"> {:L('生日')} </label>
                            <div class="col-md-4">
                                <input type="date" class="form-control" id="birthday" name="birthday" value="{$user_info['birthday'] ? date('Y-m-d',$user_info['birthday']) : ''}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-1 col-form-label"> {:L('邮箱')} </label>
                            <div class="col-md-6 d-flex align-items-center gap-2">
                                <input type="email" class="form-control" name="email" placeholder="{:L('暂未绑定邮箱信息')}" value="{$user_info.email}" readonly disabled>
                                <button class="btn btn-sm btn-outline-primary dask-ajax-open flex-shrink-0" data-url="{:url('account/modify_email')}">{:L('修改邮箱')}</button>
                            </div>
                        </div>
                        {if sms_enabled()}
                        <div class="mb-3 row">
                            <label class="col-md-1 col-form-label"> {:L('手机')} </label>
                            <div class="col-md-6 d-flex align-items-center gap-2">
                                <input type="text" class="form-control" placeholder="{:L('暂未绑定手机号码')}" name="mobile" value="{$user_info['mobile'] ? substr_replace($user_info.mobile,'****',3,4) : ''}" readonly disabled>
                                <button class="btn btn-sm btn-outline-primary dask-ajax-open flex-shrink-0" data-url="{:url('account/modify_mobile')}">{:L('修改手机号')}</button>
                            </div>
                        </div>
                        {/if}
                        <div class="row g-2 align-items-center">
                            <label class="col-md-1"> {:L('链接')} </label>
                            <div class="col-md-8 d-flex align-items-center gap-2">
                                <div class="input-group flex-grow-1">
                                    <div class="input-group-text">{$baseUrl.'/people/'}</div>
                                    <input type="text" class="form-control" name="url_token" value="{$user_info['url_token'] ? $user_info['url_token'] : $user_info['user_name']}" readonly disabled>
                                </div>
                                {if $user_id && isset($user_info['url_token_modified']) && !$user_info['url_token_modified']}
                                <button class="btn btn-sm btn-outline-primary dask-ajax-open flex-shrink-0" data-url="{:url('account/modify_urltoken')}">{:L('修改个性域名')}</button>
                                {/if}
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 d-flex justify-content-end">
                                <button class="dask-ajax-form btn btn-primary px-4" type="button">{:L('保存')}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <script>
                    $(function (){
                        //上传头像封面
                        Dask.upload.webUpload('filePicker_cover','cover_preview','avatar','avatar');
                    })
                </script>
            </div>
        </div>
    </div>
</div>
{/block}