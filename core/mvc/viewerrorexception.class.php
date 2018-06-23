<?php
namespace CORE\MVC;
class ViewErrorException extends \Exception
{
    public function __construct(string $message = "", string $reference, string $text)
    {
        if(str_replace(" ","",$message)=="")
            $message = "Error in View";
        $this->file = MVC::getFileFromActionString($reference);

        $lines = file($this->file);
        $line_number = false;

        while (list($key, $line) = each($lines) and !$line_number) {
            $line_number = (strpos($line, $text) !== FALSE) ? $key + 1 : $line_number;
        }
        $this->line = $line_number;

        parent::__construct($message, 0, null);
    }
}