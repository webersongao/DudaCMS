{extend name="$theme_block" /}
{block name="main"}
<div class="dask-onboarding">
    <header class="dask-onboarding__hero">
        <div class="dask-onboarding__avatar" title="{:L('点击更换头像')}">
            <div id="fileList_cover" class="uploader-list"></div>
            <div id="filePicker_cover" class="dask-onboarding__avatar-picker">
                <img class="image_preview_info" id="cover_preview" src="{$user_info.avatar|default='/static/common/image/default-avatar.svg'}" alt="{$user_info.user_name|default=''}">
                <span class="dask-onboarding__avatar-edit" aria-hidden="true">+</span>
            </div>
        </div>
        <h1 class="dask-onboarding__title">
            {:L('欢迎来到')} {:get_setting('site_name')}{:L('，')}{$user_info.user_name|default=''}
        </h1>
        <p class="dask-onboarding__subtitle">{:L('完善资料可帮助他人更快认识你')}</p>
    </header>

    <form action="{:url('account/welcome_first_login')}" method="post" class="dask-onboarding__form" data-dask-onboarding-form>
        <input type="hidden" name="uid" value="{$user_id}">
        <input type="hidden" name="avatar" value="{$user_info.avatar|default='/static/common/image/default-avatar.svg'}" class="avatar-input">

        <div class="dask-field">
            <label class="dask-field__label" for="onboarding-nickname">{:L('昵称')}</label>
            <input class="form-control dask-field__control" type="text" id="onboarding-nickname" name="nick_name"
                   maxlength="50" autocomplete="nickname" required
                   placeholder="{:L('输入用户昵称')}" value="{$user_info.nick_name|default=''}">
        </div>

        <fieldset class="dask-field">
            <legend class="dask-field__label">{:L('性别')}</legend>
            <div class="dask-segment" role="radiogroup" aria-label="{:L('性别')}">
                <input class="btn-check" type="radio" id="sex1" name="sex" value="1" autocomplete="off" {if ($user_info.sex|default=0) == 1}checked{/if}>
                <label class="btn dask-segment__item" for="sex1">{:L('男')}</label>
                <input class="btn-check" type="radio" id="sex2" name="sex" value="2" autocomplete="off" {if ($user_info.sex|default=0) == 2}checked{/if}>
                <label class="btn dask-segment__item" for="sex2">{:L('女')}</label>
                <input class="btn-check" type="radio" id="sex0" name="sex" value="0" autocomplete="off" {if ($user_info.sex|default=0) == 0}checked{/if}>
                <label class="btn dask-segment__item" for="sex0">{:L('保密')}</label>
            </div>
        </fieldset>

        <div class="dask-field">
            <label class="dask-field__label" for="onboarding-birthday">{:L('生日')}</label>
            <input class="form-control dask-field__control" type="date" id="onboarding-birthday" name="birthday"
                   autocomplete="bday" value="{$user_info['birthday'] && $user_info['birthday'] > 0 ? date('Y-m-d', $user_info['birthday']) : ''}">
            <p class="dask-field__hint">{:L('可稍后在个人资料中修改')}</p>
        </div>

        <div class="dask-field">
            <label class="dask-field__label" for="onboarding-signature">{:L('介绍')}</label>
            <textarea class="form-control dask-field__control" id="onboarding-signature" name="signature"
                      rows="3" maxlength="80" placeholder="{:L('一句话介绍自己')}">{$user_info.signature|default=''}</textarea>
            <p class="dask-field__hint"><span data-dask-onboarding-count>0</span>/80</p>
        </div>

        <footer class="dask-onboarding__footer">
            <a class="dask-onboarding__skip" href="#" role="button" data-action="layer-close-all">{:L('跳过')}</a>
            <button class="btn btn-primary dask-onboarding__submit dask-ajax-form" type="button">{:L('完成')}</button>
        </footer>
    </form>
</div>
<script>
    (function () {
        var scope = document.querySelector('[data-dask-onboarding-form]');
        if (!scope) { return; }
        var textarea = scope.querySelector('textarea[name="signature"]');
        var counter = scope.querySelector('[data-dask-onboarding-count]');
        if (textarea && counter) {
            var sync = function () { counter.textContent = String(textarea.value.length); };
            textarea.addEventListener('input', sync);
            sync();
        }
        if (typeof Dask !== 'undefined' && Dask.upload && Dask.upload.webUpload) {
            Dask.upload.webUpload('filePicker_cover', 'cover_preview', 'avatar', 'avatar');
        }
    })();
</script>
{/block}
