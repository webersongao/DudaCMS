<?php

namespace app\common\library\helper;

use think\facade\Request;

/**
 * DudaCMS 内置 Vditor 编辑器渲染器。
 *
 * 设计目标：
 * - 全站统一入口，替代原 plugins/editor 的编辑器事件接管方式。
 * - 保留现有内容契约：表单字段提交“净化后的 HTML”，textarea 中保存可编辑 HTML 源码。
 * - 所有行为由 dask_config group 13「编辑器」配置驱动。
 */
class EditorHelper
{
    /**
     * 渲染一个内置 Vditor 编辑器。
     */
    public static function render(array $params = []): string
    {
        // $assetsLoaded 改为请求级：CLI server / 常驻进程下 static 会跨请求保留，
        // 导致后续 AJAX 弹窗只输出内联 init 而不输出 adapter.js，编辑器渲染为空白。
        static $assetsLoaded = false;
        $isAjax = \think\facade\Request::param('_ajax_open') || \think\facade\Request::isAjax();
        if ($isAjax) {
            $assetsLoaded = false;
        }

        $name       = isset($params['name']) ? (string) $params['name'] : 'content';
        $safeName   = preg_replace('/[^A-Za-z0-9_-]/', '_', $name) ?: 'content';
        $cat        = isset($params['cat']) && $params['cat'] !== '' ? (string) $params['cat'] : 'common';
        $accessKey  = isset($params['access_key']) && $params['access_key'] !== ''
            ? (string) $params['access_key']
            : md5('dask-editor-' . $safeName);
        $value      = isset($params['value']) ? (string) $params['value'] : '';

        // 内容可能已经 htmlspecialchars 过一次（编辑页），也可能来自 form builder 的原始 HTML。
        // 先解码一次再按 textarea 上下文转义，textarea.val() 得到的仍是原始 HTML。
        $editableHtml = htmlspecialchars($value === '' ? '' : htmlspecialchars_decode($value, ENT_QUOTES), ENT_QUOTES, 'UTF-8');

        $cfg = self::settings();

        $isMobile = Request::isMobile() ? 1 : 0;
        $mode     = $cfg['mode'];
        if ($isMobile && $mode !== 'ir') {
            $mode = 'ir';
        }

        $uploadEnabled = $cfg['upload_enable'] === 'Y' && ($cfg['image_enable'] === 'Y' || $cfg['video_enable'] === 'Y');
        $acceptTypes   = [];
        if ($cfg['image_enable'] === 'Y') {
            $acceptTypes[] = 'image/*';
        }
        if ($cfg['video_enable'] === 'Y') {
            $acceptTypes[] = 'video/*';
        }
        $maxBytes = 0;
        if ($cfg['image_enable'] === 'Y') {
            $maxBytes = max($maxBytes, $cfg['max_image'] * 1024 * 1024);
        }
        if ($cfg['video_enable'] === 'Y') {
            $maxBytes = max($maxBytes, $cfg['max_video'] * 1024 * 1024);
        }
        if ($maxBytes <= 0) {
            $maxBytes = 10 * 1024 * 1024;
        }

        $uploadUrl = $uploadEnabled
            ? get_url('upload/index', [
                'upload_type' => 'auto',
                'path'        => $cat,
                'access_key'  => $accessKey,
            ])
            : '';

        $jsConfig = [
            'name'              => $safeName,
            'cdn'               => asset('vendor/vditor', false),
            'mode'              => $mode,
            'isMobile'          => $isMobile,
            'preset'            => $cfg['toolbar_preset'],
            'contentTheme'      => $cfg['content_theme'],
            'codeHighlight'     => $cfg['code_highlight'] === 'Y',
            'floatToolbar'      => $cfg['float_toolbar_enable'] === 'Y',
            'cache'             => $cfg['cache_enable'] === 'Y',
            'counter'           => $cfg['counter_enable'] === 'Y',
            'upload'            => $uploadEnabled ? [
                'url'      => $uploadUrl,
                'field'    => 'dask-upload-file',
                'accept'   => implode(',', $acceptTypes),
                'max'      => $maxBytes,
                'timeout'  => $cfg['upload_timeout'],
            ] : null,
        ];

        $json = json_encode(
            $jsConfig,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );

        $html = [];
        $html[] = '<div class="dask-editor dask-ui-editor" data-dask-editor="' . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8') . '">';
        $html[] = '<textarea style="display:none" id="' . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8') . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">' . $editableHtml . '</textarea>';
        $html[] = '<div id="vditor_' . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8') . '" class="dask-editor-vditor"></div>';
        $html[] = '</div>';

        if (!$assetsLoaded) {
            $assetsLoaded = true;
            $html[] = '<link rel="stylesheet" href="' . htmlspecialchars(asset('vendor/vditor/dist/index.css'), ENT_QUOTES, 'UTF-8') . '">';
            $html[] = '<link rel="stylesheet" href="' . htmlspecialchars(asset('common/editor/vditor-adapter.css'), ENT_QUOTES, 'UTF-8') . '">';
            $html[] = '<script src="' . htmlspecialchars(asset('vendor/vditor/dist/index.min.js'), ENT_QUOTES, 'UTF-8') . '"></script>';
            $html[] = '<script src="' . htmlspecialchars(asset('common/editor/vditor-adapter.js'), ENT_QUOTES, 'UTF-8') . '"></script>';
        }

        $html[] = '<script>window.DaskEditor = window.DaskEditor || {}; window.DaskEditor.configs = window.DaskEditor.configs || {}; window.DaskEditor.configs[' . json_encode($safeName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . '] = ' . $json . '; window.DaskEditor.init(' . json_encode($safeName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');</script>';

        return implode("\n", $html);
    }

    /**
     * 读取编辑器配置，并提供安全默认值。
     */
    private static function settings(): array
    {
        $defaults = [
            'mode'                => 'wysiwyg',
            'max_image'           => 10,
            'max_video'           => 50,
            'code_highlight'      => 'Y',
            'video_enable'        => 'Y',
            'toolbar_preset'      => 'full',
            'content_theme'       => 'auto',
            'image_enable'        => 'Y',
            'upload_enable'       => 'Y',
            'float_toolbar_enable'=> 'N',
            'cache_enable'        => 'N',
            'counter_enable'      => 'Y',
            'upload_timeout'      => 30000,
        ];

        $keys = [
            'mode'                 => 'editor_mode_default',
            'max_image'            => 'editor_upload_max_image',
            'max_video'            => 'editor_upload_max_video',
            'code_highlight'       => 'editor_code_highlight',
            'video_enable'         => 'editor_video_enable',
            'toolbar_preset'       => 'editor_toolbar_preset',
            'content_theme'        => 'editor_content_theme',
            'image_enable'         => 'editor_image_enable',
            'upload_enable'        => 'editor_upload_enable',
            'float_toolbar_enable' => 'editor_float_toolbar_enable',
            'cache_enable'         => 'editor_cache_enable',
            'counter_enable'       => 'editor_counter_enable',
            'upload_timeout'       => 'editor_upload_timeout',
        ];

        $out = [];
        foreach ($keys as $field => $name) {
            $value = get_setting($name, $defaults[$field]);
            $out[$field] = $value === null || $value === '' ? $defaults[$field] : $value;
        }

        if (!in_array($out['mode'], ['wysiwyg', 'ir', 'sv'], true)) {
            $out['mode'] = 'wysiwyg';
        }
        if (!in_array($out['toolbar_preset'], ['full', 'simple'], true)) {
            $out['toolbar_preset'] = 'full';
        }
        if (!in_array($out['content_theme'], ['auto', 'light', 'dark', 'ant-design'], true)) {
            $out['content_theme'] = 'auto';
        }
        foreach (['code_highlight', 'video_enable', 'image_enable', 'upload_enable', 'float_toolbar_enable', 'cache_enable', 'counter_enable'] as $flag) {
            $out[$flag] = $out[$flag] === 'N' ? 'N' : 'Y';
        }
        $out['max_image']      = max(0, (int) $out['max_image']);
        $out['max_video']      = max(0, (int) $out['max_video']);
        $out['upload_timeout'] = max(1000, (int) $out['upload_timeout']);

        return $out;
    }
}
