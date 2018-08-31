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
	public static function normalizeSize(float $bytes): string
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];
		$precision = 2;
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= (1 << (10 * $pow)); 
		return round($bytes, $precision) . ' ' . $units[$pow];
	}
}