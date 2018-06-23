<?php
namespace CORE\MVC;
class PartialViewNotFoundException extends \Exception
{
    public function __construct($message = "Partial View Not Found Exception", string $partialString = "", string $reference = "")
    {
        $this->line = 1;
        $this->file = MVC::getFileFromActionString($reference);
        $lines = file($this->file);
        $line_number = false;

        while (list($key, $line) = each($lines) and !$line_number) {
            $line_number = (strpos($line, $partialString) !== FALSE) ? $key + 1 : $line_number;
        }
        $this->line = $line_number;
        parent::__construct($message);
    }
}