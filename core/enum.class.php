<?php
namespace CORE;
use \ReflectionClass;
class Enum
{
	public $value;
	public $key;
	public function __construct(int $keyValue)
	{
		$class = new ReflectionClass(get_called_class());
		$constants = $class->getConstants();
		$this->value = $keyValue;
		$this->key = array_search($keyValue, $constants);
	}
	public function __toString() : string
	{
		return $this->key;
	}
    public function equals(int $constant) : bool
    {
        return $this->value === $constant;
    }
    public static function isValid(int $index) : bool
    {
		$class = new ReflectionClass(get_called_class());
		$constants = $class->getConstants();
        if(array_search($index, $constants))
            return true;
		else
            return false;
    }
    public static function getAll() : array
    {
		return (new ReflectionClass(get_called_class()))->getConstants();
    }
}