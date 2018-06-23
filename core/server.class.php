<?php
namespace CORE;
class Server
{
    public static function getOS(): int
    {
        switch(PHP_OS)
        {
            case 'WINNT':
                return ServerOs::Window;
                break;
            case 'Linux':
                return ServerOs::Linux;
            default:
                throw new \Exception("Installed operating system is not recognized by the application.");
                break;
        }
    }
}