<?php
namespace app\api\v1;

use app\service\api\FeedService;

class Feed extends Base
{
    public function index()
    {
        return Response::ok((new FeedService())->home(
            $this->uid,
            $this->stringParam('tab', 'new'),
            $this->intParam('page', 1),
            $this->intParam('page_size', 10)
        ));
    }
}
