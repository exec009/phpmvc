<?php
namespace CORE\DB;
class DBConnectException extends \Exception implements IDBException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
    {
        if(str_replace(" ","",$message)=="")
            $message = DB::$error;
        parent::__construct($message, $code, $previous);
    }
}