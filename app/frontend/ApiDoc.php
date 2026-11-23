<?php
namespace app\frontend;

use app\common\controller\Frontend;

class ApiDoc extends Frontend
{
    protected $needLogin = [];

    public function index()
    {
        $this->guard();

        $file = public_path() . 'static' . DIRECTORY_SEPARATOR . 'api-doc' . DIRECTORY_SEPARATOR . 'content.html';
        $content = is_file($file)
            ? (string) file_get_contents($file)
            : '<p>API 文档内容暂不可用。</p>';

        $this->assign([
            'content' => $content,
            'api_doc_public' => $this->isPublic(),
            'api_base' => request()->domain() . '/api.php/v1',
        ]);
        $this->TDK('API v1 文档', 'DudaCMS API v1 接口文档，支持 App、PWA、H5 接入。', 'DudaCMS API v1 接口文档');

        return $this->fetch();
    }

    public function openapi()
    {
        $this->guard();
        $file = public_path() . 'static' . DIRECTORY_SEPARATOR . 'api-doc' . DIRECTORY_SEPARATOR . 'openapi.yaml';
        if (!is_file($file)) {
            throw new \think\exception\HttpException(404, 'OpenAPI 文件不存在');
        }

        return response((string) file_get_contents($file))
            ->header(['Content-Type' => 'application/yaml; charset=utf-8']);
    }

    private function isPublic(): bool
    {
        return get_setting('api_doc_public', 'Y') === 'Y';
    }

    private function guard(): void
    {
        if ($this->isPublic()) {
            return;
        }
        if (isSuperAdmin() || isNormalAdmin()) {
            return;
        }
        throw new \think\exception\HttpException(404, '页面不存在');
    }
}
