<?php
namespace CORE;
use CORE\MVC\MVC;
use CORE\ConstantReader;
use CORE\DB\DB;
class CronJob
{
    public static function execute()
    {
        try
        {
            $logger = \CORE\Logger::addChannel('cron');
            $functionCall = $_SERVER['argv'][2] ?? null;
            if($functionCall == null && Server::getOS() !== ServerOs::Window)
            {
                foreach(CRON['Jobs'] as $data)
                {
                    if($data['active'] === false)
                        continue;
                    self::executeFunction($data['call']);
                }
            }
            else
            {
                foreach(CRON['Jobs'] as $key=>$data)
                {
                    if($data['active'] === false)
                        continue;
                    if($data['call'] != $functionCall && Server::getOS() !== ServerOs::Window)
                    {
                        continue;
                    }
                    $data['interval']=explode(" ",$data['interval']);
                    $data['interval'][0]=(int)$data['interval'][0];
                    $run=false;
                    try
                    {
                        $lastrun = \CORE\MODELS\CronJob::find()->where(['Function', '=', $data['call']])
                            ->orderBy(['Id' => 'desc'])->limit(0,1)->single();
                        $lastrun = $lastrun == null ? 0 : $lastrun->getDate()->getTimeStamp();
                    }
                    catch(\Exception $e)
                    {
                        echo $e;
                        exit();
                    }
                    $time=time()-$lastrun;
                    $interval = 0;
                    switch($data['interval'][1])
                    {
                        case 'min':
                            if($time>=(60*$data['interval'][0]))
                                $run=true;
                                $interval = (60*$data['interval'][0]);
                            break;
                        case 'h':
                            if($time>=(3600*$data['interval'][0]))
                                $run=true;
                                $interval = (3600*$data['interval'][0]);
                            break;
                        case 'd':
                            if($time>=(86400*$data['interval'][0]))
                                $run=true;
                                $interval = (86400*$data['interval'][0]);
                            break;
                        case 'm':
                            if($time>=(2592000*$data['interval'][0]))
                                $run=true;
                                $interval = (2592000*$data['interval'][0]);
                            break;
                        case 'y':
                            if($time>=(31104000*$data['interval'][0]))
                                $run=true;
                                $interval = (31104000*$data['interval'][0]);
                            break;
                        default:
                            $logger->addError("Invalid Cron Job ".$data['call']." Settings.");
                            throw new Exception("Invalid Cron Job ".$data['call']." Settings.");
                            break;
                    }
                    if($run)
                    {
                        try
                        {
                            if(Lock::isLocked($data['call']))
                            {
                                $lockTime = Lock::getTime($data['call']);
                                if(($lockTime + CRON['ThresholdTime']) > time())
                                    return;
                                else if($lockTime + $interval > time())
                                    return;
                            }
                            Lock::lock($data['call']);
                            call_user_func('\\CRON\\'.$data['call']);
                            Lock::unlock($data['call']);
                            $cron = \CORE\MODELS\CronJob::init();
                            $cron->setFunction($data['call']);
                            $cron->setDate(Date::now());
                            $cron->setStatus(\CORE\MODELS\Status::Success);
                            $cron->save();
                        }
                        catch(\Throwable $e)
                        {
                            Lock::unlock($data['call']);
                            $cron = \CORE\MODELS\CronJob::init();
                            $cron->setFunction($data['call']);
                            $cron->setStatus(\CORE\MODELS\Status::Failed);
                            $cron->setDate(Date::now());
                            $cron->setLog(DB::hack($e->getMessage()));
                            $cron->save();
                            $logger->addError($e->getMessage());
                        }
                        catch(\Exception $e)
                        {
                            Lock::unlock($data['call']);
                            $cron = \CORE\MODELS\CronJob::init();
                            $cron->setFunction($data['call']);
                            $cron->setStatus(\CORE\MODELS\Status::Failed);
                            $cron->setDate(Date::now());
                            $cron->setLog(DB::hack($e->getMessage()));
                            $cron->save();
                            $logger->addError($e->getMessage());
                        }
                    }
                }
            }
        }
        catch(\Throwable $e) {
        }
        catch(\Exception $e) {
        }
    }
    private static function executeFunction($function): Thread
    {
        $thread = new Thread(root()."cronjob.php", '--function', $function);
        return $thread;
    }
}

