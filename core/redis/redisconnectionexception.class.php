<?php
namespace CORE\REDIS;
class RedisConnectionException extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
    {
        return parent::__construct($message);
    }
}