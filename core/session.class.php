<?php
namespace CORE;
class Session
{
	private static $session;
	private static $keyName='sd7sd97';
	private static $keySign='dsf9s79';
	public static function init():void
	{
		session_start();
		if(!self::isValidSign())
		self::regenerate();
		self::$session=self::decrypt();
		self::set('time',time());
		self::set('stnid',self::getId());
	}
	public static function createSign():void
	{
		$_SESSION[self::$keySign]=self::generateSignatures();
	}
	private static function generateSignatures():string
	{
		return hash_hmac('sha256', "sd898sd9".self::getId()."98sdf897ds".($_SERVER['REMOTE_ADDR'] ?? ''),
		'ds97f98SF&*^7s68df6sd8f7ds8', false).md5($_SERVER['REMOTE_ADDR'] ?? '');
	}
	private static function getSignature():string
	{
		return $_SESSION[self::$keySign] ?? '';
	}
	public static function isValidSign()
	{
		return self::getSignature() == self::generateSignatures();
	}
	public static function set(string $name,$value):void
	{
		if($value === '+')
		self::$session[$name]+=1;
		else if($value === '-')
		self::$session[$name]-=1;
		else
		self::$session[$name]=$value;
		self::update();
	}
    public static function setArray(string $name, array $value) : bool
    {
        self::set($name, json_encode($value));
    }
	public static function exists(string $name):bool
	{
		return ((self::$session[$name]??NULL)!=NULL) ? true : false;
	}
	public static function get(string $name)
	{
		return self::$session[$name]??NULL;
	}
	public static function getArray(string $name) : array
	{
		return json_decode(self::$session[$name],true) ?? [];
	}
	private static function update():void
	{
		$_SESSION[self::$keyName]=self::encrypt();
	}
	public static function destroy():void
	{
		session_destroy();
	}
	public static function remove(string $name):void
	{
		unset(self::$session[$name]);
		self::update();
	}
	private static function encrypt():string
	{
		return base64_encode(json_encode(Hash::encryptArray(self::$session)));
	}
	private static function decrypt():array
	{
		return Hash::decryptArray(json_decode(base64_decode($_SESSION[self::$keyName] ?? ''),true) ?? []);
	}
	private static function regenerate():void
	{
		session_regenerate_id();
		self::createSign();
		self::set('stnid',self::getId());
	}
	private static function getId():string
	{
		return session_id();
	}
    function __toString()
    {
        return json_encode(self::$session);
    }
}