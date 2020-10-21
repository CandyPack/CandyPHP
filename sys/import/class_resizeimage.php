<?php
class ResizeImage
{
	private $ext;
	private $image;
	private $newImage;
	private $origWidth;
	private $origHeight;
	private $resizeWidth;
	private $resizeHeight;

	public function __construct($filename){
		if(file_exists($filename))
		{
			$this->setImage($filename);
		} else {
			throw new Exception('Image '.$filename.' can not be found, try another image.');
		}
	}

	private function setImage($filename){
		$size = getimagesize($filename);
		$this->ext = $size['mime'];
		switch($this->ext)
	    {
	    	// Image is a JPG
	        case 'image/jpg':
	        case 'image/jpeg':
	        	// create a jpeg extension
	            $this->image = imagecreatefromjpeg($filename);
	            break;
	        // Image is a GIF
	        case 'image/gif':
	            $this->image = @imagecreatefromgif($filename);
	            break;
	        // Image is a PNG
	        case 'image/png':
	            $this->image = @imagecreatefrompng($filename);
	            break;
	        // Mime type not found
	        default:
	            throw new Exception("File is not an image, please use another file type.", 1);
	    }
	    $this->origWidth = imagesx($this->image);
	    $this->origHeight = imagesy($this->image);
	}

	public function saveImage($savePath, $imageQuality="100", $download = false){
	    switch($this->ext){
	        case 'image/jpg':
	        case 'image/jpeg':
	        	// Check PHP supports this file type
	            if (imagetypes() & IMG_JPG) {
	                imagejpeg($this->newImage, $savePath, $imageQuality);
	            }
	            break;
	        case 'image/gif':
	        	// Check PHP supports this file type
	            if (imagetypes() & IMG_GIF) {
	                imagegif($this->newImage, $savePath);
	            }
	            break;
	        case 'image/png':
	            $invertScaleQuality = 9 - round(($imageQuality/100) * 9);
	            // Check PHP supports this file type
	            if (imagetypes() & IMG_PNG) {
	                imagepng($this->newImage, $savePath, $invertScaleQuality);
	            }
	            break;
	    }
	    if($download){
	    header('Content-Description: File Transfer');
			header("Content-type: application/octet-stream");
			header("Content-disposition: attachment; filename= ".$savePath."");
			readfile($savePath);
	    }
	    imagedestroy($this->newImage);
	}

	public function resizeTo( $width, $height, $resizeOption = 'default' ){
		switch(strtolower($resizeOption)){
			case 'exact':
				$this->resizeWidth = $width;
				$this->resizeHeight = $height;
			break;
			case 'maxwidth':
				$this->resizeWidth  = $width;
				$this->resizeHeight = $this->resizeHeightByWidth($width);
			break;
			case 'maxheight':
				$this->resizeWidth  = $this->resizeWidthByHeight($height);
				$this->resizeHeight = $height;
			break;
			default:
				if($this->origWidth > $width || $this->origHeight > $height)
				{
					if ( $this->origWidth > $this->origHeight ) {
				    	 $this->resizeHeight = $this->resizeHeightByWidth($width);
			  			 $this->resizeWidth  = $width;
					} else if( $this->origWidth < $this->origHeight ) {
						$this->resizeWidth  = $this->resizeWidthByHeight($height);
						$this->resizeHeight = $height;
					}
				} else {
		            $this->resizeWidth = $width;
		            $this->resizeHeight = $height;
		        }
			break;
		}
		$this->newImage = imagecreatetruecolor($this->resizeWidth, $this->resizeHeight);
    	imagecopyresampled($this->newImage, $this->image, 0, 0, 0, 0, $this->resizeWidth, $this->resizeHeight, $this->origWidth, $this->origHeight);
	}

	private function resizeHeightByWidth($width){
		return floor(($this->origHeight/$this->origWidth)*$width);
	}

	private function resizeWidthByHeight($height){
		return floor(($this->origWidth/$this->origHeight)*$height);
	}
}
