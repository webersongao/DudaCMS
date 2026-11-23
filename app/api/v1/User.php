<?php
namespace app\api\v1;

use app\service\api\UserService;

class User extends Base
{
    public function me()
    {
        $this->requireLogin();
        return Response::ok((new UserService())->me($this->uid));
    }

    public function profile(int $id)
    {
        return Response::ok((new UserService())->profile($id));
    }

    public function notifications()
    {
        $this->requireLogin();
        return Response::ok((new UserService())->notifications(
            $this->uid,
            $this->intParam('page', 1),
            $this->intParam('page_size', 15)
        ));
    }

    public function readNotification(int $id)
    {
        $this->requireLogin();
        return Response::ok((new UserService())->markRead($this->uid, $id));
    }
}
