<?php
namespace app\service\api;

use app\api\v1\ApiException;
use app\model\Question as QuestionModel;
use think\facade\Db;

class TopicService
{
    public function list(int $page, int $pageSize, string $keyword = ''): array
    {
        $page = max(1, $page);
        $pageSize = min(30, max(1, $pageSize));

        $query = Db::name('topic')->where('status', 1);
        if ($keyword !== '') {
            $query->whereLike('title', '%' . trim($keyword) . '%');
        }

        $paginator = $query->order(['top' => 'desc', 'discuss' => 'desc'])
            ->paginate(['list_rows' => $pageSize, 'page' => $page]);

        $rows = $paginator->toArray();

        return [
            'list' => array_map([Presenter::class, 'topic'], $rows['data'] ?? []),
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
        $topic = Db::name('topic')->where(['id' => $id, 'status' => 1])->find();
        if (!$topic) {
            throw new ApiException(1003, '话题不存在', 404);
        }

        $data = Presenter::topic($topic);
        $ids = Db::name('topic_relation')
            ->where(['topic_id' => $id, 'item_type' => 'question', 'status' => 1])
            ->order('id', 'desc')
            ->limit(10)
            ->column('item_id');

        $questions = $ids ? QuestionModel::getQuestionByIds($ids) : [];
        $data['questions'] = array_map(function ($q) {
            return Presenter::question($q, 0);
        }, array_values($questions ?: []));

        return $data;
    }
}
