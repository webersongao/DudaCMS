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

namespace app\model;

use app\common\library\helper\SignedValueHelper;
use app\service\StorageCleanupService;
use think\Model;

class Attach extends Model
{
    protected $name = 'attach';

    public static function updateAttach($item_type, $item_id, $attach_access_key)
    {
        if (!is_numeric($item_id) or !$attach_access_key) {
            return false;
        }
        $uid = (int) getLoginUid();
        if ($uid > 0 && !\app\common\library\helper\UploadPolicy::verifyAccessKey((string) $attach_access_key, $uid)) {
            return false;
        }

        $result = self::update(array('item_id' => $item_id), ['item_type' => $item_type, 'item_id' => 0, 'access_key' => $attach_access_key]);
        if ($result) {
            // 编辑内容后，旧批次附件不再被引用，执行带引用检查的物理清理。
            StorageCleanupService::markByItems($item_type, [(int) $item_id]);
            StorageCleanupService::flush();
        }
        return $result;
    }

    public static function removeAttach($id, $access_key): bool
    {
        $attach = db('attach')->where(['id' => intval($id), 'access_key' => $access_key])->find();
        if (!$attach) {
            return false;
        }
        return StorageCleanupService::deleteAttachNow((int) $attach['id']);
    }

    public static  function getAttach($item_type, $item_id)
    {
        if (!is_numeric($item_id)) {
            return false;
        }
        $attach_list =  db('attach')->where(['item_type' => $item_type, 'item_id' => $item_id])->order('id', 'DESC')->select()->toArray();

        if ($attach_list) {
            foreach ($attach_list as $k => $v) {
                $attach_list[$k]['auth_key'] = SignedValueHelper::sign('attach', (string) $v['id']);
            }
        }
        return $attach_list;
    }

    // 批量删除附件
    public static function batchRemoveAttach($where)
    {
        $attaches = db('attach')->where($where)->field('id')->select()->toArray();
        $ids = array_column($attaches, 'id');
        $deleted = 0;
        foreach ($ids as $attachId) {
            $deleted += StorageCleanupService::markAttach((int) $attachId) ? 1 : 0;
        }
        StorageCleanupService::flush();
        return $deleted;
    }

    /**
     * 删除附件
     * @param $item_type
     * @param $item_ids
     * @return mixed
     */
    public static function removeAttachByItemIds($item_type, $item_ids)
    {
        return StorageCleanupService::deleteByItemsNow($item_type, (array) $item_ids);
    }

    public static function attachInfo($where)
    {
        return self::where($where)->find()->toArray();
    }
}
