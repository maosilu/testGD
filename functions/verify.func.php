<?php 
/**
默认产生4位的数字验证码
@param int $fontfile 字体文件
@param int $type 1:数字 2:字母 3:数字+字母 4:汉字
@param int $length 验证码的长度
@param string $codeName 存入session的名字
@param int $pixel 干扰元素像素
@param int $line 干扰元素直线
@param int $arc 干扰元素圆弧
@param int $width 画布宽度
@param int $height 画布高度
@return void
*/
function getVerify($fontfile, $type=1, $length=4, $codeName='verifyCode', $pixel=50, $line=0, $arc=0, $width=200, $height=50){
	/**
	封装验证码函数
	添加汉字验证码
	*/
	// $width = 200;
	// $height = 50;
	$image = imagecreatetruecolor($width, $height);
	$white = imagecolorallocate($image, 255, 255, 255);
	imagefilledrectangle($image, 0, 0, $width, $height, $white);
	//设置画笔随机色
	function getRandColor($image){
		return imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
	}
	/*
	验证码的类型type：
	1:数字  2:字母  3:数字+字母  4:汉字
	*/
	// $type = 1;
	// $length = 4;
	switch($type){
		case 1:
		$string = join('', array_rand(range(0, 9), $length));
		break;
		case 2:
		$string = join('', array_rand(array_flip(array_merge(range('a', 'z'), range('A', 'Z'))), $length));
		break;
		case 3:
		$string = join('', array_rand(array_flip(array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'))), $length));
		break;
		case 4:
		$str = '经,营,国,际,化,为,企,业,带,来,走,出,去,和,引,进,来,的,双,重,机,遇,这,六,化,为,我,国,未,来,经,济,转,型,和,打,造,经,济,升,级,版,提,供,了,非,常,好,的,机,遇';
		$string = join('', array_rand(array_flip(explode(',', $str)), $length));
		break;
		default :
		die('非法类型！');
		break;
	}

	//将验证码存入session
	session_start();
	$_SESSION[$codeName] = $string;

	$textWidth = imagefontwidth(28);
	$textHeight = imagefontheight(28);
	// $fontfile = '../fonts/Songti.ttc';
	for($i=0; $i<$length; $i++){
		$size = mt_rand(20, 28);
		$angle = mt_rand(-15, 15);
		$x = ceil($width/$length)*$i+$textWidth;
		$y = mt_rand(ceil($height/2), 45);
		$color = getRandColor($image);
		$text = mb_substr($string, $i, 1, 'utf-8');
		imagettftext($image, $size, $angle, $x, $y, $color, $fontfile, $text);
	}
	//添加干扰元素
	// $pixel = 50;
	// $line = 3;
	// $arc = 2;
	//添加像素
	if($pixel > 0){
		for($i=0; $i<$pixel; $i++){
			imagesetpixel($image, mt_rand(0, $width), mt_rand(0, $height), getRandColor($image));
		}
	}
	//添加直线
	if($line > 0){
		for($i=0; $i<$line; $i++){
			imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), getRandColor($image));
		}
	}
	//添加弧线
	if($arc > 0){
		for($i=0; $i<$arc; $i++){
			imagearc($image, mt_rand(0, $width/2), mt_rand(0, $height/2), mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, 360), mt_rand(0, 360), getRandColor($image));
		}
	}


	header('Content-type:image/png');
	imagepng($image);
	imagedestroy($image);
}
// getVerify();
// getVerify(3, 5, 100, 3);