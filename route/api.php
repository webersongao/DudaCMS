<?php
// +----------------------------------------------------------------------
// | API v1 路由（独立入口 public/api.php，与 Web 控制器无关）
// +----------------------------------------------------------------------
// | 分组策略：公开路由与强制鉴权路由拆为两个 group。
// |   公开组不挂 middleware：Base::parseToken() 仍会解析 Bearer token，
// |   以便 detail/feed 等接口按登录态返回个性化数据（可选鉴权）。
// |   私有组挂 ApiAuth：在路由层统一拦截未登录请求，避免控制器逐个判断遗漏。
// |   控制器内 requireLogin() 保留作为双保险（无副作用）。
// | 顺序约定：子资源/具体路由先于单资源，单资源先于列表（与前台 route/app.php 一致）。
// +----------------------------------------------------------------------
use think\facade\Route;
use app\common\middleware\ApiAuth;

// ============================ 公开路由（可选鉴权） ============================
Route::group('v1', function () {
    // 公共
    Route::get('common/config', 'v1.Common/config');
    Route::get('common/categories', 'v1.Common/categories');
    Route::get('common/topics', 'v1.Common/topics');
    Route::get('common/health', 'v1.Common/health');
    Route::get('feed', 'v1.Feed/index');

    // 认证（登录/注册/退出为公开动作）
    Route::post('auth/login', 'v1.Auth/login');
    Route::post('auth/register', 'v1.Auth/register');
    Route::post('auth/logout', 'v1.Auth/logout');

    // 问题：search 必须在 :id 之前；读详情/列表公开
    Route::get('questions/search', 'v1.Question/search');
    Route::get('questions/:id', 'v1.Question/detail')->pattern(['id' => '\d+']);
    Route::get('questions', 'v1.Question/index');

    // 文章：具体路由先于列表
    Route::get('articles/:id', 'v1.Article/detail')->pattern(['id' => '\d+']);
    Route::get('articles', 'v1.Article/index');

    // 话题：具体路由先于列表
    Route::get('topics/:id', 'v1.Topic/detail')->pattern(['id' => '\d+']);
    Route::get('topics', 'v1.Topic/index');

    // 用户资料（:id 有 \d+ 约束，不会吞掉私有组的 users/me）
    Route::get('users/:id', 'v1.User/profile')->pattern(['id' => '\d+']);
});

// ============================ 私有路由（强制鉴权） ============================
Route::group('v1', function () {
    // 认证
    Route::get('auth/me', 'v1.Auth/me');
    Route::post('auth/refresh', 'v1.Auth/refresh');

    // 问题写操作：子资源先于单资源；投票/收藏成对的 POST + DELETE
    Route::post('questions/:id/answers', 'v1.Question/answer')->pattern(['id' => '\d+']);
    Route::post('questions/:id/vote', 'v1.Question/vote')->pattern(['id' => '\d+']);
    Route::delete('questions/:id/vote', 'v1.Question/unvote')->pattern(['id' => '\d+']);
    Route::post('questions/:id/favorite', 'v1.Question/favorite')->pattern(['id' => '\d+']);
    Route::delete('questions/:id/favorite', 'v1.Question/unfavorite')->pattern(['id' => '\d+']);
    Route::post('questions', 'v1.Question/create');

    // 用户：最长链在前，避免 users/me 前缀吞掉 notifications
    Route::post('users/me/notifications/:id/read', 'v1.User/readNotification')->pattern(['id' => '\d+']);
    Route::get('users/me/notifications', 'v1.User/notifications');
    Route::get('users/me', 'v1.User/me');

    // 上传
    Route::post('upload/image', 'v1.Upload/image');
    Route::post('upload/file', 'v1.Upload/file');
})->middleware(ApiAuth::class);
