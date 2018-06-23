<?php
namespace CORE;
class FILE
{
	public static function name(string $name):string
	{
		$name=explode("/",$name);
		$name=explode(".",$name[count($name)-1]);
		return $name[0];
	}
	public static function getName(string $name,string $separator="/"):string
	{
		$name=explode($separator,$name);
		$name=explode(".",$name[count($name)-1]);
		return $name[0];
	}
}