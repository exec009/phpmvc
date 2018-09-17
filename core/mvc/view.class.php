<?php
namespace CORE\MVC;
class View
{
	private $content,$model;
	public $isJSON;
	public static function init(string $str,IModel $model = null, bool $isJSON = false):self
	{
		$view = new self();
		$view->content = $str;
		$view->isJSON = $isJSON;
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
	public function getContent() : string
	{
		return $this->content;
	}
}
?>