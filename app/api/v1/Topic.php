<?php
namespace app\api\v1;

use app\service\api\TopicService;

class Topic extends Base
{
    public function index()
    {
        return Response::ok((new TopicService())->list(
            $this->intParam('page', 1),
            $this->intParam('page_size', 20),
            $this->stringParam('keyword')
        ));
    }

    public function detail(int $id)
    {
        return Response::ok((new TopicService())->detail($id));
    }
}
