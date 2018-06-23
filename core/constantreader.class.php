<?php
namespace CORE;
class ConstantReader
{
    public static function execute()
    {
        $files = scandir(configRoot());
        $protectedFile=['.htaccess'];
        foreach($files as $file)
        {
            if(strlen($file)>3 && !in_array($file,$protectedFile))
            {
                define(FILE::NAME(strtoupper($file)),require configRoot().$file);
            }
        }
        define('_AND_','and');
        define('_OR_','or');
        define("SITE_URL",SITE['URL']);
    }
}
