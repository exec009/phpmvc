<?php
namespace CORE\MVC;
use MODELS\SITE\Site;
use CORE\MVC\View;
use CORE\MVC\PartialView;

/**
 * @method void redirect(string $action)
 * @method void redirect(string $controller, string $action)
 * @method void redirect(string $area, string $controller, string $action)
 */
class Controller implements IController
{
	public
        $page,
        $site,
        $area,
        $controller,
        $action,
        $viewData,
        $ModelStateIsValid,
        $modelError,
        $isAjaxFormRequest;
    private static $siteStatic;
	public function __construct()
	{
        $this->viewData=new \stdClass();
        if(empty(self::$siteStatic))
        {
            if(\CORE\REDIS\Redis::$redis->exists('site_info'))
            {
                $this->site = unserialize(\CORE\REDIS\Redis::$redis->get('site_info'));
            }
            else
            {
                $this->site=Site::init(1);
                \CORE\REDIS\Redis::$redis->set('site_info', serialize($this->site));
            }
            self::$siteStatic = $this->site;
        }
        else
        {
            $this->site = self::$siteStatic;
        }
        $this->ModelStateIsValid = true;
	}
    public static function getAllControllersWithActions($path = '')
    {
        $root = root()."controllers/".$path;
        $files = scandir($root);
        $routes = [];
        $skip=['.','..','.htaccess','backup'];

        $skipAction[] = 'error';
        $reflection = new \ReflectionClass(self::class);
        $methods = $reflection->getMethods();
        foreach($methods as $method)
            $skipAction[] = $method->getName();
        foreach($files as $file)
        {
            if(!in_array($file, $skip))
            {
                if(is_dir($root.$file))
                {
                    $routes[$file] = self::getAllControllersWithActions($file."/");
                }
                else if(count(explode("Controller", $file)) > 1)
                {
                    try
                    {
                        $namespace = "\\Controllers\\";
                        if($path != '')
                        {
                            $namespace.= str_replace("/","",$path)."\\";
                        }
                        $class = strtoupper($namespace).ucfirst(str_replace(".php","",$file));
                        $reflection = new \ReflectionClass($class);
                        $methods = $reflection->getMethods();
                        foreach($methods as $method)
                        {
                            if($method->isPublic() && !$method->isStatic() && !in_array($method->getName(), $skipAction))
                            {
                                $routes[str_replace("Controller.php","",$file)][] = $method->getName();
                            }
                        }
                    }
                    catch(\Exception $e)
                    {

                    }
                }
            }
        }
        return $routes;
    }
    public static function init()
    {
        $class = static::class;
        return new $class();
    }
    /*
	public function error():void
	{
		echo "<h1>Error 404. Page Not Found</h1>";
	}*/
	public function beforeRender() : void
	{
        if((isAjax() && count($_POST) > 0 ) || isset($_POST['isAjaxScript']) || isset($_GET['isAjaxScript']))
            $this->isAjaxFormRequest = true;
        else
            $this->isAjaxFormRequest = false;
		$this->page = $this->action;
	}
	public function afterRender():void
	{
        if($this->ModelStateIsValid && isset($_POST['anit_forgery_verification_token']))
        {
            $db = \CORE\DB\DB::getInstanceName();
            \CORE\DB\DB::init('INTERNAL');
            $forgToken = \CORE\MODELS\ForgeryToken::init();
            $forgToken->setToken($_POST['anit_forgery_verification_token']);
            $forgToken->save();
            \CORE\DB\DB::init($db);
        }
	}
	public function activeCheck(string $action):void
	{

	}
    public function partialView(object $Model=null) : PartialView
    {
        ob_start();
        if($this->area=="" || $this->area=="none")
        {
            if(file_exists(strtolower(viewRoot()."partials/".$this->controller."/".$this->action.".php")))
                require strtolower(viewRoot()."partials/".$this->controller."/".$this->action.".php");
            else
                throw new PartialViewNotFoundException($this->controller.":".$this->action.".php". ": Partial View Not Found");
        }
        else
        {
            if(file_exists(strtolower(viewRoot()."partials/".$this->area."/".$this->controller."/".$this->action.".php")))
            require strtolower(viewRoot()."partials/".$this->area."/".$this->controller."/".$this->action.".php");
            else
                throw new PartialViewNotFoundException($this->area.":".$this->controller.":".$this->action. " Partial View Not Found");
        }
        $data=ob_get_clean();
        return PartialView::init($data);
    }
    public function view(IModel $model=null):View
    {
        ob_start();
        if($this->area=="" || $this->area=="none")
        {
            if(file_exists(strtolower(viewRoot().$this->controller."/".$this->action.".php")))
                require strtolower(viewRoot().$this->controller."/".$this->action.".php");
            else
                throw new ViewNotFoundException($this->action. ': View Not Found at following location '. strtolower(viewRoot().$this->controller."/".$this->action.".php"));
        }
        else
        {
            if(file_exists(strtolower(viewRoot().$this->area."/".$this->controller."/".$this->action.".php")))
                require strtolower(viewRoot().$this->area."/".$this->controller."/".$this->action.".php");
            else
                throw new ViewNotFoundException($this->action. ': View Not Found at following location '. strtolower(viewRoot().$this->area."/".$this->controller."/".$this->action.".php"));
        }
        $data=ob_get_clean();
        return View::init($data,$model);
    }
    public function content($data) : View
    {
		if(is_string($data))
        return View::init($data);
		else if(is_object($data) || is_array($data))
        return View::init(json_encode($data));
		else if(is_int($data))
        return View::init((string) $data);
		else
		throw new \InvalidArgumentException("Controller::content 1st argument must be (String, Array, Object, Int). Other datatypes are not allowed.");
    }
    public function redirect(string ...$args) : void
    {
        if($this->isAjaxFormRequest)
            return;
        $count=count($args);
        $url="";
        if($count>3)
            throw new \InvalidArgumentException("Invalid Arguments For function Redirect.");
        else if($count==3)
        {
            $args = array_filter($args);
            $url=implode("/",$args);
        }
        else if($count==2)
        {
            $args = array_filter($args);
            $url=implode("/", $args);
			if($this->area != 'none')
            $url = $this->area."/".$url;
        }
        else
        {
            $args = array_filter($args);
            $url=implode("/", $args);
			if($this->area != 'none')
            $url = $this->area."/".$this->controller."/".$url;
			else
            $url = $this->controller."/".$url;
        }
        $url = siteUrl().$url;
        redirect($url);
    }
    public function getId()
    {
        $id=explode("/",$_GET['route']);
        $id=$id[count($id)-1];
        return $id==$this->action || $id == $this->controller ? null : $id;
    }
}