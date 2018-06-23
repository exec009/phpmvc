<?php
namespace CORE;
class FileUploader
{
	private $fileHandler, $path;
	public function __construct($handlerName,$size=0)//0 mb
	{
		$this->fileHandler=$handlerName;
		if($size>0)
		{
			if(!is_array($_FILES[$this->fileHandler]['name']))
			if($this->getSize()>$size)
			throw new Exception("File is Too Big. Please try to upload smaller file. Maximum file size limit is ".$this->getSize()." MB");
			else
			{
				$cnt=$this->getCount();
				for($i=0;$i<$cnt;$i++)
				{
					if($this->getSize($i)>$size)
					throw new Exception($this->getName($i)." File is Too Big. Please try to upload smaller file. Maximum file size limit is ".$this->getSize()." MB");
				}
			}
		}
	}
	public function getCount()
	{
		if(is_array($_FILES[$this->fileHandler]['name']))
		return count($_FILES[$this->fileHandler]['name']);
		else
		return 1;
	}
	public function getName($i=0)
	{
		if(is_array($_FILES[$this->fileHandler]['name']))
		return $_FILES[$this->fileHandler]['name'][$i];
		else
		return $_FILES[$this->fileHandler]['name'];
	}
	public function isImage($i=0)
	{
		if(is_array($_FILES[$this->fileHandler]['name']))
		return Image::isImage($_FILES[$this->fileHandler]['name'][$i]);
		else
		return Image::isImage($_FILES[$this->fileHandler]['name']);
	}
	public function getExtension($i=0)
	{
		if(is_array($_FILES[$this->fileHandler]['name']))
		{
			$img= explode(".",$_FILES[$this->fileHandler]['name'][$i]);
			return $img[count($img)-1];
		}
		else
		{
			$img= explode(".",$_FILES[$this->fileHandler]['name']);
			return $img[count($img)-1];
		}
	}
	public function getSize($i=0)
	{
		if(is_array($_FILES[$this->fileHandler]['size']))
		return ceil($_FILES[$this->fileHandler]['size'][$i]/1000000);
		else
		return ceil($_FILES[$this->fileHandler]['size']/1000000);
	}
    public function exists()
    {
        if(is_array($_FILES[$this->fileHandler]['name']) && count($_FILES[$this->fileHandler]['name']) > 0)
        {
            return true;
        }
        else if(strlen($_FILES[$this->fileHandler]['name']) > 0)
        {
            return true;
        }
        else
            return false;
    }
    public function getPath() : string
    {
        return $this->path;
    }
	public function upload($destination,$i=0)
	{
        $this->path = $destination;
		if(is_array($_FILES[$this->fileHandler]['tmp_name']))
		{
			if(!move_uploaded_file($_FILES[$this->fileHandler]['tmp_name'][$i],root().$destination))
			throw new \Exception("There is an error in uploading file. Please tryagain.");
		}
		else
		{
			if(!move_uploaded_file($_FILES[$this->fileHandler]['tmp_name'],root().$destination))
			throw new \Exception("There is an error in uploading file. Please tryagain.");
		}
	}
}