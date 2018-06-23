<?php
require_once "Core/app.class.php";
use CORE\App;
App::init()
->isCron()
->loadFiles()
->configureServices(
    function()
    {
        CORE\Debug::init()::ipFilter('*')::on();
        CORE\DB\DB::init();
        CORE\Lock::init();
        CORE\Hash::init();
        CORE\SESSION::init();
        \CORE\DB\DataStore::init();
    }
)->startCronJob(
    function()
    {
    }
)->terminate(
    function()
    {
        CORE\DB\DB::close();
        echo "Success";
    }
);
