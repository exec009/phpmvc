<?php
namespace CORE\MVC;
class ModelInventory
{
	private $table;
	private $data;
	public function updateData($columnName,$columnValue)
	{
		$result=DB::update("update ".$this->table." set $columnName='$columnValue' where id='".$this->getId()."'");
		if($result)
		return true;
		else
		return false;
	}
}