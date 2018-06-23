<?php
namespace CORE\MVC;
class ModelPropertyErrorException extends \Exception
{
    public function __construct($message = "", $class, $key)
    {
        $trace = debug_backtrace();
        $this->file = root().strtolower($class).".class.php";
        $this->line = 3;
        if(str_replace(" ","",$message)=="")
            $message = "Model Not Found Exception";
        $lines = file($this->file);
        $line_number = false;
        while (list($key, $line) = each($lines) and !$line_number) {
            $line_number = (strpos($line, $key) !== FALSE) ? $key + 1 : $line_number;
        }
        $this->line = $line_number;
        parent::__construct($message);
    }
}