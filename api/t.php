<?php
define('TEST_ROOT', dirname(__FILE__));
$tname = $_GET['t'];
$reqdata = array(); 
$reqdata['appid'] = 1;

if($tname=='login'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'login';
	$reqdata['username'] = 'qq123';
	$reqdata['password'] = '123456';
}elseif($tname=='getinfo'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'getinfo';
	$reqdata['token'] = '947580df38b1c075d721ed5d8f325684';
}



$reqdata = json_encode($reqdata);
$_REQUEST['datas'] = $reqdata;

require TEST_ROOT.'/m.php';