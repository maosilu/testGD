<?php
/**
 这个函数封装用来生成图片的缩略图
 指定缩放比例
 最大宽度和高度，等比缩放
 可以对缩略图文件添加前缀
 选择是否删除缩略图源文件
*/
/**
返回图片信息
@param [string] $filename 文件名
@return array $fileInfo 文件信息，包含图片宽、高、创建和输出的字符以及扩展名
*/ 
function getImageInfo($filename){
	if(@!$info = getimagesize($filename)){
		die('文件不是真实图片');
	}
	// var_dump("<pre>",$info);die;
	$fileInfo['width'] = $info[0];
	$fileInfo['height'] = $info[1];
	$mime = image_type_to_mime_type($info[2]);
	$fileInfo['createFun'] = str_replace('/', 'createfrom', $mime);
	$fileInfo['outFun'] = str_replace('/', null, $mime);
	$fileInfo['ext'] = strtolower(image_type_to_extension($info[2]));
	// var_dump("<pre>", $fileInfo);die;
	return $fileInfo;
}

/**
形成缩略图的函数
@param   string   $filename     源文件路径
@param   float    $percent      图片缩放比例
@param   int      $dst_w        缩略图的最大宽度
@param   int      $dst_h        缩略图的最大高度
@param   string   $dest         缩略图的存放目录
@param   string   $pre          缩略图命名前缀
@param   bool     $delSource    是否删除源文件
@return  string   $destination  缩略图的存储路径
*/
function thumb($filename, $percent=0.5, $dst_w=null, $dst_h=null, $dest='thumb', $pre = 'thumbs_', $delSource = false){
	$fileInfo = getImageInfo($filename);
	$src_w = $fileInfo['width'];
	$src_h = $fileInfo['height'];
	//如果指定最大高度和宽度，按照等比例缩放进行处理
	if(is_numeric($dst_w) && is_numeric($dst_h)){
		$ratio = $src_w / $src_h;
		if($dst_w/$dst_h > $ratio){
			$dst_w = $dst_h * $ratio;
		}else{
			$dst_h = $dst_w / $ratio;
		}
	}else{
		//没指定按照默认的缩放比例处理
		$dst_w = ceil($src_w * $percent);
		$dst_h = ceil($src_h * $percent);
	}
	$dst_image = imagecreatetruecolor($dst_w, $dst_h);
	$src_image = $fileInfo['createFun']($filename);
	imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
	//检测目标目录是否存在，不存在则创建
	if($dest & !file_exists($dest)){
		mkdir($dest, 0777, true);
	}
	$randNum = mt_rand(100000, 999999);
	$dstName = "{$pre}{$randNum}".$fileInfo['ext'];
	$destination = $dest ? $dest.'/'.$dstName : $dstName;
	$fileInfo['outFun']($dst_image, $destination);

	imagedestroy($src_image);
	imagedestroy($dst_image);

	if($delSource){
		@unlink($filename);
	}

	return $destination;
}

/**
添加图片文字水印的函数
@param string   $filename      文件路径
@param   string   $fontfile    字体文件路径
@param   string   $text        需要显示的水印字体
@param   string   $dest        水印图片最终保存的路径 
@param   string   $pre         保存图片的名称前缀
@param   bool     $delSource   是否删除源文件，默认是false，不删除
@param   int      $red         画笔的颜色，跟以下两个组成rgb
@param   int      $green       画笔的颜色
@param   int      $blue        画笔的颜色
@param   int      $alpha       文字水印的透明度
@param   int      $size        文字水印字体大小
@oaram   int      $angle       文字水印字体旋转角度
@param   int      $x           文字水印x轴起始位置
@param   int      $y           文字水印y轴起始位置
@return  string   $destination 文字水印图片的保存路径
*/
function waterText($filename, $fontfile, $text='小美美beautiful', $dest='waterText', $pre='waterText_', $delSource=false, $red=255, $green=0, $blue=0, $alpha=60, $size=30, $angle=0, $x=0, $y=30){
	
	$fileInfo = getImageInfo($filename);

	$image = $fileInfo['createFun']($filename);
	$color = imagecolorallocatealpha($image, $red, $green, $blue, $alpha);
	imagettftext($image, $size, $angle, $x, $y, $color, $fontfile, $text);

	if($dest && !file_exists($dest)){
		mkdir($dest, 0777, true);
	}
	
	$randNum = mt_rand(100000, 999999);
	$dstName = "{$pre}{$randNum}".$fileInfo['ext'];
	$destination = $dest ? $dest.'/'.$dstName : $dstName;
	$fileInfo['outFun']($image, $destination);

	imagedestroy($image);

	//是否删除源文件，默认不删除
	if($delSource){
		@unlink($filename);
	}

	return $destination;
}

/**
实现给图片添加图片水印的效果
@param   string   $srcName      水印图片路径
@param   string   $dstName      目标图片路径
@param   int      $position     水印图片的显示位置
@param   int      $pct          水印图片透明度
@param   string   $dest         目标图片的存储文件夹
$param   string   $pre          目标图片命名前缀
@param   bool     $delSource    是否删除源文件，默认否，不删除
@return  string   $destination  添加图片水印后图片的保存路径
*/
function waterPic($srcName, $dstName, $position=0, $pct=50, $dest='waterPic', $pre='waterPic_', $delSource=false){
	$srcInfo = getImageInfo($srcName);
	$dstInfo = getImageInfo($dstName);
	$dst_im = $dstInfo['createFun']($dstName);
	$src_im = $srcInfo['createFun']($srcName);
	$src_w = $srcInfo['width'];
	$src_h = $srcInfo['height'];

	switch($position){
		case 0:
		$dst_x = 0;
		$dst_y = 0;
		break;
		case 1:
		$dst_x = ($dstInfo['width']-$srcInfo['width'])/2;
		$dst_y = 0;
		break;
		case 2:
		$dst_x = $dstInfo['width']-$srcInfo['width'];
		$dst_y = 0;
		break;
		case 3:
		$dst_x = 0;
		$dst_y = ($dstInfo['height']-$srcInfo['height'])/2;
		break;
		case 4:
		$dst_x = ($dstInfo['width']-$srcInfo['width'])/2;
		$dst_y = ($dstInfo['height']-$srcInfo['height'])/2;
		break;
		case 5:
		$dst_x = $dstInfo['width']-$srcInfo['width'];
		$dst_y = ($dstInfo['height']-$srcInfo['height'])/2;
		break;
		case 6:
		$dst_x = 0;
		$dst_y = $dstInfo['height']-$srcInfo['height'];
		break;
		case 7:
		$dst_x = ($dstInfo['width']-$srcInfo['width'])/2;
		$dst_y = $dstInfo['height']-$srcInfo['height'];
		break;
		case 8:
		$dst_x = $dstInfo['width']-$srcInfo['width'];
		$dst_y = $dstInfo['height']-$srcInfo['height'];
		break;
		default :
		$dst_x = 0;
		$dst_y = 0;
		break;
	}

	
	imagecopymerge($dst_im, $src_im, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);

	if($dest && !file_exists($dest)){
		mkdir($dest, 0777, true);
	}

	$randNum = mt_rand(100000, 999999);
	$destName = "{$pre}{$randNum}".$dstInfo['ext'];
	$destination = $dest ? $dest.'/'.$destName : $destName;
	$dstInfo['outFun']($dst_im, $destination);

	imagedestroy($dst_im);
	imagedestroy($src_im);

	if($delSource){
		@unlink($srcName);
		@unlink($dstName);
	}

	return $destination;
}








