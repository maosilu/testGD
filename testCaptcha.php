<?php
require_once 'extend/Captcha.class.php';
$config = array(
	'fontfile' => 'fonts/Arial.ttf',
	'snow' => 20,
);
$captcha = new Captcha($config);
session_start();
$_SESSION['varifyName'] = $captcha->getCaptcha();