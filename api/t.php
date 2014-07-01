<?php
error_reporting(E_ERROR);
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
}elseif($tname=='adv'){
	$_REQUEST['app'] = 'index';
	$_REQUEST['act'] = 'adv';
}elseif($tname=='newgoods'){
	$_REQUEST['app'] = 'index';
	$_REQUEST['act'] = 'newgoods';
}elseif($tname=='logout'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'logout';
	$reqdata['token'] = '9f6216bc5a380bacf70de98962becec3';
}



$reqdata = json_encode($reqdata);
$_REQUEST['datas'] = $reqdata;

require TEST_ROOT.'/m.php';