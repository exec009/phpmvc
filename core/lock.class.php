<?php
namespace CORE;
use CORE\MVC\MVC;
use CORE\ConstantReader;
use CORE\DB\DB;
class Lock
{
    private static $lockFilePath;
    public static function init()
    {
        self::$lockFilePath = root()."cron.lock";
    }
    public static function lock(string $functionName, $extraData = null): void
    {
        $content = self::read();
        $content[$functionName]['status'] = true;
        $content[$functionName]['time'] = time();
        $content[$functionName]['extra'] = $extraData;
        self::write($content);
    }
    public static function unlock(string $functionName): void
    {
        $content = self::read();
        $content[$functionName]['status'] = false;
        $content[$functionName]['time'] = time();
        $content[$functionName]['extra'] = null;
        self::write($content);
    }
    public static function isLocked(string $functionName): bool
    {
        $content = self::read();
        return isset($content[$functionName]) ? $content[$functionName]['status'] ?? false : false;
    }
    public static function getTime(string $functionName): int
    {
        $content = self::read();
        return isset($content[$functionName]) ? $content[$functionName]['time'] ?? 0 : 0;
    }
    public static function getExtraData(string $functionName)
    {
        $content = self::read();
        return isset($content[$functionName]) ? $content[$functionName]['extra'] ?? null : null;
    }
    private static function read(): array
    {
        return file_exists(self::$lockFilePath) ? json_decode(file_get_contents(self::$lockFilePath), true) ?? [] : [];
    }
    private static function write(array $json): void
    {
        file_put_contents(self::$lockFilePath, json_encode($json));
    }
}