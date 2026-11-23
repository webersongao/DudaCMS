{extend name="$theme_block" /}
{block name="header"}{/block}
{block name="main"}
<style>
    /* 私信会话：原生弹窗内只有消息流一处滚动，输入区固定在底部，杜绝双层滚动条 */
    html, body { height: auto; }
    .layui-layer-content { overflow: hidden !important; }
    .dask-im {
        display: flex; flex-direction: column;
        height: 480px; min-height: 480px; background: var(--dask-bg);
    }
    @media (max-width: 767.98px) {
        .dask-im { height: 70vh; min-height: 380px; }
    }
    .dask-im-stream {
        flex: 1 1 auto; min-height: 0; overflow-y: auto; overscroll-behavior: contain;
        padding: 18px 20px 12px; background: var(--dask-bg);
    }
    .dask-im-item { display: flex; align-items: flex-start; gap: 10px; margin: 0 0 16px; }
    .dask-im-item.me { flex-direction: row-reverse; }
    .dask-im-avatar {
        flex: 0 0 34px; width: 34px; height: 34px; border-radius: 50%;
        object-fit: cover; background: var(--dask-blue-tint);
    }
    .dask-im-msg { display: flex; flex-direction: column; align-items: flex-start; min-width: 0; max-width: min(74%, 520px); }
    .dask-im-item.me .dask-im-msg { align-items: flex-end; }
    .dask-im-bubble {
        padding: 9px 14px; border-radius: 12px; border: 1px solid var(--dask-line);
        background: var(--dask-surface); color: var(--dask-text);
        font-size: 14px; line-height: 1.65; word-break: break-word; overflow-wrap: anywhere;
        white-space: pre-wrap; box-shadow: 0 1px 2px rgba(18, 18, 18, .04);
        border-top-left-radius: 3px;
    }
    .dask-im-item.me .dask-im-bubble {
        background: var(--dask-blue); border-color: var(--dask-blue); color: #fff;
        border-top-left-radius: 12px; border-top-right-radius: 3px;
    }
    .dask-im-bubble p { margin: 0; font-size: inherit; line-height: inherit; }
    .dask-im-meta { margin-top: 4px; font-size: 11px; line-height: 1.4; color: var(--dask-text-3); }
    .dask-im-meta .unread { color: var(--dask-text-3); }
    .dask-im-composer { flex: 0 0 auto; background: var(--dask-surface); border-top: 1px solid var(--dask-line); padding: 12px 16px 14px; }
    .dask-im-composer textarea {
        display: block; width: 100%; min-height: 76px; max-height: 180px; resize: vertical;
        border: 1px solid var(--dask-line-2) !important; border-radius: var(--dask-r-md) !important;
        background: var(--dask-surface) !important; padding: 10px 12px; font-size: 14px; line-height: 1.6;
    }
    .dask-im-composer textarea:focus { border-color: var(--dask-blue) !important; box-shadow: var(--dask-focus) !important; }
    .dask-im-actions { display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 10px; }
    .dask-im-actions .btn { margin: 0 !important; }
    .dask-im-empty { text-align: center; color: var(--dask-text-3); font-size: 13px; padding: 40px 0; }
    .dask-im-tools { display: flex; align-items: center; gap: 8px; margin-top: 8px; position: relative; }
    .dask-im-tool {
        display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px;
        border: 1px solid var(--dask-line-2); background: var(--dask-surface); color: var(--dask-text-2);
        font-size: 12px; line-height: 1.4; cursor: pointer;
    }
    .dask-im-tool:hover, .dask-im-tool:focus-visible { color: var(--dask-blue); border-color: var(--dask-blue); }
    .dask-im-emoji-panel {
        position: absolute; left: 0; bottom: 36px; z-index: 30; width: min(340px, calc(100vw - 48px));
        max-height: 216px; overflow-y: auto; padding: 8px; border-radius: 10px;
        border: 1px solid var(--dask-line); background: var(--dask-elevated, var(--dask-surface));
        box-shadow: 0 10px 30px rgba(0, 0, 0, .12);
        display: grid; grid-template-columns: repeat(8, 1fr); gap: 2px;
    }
    .dask-im-emoji-panel[hidden] { display: none; }
    .dask-im-emoji-panel button {
        display: flex; align-items: center; justify-content: center; width: 100%; aspect-ratio: 1;
        padding: 2px; border: 0; border-radius: 6px; background: transparent; cursor: pointer;
    }
    .dask-im-emoji-panel button:hover { background: var(--dask-blue-tint); }
    .dask-im-emoji-panel img { width: 100%; height: 100%; object-fit: contain; }
    .dask-im-bubble .dask-im-emoji { width: 22px; height: 22px; vertical-align: -5px; margin: 0 1px; }
    .dask-im-bubble .dask-im-image { display: block; max-width: 100%; max-height: 240px; border-radius: 8px; margin: 6px 0; }
</style>
<div class="dask-im">
    <div id="inbox-dialog-container" class="dask-im-stream">
        {if !$user_name}
        <div class="dask-im-empty">{:L('选择一位用户开始对话')}</div>
        {/if}
    </div>

    <form method="post" action="{:url('inbox/send')}" class="dask-im-composer">
        {if $user_name}
        <input type="hidden" name="recipient_uid" value="{$user_name}">
        {else/}
        <div class="mb-3 position-relative mb-2">
            <input type="text" class="form-control" id="searchUser" name="recipient_uid" value="" placeholder="{:L('搜索您想要发送私信的用户')}...">
            <div class="dask-ui-dropdown mt-2 border px-3" style="display: none">
                <div class="dask-ui-dropdown-list dask-overflow-auto text-start"></div>
            </div>
            <script>
                Dask.Dropdown.bind_dropdown_list('#searchUser', 'inbox');
            </script>
        </div>
        {/if}
        <textarea name="message" rows="3" class="form-control" placeholder="{:L('私信内容')}"></textarea>
        <div class="dask-im-tools">
            <button type="button" class="dask-im-tool" data-im-tool="emoji" aria-label="{:L('表情')}">{:L('表情')}</button>
            <button type="button" class="dask-im-tool" data-im-tool="image" aria-label="{:L('图片')}">{:L('图片')}</button>
            <div class="dask-im-emoji-panel" hidden></div>
            <input type="file" name="inbox_image" accept="image/*" hidden>
        </div>
        <div class="dask-im-actions">
            <button class="btn btn-primary px-4 dask-ajax-submit" type="button">{:L('发送私信')}</button>
        </div>
    </form>
</div>
{/block}
{block name="script"}
<script src="{$static_url}js/ajax/inbox.js?v={:asset_version('frontend/js/ajax/inbox.js')}"></script>
{/block}
