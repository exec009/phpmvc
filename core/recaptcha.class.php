<?php
namespace CORE;
use CORE\MVC\MVC;
class Recaptcha
{
    public static function verify()
    {
        $captcha = $_POST['g-recaptcha-response'];
        $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".RECAPTCHA['secretKey']."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
        $obj = json_decode($response);
        if($obj->success == true)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}