<?php
/**
 * ImageUploader - functions to work with uploading an image
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */
class Zend_Controller_Action_Helper_ImageManager extends Zend_Controller_Action_Helper_Abstract
{
	//RESIZE IMAGE UPLOAD
	function resizeImage($filename,$max_width,$max_height='',$newfilename="",$withSampling=true)
	{
	    if($newfilename=="")
	        $newfilename=$filename;
	    // Get new sizes
	    list($width, $height) = getimagesize($filename);

	    //-- dont resize if the width of the image is smaller or equal than the new size.
	    if($width<=$max_width)
	        $max_width=$width;

	    $percent = $max_width/$width;

	    $newwidth = $width * $percent;
	    if($max_height=='') {
	        $newheight = $height * $percent;
	    } else
	        $newheight = $max_height;

	    // Load
	    $thumb = imagecreatetruecolor($newwidth, $newheight);
	    $ext = strtolower($this->getExtension($filename));

	    if($ext=='jpg' || $ext=='jpeg')
	        $source = imagecreatefromjpeg($filename);
	    if($ext=='gif')
	        $source = imagecreatefromgif($filename);
	    if($ext=='png')
	        $source = imagecreatefrompng($filename);

	    // Resize
	    if($withSampling)
	        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	    else   
	        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	    // Output
	    if($ext=='jpg' || $ext=='jpeg')
	        return imagejpeg($thumb,$newfilename);
	    if($ext=='gif')
	        return imagegif($thumb,$newfilename);
	    if($ext=='png')
	        return imagepng($thumb,$newfilename);

	}
	
	function getExtension($file)
	{
	    if (preg_match("/.+[\.](.*)$/", $file, $matches))
	    {
		if (strlen($matches[1]) > 0)
		  return $matches[1];
	    }
	    return ''; // no ext
	}
    
}
