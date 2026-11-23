<?php
namespace app\api\v1;

use app\service\api\CommonService;
use app\service\api\TopicService;

class Common extends Base
{
    public function config()
    {
        return Response::ok((new CommonService())->config());
    }

    public function categories()
    {
        $type = $this->stringParam('type', 'question');
        return Response::ok((new CommonService())->categories($type));
    }

    public function topics()
    {
        $data = (new TopicService())->list(
            $this->intParam('page', 1),
            $this->intParam('page_size', 20),
            $this->stringParam('keyword')
        );
        return Response::ok($data);
    }

    public function health()
    {
        return Response::ok(['status' => 'ok', 'time' => time()]);
    }
}
