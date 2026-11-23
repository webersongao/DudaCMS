<?php

use think\facade\Route;

// 前台静态路由：原数据库 route_rule 已全量固化到这里。
// 约定：
//   1. 规则顺序遵循「具体在前、通配在后」，避免列表/动作被详情路由吞掉；
//   2. 纯展示页用 Route::get，纯写动作用 Route::post；
//      发布/预览/话题编辑这类「同一 URL 兼具 GET 展示 + POST 提交」的保留 Route::rule；
//   3. 多段路径用 pattern 约束每段取值，从根上消除「-」分隔变量的边界歧义；
//   4. 通配段（people/:name）用 pattern 排除固定入口保留字，不单纯依赖注册顺序；
//   5. 第三方登录回调走 TP8 默认路由 ThirdAuth/callback.html?platform=xxx，
//      token 经 query 传递（SDK 的 url() 反向解析自动生成），不进路径。

Route::group('', function () {
    // ── 公开工具入口 ──────────────────────────────────────
    Route::get('api-docs/openapi.yaml', 'ApiDoc/openapi');
    Route::get('api-docs', 'ApiDoc/index');

    // ── 发现 / 问题 ───────────────────────────────────────
    Route::get('explore/[:sort]', 'Index/index')
        ->pattern(['sort' => '[a-z]+']);
    // 发布页 GET 展示 + POST 提交，保持 rule
    Route::rule('publish/question/[:id]', 'Question/publish');
    // 回答列表 AJAX（展示）：sort 字母 + question_id 数字，消除 - 歧义
    Route::get('answers/[:sort]-[:question_id]', 'Question/answers')
        ->pattern(['sort' => '[a-z]+', 'question_id' => '\d+']);
    Route::get('questions/[:sort]-[:category_id]', 'Question/index')
        ->pattern(['sort' => '[a-z]+', 'category_id' => '\d+']);
    Route::get('question/:id-[:answer]-[:sort]', 'Question/detail')
        ->pattern(['id' => '\d+', 'answer' => '\d+', 'sort' => '[a-z]+']);

    // ── 文章 ─────────────────────────────────────────────
    // 预览页 GET 展示 + POST 提交，保持 rule
    Route::rule('preview/article', 'Article/preview');
    Route::rule('publish/article/[:id]', 'Article/publish');
    Route::get('articles/[:sort]-[:category_id]', 'Article/index')
        ->pattern(['sort' => '[a-z]+', 'category_id' => '\d+']);
    Route::get('article/:id', 'Article/detail')->pattern(['id' => '\d+']);

    // ── 专栏 ──
    Route::get('columns/[:sort]', 'Column/index')
        ->pattern(['sort' => '[a-z]+']);
    Route::get('column/detail/:id', 'Column/detail')->pattern(['id' => '\d+']);

    // ── 话题：固定动作必须先于 topic/:id ───────────────────
    // 话题选择/编辑 GET 展示 + POST 提交，保持 rule
    Route::rule('select/topic', 'Topic/select');
    Route::rule('manager/topic/[:id]', 'Topic/manager');
    Route::get('topics/[:type]-[:pid]', 'Topic/index')
        ->pattern(['type' => '[a-z]+', 'pid' => '\d+']);
    Route::get('topic/:id-[:sort]-[:type]', 'Topic/detail')
        ->pattern(['id' => '\d+', 'sort' => '[a-z]+', 'type' => '[a-z]+']);

    // ── 用户：固定入口必须先于 people/:name ────────────────
    // 用户列表统一使用 people/lists
    Route::get('people/lists', 'People/lists');
    Route::get('people/index', 'People/index');
    Route::get('people/:name/[:type]', 'People/index')
        ->pattern(['name' => '(?!(lists|index)$)[\w\.]+', 'type' => '[a-z]+']);
    Route::get('users/:uid', 'People/index')->pattern(['uid' => '\d+']);
    Route::get('creator', 'Creator/index');

    // ── 其余前台 clean URL ─────────────────────────────────
    Route::get('ask', 'Question/publish');
    Route::get('records/[:page]', 'Records/index')->pattern(['page' => '\d+']);
    // 关注列表 AJAX（纯 POST + data），动作先于 focus/[:type]
    Route::post('focus/list', 'Focus/focus_list');
    Route::get('focus/[:type]', 'Focus/index')->pattern(['type' => '[a-z]+']);
    // 私信动作先于 inbox/[:type]
    Route::get('inbox/detail', 'Inbox/detail');
    Route::post('inbox/send', 'Inbox/send');
    Route::get('inbox/[:type]', 'Inbox/index')->pattern(['type' => '[a-z]+']);
    Route::get('search', 'Search/index');
    // 积分规则是独立动作，不能被单页 url_name 通配吞掉
    Route::get('page/credit', 'Page/credit');
    Route::get('page/:url_name', 'Page/index');
});

// 后台单段动作：使用静态路由。
if (defined('ENTRANCE') && ENTRANCE === 'backend') {
    foreach (['login', 'logout', 'statistic', 'clear', 'icons', 'send_test_email'] as $action) {
        Route::rule($action, 'Index/' . $action);
    }
}

// API 独立入口：显式静态路由，不再读取数据库规则
if (defined('ENTRANCE') && ENTRANCE === 'api') {
    require __DIR__ . '/api.php';

    Route::miss(function () {
        return \app\api\v1\Response::error(1003, '接口不存在', 404);
    });
}
