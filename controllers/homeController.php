<?php
namespace CONTROLLERS;
use \CORE\MVC\View;
use \CORE\MVC\PartialView;
use CORE\MVC\Controller;
use CORE\MVC\IController;
class HomeController extends Controller implements IController
{
	public function index() : View
	{
        return $this->view();
	}
	public function error() : View
	{
        return $this->view();
	}
	public function termsConditions() : View
	{
        return $this->view();
	}
}
