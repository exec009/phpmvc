<?php
namespace CORE;
class Cookie
{
    public static function getAll(): array
    {
        $ck = [];
        foreach($_COOKIE as $key => $data)
        {
            $ck[$key] = self::get($key);
        }
        return $ck;
    }
    public static function set(string $name, $value, int $expireyInSeconds = 86400): void
    {
        setcookie($name, Hash::encrypt($value), time() + ($expireyInSeconds), "/");
    }
    public static function get(string $name)
    {
        return self::exists($name) ? Hash::decrypt($_COOKIE[$name]) : null;
    }
    public static function exists(string $name): bool
    {
        return isset($_COOKIE[$name]) && !empty($_COOKIE[$name]);
    }
    public static function remove(string $name):void
    {
        setcookie($name, "", time() - 3600);
    }
}