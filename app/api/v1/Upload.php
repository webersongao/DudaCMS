<?php
namespace app\api\v1;

use app\service\api\UploadService;

class Upload extends Base
{
    public function image()
    {
        $this->requireLogin();
        return Response::ok((new UploadService())->upload($this->uid, $this->stringParam('path', 'common'), 'image'), '上传成功');
    }

    public function file()
    {
        $this->requireLogin();
        return Response::ok((new UploadService())->upload($this->uid, $this->stringParam('path', 'common'), 'file'), '上传成功');
    }
}
