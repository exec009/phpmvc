<?php
namespace CORE;
class Message
{
    const Info = 0;
    const Success = 1;
    const Danger = 2;
    const Warning = 3;
    private static $status = ['default', 'success', 'error'];
    private static $classes = ['primary', 'success', 'danger', 'warning'];
    private static $icon = ['md-notifications', 'md-check', 'md-close', 'md-alert-circle-o'];
    private static $heading = ['Note', 'Success', 'Error'];
    private static $messages = [];
    private static $key = "message_fsd8f78";
	public static function add(string $message, int $status)
	{
        self::$messages[] = ['status' => $status, 'message' => $message];
	}
    public static function addGlobal(string $message, int $status)
    {
        self::add($message, $status);
        $data = json_decode(Session::exists(self::$key) ? Session::get(self::$key) : '{}', true);
        $data[] = ['status' => $status, 'message' => $message];
        Session::set(self::$key, json_encode($data));
    }
    private static function constructMessage(array $message)
    {
        return '<div class="notif-msgs-38 alert dark alert-icon alert-' . self::$classes[$message['status']] . ' alert-dismissible" role="alert">
        <button type="button" class="close alert-close-btn" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        <i class="icon ' . self::$icon[$message['status']] . '" aria-hidden="true"></i> ' . $message['message'] . '</div>';
    }
    public static function clearAll()
    {
        \CORE\Session::remove(self::$key);
        self::$messages = [];
    }
    public static function toString() : string
    {
       $str = '';
       $msgs = json_decode(Session::get(self::$key), true) ?? [];
       Session::remove(self::$key);
       foreach($msgs as $msg)
       {
           $str.= self::constructMessage($msg);
       }
       foreach(self::$messages as $msg)
       {
           $str.= self::constructMessage($msg);
       }
       return $str;
    }
    public static function getAll()
    {
        return self::$messages;
    }
    public static function getLastMessage(): array
    {
        return self::$messages[count(self::$messages)-1];
    }
}
?>