<?php
error_reporting(E_ERROR);
define('TEST_ROOT', dirname(__FILE__));
$tname = $_GET['t'];
$reqdata = array(); 
$reqdata['appid'] = 1;

if($tname=='login'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'login';
	$reqdata['username'] = 'pang20c';
	$reqdata['password'] = '111111';
}elseif($tname=='getinfo'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'getinfo';
	$reqdata['token'] = 'd4a9526c1a799170e4f73f0e16d53816';
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
}elseif($tname=='goodscat'){
	$_REQUEST['app'] = 'index';
	$_REQUEST['act'] = 'goodscat';
	$reqdata['fid'] = 4;
	$reqdata['onlyroot'] = 1;
}elseif($tname=='getstore'){
	$_REQUEST['app'] = 'index';
	$_REQUEST['act'] = 'getstore';
}elseif($tname=='goodslist'){
	$_REQUEST['app'] = 'goods';
	$_REQUEST['act'] = 'goodslist';
	$reqdata['catid'] = 22;
	$reqdata['page'] = 0;
}elseif($tname=='goodsinfo'){
	$_REQUEST['app'] = 'goods';
	$_REQUEST['act'] = 'goodsinfo';
	$reqdata['id'] = 142;
}elseif($tname =='buy'){
	$_REQUEST['app'] = 'goods';
	$_REQUEST['act'] = 'buy';
	$reqdata['id'] = 4;
	$reqdata['token'] = 'd4a9526c1a799170e4f73f0e16d53816';
}elseif($tname =='gbuy'){
	$_REQUEST['app'] = 'groupbuy';
	$_REQUEST['act'] = 'adv';
	$reqdata['token'] = 'd4a9526c1a799170e4f73f0e16d53816';
}elseif($tname =='servertime'){
	$_REQUEST['app'] = 'groupbuy';
	$_REQUEST['act'] = 'servertime';
	$reqdata['token'] = 'f0c372c7c7653bbf00986bec467635eb';
}elseif($tname=='storeinfo'){
	$_REQUEST['app'] = 'store';
	$_REQUEST['act'] = 'info';
	$reqdata['id'] = 34;
}elseif($tname=='storegoods'){
	$_REQUEST['app'] = 'store';
	$_REQUEST['act'] = 'goods';
	$reqdata['id'] = 47;
}elseif($tname=='addfavorite'){
	$_REQUEST['app'] = 'store';
	$_REQUEST['act'] = 'addfavorite';
	$reqdata['id'] = 104;
	$reqdata['type'] = 'goods';
	$reqdata['token'] = 'd6580b6fd88cfb87fdec4f3d5be2afcd';
}elseif($tname=='favorlist'){
	$_REQUEST['app'] = 'store';
	$_REQUEST['act'] = 'favorlist';
	$reqdata['type'] = 'store';
	$reqdata['token'] = 'ef6aee37c3b4ea69c0e188a66ff2ba0d';
}elseif($tname=='search'){
	$_REQUEST['app'] = 'goods';
	$_REQUEST['act'] = 'search';
	$reqdata['word'] = '粥';
}elseif($tname=='changepwd'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'changepwd';
	$reqdata['oldpwd'] = 111111;
	$reqdata['newpwd'] = '111111';
	$reqdata['token'] = 'ef6aee37c3b4ea69c0e188a66ff2ba0d';
}



$reqdata = json_encode($reqdata);
$_REQUEST['datas'] = $reqdata;
require TEST_ROOT.'/m.php';