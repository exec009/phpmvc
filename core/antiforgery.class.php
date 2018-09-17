<?php
namespace CORE;

class AntiForgery
{
    private static $tokenName = 'aft_token';
    private static $tokenTime = 'aft_tokent';
    public static function createToken(): string
    {
        if((json_decode(Cookie::get(self::$tokenName), true)[1]) === false)
        {
            return self::generateHash();
        }
        else
        {
            $cookie = self::generateSignature();
            Cookie::set(self::$tokenName, json_encode([$cookie->key, false]));
            Cookie::set(self::$tokenTime, time());
            return $cookie->hash;
        }
    }
    private static function generateSignature(): AntiForgeryToken
    {
        $key = Hash::getSalt();
        $hash = self::generateHash($key);
        return new AntiForgeryToken($hash, $key);
    }
    private static function getUserInfo(): string
    {   
        return $_SERVER['HTTP_USER_AGENT'] . getIp() . hash("sha256", $_SERVER['HTTP_USER_AGENT']);
    }
    public static function verifyToken(string $hash): bool
    {
        $key = json_decode(Cookie::get(self::$tokenName), true)[0];
        $time = (time() - intval(Cookie::get(self::$tokenTime))); //12 hours
        if($key !== null && !empty($hash) && $time <= 43200 && $time >= 0)
        {
            $rs = Hash::decrypt($hash) === hash('sha256', $key.self::getUserInfo().$key);
            if($rs)
                self::markUsed();
            return $rs;
        }
        else
        return false;
    }
    private static function generateHash($key = null): string
    {
        if($key === null)
        $key = json_decode(Cookie::get(self::$tokenName), true)[0];
        return Hash::encrypt(hash('sha256', $key.self::getUserInfo().$key));
    }
    private static function markUsed(): void
    {
        $token = json_decode(Cookie::get(self::$tokenName), true);
        $token[1] = true;
        Cookie::set(self::$tokenName, json_encode($token));
    }
}

class AntiForgeryToken {
    public $hash;
    public $key;
    function __construct($hash, $key) {
        $this->hash = $hash;
        $this->key = $key;
    }
}
