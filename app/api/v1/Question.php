<?php
namespace app\api\v1;

use app\service\api\QuestionService;

class Question extends Base
{
    public function index()
    {
        $data = (new QuestionService())->list(
            $this->uid,
            $this->stringParam('sort', 'new'),
            $this->intParam('category_id'),
            $this->intParam('page', 1),
            $this->intParam('page_size', 10)
        );
        return Response::ok($data);
    }

    public function detail(int $id)
    {
        $data = (new QuestionService())->detail(
            $id,
            $this->uid,
            $this->intParam('answer_page', 1),
            $this->intParam('answer_size', 10)
        );
        return Response::ok($data);
    }

    public function create()
    {
        $this->requireLogin();
        $data = (new QuestionService())->create($this->uid, $this->input());
        return Response::ok($data, '发布成功');
    }

    public function answer(int $id)
    {
        $this->requireLogin();
        $input = $this->input();
        $data = (new QuestionService())->answer(
            $this->uid,
            $id,
            (string) ($input['content'] ?? ''),
            (string) ($input['access_key'] ?? '')
        );
        return Response::ok($data, '回答成功');
    }

    public function vote(int $id)
    {
        $this->requireLogin();
        $input = $this->input();
        $data = (new QuestionService())->vote($this->uid, $id, (int) ($input['value'] ?? 0));
        return Response::ok($data);
    }

    public function favorite(int $id)
    {
        $this->requireLogin();
        return Response::ok((new QuestionService())->favorite($this->uid, $id));
    }

    /**
     * 取消投票：RESTful DELETE 语义，对应 POST vote。
     */
    public function unvote(int $id)
    {
        $this->requireLogin();
        return Response::ok((new QuestionService())->unvote($this->uid, $id));
    }

    /**
     * 取消收藏：RESTful DELETE 语义；POST favorite 保留为 toggle（兼容既有调用）。
     */
    public function unfavorite(int $id)
    {
        $this->requireLogin();
        return Response::ok((new QuestionService())->unfavorite($this->uid, $id));
    }

    public function search()
    {
        $data = (new QuestionService())->search(
            $this->stringParam('q'),
            $this->uid,
            $this->intParam('page', 1),
            $this->intParam('page_size', 10)
        );
        return Response::ok($data);
    }
}
