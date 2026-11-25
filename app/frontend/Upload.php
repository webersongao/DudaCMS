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
namespace app\frontend;

use app\common\controller\Frontend;
use app\common\library\helper\AttachPathHelper;
use app\common\library\helper\SignedValueHelper;
use app\common\library\helper\UploadHelper;
use app\common\library\helper\UploadPolicy;
use app\model\Attach as AttachModel;

class Upload extends Frontend
{
    // 上传文件
    public function index()
    {
        $uploadObj = UploadHelper::instance();

        $uploadPath = UploadPolicy::normalizePath($this->request->param('path', 'common'));
        $access_key = (string) ($this->request->param('access_key', '') ?: bin2hex(random_bytes(16)));
        $uploadType = $this->request->param('upload_type', 'img');

        // 编辑器统一上传入口：按文件后缀自动识别图片 / 附件，避免前端维护两个地址。
        if ($uploadType === 'auto') {
            $file = $this->request->file('dask-upload-file') ?: $this->request->file('file');
            $ext = $file ? strtolower((string) $file->extension()) : '';
            $uploadType = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true) ? 'img' : 'file';
        }
        $uploadObj->setUploadType(UploadPolicy::normalizeType($uploadType));

        //上传初始化钩子
        $result = $uploadObj->setAccessKey($access_key)->setUploadPath($uploadPath)->upload($this->user_id, '', '');
        return json($result);
    }

    //删除附件
    public function remove_attach()
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id', 0, 'intval');
            $access_key = $this->request->post('access_key');
            if (!$id || !$access_key) {
                $this->error('删除内容错误');
            }
            //删除附件钩子

            if (AttachModel::removeAttach($id, $access_key)) {
                $this->success('删除成功');
            }
            $this->error('删除失败');
        }
    }

    // 附件下载
    public function download()
    {
        if ($this->request->isPost()) {
            if (!$this->user_id) $this->error('请先登录', $this->returnUrl);
            $name = $this->request->post('name', '', 'trim');
            $attachId = SignedValueHelper::verify('attach', $name);
            if ($attachId === null || !ctype_digit($attachId)) {
                $this->error('附件不存在或已被删除', $this->returnUrl);
            }
            $attach = db('attach')->where(['id' => (int) $attachId])->find();
            if (!$attach) $this->error('附件不存在或已被删除', $this->returnUrl);
            // 兼容新旧 path 格式（相对路径 / 旧绝对路径 / URL）
            $filePath = AttachPathHelper::resolve((string) $attach['path']);
            if ($filePath === '' || !file_exists($filePath)) $this->error('附件不存在或已被删除', $this->returnUrl);
            $attach['path'] = $filePath;

            /*附件下载钩子*/

            // 下载附件
            $file_size = filesize($filePath);
            $file = fopen($filePath, "rb");
            header("Content-type: application/octet-stream");  // 二进制流
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . $file_size);
            $filename = $attach['name'] ?: md5($this->user_id . microtime()) . '.' . $attach['ext'];
            $filename = str_replace(["\r", "\n", "\0"], '', basename((string) $filename));
            header("Content-Disposition: attachment; filename=\"" . addslashes($filename) . "\"; filename*=UTF-8''" . rawurlencode($filename));
            echo fread($file, $file_size);  // 输出文件流
            fclose($file);
            exit;
        }
    }

    public function download_file()
    {
        $fileName = trim($this->request->post('url'));
        if (!$this->user_id) $this->error('请先登录', $this->returnUrl);

        $fileName = online_decrypt($fileName);
        $_tmp = parse_url($fileName);
        if (!$_tmp['host']) {
            $fileName = get_setting('cdn_url', $this->baseUrl) . $fileName;
        }
        $this->result(['file' => $fileName], 1);
    }
}
