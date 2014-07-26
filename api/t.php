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
	//$reqdata['onlyroot'] = 1;
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
	$reqdata['id'] = 36;
}elseif($tname=='storegoods'){
	$_REQUEST['app'] = 'store';
	$_REQUEST['act'] = 'goods';
	$reqdata['id'] = 47;
	$reqdata['cat_id'] = 87;
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
	$reqdata['token'] = '327d5b17ff7866b60bda52588363dd9b';
}elseif($tname=='search'){
	$_REQUEST['app'] = 'goods';
	$_REQUEST['act'] = 'search';
	$reqdata['word'] = '粥';
}elseif($tname=='changepwd'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'changepwd';
	$reqdata['oldpwd'] = 15010753496;
	$reqdata['newpwd'] = '111111';
	$reqdata['token'] = 'f2d14f5d4823295577320a10024b5c54';
}elseif($tname=='orderlist'){
	$_REQUEST['app'] = 'order';
	$_REQUEST['act'] = 'orders';
	$reqdata['add_time_from'] = '2014-07-01 01:30:20';
	//$reqdata['add_time_to'] = '2014-07-02 01:30:20';
	$reqdata['token'] = '2a8730569d62219527f6bc5dda132657';
	
}elseif($tname=='orderview'){
	$_REQUEST['app'] = 'order';
	$_REQUEST['act'] = 'view';
	$reqdata['order_id'] = 144;
	$reqdata['token'] = '8a0ed89a9f30220bbd831ac83deba219';
}elseif($tname =='glist'){
	$_REQUEST['app'] = 'groupbuy';
	$_REQUEST['act'] = 'glist';
	$reqdata['token'] = 'd4a9526c1a799170e4f73f0e16d53816';
}elseif($tname=='sendcode'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'sendcode';
	$reqdata['mobile'] = '15010753496';
	$reqdata['type'] = 'resetpwd';
	$reqdata['token'] = '2560fb83702bf8c6119cd6a2dd435ac0';
}elseif($tname=='register'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'register';
	$reqdata['mobile'] = '15010753496';
	$reqdata['mobilecode'] = '766895';
	$reqdata['username'] = 'ppwww';
	$reqdata['password'] = '111111';
	$reqdata['email'] = '333@qq.com';
}elseif($tname=='resetpwd'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'resetpwd';
	$reqdata['mobile'] = '15010753496';
	$reqdata['mobilecode'] = '593363';
}elseif($tname=='bindphone'){
	$_REQUEST['app'] = 'user';
	$_REQUEST['act'] = 'bindphone';
	$reqdata['token'] = '2560fb83702bf8c6119cd6a2dd435ac0';
	$reqdata['mobile'] = '15010753496';
	$reqdata['mobilecode'] = '535556'; 
}elseif($tname=='dropfavorite'){
	$_REQUEST['app'] = 'store';
	$_REQUEST['act'] = 'dropfavorite';
	$reqdata['id'] = 47;
	$reqdata['type'] = 'store';
	$reqdata['token'] = '327d5b17ff7866b60bda52588363dd9b';
}elseif($tname=='scate'){
	$_REQUEST['app'] = 'store';
	$_REQUEST['act'] = 'scate';
	$reqdata['id'] = 47;
	
}elseif($tname=='brand'){
	$_REQUEST['app'] = 'store';
	$_REQUEST['act'] = 'brand';
}



$reqdata = json_encode($reqdata);
$_REQUEST['datas'] = $reqdata;
require TEST_ROOT.'/m.php';