<?php
namespace CORE\DB;
use \stdClass;
use \mysqli;
class DB
{
	protected static $mysqli;
	private $result;
	public $num_rows;
	public static $error;
	public static $get,$post;
    private static $logger;
    private static $initialized = false;
    private static $db;
	public static function init(string $db='DEFAULT'):void
	{
        self::$db = $db;
        if(!self::$initialized)
        {
		    self::$get=[];
		    self::$post=[];
            self::$logger = \CORE\Logger::addChannel('database');
        }
		self::$mysqli=new \mysqli(DB[$db]['host'],DB[$db]['username'],DB[$db]['password'],DB[$db]['database']);
		self::$mysqli->set_charset('utf8');
		if(!self::$initialized)
		DB::sanitze();
		$common_error=ERROR;
		self::$error=self::$mysqli->error;
		if(mysqli_connect_errno())
		{
            self::$logger->addEmergency(self::$error);
            throw new DBConnectException();
		}
        self::$initialized = true;
	}
    public static function getInstanceName()
    {
        return self::$db;
    }
	public static function select(string $query)
	{
		$instance=new DB();
		self::reconnectIfDown();
		$instance->result=self::$mysqli->query($query);
		self::$error=self::$mysqli->error;

		if($instance->result)
		{
    		$instance->num_rows=$instance->result->num_rows;
		}
		else
        {
            self::$logger->error(self::$error);
            throw new DBSelectException(self::$error);
        }

		if($instance->result)
		return $instance;
		else
		return false;
	}
	public function fetchObject():array
	{
        $ar=[];
		while($data=$this->result->fetch_object())
		{
			$ar[]=$data;
		}
        return $ar;
	}
	public function getObject()
	{
        return $this->result->fetch_object();
	}
	public function getArray()
	{
        return $this->result->fetch_array(MYSQLI_ASSOC);
	}
	public function fetchArray(int $simple=0):array
	{
        $ar=[];
		if($simple==0)
		{
			while($data=$this->result->fetch_array(MYSQLI_ASSOC))
			{
				$ar[]=$data;
			}
		}
		else
		{
			while($data=$this->result->fetch_array(MYSQLI_NUM))
			{
				$ar[]=$data;
			}
		}
        return $ar;
	}
	public static function insert(string $query):int
	{
		self::reconnectIfDown();
		$result=self::$mysqli->query($query);
		self::$error=self::$mysqli->error;
		if($result)
		return (int)self::$mysqli->insert_id;
		else
		{
            self::$logger->error(self::$error);
            throw new DBInsertException(self::$error);
		}
	}
	public static function update(string $query):bool
	{
		self::reconnectIfDown();
		$result=self::$mysqli->query($query);
		self::$error=self::$mysqli->error;
		if($result)
		return true;
		else
		{
            self::$logger->error(self::$error);
            throw new DBUpdateException(self::$error);
		}
	}
	public static function delete(string $query):bool
	{
		self::reconnectIfDown();
		$result=self::$mysqli->query($query);
		self::$error=self::$mysqli->error;
		if($result)
		return true;
		else
		{
            self::$logger->error(self::$error);
            DBDeleteException(DB::$error);
		}
	}
	public static function query(string $query):bool
	{
		self::reconnectIfDown();
		$result=self::$mysqli->query($query);
		self::$error=self::$mysqli->error;
		if($result)
		return true;
		else
		{
            self::$logger->error(self::$error);
            throw new DBException(DB::$error);
		}
	}
	public static function hack($var)
	{
		global $mysqli;
		if(is_array($var))
		{
			foreach($var as $key=>$data)
			{
				$var[$key]=self::hack($data);
			}
			return $var;
		}
		else
		{
			$var=htmlentities($var);
			$var=strip_tags($var);
			$var=stripslashes($var);
			return mysqli_real_escape_string(self::$mysqli,$var);
		}
	}
	public static function sanitze():void
	{
		define('GET',$_GET);
		define('POST',$_POST);
		foreach($_GET as $key=>$val)
		{
			if(!is_array($_GET[$key]))
			self::$get[$key]=self::hack($val);
			else
			{
				foreach($_GET[$key] as $k1=>$v1)
				self::$get[$key][$k1]=self::hack($v1);
			}
		}
		foreach($_POST as $key=>$val)
		{
			if(!is_array($_POST[$key]))
			self::$post[$key]=self::hack($val);
			else
			{
				foreach($_POST[$key] as $k1=>$v1)
				self::$post[$key][$k1]=self::hack($v1);
			}
		}
		$_GET=self::$get;
		$_POST=self::$post;
	}
	public static function getVal($query)
	{
		$rs=self::select($query);
		if(!$rs)
		{
			Log::add('DB',self::$error);
			exit(self::$error);
		}
		$ar=$rs->fetchArray(1);
		return $ar[0][0]??NULL;
	}
	public static function beginTransaction()
	{
		self::$mysqli->autocommit(FALSE);
	}
	public static function commit()
	{
		self::$mysqli->commit();
		self::$mysqli->autocommit(true);
	}
	public static function rollback()
	{
		self::$mysqli->rollback();
		self::$mysqli->autocommit(true);
	}
	public static function close()
	{
		self::$mysqli->close();
		self::$mysqli=NULL;
	}
	public static function reconnect()
	{
		try
		{
			self::close();
		}
		catch(\Exception $e)
		{

		}
		self::init();
	}
	public static function reconnectIfDown()
	{
		if(!self::$mysqli->ping()) self::reconnect();
	}
    public static function consoleBackup(string $db = 'DEFAULT', string $file)
    {
        exit("mysqldump --user=".DB['DEFAULT']['username']." --password=".DB['DEFAULT']['password']." --host=".DB['DEFAULT']['host']." ".DB['DEFAULT']['database']." > $file");
        exec("mysqldump --user=".DB[$db]['username']." --password=".DB[$db]['password']." --host=".DB[$db]['host']." ".DB['$db']['database']." > $file");
    }
    public static function backup(string $db,string $backupFile) : void 
    {
        $host=DB[$db]['host'];
        $user = DB[$db]['username'];
        $pass = DB[$db]['password'];
        $name = DB[$db]['database'];
        $tables=false;
        set_time_limit(3000); 
        $mysqli = new mysqli($host,$user,$pass,$name); $mysqli->select_db($name); $mysqli->query("SET NAMES 'utf8'");
        $queryTables = $mysqli->query('SHOW TABLES'); while($row = $queryTables->fetch_row()) { $target_tables[] = $row[0]; }	if($tables !== false) { $target_tables = array_intersect( $target_tables, $tables); }
        $content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `".$name."`\r\n--\r\n\r\n\r\n";
        foreach($target_tables as $table)
        {
            if (empty($table)){ continue; }

            $result	= $mysqli->query('SELECT * FROM `'.$table.'`');  	$fields_amount=$result->field_count;  $rows_num=$mysqli->affected_rows; 	$res = $mysqli->query('SHOW CREATE TABLE '.$table);	$TableMLine=$res->fetch_row();
            $content .= "\n\n".$TableMLine[1].";\n\n";   $TableMLine[1]=str_ireplace('CREATE TABLE `','CREATE TABLE IF NOT EXISTS `',$TableMLine[1]);
            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0)
            {
                while($row = $result->fetch_row())
                {
                    if ($st_counter%100 == 0 || $st_counter == 0 )	{$content .= "\nINSERT INTO ".$table." VALUES";}
					$content .= "\n(";    for($j=0; $j<$fields_amount; $j++){ $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); if (isset($row[$j])){$content .= '"'.$row[$j].'"' ;}  else{$content .= '""';}	   if ($j<($fields_amount-1)){$content.= ',';}   }        $content .=")";
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) {$content .= ";";} else {$content .= ",";}	$st_counter=$st_counter+1;
                }
            } 
            $content .="\n\n\n";
        }
        $content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
        file_put_contents($backupFile, $content);
    }
}