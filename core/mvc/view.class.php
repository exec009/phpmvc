<?php
namespace CORE\MVC;
class View
{
	private $content,$model;
	public static function init(string $str,IModel $model = null):self
	{
		$view = new self();
		$view->content = $str;
        $view->model = $model;
        return $view;
	}
    public function getModel() : ?IModel
    {
        return $this->model;
    }
	public function getHtml() : string
	{
		return $this->content;
	}
}
?>