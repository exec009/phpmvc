<?php
namespace CORE;
use \CORE\DB\DataStore;
class TaskRunner
{
    const TABLE = "TaskRunner";
	public static function addTask(string $task, string ...$params)
	{
        $task = [$task => $params];
        DataStore::add(self::TABLE, $task);
	}
    public static function executeAll()
    {
        $logger = \CORE\Logger::addChannel('taskRunner');
        $tasks = DataStore::readAll(self::TABLE);
        foreach($tasks as $index => $task)
        {
            foreach($task as $key => $data)
            {
                try
                {
                    if((new \ReflectionFunction($key))->getReturnType() == "bool")
                    {
                        if(!call_user_func_array($key, $data))
                        {
                            throw new \Exception("Error");
                        }
                    }
                    else
                    {
                        call_user_func_array($key, $data);
                    }
                    unset($tasks[$index]);
                }
                catch(\Exception $e)
                {
                    $logger->addError($e->getMessage());
                }
            }
        }
        DataStore::update(self::TABLE, $tasks);
    }
}