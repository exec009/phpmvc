<?php
namespace CORE;
class Debug
{
	private static $debug;
	private static $ip;
	public static function init():string
	{
		self::off();
        return get_class();
	}
	public static function isOn():bool
	{
		return self::$debug;
	}
	public static function ipFilter(string ...$ips)
	{
		$ips[]='::1';
		self::$ip=$ips;
        return get_class();
	}
	public static function on():string
	{
		if(in_array(getIp(),self::$ip) || in_array('*',self::$ip))
		{
			self::$debug=true;
			error_reporting(-1);
			ini_set("display_errors",'0');
			if(!SSH)
			{
				$whoops = new \Whoops\Run;
				$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
				$whoops->register();
			}
		}
        return get_class();
	}
	public static function off():void
	{
		self::$debug=false;
		error_reporting(0);
		ini_set("display_errors",'0');
	}
}
