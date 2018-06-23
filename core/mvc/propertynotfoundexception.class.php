<?php
namespace CORE\MVC;
class PropertyNotFoundException extends \Exception
{
    public function __construct($message = "")
    {
        if(str_replace(" ","",$message)=="")
            $message = "Property Not Found Exception";
        $trace = debug_backtrace();
        $this->file = $trace[1]['file'];
        $this->line = $trace[1]['line'];
        parent::__construct($message);
    }
}