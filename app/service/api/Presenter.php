<?php
namespace app\service\api;

use app\common\library\helper\HtmlHelper;
use app\common\library\helper\ImageHelper;
use app\common\library\helper\PresentationHelper;

class Presenter
{
    public static function url(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url)) {
            return preg_replace('#(?<!:)//+#', '/', $url);
        }
        if (strpos($url, '//') === 0) {
            return 'https:' . $url;
        }
        return rtrim(request()->domain(), '/') . '/' . ltrim($url, '/');
    }

    public static function avatar(?string $avatar): string
    {
        return PresentationHelper::avatar($avatar, true);
    }

    public static function user(?array $user, bool $private = false): array
    {
        if (!$user) {
            return [
                'uid' => 0,
                'user_name' => '',
                'nick_name' => '匿名用户',
                'avatar' => self::avatar(''),
                'signature' => '',
                'credit' => 0,
                'answer_count' => 0,
                'question_count' => 0,
                'fans_count' => 0,
                'verified' => 0,
            ];
        }

        $data = [
            'uid' => (int) ($user['uid'] ?? 0),
            'user_name' => (string) ($user['user_name'] ?? ''),
            'nick_name' => (string) ($user['nick_name'] ?? $user['user_name'] ?? ''),
            'avatar' => self::avatar($user['avatar'] ?? ''),
            'signature' => (string) ($user['signature'] ?? ''),
            'credit' => (int) ($user['credit'] ?? 0),
            'answer_count' => (int) ($user['answer_count'] ?? 0),
            'question_count' => (int) ($user['question_count'] ?? 0),
            'fans_count' => (int) ($user['fans_count'] ?? 0),
            'verified' => !empty($user['verified']) ? 1 : 0,
        ];

        if ($private) {
            $data['email'] = (string) ($user['email'] ?? '');
            $data['mobile'] = (string) ($user['mobile'] ?? '');
            $data['group_id'] = (int) ($user['group_id'] ?? 0);
            $data['friend_count'] = (int) ($user['friend_count'] ?? 0);
            $data['notify_unread'] = (int) ($user['notify_unread'] ?? 0);
            $data['inbox_unread'] = (int) ($user['inbox_unread'] ?? 0);
        }

        return $data;
    }

    public static function topic(?array $topic): array
    {
        if (!$topic) {
            return [];
        }
        return [
            'id' => (int) ($topic['id'] ?? 0),
            'title' => preg_replace('/^#\s*/u', '', (string) ($topic['title'] ?? '')),
            'url_token' => (string) ($topic['url_token'] ?? ''),
            'description' => (string) ($topic['description'] ?? ''),
            'pic' => self::url($topic['pic'] ?? ''),
            'discuss' => (int) ($topic['discuss'] ?? 0),
            'focus' => (int) ($topic['focus'] ?? 0),
        ];
    }

    public static function question(array $q, int $uid = 0): array
    {
        $detail = (string) ($q['detail'] ?? $q['content'] ?? '');
        $images = $q['images'] ?? [];
        if (!is_array($images)) {
            $images = [];
        }
        if (!$images) {
            $images = ImageHelper::srcList(htmlspecialchars_decode($detail));
        }
        $images = array_values(array_filter(array_map([self::class, 'url'], array_slice($images, 0, 3))));

        $author = $q['user_info'] ?? null;
        if (!empty($q['is_anonymous'])) {
            $author = null;
        }

        return [
            'id' => (int) ($q['id'] ?? 0),
            'title' => strip_tags(htmlspecialchars_decode((string) ($q['title'] ?? ''))),
            'excerpt' => str_cut(strip_tags(htmlspecialchars_decode($detail)), 0, 140),
            'images' => $images,
            'answer_count' => (int) ($q['answer_count'] ?? 0),
            'view_count' => (int) ($q['view_count'] ?? 0),
            'agree_count' => (int) ($q['agree_count'] ?? 0),
            'comment_count' => (int) ($q['comment_count'] ?? 0),
            'is_top' => !empty($q['set_top']) ? 1 : 0,
            'has_focus' => !empty($q['has_focus']) ? 1 : 0,
            'vote_value' => (int) ($q['vote_value'] ?? 0),
            'create_time' => (int) ($q['create_time'] ?? 0),
            'create_time_text' => isset($q['create_time']) ? date_friendly($q['create_time']) : '',
            'author' => self::user($author),
            'topics' => array_values(array_map([self::class, 'topic'], $q['topics'] ?? [])),
            'url' => '/question/detail.html?id=' . (int) ($q['id'] ?? 0),
        ];
    }

    public static function answer(array $a, int $uid = 0): array
    {
        $content = HtmlHelper::purify(htmlspecialchars_decode((string) ($a['content'] ?? '')));
        $author = !empty($a['is_anonymous']) ? null : ($a['user_info'] ?? null);

        return [
            'id' => (int) ($a['id'] ?? 0),
            'content' => $content,
            'excerpt' => str_cut(strip_tags($content), 0, 160),
            'agree_count' => (int) ($a['agree_count'] ?? 0),
            'against_count' => (int) ($a['against_count'] ?? 0),
            'comment_count' => (int) ($a['comment_count'] ?? 0),
            'is_best' => !empty($a['is_best']) ? 1 : 0,
            'is_anonymous' => !empty($a['is_anonymous']) ? 1 : 0,
            'vote_value' => (int) ($a['vote_value'] ?? 0),
            'create_time' => (int) ($a['create_time'] ?? 0),
            'create_time_text' => isset($a['create_time']) ? date_friendly($a['create_time']) : '',
            'author' => self::user($author),
        ];
    }

    public static function article(array $a): array
    {
        $message = (string) ($a['message'] ?? $a['content'] ?? '');
        return [
            'id' => (int) ($a['id'] ?? 0),
            'title' => strip_tags(htmlspecialchars_decode((string) ($a['title'] ?? ''))),
            'excerpt' => str_cut(strip_tags(htmlspecialchars_decode($message)), 0, 160),
            'cover' => self::url($a['cover'] ?? ''),
            'view_count' => (int) ($a['view_count'] ?? 0),
            'agree_count' => (int) ($a['agree_count'] ?? 0),
            'comment_count' => (int) ($a['comment_count'] ?? 0),
            'create_time' => (int) ($a['create_time'] ?? 0),
            'create_time_text' => isset($a['create_time']) ? date_friendly($a['create_time']) : '',
            'author' => self::user($a['user_info'] ?? null),
            'url' => '/article/detail.html?id=' . (int) ($a['id'] ?? 0),
        ];
    }
}
