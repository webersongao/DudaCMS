<?php
// +----------------------------------------------------------------------
// | DudaCMS 统一媒体后处理：图片压缩/水印、可选视频压缩与视频封面。
// +----------------------------------------------------------------------

namespace app\common\library\helper;

class MediaProcessor
{
    /** 图片上传后统一处理：先生成缩略图，再给原图与缩略图叠加水印。 */
    public static function processImage(string $absoluteFile, string $extension, string $url): array
    {
        $thumb = $url;
        $ext = strtolower($extension);
        if (config_bool('upload_image_thumb_enable', true) && !in_array($ext, ['gif', 'bmp'], true)) {
            $percent = (float) get_setting('upload_image_thumb_percent', 0.7);
            if ($percent > 0 && $percent < 1) {
                $thumbPath = UploadPolicy::thumbName($absoluteFile);
                ImageHelper::compressImg($thumbPath, $absoluteFile, $percent);
                if (is_file($thumbPath)) {
                    $thumb = UploadPolicy::thumbName($url);
                }
            }
        }

        if (config_bool('watermark_enable')) {
            ImageHelper::addTextWatermark($absoluteFile);
            $thumbPath = UploadPolicy::thumbName($absoluteFile);
            if (is_file($thumbPath)) {
                ImageHelper::addTextWatermark($thumbPath);
            }
        }

        return ['url' => $url, 'thumb' => $thumb];
    }

    /** 视频处理：默认不改变上传文件；仅在显式开启且 ffmpeg 可用时转码。 */
    public static function processVideo(string $absoluteFile, string $extension): void
    {
        if (!config_bool('video_compress_enable')) {
            return;
        }
        $ffmpeg = trim((string) get_setting('ffmpeg_path', 'ffmpeg'));
        if ($ffmpeg === '' || !is_executable($ffmpeg)) {
            return;
        }
        $ext = strtolower($extension);
        if (!in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm', 'flv'], true)) {
            return;
        }

        $crf = max(18, min(40, config_int('video_compress_crf', 28)));
        $preset = strtolower(trim((string) get_setting('video_compress_preset', 'veryfast')));
        $presets = ['ultrafast', 'superfast', 'veryfast', 'faster', 'fast', 'medium', 'slow'];
        $preset = in_array($preset, $presets, true) ? $preset : 'veryfast';
        $maxWidth = max(320, config_int('video_compress_max_width', 1280));

        $tmp = $absoluteFile . '.transcoded.mp4';
        $cmd = sprintf(
            '%s -y -i %s -c:v libx264 -preset %s -crf %d -vf %s -c:a aac -b:a 128k -movflags +faststart %s 2>&1',
            escapeshellarg($ffmpeg), escapeshellarg($absoluteFile), escapeshellarg($preset),
            $crf, escapeshellarg('scale=trunc(min(iw\\,' . $maxWidth . ')/2)*2:-2'), escapeshellarg($tmp)
        );
        try {
            exec($cmd, $output, $code);
            if ($code === 0 && is_file($tmp) && filesize($tmp) > 0) {
                @rename($tmp, $absoluteFile);
            } else {
                @unlink($tmp);
            }
        } catch (\Throwable $e) {
            @unlink($tmp);
        }
    }
}
