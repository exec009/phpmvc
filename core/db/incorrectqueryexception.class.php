<?php
namespace CORE\DB;
class IncorrectQueryException extends \Exception implements IDBException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
    {
        $this->line=debug_backtrace()[1]['line'];
        $this->file=debug_backtrace()[1]['file'];
        if(str_replace(" ","",$message)=="")
            $message = DB::$error;
        parent::__construct($message, $code, $previous);
    }
}