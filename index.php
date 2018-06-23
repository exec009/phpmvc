<?php
declare(strict_types=1);
/******************************************************************************************************************
Framework Requirements
PHP Version: PHP 7.1 (Preferrably FastCgi Mode)
MYSQL Database Engine: Innodb
PHP Mode: Strict
Cache Server: Redis 2.7 or Later
******************************************************************************************************************/

require_once "core/app.class.php";
use CORE\App;
App::init()
->isHttp()
->loadFiles()
->configureServices(
    function()
    {
        CORE\Hash::init();
        CORE\SESSION::init();
        CORE\Debug::init()::ipFilter('*')::on();
        CORE\Logger::addChannel('main');
        CORE\DB\DB::init();
        CORE\REDIS\Redis::init();
        CORE\DB\DataStore::init();
//        App::install();//call it first time only
    }
)->run(
    function()
    {
    }
)->terminate(
    function()
    {
        CORE\DB\DB::close();
    }
);