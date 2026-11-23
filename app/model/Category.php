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

use think\Model;

/**
 * 分类模型
 * Class Category
 * @package app\ask\model
 */
class Category extends Model
{
    /**
     * 根据分类类型获取分类列表
     * @param string $type
     * @param bool $only
     * @param string[] $sort
     * @return array
     */
    public static function getCategoryListByType(string $type = 'common', bool $only = false, $sort = ['sort' => 'DESC']): array
    {
        $where = $only ? [$type] : ['common', $type];
        return db('category')->where(['status' => 1])->whereIn('type', $where)->order($sort)->column('id,title,icon,url_token');
    }

    /**
     * 分类体系已拍平，分类筛选只使用当前分类 ID。
     * @param int $category_id
     * @return array|false
     */
    public static function getCategoryIds($category_id)
    {
        return $category_id ? [(int)$category_id] : false;
    }

    /**
     * @param $ids
     * @return false
     */
    public static function getCategoryListByIds($ids)
    {
        if (!$ids) return false;
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        return db('category')->whereIN('id', $ids)->column('id,title,icon,url_token');
    }
}
