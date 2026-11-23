<?php
namespace app\api\v1;

use app\service\api\ArticleService;

class Article extends Base
{
    public function index()
    {
        return Response::ok((new ArticleService())->list(
            $this->intParam('page', 1),
            $this->intParam('page_size', 10)
        ));
    }

    public function detail(int $id)
    {
        return Response::ok((new ArticleService())->detail($id));
    }
}
