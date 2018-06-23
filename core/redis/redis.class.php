<?php
namespace CORE\REDIS;
class Redis
{
    public static $redis;
    public static function init()
    {
        try
        {
            self::$redis = new \Predis\Client(REDIS);
            self::$redis->get('*');
        }
        catch(\Exception $e)
        {
            throw new RedisConnectionException("Unable to connect to redis server.");
        }
    }
    public static function get()
    {
        return self::$redis;
    }
    public static function cache($function, string $keyName, int $expirationTime = 300)
    {
        if(self::$redis->exists($keyName))
        {
            return self::$redis->get($keyName);
        }
        else
        {
            $res = $function();
            self::$redis->set($keyName, $res);
            self::$redis->expire($keyName, 86400);
            return $res;
        }
    }
};

class RedisConnectionException extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
    {
        return parent::__construct($message);
    }
}