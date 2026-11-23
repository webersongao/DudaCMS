<?php
// +----------------------------------------------------------------------
// | DudaCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2026 https://ask.btbk.org
// +----------------------------------------------------------------------
// | DudaCMS团队一款基于TP8开发的社交化知识付费问答系统、企业内部知识库系统，打造私有社交化问答、内部知识存储
// +----------------------------------------------------------------------
// | Author: DudaCMS团队 <ask@btbk.org>
// +----------------------------------------------------------------------

namespace app\common\library\helper;

class HtmlHelper
{
    /**
     * 替换内容中的图片地址
     * @param string $content 内容原始html
     * @return string 返回替换后的内容
     */
    public static function parseImgUrl(string $content, $url = ''): string
    {
        $images = ImageHelper::srcList($content);
        $url = $url ?: request()->domain();
        if ($images) {
            foreach ($images as $v) {
                if (!strstr($v, 'http') && !strstr($v, 'https')) {
                    $image_url = $url . $v;
                    $content = str_replace($v, $image_url, $content);
                }
            }
        }
        return $content;
    }

    public static function replaceVideo(string $content, string $url = ''): mixed
    {
        preg_match_all('/<iframe.*?\/iframe>/i', $content, $match2);
        $url = $url ?: request()->domain();
        $video = '';
        if (!empty($match2)) {

            foreach ($match2[0] as $v) {
                preg_match('/[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/i', $v, $match);
                if (!empty($match)) {
                    $video = $match[1];
                }
                if (!strstr($video, 'http') && !strstr($video, 'https')) {
                    str_replace($video, $url . $video, $content);
                }
            }
        }
        return str_replace('iframe', 'video', $content);
    }

    /**
     * 解析编辑器内容中单个视频地址
     * @param string $content
     * @param string $url
     * @return mixed|string
     */
    public static function parseVideoUrl(string $content, string $url = ''): mixed
    {
        preg_match('/<iframe.*?\/iframe>/i', $content, $match2);
        $url = $url ?: request()->domain();
        $video = '';
        if (!empty($match2)) {
            preg_match('/[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/i', $match2[0], $match);
            if (!empty($match)) {
                $video = $match[1];
            }
        }

        if (!strstr($video, 'http') && !strstr($video, 'https')) {
            $video = $url . $video;
        }
        return $video;
    }

    /**
     * 获取内容中的视频
     * @param $content
     * @return array|mixed
     */
    public static function parseVideo($content)
    {
        preg_match_all('/<video[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/i', $content, $matches);
        $video = [];
        if (!empty($matches)) {
            $video = $matches[1];
        }
        return !empty($video) ? $video : [];
    }

    public static function replaceVideoUrl($content, $url = '')
    {
        preg_match_all('/<video[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/i', $content, $matches);
        if (empty($matches)) {
            return $content;
        }

        $video = $matches[1];
        $url = $url ?: request()->domain();
        if ($video) {
            foreach ($video as $v) {
                if (!strstr($v, 'http') && !strstr($v, 'https')) {
                    $image_url = $url . $v;
                    $content = str_replace($v, $image_url, $content);
                }
            }
        }
        return $content;
    }
    /**
     * 提取内容中的图片并本地化处理
     * @param $content
     * @param $type
     * @param $uid
     * @param bool $convert 是否解码html
     * @param string $access_key 上传批次 key，传入后本地化图片可被内容保存流程绑定
     * @return array|mixed|string|string[]
     */
    public static function fetchContentImagesToLocal($content, $type, $uid, bool $convert = false, string $access_key = '')
    {
        if (get_setting('download_local_enable') == 'N') {
            // 未开启远程图片本地化时，仍必须做富文本清洗
            $content = DomainHelper::toRelativeHtml(htmlspecialchars_decode((string) $content));
            return htmlspecialchars(self::purify($content));
        }

        //远程图片下载到本地逻辑处理钩子

        $content = htmlspecialchars_decode($content);

        $images = ImageHelper::srcList($content);
        if (!is_array($images)) return $content;

        foreach ($images as  $v) {
            if ((strpos($v, 'http://') !== false || strpos($v, 'https://') !== false) && strpos($v, request()->domain()) === false) {
                // 防 SSRF：只抓取公网 HTTP(S) 图片，且不跟随重定向
                if (!UrlHelper::isPublicHttpUrl($v)) {
                    continue;
                }
                $fileName = explode('/', $v);
                $fileName = end($fileName);
                $imgByte = HttpHelper::get($v, [], [CURLOPT_FOLLOWLOCATION => false]);
                if ($imgByte['code']) {
                    $file = $imgByte['data'];
                    $result = UploadHelper::instance()
                        ->setAccessKey($access_key !== '' ? $access_key : md5($uid . time() . $v))
                        ->setUploadPath($type)
                        ->upload($uid, $fileName, $file);
                    if ($result['code']) {
                        $content = str_replace($v, $result['url'], $content);
                    }
                }
            }
        }
        // 域名解耦：入库前把指向本站的绝对 URL 转为相对路径，
        // 换域名时无需批量改库（站外链接原样保留）。
        $content = DomainHelper::toRelativeHtml($content);
        return htmlspecialchars(self::purify($content));
    }

    /**
     * 远程下载图片到本地
     * @param $url
     * @param string|null $save_dir 目录名称
     * @param int $uid
     * @return string
     */
    public static function downloadImageToLocal($url, string $save_dir = 'common', int $uid = 0)
    {
        if ($url != "") {
            if (!UrlHelper::isPublicHttpUrl($url)) {
                return false;
            }
            $url = str_replace(['&amp;'], ['&'], $url); //url中特定字符替换
            $ext = strrchr($url, '.');
            $mimes = array('.gif', '.jpg', '.png', '.ico');
            if (!in_array($ext, $mimes)) {
                $ext = '.jpg';
            }

            $save_path = config('filesystem.disks.public.root') . DS . $save_dir . DS . date('Ymd', time()) . DS;

            if (!file_exists($save_path)) {
                FileHelper::mkDirs($save_path);
            }

            $filename_r = md5(date('YmdHis', time())) . $ext;    //给图片命名
            $filename = $save_path . $filename_r;
            $file_url = config('filesystem.disks.public.url') . '/' . $save_dir . '/' . date('Ymd', time()) . '/' . $filename_r;
            $imgByte = HttpHelper::get($url, [], [CURLOPT_FOLLOWLOCATION => false]);
            if ($imgByte['data']) {
                FileHelper::createFile($filename, $imgByte['data']);
            }

            return is_file($filename) ? $file_url : '';
        } else {
            return false;
        }
    }

    /**
     * 富文本安全过滤（服务端强制，不依赖 remove_xss 开关）
     */
    public static function purify(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><div id="__dask_root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__dask_root');
        if (!$root) {
            return '';
        }

        $blockedTags = ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'meta', 'link', 'base'];
        $walk = function (\DOMNode $node) use (&$walk, $blockedTags) {
            if ($node instanceof \DOMElement) {
                if (in_array(strtolower($node->tagName), $blockedTags, true)) {
                    $node->parentNode->removeChild($node);
                    return;
                }

                foreach (iterator_to_array($node->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    $value = trim($attr->value);

                    if (strpos($name, 'on') === 0 || $name === 'srcdoc' || $name === 'formaction') {
                        $node->removeAttribute($attr->name);
                        continue;
                    }

                    if (in_array($name, ['href', 'src', 'action', 'xlink:href'], true)) {
                        $lower = strtolower($value);
                        $isImageData = ($node->tagName === 'img' && strpos($lower, 'data:image/') === 0);
                        if (
                            preg_match('#^(javascript|vbscript):#i', $lower)
                            || (strpos($lower, 'data:') === 0 && !$isImageData)
                            || strpos($lower, 'data:text/html') === 0
                        ) {
                            $node->removeAttribute($attr->name);
                        }
                    }

                    if ($name === 'style' && preg_match('/expression\s*\(|javascript\s*:|behavior\s*:/i', $value)) {
                        $node->removeAttribute($attr->name);
                    }
                }
            }

            foreach (iterator_to_array($node->childNodes) as $child) {
                $walk($child);
            }
        };
        $walk($root);

        $result = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $result .= $doc->saveHTML($child);
        }
        return $result;
    }
}
