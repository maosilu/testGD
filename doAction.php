<?php
header("content-type:text/html;charset=utf-8");
session_start();
var_dump("<pre>", $_POST);
var_dump($_SESSION);