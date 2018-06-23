<?php
namespace CORE;
use \CORE\DB\DataStore;
class Thread
{
    private $processId;
    private $status;
    public function __construct(string $filename, string ...$params)
    {
        echo "php ".$filename. " ". implode(" ", $params);
        $this->processId = self::execute("php ".$filename. " ". implode(" ", $params));
    }
    /*
    public function OnComplete($callBack)
    {
        if(is_callable($callBack))
        $callBack($this->processId);
    }
    public function OnError($callBack)
    {
        if(is_callable($callBack))
            $callBack($this->processId);
    }*/
    public function IsAlive()
    {
        return file_exists("/proc/".$this->processId);
    }
    private static function execute(string $cmd): int
    {
        if (substr(php_uname(), 0, 7) == "Windows")
        {
            echo "\nstart /B ". $cmd."\n";
            pclose(popen("start /B ". $cmd, "r"));
            return 0;
        }
        else
        {
            exec($cmd . ' > /dev/null 2>&1 & echo $!; ', $output);
            return (int) $output[0];
        }
    }
}