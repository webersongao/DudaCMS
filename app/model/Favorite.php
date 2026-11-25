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


class Favorite extends BaseModel
{
    protected $name = 'users_favorite';

    //获取收藏标签列表
    public static function getFavoriteTags($uid, $page = 1, $per_page = 10, $pjax = 'dask-index-main'): array
    {
        $where = ['uid' => $uid];
        $list = db('users_favorite_tag')->where($where)->paginate(
            [
                'list_rows' => $per_page,
                'page' => $page,
                'query' => request()->param(),
                'pjax' => $pjax
            ]
        );
        $pageVar = $list->render();
        $list = $list->all();
        return ['list' => $list, 'page' => $pageVar];
    }

    /**
     * 根据收藏标签ID获取收藏列表
     * @param $uid
     * @param $tag_id
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public static function getFavoriteListByTagId($uid, $tag_id, $page = 1, $per_page = 10): array
    {
        $list = db('users_favorite')->where(['tag_id' => $tag_id])->paginate(
            [
                'list_rows' => $per_page,
                'page' => $page,
                'query' => request()->param()
            ]
        );
        $pageVar = $list->render();
        $list = $list->all();
        $list = PostRelation::processPostList($list, $uid);

        return ['list' => $list, 'page' => $pageVar];
    }

    //检查是否收藏
    public static function checkFavorite($uid, $item_type, $item_id)
    {
        return db('users_favorite')->where(['item_type' => $item_type, 'item_id' => intval($item_id), 'uid' => $uid])->value('id');
    }

    //添加收藏
    public static function saveFavorite($uid, $tag_id, $item_id, $item_type)
    {
        if (!$uid || !$tag_id || !$item_type || !$item_id) return false;

        $id = db('users_favorite')->where(['item_type' => $item_type, 'item_id' => intval($item_id), 'tag_id' => $tag_id, 'uid' => $uid])->value('id');

        if ($id) {
            $result = db('users_favorite')->where(['id' => $id])->delete();
        } else {
            $data = array(
                'item_type' => $item_type,
                'item_id' => (int)$item_id,
                'tag_id' => (int)$tag_id,
                'uid' => (int)$uid,
                'create_time' => time()
            );
            $result = db('users_favorite')->insertGetId($data);
        }

        $post_count = db('users_favorite')->where(['tag_id' => $tag_id])->count();
        db('users_favorite_tag')->where(['id' => $tag_id])->update(['post_count' => $post_count]);
        return ['post_count' => $post_count];
    }

    //添加收藏标签
    public static function saveFavoriteTag($uid, $title, $is_public = 0)
    {
        if (!$uid || !$title) return false;
        $favorite_info = db('users_favorite_tag')->where(['title' => trim($title), 'uid' => $uid])->value('id');
        if ($favorite_info) {
            self::setError('收藏夹已存在');
            return false;
        }

        return db('users_favorite_tag')->insertGetId(array(
            'uid' => (int)$uid,
            'title' => trim(htmlspecialchars($title)),
            'is_public' => (int)$is_public,
            'create_time' => time()
        ));
    }
}
