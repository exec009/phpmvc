<?php
namespace CORE\MVC;
class PartialView
{
	private $content;
	public static function init(string $str):self
	{
		$partialView=new self();
		$partialView->content=$str;
        return $partialView;
	}
	public function getHtml():string
	{
		return $this->content;
	}
}
?>