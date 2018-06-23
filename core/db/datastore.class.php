<?php
namespace CORE\DB;
class DataStore
{
    private static $dir;
    private static $file;
    public static function init()
    {
        self::$dir = DATASTORE['DIR'];
    }
    private static function getCollectionFile(string $name) : string
    {
        return self::clean($name);
    }
    private static function createCollection(string $name) : string
    {
        $name = self::getCollectionFile($name);
        fopen(self::$dir.".data", "wb");
    }
    public static function add(string $table, array $data)
    {
        $content = self::readTable($table);
        $content[] = $data;
        ftruncate(self::$file, 0);
        self::updateTable($content);
    }
    public static function readAll(string $table) : array
    {
        return self::readTable($table) ?? [];
    }
    public static function update(string $table, array $data)
    {
        if(count($data) < 1)
        {
            if(file_exists(self::$dir. self::getCollectionFile($table) .".data"))
            {
                fclose(self::$file);
                unlink(self::$dir. self::getCollectionFile($table) .".data");
            }
        }
        else
        {
            ftruncate(self::$file, 0);
            self::updateTable($data);
        }
    }
    private static function readTable(string $name) : array
    {
        if(empty(self::$dir))
            throw new \Exception("DataStore service is not initialized. Initialize the service first using DataStore::init()");
        $filePath = self::$dir. self::getCollectionFile($name) .".data";
        if(!file_exists($filePath))
        {
            fopen($filePath, "wb");
        }
        self::$file = fopen($filePath, "r+");
        $filesize = filesize($filePath);
        $content = fread(self::$file, $filesize < 1 ? 1 : $filesize );
        $content = self::decrypt($content);
        return $content;
    }
    private static function updateTable(array $content) : void
    {
        if(empty(self::$dir))
            throw new \Exception("DataStore service is not initialized. Initialize the service first using DataStore::init()");
        fwrite(self::$file, self::encrypt($content));
        flock(self::$file, LOCK_EX);
        fclose(self::$file);
    }
    private static function encrypt(array $content) : string
    {
        $content = json_encode($content);
        $ccnt = ceil(strlen($content)/20);
        $array = [];
        for($i=0; $i< $ccnt; $i++)
        {
            $array[] = substr($content, $i*20, 20);
        }
        $array = \CORE\Hash::encryptArray($array);
        return base64_encode(implode("_--_",$array));
    }
    private static function decrypt(string $content) : array
    {
        return json_decode(implode("", \CORE\Hash::decryptArray(explode("_--_", base64_decode($content)))), true) ?? [];
    }
    private static function clean($string)
    {
        $string = str_replace(' ', '-', $string);
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }
}
