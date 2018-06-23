<?php
namespace CORE\MVC;
class ModelErrorException extends \Exception
{
    public function __construct($message = "", $traceLine = 0)
    {
        $trace = debug_backtrace();
        $traceLine = 1 + $traceLine;
//        $this->file = $trace[$traceLine]['file'];
//        $this->line = $trace[$traceLine]['line'];
        if(str_replace(" ","",$message)=="")
            $message = "Model Not Found Exception";
        parent::__construct($message);
    }
}