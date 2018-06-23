<?php
spl_autoload_register(function ($class) {
	$class=strtolower($class);
    $class=str_replace('\\','/',$class);
    $namespace = explode("/",trim($class,"/"));
    $pathFix = root().$class.'.class.php';
    if(count(explode("core", $class)) <= 1)
    {
        if(count(explode("parent", $class)) <= 1)
            $pathFix = str_replace('controller.class.php', 'Controller.php', $pathFix);
    }
    if($namespace[0] == "cron")
    {
        $pathFix = root().$class.'.php';
    }
    if(file_exists($pathFix))
    {
        require_once $pathFix;
    }
});
function issetPost(string ...$keys) : bool
{
    foreach($keys as $key)
    {
        if(!isset($_POST[$key]) || empty($_POST[$key]))
        {
            return false;
        }
    }
    return true;
}
function root():string
{
	return SITE_ROOT;
}
function viewRoot():string
{
	return root()."views/";
}
function configRoot():string
{
	return root()."config/";
}
function uploadRoot():string
{
	return root()."uploads/";
}
function interfaceRoot():string
{
	return root()."interfaces/";
}
function exceptionsRoot():string
{
	return root()."exceptions/";
}
function initRoot():string
{
	return root()."init/";
}
function controllerRoot():string
{
	return root()."controllers/";
}
function siteUrl():string
{
	return SITE['URL'];
}
function redirect(string $url):void
{
    if (headers_sent()){
      die('<script type="text/javascript">window.location.href="' . $url . '";</script>');
    }else{
      header('Location: ' . $url);
      die();
    }
	exit();
}
function year(int $val):void
{
	$y=date("Y");
	for($i=$y;$i>=1950;$i--)
	{
		if($val==$i)
		echo "<option selected>".$i."</optio>";
		else
		echo "<option>".$i."</optio>";
	}
}
function parseData(string $var):array
{
	$var=explode("\n",$var);
	$ar=array();
	$cols=array();
	foreach($var as $key=>$data)
	{
		if(strlen($data)<2)
		continue;
		$data=explode("\t",$data);
		foreach($data as $key1=>$data1)
		{
			$ar[$key][]=$data1;
		}
	}
	return $ar;
}
function message(string $var):void
{
	echo '<script>alert("'.$var.'");</script>';
}
function isAjax():bool//function is used for ajax requested
{
	if(!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))
	return false;
	else
	return true;
}
function smallEncode(string $data):string
{
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function smallDecode(string $data):string
{
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
function currentUrl(bool $page_name_only=false) : string
{
	$pageURL = 'http';
	if(isset($_SERVER["HTTPS"]))
	{
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}
	else
	{
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	if($page_name_only)
	$pageURL=$_SERVER["REQUEST_URI"];
	return $pageURL;
}
function dateDayDate(int $timestamp):string
{
	$timestamp=strtotime($timestamp);
    $date = date('d/M/Y', $timestamp);

    if($date == date('d/M/Y')) {
      $date = 'Today';
    }
    else if($date == date('d/M/Y',time() - (24 * 60 * 60))) {
      $date = 'Yesterday';
    }
    return $date.", ".date('ga',$timestamp);
}
function getUniqueStr():string
{
	return rand().time().rand();
}
function getExtension(string $name):string
{
	$img= explode(".",$name);
	return $img[count($img)-1];
}
function getId()
{
	$id=explode("/",GET['route']);
	return $id[count($id)-1];
}
function getCount(Generator $functor)
{
   $count = 0;
   foreach($functor as $value)
   {
      $count++;
   }
   return $count;
}
function getIp():string
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
if (!function_exists('getallheaders')) 
{ 
    function getallheaders() 
    { 
       $headers = array (); 
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
} 
if(!function_exists('apache_request_headers'))
{
    function apache_request_headers() {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
        $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'] ?? 0;
        return $headers;
    }
}
if (!function_exists('apache_response_headers')) {
    function apache_response_headers () {
        $arh = array();
        $headers = headers_list();
        foreach ($headers as $header) {
            $header = explode(":", $header);
            $arh[array_shift($header)] = trim(implode(":", $header));
        }
        return $arh;
    }
}
