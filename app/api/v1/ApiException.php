<?php
namespace app\api\v1;

class ApiException extends \Exception
{
    protected int $apiCode;
    protected int $httpStatus;
    protected $data;

    public function __construct(int $apiCode, string $message, int $httpStatus = 200, $data = null)
    {
        parent::__construct($message);
        $this->apiCode = $apiCode;
        $this->httpStatus = $httpStatus;
        $this->data = $data;
    }

    public function getApiCode(): int
    {
        return $this->apiCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getData()
    {
        return $this->data;
    }
}
