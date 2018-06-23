<?php
namespace CORE;
class Image
{
	public static function resize($file, $w, $h,$output,$quality=20,$crop=false)
	{
		list($width, $height) = getimagesize($file);
		$r = $width / $height;
		if ($crop) {
			if ($width > $height) {
				$width = ceil($width-($width*abs($r-$w/$h)));
			} else {
				$height = ceil($height-($height*abs($r-$w/$h)));
			}
			$newwidth = $w;
			$newheight = $h;
		} else {
			if ($w/$h > $r) {
				$newwidth = $h*$r;
				$newheight = $h;
			} else {
				$newheight = $w/$r;
				$newwidth = $w;
			}
		}
		$src='';
		$ext=explode(".",$file);
		$dst = imagecreatetruecolor($newwidth, $newheight);
		if($ext[count($ext)-1]=='png')
		{

			imagealphablending( $dst, false );
			imagesavealpha( $dst, true );
			$src = imagecreatefrompng($file);
		}
		else if($ext[count($ext)-1]=='gif')
		$src = imagecreatefromgif($file);
		else if($ext[count($ext)-1]=='bmp')
		$src = imagecreatefromwbmp($file);
		else
		$src = imagecreatefromjpeg($file);
//		$dst = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		$outputs=explode(".",$output);
		$ext=strtolower($outputs[count($outputs)-1]);
		if($ext=="png")
		{
			imagepng($dst,$output,round($quality/10));
		}
		else
		imagejpeg($dst,$output,$quality);	
//		return $dst;
	}
	public static function isImage($target_file)
	{
		if(count(explode("exe",$target_file))>1)
		{
			return false;
		}
		else
		{
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			if($imageFileType != "jpg" && $imageFileType != "png" 
			&& $imageFileType != "jpeg" && $imageFileType != "gif" )
			return false;
			else
			return true;
		}
	}

}