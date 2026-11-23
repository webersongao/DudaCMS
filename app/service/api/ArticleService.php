<?php
namespace app\service\api;

use app\api\v1\ApiException;
use app\model\Article as ArticleModel;
use app\model\Users;
use think\facade\Db;

class ArticleService
{
    public function list(int $page, int $pageSize): array
    {
        $page = max(1, $page);
        $pageSize = min(20, max(1, $pageSize));

        $paginator = Db::name('article')
            ->where('status', 1)
            ->order('create_time', 'desc')
            ->paginate(['list_rows' => $pageSize, 'page' => $page]);

        $rows = $paginator->toArray();
        $items = $rows['data'] ?? [];
        $uids = array_unique(array_column($items, 'uid'));
        $users = $uids ? Users::getUserInfoByIds($uids) : [];

        foreach ($items as &$item) {
            $item['user_info'] = $users[$item['uid']] ?? null;
        }
        unset($item);

        return [
            'list' => array_map([Presenter::class, 'article'], $items),
            'pagination' => [
                'page' => (int) ($rows['current_page'] ?? $page),
                'page_size' => (int) ($rows['per_page'] ?? $pageSize),
                'total_pages' => (int) ($rows['last_page'] ?? 0),
                'has_more' => (int) ($rows['current_page'] ?? 1) < (int) ($rows['last_page'] ?? 1),
            ],
        ];
    }

    public function detail(int $id): array
    {
        $article = ArticleModel::getArticleInfo($id);
        if (!$article) {
            throw new ApiException(1003, '文章不存在或已删除', 404);
        }

        $article['user_info'] = Users::getUserInfoByUid((int) $article['uid']);
        $data = Presenter::article($article);
        $data['content'] = $article['message'] ?? '';
        $data['topics'] = array_map([Presenter::class, 'topic'], \app\model\Topic::getTopicByItemType('article', $id));

        return $data;
    }
}
