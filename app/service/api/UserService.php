<?php
namespace app\service\api;

use app\api\v1\ApiException;
use app\common\library\helper\HtmlHelper;
use app\model\Users;
use think\facade\Db;

class UserService
{
    public function profile(int $uid): array
    {
        $user = Users::getUserInfo($uid);
        if (!$user) {
            throw new ApiException(1003, '用户不存在', 404);
        }
        return Presenter::user($user);
    }

    public function me(int $uid): array
    {
        $user = Users::getUserInfo($uid);
        if (!$user) {
            throw new ApiException(1001, '登录状态已失效', 401);
        }
        $data = Presenter::user($user, true);
        $data['question_count'] = (int) Db::name('question')->where(['uid' => $uid, 'status' => 1])->count();
        $data['answer_count'] = (int) Db::name('answer')->where(['uid' => $uid, 'status' => 1])->count();
        return $data;
    }

    public function notifications(int $uid, int $page, int $pageSize): array
    {
        $page = max(1, $page);
        $pageSize = min(30, max(1, $pageSize));

        $paginator = Db::name('users_notify')
            ->where(['recipient_uid' => $uid, 'status' => 1])
            ->order('create_time', 'desc')
            ->paginate(['list_rows' => $pageSize, 'page' => $page]);

        $rows = $paginator->toArray();
        $list = array_map(function ($item) {
            return [
                'id' => (int) $item['id'],
                'type' => (string) ($item['action_type'] ?? ''),
                'subject' => strip_tags(htmlspecialchars_decode((string) ($item['subject'] ?? ''))),
                'content' => HtmlHelper::purify(htmlspecialchars_decode((string) ($item['content'] ?? ''))),
                'read' => !empty($item['read_flag']) ? 1 : 0,
                'create_time' => (int) ($item['create_time'] ?? 0),
                'create_time_text' => date_friendly($item['create_time'] ?? time()),
            ];
        }, $rows['data'] ?? []);

        return [
            'list' => $list,
            'unread' => (int) Db::name('users_notify')->where(['recipient_uid' => $uid, 'status' => 1, 'read_flag' => 0])->count(),
            'pagination' => [
                'page' => (int) ($rows['current_page'] ?? $page),
                'page_size' => (int) ($rows['per_page'] ?? $pageSize),
                'total_pages' => (int) ($rows['last_page'] ?? 0),
                'has_more' => (int) ($rows['current_page'] ?? 1) < (int) ($rows['last_page'] ?? 1),
            ],
        ];
    }

    public function markRead(int $uid, int $id): array
    {
        Db::name('users_notify')->where(['id' => $id, 'recipient_uid' => $uid])->update(['read_flag' => 1]);
        return ['id' => $id];
    }
}
