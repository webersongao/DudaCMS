<?php
namespace app\service\api;

use app\model\Config;
use app\model\api\v1\Common as CommonModel;

class CommonService
{
    public function config(): array
    {
        $config = Config::getConfigs();
        $keys = [
            'site_name', 'seo_description', 'seo_keywords',
            'register_type', 'topic_enable', 'enable_category', 'contents_per_page',
            'upload_image_ext', 'upload_file_ext', 'upload_max_size',
            'answer_unique',
        ];
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $config[$key] ?? '';
        }
        // 站点描述/关键词统一复用 SEO 配置；logo 使用项目内置静态资源。
        $data['site_description'] = $data['seo_description'];
        $data['site_keywords'] = $data['seo_keywords'];
        $data['site_logo'] = \app\common\library\helper\SiteConfigHelper::assetUrl('logo');
        $data['contents_per_page'] = max(1, (int) $data['contents_per_page']);
        $data['topic_enable'] = ($data['topic_enable'] ?? 'N') === 'Y';
        $data['enable_category'] = ($data['enable_category'] ?? 'N') === 'Y';
        $data['register_type'] = $data['register_type'] ?: 'email';
        // 供纯静态 SPA / PWA 读取当前服务端语言（cookie 为 HttpOnly，前端无法自行读取）
        $lang = (string) \think\facade\Request::cookie('dask_lang', config('lang.default_lang'));
        $data['lang'] = in_array($lang, config('lang.allow_lang_list'), true) ? $lang : config('lang.default_lang');
        return $data;
    }

    public function categories(string $type = 'question'): array
    {
        $type = in_array($type, ['question', 'article', 'common'], true) ? $type : 'question';
        $categories = CommonModel::getCategoryListByType($type);
        $result = [];
        foreach ($categories as $item) {
            $result[] = [
                'id' => (int) ($item['id'] ?? 0),
                'title' => (string) ($item['title'] ?? ''),
                'icon' => (string) ($item['icon'] ?? ''),
                'url_token' => (string) ($item['url_token'] ?? ''),
            ];
        }
        return $result;
    }
}
