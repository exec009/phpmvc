<?php
namespace CORE\MVC;

class PageNotFoundException extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
    {
        if(str_replace(" ","",$message)=="")
            $message = "Page Not Found Exception";
        parent::__construct($message, $code, $previous);
    }
}