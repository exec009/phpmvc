<?php
return [
    'ThresholdTime' => 3600, //before cronjob restart again in case of fatal error
    'Jobs' => [
	    'Core Jobs'=> [
		    'interval'=>'1 min',
		    'call'=>'CronController::coreJobs',
		    'active'=>true
	    ],
	    'Database Backup'=>[
		    'interval'=>'1 h',
		    'call'=>'CronController::databaseBackup',
		    'active'=>false
	    ],
	    'My Custom Cronjob'=> [
		    'interval'=>'2 h',
		    'call'=>'CronController::myCustomCronjob',
		    'active'=>true
	    ],
    ]
];
/*
active=>
	true=> run cron job
	false=> disable (Don't run) cron job
call=>
	function name in controller
intervals=>
	1 min=1 Minute
	1 h=1 Hour
	1 d=1 Day
	1 m=1 Month,
	1 y=1 year
*/
?>