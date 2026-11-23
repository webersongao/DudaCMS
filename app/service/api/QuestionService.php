<?php
namespace app\service\api;

use app\api\v1\ApiException;
use app\logic\common\FocusLogic;
use app\model\Answer;
use app\model\Question as QuestionModel;
use app\model\Topic;
use app\model\Users;
use app\model\UsersFavorite;
use app\model\Vote;
use think\facade\Db;

class QuestionService
{
    public function list(int $uid, string $sort, int $categoryId, int $page, int $pageSize): array
    {
        $sort = safe_content_sort($sort, 'new');
        $page = max(1, $page);
        $pageSize = min(20, max(1, $pageSize));

        $data = QuestionModel::getQuestionList($uid, $sort, $categoryId ?: null, null, $page, $pageSize, 0, 'tabMain');
        $list = $data['list'] ?? [];
        $totalPages = max(1, (int) ($data['total'] ?? 1));

        return [
            'list' => array_map(function ($item) use ($uid) {
                return Presenter::question($item, $uid);
            }, $list),
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages,
            ],
        ];
    }

    public function detail(int $id, int $uid, int $answerPage = 1, int $answerSize = 10): array
    {
        $question = QuestionModel::getQuestionInfo($id);
        if (!$question || (isset($question['status']) && (int) $question['status'] === 0)) {
            throw new ApiException(1003, '问题不存在或已被删除', 404);
        }

        QuestionModel::updateQuestionViews($id, $uid);

        $question['user_info'] = Users::getUserInfoByUid((int) $question['uid']);
        $question['topics'] = Topic::getTopicByItemType('question', $id);
        $question['has_focus'] = FocusLogic::checkUserIsFocus($uid, 'question', $id) ? 1 : 0;
        $question['vote_value'] = Vote::getVoteByType($id, 'question', $uid);
        $question['is_favorite'] = UsersFavorite::checkFavorite($uid, 'question', $id) ? 1 : 0;

        $answerPage = max(1, $answerPage);
        $answerSize = min(20, max(1, $answerSize));
        $answers = Answer::getAnswerByQuestionId($id, 0, $answerPage, $answerSize, 'new', $uid);

        $detail = Presenter::question($question, $uid);
        $detail['detail'] = $question['detail'] ?? '';
        $detail['focus_count'] = (int) ($question['focus_count'] ?? 0);
        $detail['is_favorite'] = (int) ($question['is_favorite'] ?? 0);
        $detail['answers'] = [
            'list' => array_map(function ($a) use ($uid) {
                return Presenter::answer($a, $uid);
            }, $answers['data'] ?? []),
            'pagination' => [
                'page' => (int) ($answers['current_page'] ?? $answerPage),
                'page_size' => (int) ($answers['per_page'] ?? $answerSize),
                'total_pages' => (int) ($answers['last_page'] ?? 1),
                'has_more' => !empty($answers['has_more']),
            ],
        ];

        return $detail;
    }

    public function create(int $uid, array $input): array
    {
        $title = trim(strip_tags((string) ($input['title'] ?? '')));
        $detail = (string) ($input['detail'] ?? '');
        $plain = trim(strip_tags(htmlspecialchars_decode($detail)));

        if (mb_strlen($title) < 5 || mb_strlen($title) > 120) {
            throw new ApiException(1004, '标题需为 5-120 个字符', 422);
        }
        if ($plain === '') {
            throw new ApiException(1004, '请填写问题详情', 422);
        }
        if (mb_strlen($plain) > 20000) {
            throw new ApiException(1004, '问题详情过长', 422);
        }

        $topics = $this->normalizeTopics($input['topics'] ?? []);
        if (get_setting('topic_enable') === 'Y' && !$topics) {
            throw new ApiException(1004, '请至少选择一个话题', 422);
        }

        // 远端图片本地化后需要绑定批次，否则会成为游离附件。
        $accessKey = (string) ($input['access_key'] ?? '');
        if ($accessKey === '') {
            $accessKey = md5($uid . '|' . microtime(true) . '|' . bin2hex(random_bytes(6)));
        }

        $id = QuestionModel::saveQuestion($uid, [
            'id' => 0,
            'title' => $title,
            'detail' => $detail,
            'is_anonymous' => 0,
            'category_id' => (int) ($input['category_id'] ?? 0),
            'topics' => $topics,
            'status' => 1,
            'question_type' => 'normal',
        ], $accessKey);

        if (!$id) {
            throw new ApiException(2001, QuestionModel::getError() ?: '发布失败');
        }

        return [
            'id' => (int) $id,
            'url' => '/question/detail.html?id=' . (int) $id,
        ];
    }

    public function answer(int $uid, int $questionId, string $content, string $accessKey = ''): array
    {
        $question = QuestionModel::getQuestionInfo($questionId, 'id,title,uid,status');
        if (!$question || (int) ($question['status'] ?? 0) === 0) {
            throw new ApiException(1003, '问题不存在或已被删除', 404);
        }

        $plain = trim(strip_tags(htmlspecialchars_decode($content)));
        if ($plain === '') {
            throw new ApiException(1004, '请填写回答内容', 422);
        }
        if (get_setting('answer_unique') === 'Y'
            && Db::name('answer')->where(['uid' => $uid, 'question_id' => $questionId, 'status' => 1])->count()
        ) {
            throw new ApiException(2001, '一个问题只能回答一次，你可以编辑已有回答');
        }
        if (mb_strlen($plain) > 20000) {
            throw new ApiException(1004, '回答内容过长', 422);
        }

        if ($accessKey === '') {
            $accessKey = md5($uid . '|' . microtime(true) . '|' . bin2hex(random_bytes(6)));
        }
        $result = Answer::saveAnswer([
            'id' => 0,
            'question_id' => $questionId,
            'uid' => $uid,
            'content' => $content,
            'is_anonymous' => 0,
        ], $accessKey);

        if (!$result) {
            throw new ApiException(2001, Answer::getError() ?: '回答失败');
        }

        $answerId = (int) ($result['info']['id'] ?? 0);
        return [
            'id' => $answerId,
            'answer_count' => (int) ($result['answer_count'] ?? 0),
            'url' => '/question/detail.html?id=' . $questionId . '#answer-' . $answerId,
        ];
    }

    public function vote(int $uid, int $id, int $value): array
    {
        if (!in_array($value, [1, -1], true)) {
            throw new ApiException(1004, '投票值只能是 1 或 -1', 422);
        }
        if (!QuestionModel::getQuestionInfo($id, 'id')) {
            throw new ApiException(1003, '问题不存在', 404);
        }

        Vote::saveVote($uid, $id, 'question', $value);
        $row = Db::name('question')->where('id', $id)->field('agree_count,against_count')->find();

        return [
            'agree_count' => (int) ($row['agree_count'] ?? 0),
            'against_count' => (int) ($row['against_count'] ?? 0),
            'vote_value' => Vote::getVoteByType($id, 'question', $uid),
        ];
    }

    public function favorite(int $uid, int $id): array
    {
        if (!QuestionModel::getQuestionInfo($id, 'id')) {
            throw new ApiException(1003, '问题不存在', 404);
        }

        $where = ['uid' => $uid, 'item_type' => 'question', 'item_id' => $id];
        $exists = Db::name('users_favorite')->where($where)->find();
        if ($exists) {
            Db::name('users_favorite')->where('id', $exists['id'])->delete();
            $favorited = false;
        } else {
            Db::name('users_favorite')->insert($where + ['tag_id' => 0, 'create_time' => time()]);
            $favorited = true;
        }

        return ['favorited' => $favorited];
    }

    /**
     * 取消投票（DELETE .../vote）：value=0 走 Vote::saveVote 的删除分支。
     */
    public function unvote(int $uid, int $id): array
    {
        if (!QuestionModel::getQuestionInfo($id, 'id')) {
            throw new ApiException(1003, '问题不存在', 404);
        }

        Vote::saveVote($uid, $id, 'question', 0);
        $row = Db::name('question')->where('id', $id)->field('agree_count,against_count')->find();

        return [
            'agree_count' => (int) ($row['agree_count'] ?? 0),
            'against_count' => (int) ($row['against_count'] ?? 0),
            'vote_value' => Vote::getVoteByType($id, 'question', $uid),
        ];
    }

    /**
     * 取消收藏（DELETE .../favorite）：显式删除，幂等（未收藏也返回成功）。
     */
    public function unfavorite(int $uid, int $id): array
    {
        if (!QuestionModel::getQuestionInfo($id, 'id')) {
            throw new ApiException(1003, '问题不存在', 404);
        }

        $where = ['uid' => $uid, 'item_type' => 'question', 'item_id' => $id];
        Db::name('users_favorite')->where($where)->delete();

        return ['favorited' => false];
    }

    public function search(string $keyword, int $uid, int $page, int $pageSize): array
    {
        $keyword = trim($keyword);
        $page = max(1, $page);
        $pageSize = min(20, max(1, $pageSize));
        if ($keyword === '') {
            return ['list' => [], 'pagination' => ['page' => $page, 'page_size' => $pageSize, 'total_pages' => 0, 'has_more' => false]];
        }

        $paginator = Db::name('question')
            ->where('status', 1)
            ->where(function ($query) use ($keyword) {
                $query->whereLike('title', '%' . $keyword . '%')
                    ->whereOr('detail', '%' . $keyword . '%');
            })
            ->order('create_time', 'desc')
            ->paginate(['list_rows' => $pageSize, 'page' => $page]);

        $rows = $paginator->toArray();
        $items = $rows['data'] ?? [];
        $uids = array_unique(array_column($items, 'uid'));
        $users = $uids ? Users::getUserInfoByIds($uids) : [];
        $topics = Topic::getTopicByItemIds(array_column($items, 'id'), 'question');

        foreach ($items as &$item) {
            $item['user_info'] = $users[$item['uid']] ?? null;
            $item['topics'] = $topics[$item['id']] ?? [];
            $item['vote_value'] = 0;
            $item['has_focus'] = 0;
        }
        unset($item);

        return [
            'list' => array_map(function ($item) use ($uid) {
                return Presenter::question($item, $uid);
            }, $items),
            'pagination' => [
                'page' => (int) ($rows['current_page'] ?? $page),
                'page_size' => (int) ($rows['per_page'] ?? $pageSize),
                'total_pages' => (int) ($rows['last_page'] ?? 0),
                'has_more' => (int) ($rows['current_page'] ?? 1) < (int) ($rows['last_page'] ?? 1),
            ],
        ];
    }

    private function normalizeTopics($topics): array
    {
        if (!is_array($topics)) {
            $topics = explode(',', (string) $topics);
        }
        $result = [];
        foreach ($topics as $topic) {
            $title = is_array($topic) ? ($topic['title'] ?? '') : $topic;
            $title = trim(strip_tags((string) $title));
            if ($title !== '') {
                $result[] = $title;
            }
        }
        return array_values(array_unique($result));
    }
}
