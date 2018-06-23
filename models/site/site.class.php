<?php
namespace MODELS\SITE;
use CORE\MVC\Model;
use CORE\MVC\IModel;
use CORE\Session;
class Site extends Model implements IModel
{
    protected static $table="site_settings";
    protected static $model = [
        'Id'=>['Key'=>true,'Type'=>'Int','Required'=>true, 'ColumnName'=>'id'],
        'Name'=>['Max'=>75,'Min'=>3,'Type'=>'String','Required'=>true,'ColumnName'=>'name','Display'=>'Site Name'],
        'Logo'=>['Type'=>'String','Required'=>true,'ColumnName'=>'logo','Display'=>'Site Logo'],
        'Logo1'=>['Type'=>'String','Required'=>true,'ColumnName'=>'footer_logo','Display'=>'Site 2nd Logo'],
        'Title'=>['Max'=>75,'Min'=>3,'Type'=>'String','Required'=>true,'ColumnName'=>'title','Display'=>'Site Title'],
    ];
    public static function createTable()
    {
        \CORE\DB\DB::query("CREATE TABLE `site_settings` (
          `id` int(1) NOT NULL auto_increment Key,
          `title` varchar(75) DEFAULT NULL,
          `name` varchar(100) DEFAULT NULL,
          `logo` varchar(100) DEFAULT NULL,
          `footer_logo` varchar(100) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
        \CORE\DB\DB::query("INSERT INTO `site_settings` (`id`, `title`, `name`, `logo`, `footer_logo`) VALUES
        (1, 'Zech', 'Zech', 'assets/img/logo00039.png', 'assets/img/logo100039.png');");
    }
}
?>