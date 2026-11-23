<?php
namespace app\service\api;

/**
 * 首页混合信息流。
 * 上层 Web/PWA/API 共用该服务，避免分别拼装问题与文章列表。
 */
class FeedService
{
    public function __construct(
        private readonly QuestionService $questions = new QuestionService(),
        private readonly ArticleService $articles = new ArticleService()
    ) {
    }

    public function home(int $uid, string $tab, int $page, int $pageSize): array
    {
        $tab = in_array($tab, ['recommend', 'new', 'hot'], true) ? $tab : 'new';
        $page = max(1, $page);
        $pageSize = min(20, max(1, $pageSize));

        $questionData = $this->questions->list($uid, $tab === 'hot' ? 'hot' : 'new', 0, $page, $pageSize);
        $articleData = $this->articles->list($page, $pageSize);

        $items = [];
        foreach ($questionData['list'] ?? [] as $item) {
            $item['type'] = 'question';
            $items[] = $item;
        }
        foreach ($articleData['list'] ?? [] as $item) {
            $item['type'] = 'article';
            $items[] = $item;
        }

        usort($items, static function (array $a, array $b): int {
            return (int) ($b['create_time'] ?? 0) <=> (int) ($a['create_time'] ?? 0);
        });

        $items = array_slice($items, 0, $pageSize);
        $pagination = $questionData['pagination'] ?? [
            'page' => $page,
            'page_size' => $pageSize,
            'total_pages' => 1,
            'has_more' => false,
        ];

        return [
            'tab' => $tab,
            'list' => $items,
            'pagination' => $pagination,
        ];
    }
}
