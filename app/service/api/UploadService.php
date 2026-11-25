<?php
namespace app\service\api;

use app\api\v1\ApiException;
use app\common\library\helper\UploadHelper;
use app\common\library\helper\UploadPolicy;

class UploadService
{
    public function upload(int $uid, string $path, string $type): array
    {
        $path = UploadPolicy::normalizePath($path);
        $type = UploadPolicy::normalizeType($type);

        $result = UploadHelper::instance()
            ->setUploadPath($path)
            ->setUploadType($type)
            ->setAccessKey(bin2hex(random_bytes(16)))
            ->upload($uid, '', '');

        if (empty($result['code'])) {
            throw new ApiException(2001, $result['msg'] ?? '上传失败');
        }

        return [
            'url' => Presenter::url($result['url'] ?? ''),
            'attach_id' => (int) ($result['attach_id'] ?? 0),
            'file_name' => (string) ($result['file_name'] ?? ''),
            'is_image' => (int) ($result['is_image'] ?? 0),
            'width' => (int) ($result['width'] ?? 0),
            'height' => (int) ($result['height'] ?? 0),
        ];
    }
}
