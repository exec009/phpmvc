<?php
namespace CORE\MVC;
class ActionNotFoundException extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
    {
        if(str_replace(" ","",$message)=="")
            $message = "Action Not Found Exception";
        parent::__construct($message, $code, $previous);
    }
}