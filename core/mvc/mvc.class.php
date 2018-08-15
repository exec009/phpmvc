<?php
namespace CORE\MVC;
class MVC
{
    public static $counter=0;
	private function __construct()
	{

	}
	public static function getView():string
	{
		$tr=debug_backtrace();
		return strtolower(viewRoot().(str_replace('Controller','',$tr[1]['class'])."/".$tr[1]['function'].".php"));
	}
    public static function callAction($controller,$action,$area="none",$lastrun=false):void
    {
        self::$counter++;
        try
        {
            $controller=trim(strtolower($controller));
            $action=trim(strtolower($action));
            if($controller=="home" && $action=="error")
            {
                throw new PageNotFoundException();
            }
            if($area=="none")
            {
                if(!file_exists("controllers/".$controller."Controller.php"))
                {
                    $action = $controller;
                    $controller = "home";
//                    throw new ActionNotFoundException();
                }
                require_once "controllers/".$controller."Controller.php";
                $controller=(ucfirst($controller)."Controller");
            }
            else
            {
                $controller = $controller == "index" ? "default" : $controller;
                if(!file_exists("controllers/".$area."/".$controller."Controller.php"))
                    throw new ActionNotFoundException();
                require_once "controllers/".$area."/".$controller."Controller.php";
                $controller=strtoupper($area)."\\".(ucfirst($controller)."Controller");
            }
            $controller="\\CONTROLLERS\\".$controller;
            $controller = new $controller;
	        $action=explode("-",$action);
	        for($i=1;$i<count($action);$i++)
	        {
		        $action[$i]=ucfirst($action[$i]);
	        }
            $lastrun=true;
	        $action=implode("",$action);
            if(!method_exists($controller,$action))
                throw new ActionNotFoundException();
            $reflection = new \ReflectionMethod($controller, $action);
            if(!$reflection->isPublic() || $reflection->isStatic())
                throw new ActionNotFoundException();
            $params = $reflection->getParameters();
            $cache = false;
            foreach($params as $param)
            {
                if($param->getName() == 'cacheRules')
                {
                    $cache = $param->getDefaultValue();
                    break;
                }
            }
            if($cache && ($cache['disable'] ?? false) == false)
            {
                $key = currentUrl();/*
                if(($cache['userSpecific'] ?? false) == true)
                {
                    $key .= 'change this to user login function';
                }*/
                if(isset($cache['params']))
                {
                    $key .= implode("-",$param);
                }
                $redis = \CORE\REDIS\Redis::get();
                if($redis->exists($key))
                {
                    echo $redis->get($key);
                }
                else
                {
                    ob_start();
                    self::pageInit($controller,$action,$area);
                    echo $content = ob_get_clean();
                    $redis->set($key, $content);
                    $redis->expire($key, $cache['expire'] ?? 300);
                }
            }
            else
            self::pageInit($controller,$action,$area);
        }
        catch(PageNotFoundException $e)
        {
            require_once "controllers/homeController.php";
            self::pageInit(new \CONTROLLERS\HomeController(),$action);
        }
        catch(ActionNotFoundException $e)
        {
            if($area=="none" || $lastrun==true)
            {
                self::callAction("Default","Error");
            }
            else
            {
                self::callAction("Default",$controller,$area,true);
            }
        }
    }
    private static function processHTML(\CORE\MVC\IController $mainController, string $html, IModel $model = null, string $parentFile = '', bool $antiForgeryToken = false):string
    {
        $html=\CORE\SimpleHTMLDomApi::stringGetHTML($html);
        if($html==null)
            return $html;
        try
        {
            $layout=$html->find("layout",0)->innertext;
            if($layout==null)
                throw new \Exception("Layout Not Defined");
            $html->find("Layout",0)->outertext="";
            $BodyInnerSection=$html;
            ob_start();
            $controller = $mainController;
            require_once viewRoot().$layout.".php";
            $layout=ob_get_clean();
            $html=null;
            $layout=\CORE\SimpleHTMLDomApi::stringGetHTML($layout);
        }
        catch(\Exception $e)
        {
            $layout=$html;
        }
        foreach($layout->find('PartialLayout') as $key=>$partial)
        {
            $mainParameter = $partial->innertext;
            $params=explode("=>",$partial->innertext);
            $cn=explode(":",$params[0]);
            $area="";
            if(count($cn)>2)
            {
                $area=$cn[0];
                $controller=($cn[1]);
                $action=$cn[2];
                require_once root()."controllers/".lcfirst($cn[0])."/".$controller.".php";
                $controller=$cn[0]."\\".$controller;
            }
            else
            {
                $area="none";
                $controller=($cn[0]);//."Controller"
                $action=$cn[1];
                require_once root()."controllers/".lcfirst($cn[0]).".php";
            }
            $controller='\\CONTROLLERS\\'.$controller;
            $controller=new $controller;
            $cntrl=explode("\\",get_class($controller));
            $cntrl=$cntrl[count($cntrl)-1];
            $controller->controller=str_replace("controller","",strtolower($cntrl));
            $controller->action=$action;
            $controller->area=$area;
            if(isset($params[1]))
            {
                $params[1]=json_decode($params[1],true);
                if(json_last_error() == JSON_ERROR_NONE)
                {
                    if(method_exists($controller, $action))
                    {
                        $data=$controller->{$action}($params[1]);
                    }
                    else
                    {
                        throw new PartialViewNotFoundException($action.": Partial View Not Found", $mainParameter, $parentFile);
                    }
                }
                else
                {
                    if(method_exists($controller, $action))
                    {
                        $data=$controller->{$action}();
                    }
                    else
                    {
                        throw new PartialViewNotFoundException($action.": Partial View Not Found", $mainParameter, $parentFile);
                    }
                }
            }
            else
            {
                if(method_exists($controller, $action))
                {
                    $data=$controller->{$action}();
                }
                else
                {
                    throw new PartialViewNotFoundException($action.": Partial View Not Found", $mainParameter, $parentFile);
                }
            }
            if(get_class($data)!="PartialView" && get_class($data)!="CORE\MVC\PartialView")
            {
                throw new \Exception("Only Partial Actions are allowed to being called as partial");
            }
            $partial->outertext = self::processHTML($controller, $data->getHtml(), null, $mainParameter);
        }
        foreach($layout->find('a') as $key=>$link)
        {
            if(($link->attr['href'] ?? null) != null && ($link->attr['action'] ?? null) != null)
            {
                throw new \CORE\MVC\ViewErrorException("Both action and href cannot be specified in link at the same time", $parentFile, $link);
            }
            else if(($link->attr['action'] ?? null) == null)
            {
                continue;
            }
//            $area = strtolower((isset($link->attr['area']) ? $link->attr['area']."/" : $mainController->area."/"));
			$area = strtolower((isset($link->attr['area']) ? $link->attr['area']."/" : $mainController->area == 'none' ? '' : $mainController->area."/"));
            $controller = strtolower((isset($link->attr['controller']) ? $link->attr['controller']."/" : $mainController->controller."/"));
            $action = strtolower((isset($link->attr['action']) ? strtolower(preg_replace("/([A-Z])/", "-$1",$link->attr['action']))."/" : ""));
            $routeId = $link->attr['route-id'] ?? null;
            if(!empty($route) && empty($controller))
            {
                throw new \CORE\MVC\ViewErrorException("Controller option is Not Specified", $parentFile, $link);
            }
            else if(!empty($controller) && empty($action))
            {
                throw new \CORE\MVC\ViewErrorException("Action option is Not Specified", $parentFile, $link);
            }
            if($controller == "default/")
                $controller = '';
            $link->{'area'} = null;
            $link->{'controller'} = null;
            $link->{'route-id'} = null;
            $link->{'action'} = null;
            $link->{'href'} = SITE_URL.$area.$controller.$action.$routeId;
        }
        foreach($layout->find('img, script') as $img)
        {
            $src = $img->{'src'};
            if(($src[0] ?? '') == '~')
            {
                $img->{'src'} = SITE_URL.trim($src,'~');
            }
        }
        foreach($layout->find('link') as $img)
        {
            $src = $img->{'href'};
            if(($src[0] ?? '') == '~')
            {
                $img->{'href'} = SITE_URL.trim($src,'~');
            }
        }
        foreach($layout->find('form[web-bind-model=true]') as $formKey=>$form)
        {
//            $model=$form->attr['web-model'];
//            $form->attr['web-model']=null;
            $form->attr['web-bind-model']=null;
            $form->{'data-validation'}="true";
//            $model=new $model;
            $properties = $model==null ? [] : $model->getModelProperties();
            $arrayCounter = [];
            foreach($form->find('input,select') as $input)
            {
                if(!isset($input->attr['web-name']))
                {
                    continue;
                }
                $name=$input->attr['web-name'];

                if(!isset($arrayCounter[$name]))
                    $arrayCounter[$name] = 1;
                else
                    $arrayCounter[$name] += 1;

                $displayName=$properties[$name]["Display"] ?? $name;
                $input->{"data-display"}=$displayName;
                $id=$name."-".($formKey+$arrayCounter[$name]);
                $input->{'id'}=$id;
                //$input->next_sibling();
                $span=$form->find('span[web-for='.$name.']',0) ?? new \stdClass();
                $span->{'web-for'}=null;
                $span->{'for'}=$id;
                $input->{"web-name"}=null;
                $input->{'name'}=$name;
                try
                {
                    if($model == null && $name != null)
                        throw new \ErrorException("Model is not defined.");
                    $isArray = false;
                    $oldName = $name;
                    $name = trim($name, '[]');
                    if($name!=$oldName)
                        $isArray = true;
                    if(($model->{"get".$name}() != null || ($isArray ? count($model->{"get".$name}()) : $model->{"get".$name}()=="")))// && isset($_POST['form_submitted']))
                    {
                        if(strlen($input->innertext)<5)
                        {
                            if(strtolower($input->type)=='checkbox' || strtolower($input->type)=='radio')
                            {
                                //$arrayCounter[$oldName]
                                if($properties[$name]['Type'] === 'Enum')
                                {
                                    if($isArray)
                                        $input->checked = in_array($input->{'value'} , ($model == null ? null : ($model->{"get".$name}())));
                                    else
                                        $input->checked = $model == null ? false : empty($model->{"get".$name}()) ? $properties[$name]['Default'] : $model->{"get".$name}()->equals($input->{'value'});
                                }
                                else
                                {
                                    if($isArray)
                                        $input->checked = in_array($input->{'value'} , ($model == null ? null : ($model->{"get".$name}())));
                                    else
                                        $input->checked = ($input->{'value'} == ($model == null ? null : (empty($model->{"get".$name}()) ? $properties[$name]['Default'] : $model->{"get".$name}() )));// == false ? ($model != null && $input->{'value'} == $properties[$name]['Default'] ?? null) : false;
                                }
                            }
                            else if(strtolower($input->type) != "password")
                            {
                                if($isArray)
                                    $vvvv = $_POST[$name][$arrayCounter[$oldName] - 1] ?? ($model == null ? null : ($model->{"get".$name}()[$arrayCounter[$oldName] - 1]));
                                else
                                    $vvvv = $_POST[$name] ?? ($model == null ? null : ($model->{"get".$name}()));
                                if(!empty($vvvv))
                                $input->{'value'} = $vvvv;
                            }
                        }
                        else
                        {
                            $modelValue_1 = null;
                            if($model != null)
                            {
                                if($model::isForeignKey($name))
                                {
                                    $modelValue_1 = $model->getForeignKeyValue($name);
                                }
                                else if($model::isEnum($name))
                                {
                                    $modelValue_1 = $model->{"get".$name}()->value;
                                }
                                else
                                {
                                    $modelValue_1 = $model->{"get".$name}();
                                }
                            }
                            if(isset($_POST[$name]))
                            {
                                $modelValue_1 = $_POST[$name];
                            }
                            foreach($input->find("option") as $option)
                            {
                                if($option->value == '')
                                    continue;
                                if($option->value == $modelValue_1 && $modelValue_1 != null)
                                    $option->selected=true;
                            }
                        }
                        $result = \CORE\Validator::validateField($displayName, $properties[$name], $model->{"get".$name}());
                        if(!$result->status && count($_POST)>0)
                        {
                            $span->innertext=$result->message;
                        }
                    }
                }
                catch(\BadMethodCallException $e)
                {

                }
                if(isset($properties[$name]))
                {
                    $rules=self::createValidationRules($properties[$name], $name, get_class($model));
                    if(strtolower($input->type)=="checkbox")
                    {
                        $rules[' required']="ds98s7d9";
                        unset($rules[' required']);
                    }
                    $reg = "data-regular-expression";
                    foreach($rules as $name=>$value)
                    {
                        if($input->$reg && $name == " data-regular-expression")
                            continue;
                        $input->$name=$value;
                    }
                }
                if(strtolower($input->type)=="checkbox")
                {
                    $input->required = null;
                    $input->{"data-required"} = "true";
                }
            }
            $form->innertext .= '<input name="form_submitted" type="hidden" value="1"/>';
            if($antiForgeryToken)
            {
                $token = \CORE\Hash::createAntiForgeryToken();
                $form->innertext .= '<input type="hidden" name="anit_forgery_verification_token" value="'.$token.'"/>';
            }
        }
        $layout = preg_replace( '/<!--(.|\s)*?-->/' , '' , $layout);
        $layout = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '', $layout);
        return $layout;
    }
    public static function validateField(string $field,$value)
    {
        $status =  new \stdClass();
        return $status;
    }
    public static function createValidationRules(array $mainRules, string $keyName, string $modelName):array
    {
        $rules=[];
        if(!isset($mainRules['Type']))
            throw new ModelPropertyErrorException("Type parameter is not defined for $keyName in $modelName", $modelName, $keyName);
        foreach($mainRules as $key=>$data)
        {
            switch($key)
            {
                case 'ColumnName':
                    break;
                case 'Min':
                    if($mainRules['Type']=="Int" || $mainRules['Type']=="Number")
                        $rules[' data-min']=floatval($data);
                    else
                        $rules[' data-min-length']=floatval($data);
                    break;
                case 'Max':
                    if($mainRules['Type']=="Int" || $mainRules['Type']=="Number")
                        $rules[' data-max']=floatval($data);
                    else
                        $rules[' data-max-length']=floatval($data);
                    break;
                case 'Regexp':
                    $rules[' data-regular-expression']=htmlentities($data);
                    break;
                case 'Required':
                    $rules[' required']=true;
                    break;
                case 'Type':
                    switch($data)
                    {
                        case 'Int':
                            $rules[' data-regular-expression']='^[0-9]*$';
                            break;
                        case 'Name':
                            $rules[' data-regular-expression']='^[a-zA-Z\\s]*$';
                            break;
                        case 'Email':
                            $rules[' data-regular-expression']='/(?:[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/i';
                            break;
                        case 'Number':
                            $rules[' data-regular-expression']='^[0-9.]*$';
                            break;
                    }
                    break;
            }
        }
        return $rules;
    }
    private static function pageInit(\CORE\MVC\Controller $controller,string $action,string $area=""):void
    {
        $cntrl=explode("\\",get_class($controller));
        $cntrl=$cntrl[count($cntrl)-1];
        $controller->area=$area;
        $controller->controller=str_replace("controller","",strtolower($cntrl));
        $controller->action=$action;
        $controller->beforeRender();
        $model=null;
        $antiForgeryToken = true;
		$properties = null;
		$modelIsNull = true;
        try
        {
            $model = (new \ReflectionMethod("\\".get_class($controller),$action))->getParameters();
            $indexes=['method'=>-1,'bind'=>-1, 'autoLoadModel'=> -1];
            $reservedKey = ['method', 'bind', 'cacheRules', 'id','validateAntiForgeryToken', 'autoLoadModel'];
            $modelCount = 0;
            $mainModel = '';
            foreach($model as $key1=>$data1)
            {
                if(!in_array($data1->getName(), $reservedKey))
                {
                    if(in_array('CORE\MVC\IModel', $data1->getClass()->getInterfaceNames() ?? []))
                    {
                        $mainModel = $data1->getType();
                        $modelCount += 1;
                    }
                }
                $indexes[$data1->getName()]=$key1;
            }
            if($modelCount>1)
            {
                throw new \ErrorException($modelCount." models are specified in action, Only 1 model can be assigned to an action.");
            }
            $antiForgeryToken = isset($indexes['validateAntiForgeryToken']) ?
                (($indexes['validateAntiForgeryToken'] ?? true) ?
                $model[$indexes['validateAntiForgeryToken']]->getDefaultValue() : true) : true;
            $type = isset($model[$indexes['method']]) ? $model[$indexes['method']]->getDefaultValue() : "POST";
            if($type == "GET")
            {
                $formVars = $_GET;
            }
            else
            {
                $formVars = $_POST;
            }
            $controller->isPost = count($_POST) > 0;
			
            if($antiForgeryToken && !\CORE\Hash::validateAntiForgeryToken($formVars['anit_forgery_verification_token'] ?? ''))
            {
                $controller->ModelStateIsValid = false;
            }

            if(count($model)<1)
            {
                throw new \InvalidArgumentException();
            }

            $bindedAttribute = isset($model[$indexes['bind']]) ? $model[$indexes['bind']]->getDefaultValue() : [];
            $dataType = isset($indexes['id']) ? $model[$indexes['id']]->getType() : null;
            $autoload = isset($model[$indexes['autoLoadModel']]) ? $model[$indexes['autoLoadModel']]->getDefaultValue() : true;
            $routeId = null;
            if($dataType == 'string')
            {
                $routeId = (string) getId();
            }
            else if($dataType == 'int')
            {
                $routeId = (int) getId();
            }
                if(!empty($mainModel))
                    $model="\\".$mainModel;
                else
                $model=null;
                if($routeId != null && (int)$routeId > 0 && $dataType == 'int' && $autoload)
                {
                    try
                    {
                        $model = $model::init($routeId);
						$properties = $model->getModelProperties();
                    }
                    catch(ObjectNotFoundException $e)
                    {
                        throw new ActionNotFoundException();
                    }
                }
                else if($model != null)
                {
                    $model = $model::init();
                    $properties = $model->getModelProperties();
                }
                $status=false;
                $cnts=0;
                foreach($formVars as $key=>$val)
                {
                    $cnts=1;
                    if(!isset($properties[$key]))
                        continue;
                    $displayName=$properties[$key]["Display"] ?? $key;
                    try
                    {
                        if(in_array($key,$bindedAttribute) || count($bindedAttribute)<1)
                        {
							$modelIsNull = false;
                            try
                            {
                                if($model::isForeignKey($key))
                                {
                                    $model->{"set".$key}($model->createForeignObject($key, $val));
                                }
                                else
                                $model->{"set".$key}($val);
                            }
                            catch(\Exception $e)
                            {
                                $model->{"set".$key}((int)$val);
                            }
                        }
                        else
                        {
                        }
                        $validation1 = \CORE\Validator::validateField($displayName, $properties[$key], $val);
                        if(!$validation1->status)
                        {
                            $controller->modelError = $validation1->message;
                            $controller->ModelStateIsValid = false;
                        }
                    }
                    catch(\BadMethodCallException $e)
                    {

                    }
                }
                if($cnts!=1)
                {
                    $controller->modelError = "Form Not Submitted";
                    $controller->ModelStateIsValid = false;
                }
            if($routeId == null)
            $page=$controller->{$action}($model);
            else if($model == null)
            $page=$controller->{$action}($routeId);
            else
            $page=$controller->{$action}($model, $routeId);
        }
        catch(\InvalidArgumentException $e)
        {
            $model=null;
            $page=$controller->{$action}();
        }
        if(get_class($page)=="partial")
        {
            self::pageInit("Home","Error");
            return;
        }
		if($modelIsNull === true && $page->getModel() !== null)
			$model = $page->getModel();
        if($controller->isAjaxFormRequest)
        {
            header("Content-type:application/json");
            $messages = \CORE\Message::getAll();
            \CORE\Message::clearAll();
            if(count($messages) > 0 && $controller->ModelStateIsValid)
                $messages[0]['token'] = \CORE\Hash::createAntiForgeryToken();
            $json = json_decode($page->getHtml(), true);
            echo json_encode(count($messages) > 0 ? $messages[0] : ( is_array($json) ? $json : ['status'=>3, 'message'=> 'No Message is returned']));
        }
        else
        echo self::processHTML($controller, $page->getHtml(),$model,
            $controller->area != '' ?
            ucfirst($controller->area).":".ucfirst($controller->controller).":".ucfirst($controller->action)
            : ucfirst($controller->controller).":".ucfirst($controller->action),
            $antiForgeryToken
        );
        $controller->afterRender($action);
    }
    public static function getFileFromActionString(string $actionString) : string
    {
        if(empty($actionString))
            throw new \ErrorException("Action string is empty");
        $actionString = explode(":", $actionString);
        return root().'views/'.strtolower(implode("/",$actionString)).'.php';
    }
}