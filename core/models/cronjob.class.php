<?php
namespace CORE\MODELS;
use CORE\DB\DB;
use CORE\MVC\Model;
use CORE\MVC\IModel;
class CronJob extends Model implements IModel
{
    protected static $table="cronjob";
    protected static $model = [
        'Id'=>['Key'=>true,'Type'=>'Int','Required'=>true, 'ColumnName'=>'id'],
        'Function'=>['Type'=>'string','Required'=>true,'ColumnName'=>'function'],
        'Status'=>['Type'=>'Enum','Required'=>true,'ColumnName'=>'status','Enum' => '\MODELS\ADMIN\Status', 'Default' => Status::Success],
        'Log'=>['Type'=>'Name','Required'=>true,'ColumnName'=>'function'],
        'Date'=>['Type'=>'DateTime','Required'=>true,'ColumnName'=>'lastrun']
    ];
    public static function createTable()
    {
		DB::query("create table if not exists ".self::$table."(function varchar(70), lastrun int(10), status tinyint, log varchar(500), id int(10) auto_increment key) engine InnoDB");
    }
}

/*
class CronJob
{
	private static $table='cronjob';
	public static function createTable():void
	{
		DB::query("create table if not exists ".self::$table."(function varchar(70), lastrun int(10), status tinyint, log varchar(500), id int(10) auto_increment key) engine InnoDB");
	}
	public static function add(string $function,string $log,int $status):void
	{
		DB::insert("insert into ".self::$table." (function,lastrun,log,status)
		values('$function','".time()."','$log','$status')");
	}
	public static function getLastRun(string $function):int
	{
		return (int)DB::getVal("select lastrun From ".self::$table." where function='$function' order by id desc limit 0,1");
	}
}*/

?>