<?php
namespace CONTROLLERS\MAIL;
use CORE\MVC\Controller;
use CORE\MVC\IController;
use \CORE\MVC\View;
class MailController extends Controller implements IController
{
	public function home():void
	{
	}
	public function signup(array $param) : View
	{
        return $this->view();
	}
	public function forgetPass() : View
	{
        return $this->view();
	}
}
?>