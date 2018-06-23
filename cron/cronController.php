<?php
namespace CRON;
use MODELS\ADMIN\AdminInventory;
use INTERFACES\ControllerInterface;
use MODELS\ADMIN\AdminRoleInventory;
class CronController
{
	public static function coreJobs(): void
	{
        file_put_contents(root()."testsss.json", time());
        \CORE\TaskRunner::executeAll();
	}
    public static function databaseBackup(): void
    {
        \CORE\BackupManager::backupDatabase();
    }
	public static function myCustomCronjob(): void
	{
		//do the task here
	}
}
?>