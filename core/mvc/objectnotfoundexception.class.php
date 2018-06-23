<?php
namespace CORE\MVC;
class ObjectNotFoundException extends \Exception
{
    public function __construct($message = "")
    {
        $trace = debug_backtrace();
        $this->file = $trace[1]['file'];
        $this->line = $trace[1]['line'];
        if(str_replace(" ","",$message)=="")
            $message = "Object Not Found Exception";
        parent::__construct($message);
    }
}