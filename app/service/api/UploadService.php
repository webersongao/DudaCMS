<?php
namespace app\service\api;

use app\api\v1\ApiException;
use app\common\library\helper\UploadHelper;

class UploadService
{
    public function upload(int $uid, string $path, string $type): array
    {
        $path = in_array($path, ['question', 'answer', 'article', 'avatar', 'common'], true) ? $path : 'common';
        $type = $type === 'file' ? 'file' : 'image';

        $result = UploadHelper::instance()
            ->setUploadPath($path)
            ->setUploadType($type)
            ->setAccessKey(md5($uid . time() . random_int(1000, 9999)))
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
