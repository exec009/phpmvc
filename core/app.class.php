<?php
namespace CORE;
use CORE\MVC\MVC;
use CORE\ConstantReader;
use CORE\DB\DB;
class App
{
    public static function init($execute = "time",$loadFiles = "time") : self
    {
        ini_set("display_errors","1");
        error_reporting(-1);
        $site = require "config/site.php";
        define("SITE_FOLDER",$site['FOLDER']);
        define("SITE_DOMAIN",$site['URL']);
        define("SITE_ROOT",$site['ROOT_DIRECTORY']);
        define("KEYFILE",$site['KEYFILE']);
        define("ERROR","There is an error. Please tryagain after sometime.");
        $execute();
        $self = new self();
        return $self;
    }
    public function isCron()
    {
        define('SSH',true);
        return $this;
    }
    public function isHttp()
    {
        define('SSH',false);
        return $this;
    }
    public function configureServices($execute = "time") : self
    {
        $execute();
        $info = [
            'Server' => $_SERVER,
            'Get' => $_GET,
            'POST' => $_POST,
            'Headers' => getallheaders(),
            'Request' => apache_request_headers(),
            'Response' => apache_response_headers(),
            'Cookies' => $_COOKIE
        ];
        // 365 = 1 Years
        \CORE\Logger::addChannel('traffic')->addInfo(
            json_encode($info));
        return $this;
    }
    public function startCronJob():self
    {
        CronJob::execute();
        return $this;
    }
    public function run($execute = "time", $fileLoader = "time") : self
    {
        $route = isset($_GET['route']) ? explode("/",$_GET['route']) : ['',''];
        $controller=(strlen($route[0]??'')>0)? $route[0] : ROUTES['Default']['Controller'];
        $action=(strlen($route[1]??'')>0)? $route[1] : ROUTES['Default']['Action'];
        if(in_array(strtolower($controller),ROUTES['Areas']))
        {
            $area=$controller;
            $controller=$action;
            $action=(strlen($route[2]??'')>0)? $route[2] : ROUTES['Default']['Action'];
            MVC::callAction($controller,$action,$area);
        }
        else
        {
            MVC::callAction($controller,$action);
        }
        $execute();
        return $this;
    }
    public function loadFiles($execute = "time") : self
    {
        require_once "core/function.php";
        require_once root()."vendor/autoload.php";
        \CORE\ConstantReader::execute();
        $execute();
        return $this;
    }
    public function terminate($execute = "time")
    {
        $execute();
    }
    public static function install()
    {
        CronJob::createTable();
        \CORE\MODELS\ForgeryToken::createTable();
        \CORE\MODELS\Option::createTable();
        \MODELS\SITE\Site::createTable();
        \MODELS\ADMIN\Admin::createTable();
        \MODELS\ADMIN\Role::createTable();
    }
}