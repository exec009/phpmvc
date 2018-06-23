<?php
namespace CORE;
use Monolog\Handler\StreamHandler;
class Logger
{
    private static $loggers;
    public static function addChannel(string $channelName) : \Monolog\Logger
    {
        self::$loggers[$channelName] = new \Monolog\Logger($channelName);
        if(Debug::isOn())
        {
            if($channelName != 'traffic')
            self::$loggers[$channelName]->pushHandler(new \Monolog\Handler\BrowserConsoleHandler());
        }
        self::loadHandlers($channelName);
        return self::$loggers[$channelName];
    }
    private static function loadHandlers(string $channelName) : void
    {
        foreach(LOG['channels'][$channelName]['handlers'] as $data)
        {
            $config = LOG['handlers'][$data];
            $handler = '';
            $level = self::getLevel($config['level'] ?? '', $data);
            switch($config['type'])
            {
                case 'stream':
                    $handler =  new \Monolog\Handler\StreamHandler($config['path']);
                    break;
                case 'pushOver':
                    $handler = new \Monolog\Handler\PushoverHandler($config['token'], $config['users'], $config['title'] ?? null, $level, $config['bubble'] ?? true, $config['useSSL'] ?? true, self::getLevel($config['highPriorityLevel'] ?? '', $data) ?? \Monolog\Logger::CRITICAL, self::getLevel($config['emergencyLevel'] ?? '', $data) ?? \Monolog\Logger::EMERGENCY, $config['retry'] ?? 30, $config['expire'] ?? 25200);
                    break;
                case 'loggly':
                    $handler = new \Monolog\Handler\LogglyHandler($config['token']);
                    break;
                case 'slack':
                    $handler = new \Monolog\Handler\SlackWebhookHandler($config['webHookUrl'], $config['channel'], $config['user']);
                    break;
                case 'mysql':
                    $pdo = new \PDO('mysql:dbname='.$config['database'].';host='.$config['host'], $config['username'], $config['password']);
                    $handler = new \MySQLHandler\MySQLHandler($pdo, "log", array('stack', 'extraInfo','name'), \Monolog\Logger::DEBUG);
                    break;
            }
            self::$loggers[$channelName]->pushHandler($handler);
        }
    }
    private static function getLevel(string $level, string $handler)
    {
        switch($level)
        {
            case 'critical':
                return \Monolog\Logger::CRITICAL;
                break;
            case 'info':
                return \Monolog\Logger::INFO;
                break;
            case 'notice':
                return \Monolog\Logger::NOTICE;
                break;
            case 'warning':
                return \Monolog\Logger::WARNING;
                break;
            case 'debug':
                return \Monolog\Logger::DEBUG;
                break;
            case 'emergency':
                return \Monolog\Logger::EMERGENCY;
                break;
            case 'error':
                return \Monolog\Logger::ERROR;
                break;
            case 'alert':
                return \Monolog\Logger::ALERT;
                break;
            case '':
                return null;
                break;
            default:
                throw new \Exception("Invalid Level: ".$level. " for Handler ".$handler. " in ".configRoot()."log.php");
                break;
        }
    }
    public static function get(string $logger)
    {
        return self::$loggers[$logger];
    }
}
