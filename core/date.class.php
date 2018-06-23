<?php
namespace CORE;
$date = new Date(); // getDate();
class Date
{
    public $date;
    private $time;
    private static $databaseDateFormat="Y-m-d H:i:s";
    private static $dateFormat="Y-m-d";//american Y-m-d | European d-m-Y
    private static $timeformat="H:i";
    public static function now() : self
    {
        return new self();//
    }
    private static function nowDate() : string
    {
        return date(self::$databaseDateFormat);
    }
    public function __construct($date="Now")
    {
        if($date=="Now")
            $this->date=self::nowDate();
        else if(is_string($date))
            $this->date=date(self::$databaseDateFormat,strtotime($date));
        else
            $this->date=date(self::$databaseDateFormat, $date);
        $this->time=strtotime($this->date);
    }
    public function getDay() : string
    {
        return date('d',$this->time);
    }
    public function getDayName() : string
    {
        return date('D',$this->time);
    }
    public function getMonthInt() : string
    {
        return date('m',$this->time);
    }
    public function getMonth() : string
    {
        return date('M',$this->time);
    }
    public function getFullMonth() : string
    {
        return date('F',$this->time);
    }
    public function getYear() : string
    {
        return date('Y',$this->time);
    }
    public function getDate() : string
    {
        return date(self::$dateFormat,$this->time);
    }
    public function getDatabaseDate() : string
    {
        return date(self::$databaseDateFormat,$this->time);
    }
    public function getDateTime() : string
    {
        return date(self::$dateFormat." ".self::$timeformat,$this->time);
    }
    public function getTime() : string
    {
        return date(self::$timeformat,$this->time);
    }
    public function getTimeStamp() : string
    {
        return $this->time;
    }
    public function getHours() : string
    {
        return date("h",$this->time);
    }
    public function getMinutes() : string
    {
        return date("i",$this->time);
    }
    public function getSeconds() : string
    {
        return date("s",$this->time);
    }
    public function __toString() : string
    {
        return $this->getDateTime();
    }
    public function addYears(int $years) : self
    {
        $this->time += ($years * 31536000);
        return $this;
    }
    public function addMonths(int $months) : self
    {
        $this->time += ($months * 2628000);
        return $this;
    }
    public function addDays(int $days) : self
    {
        $this->time += ($days * 86400);
        return $this;
    }
    public function addHours(int $hours) : self
    {
        $this->time += ($hours * 3600);
        return $this;
    }
    public function addMinutes(int $minutes) : self
    {
        $this->time += ($minutes * 60);
        return $this;
    }
    public function addSeconds(int $seconds) : self
    {
        $this->time += $seconds;
        return $this;
    }
}