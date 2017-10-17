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