<?php
error_reporting(E_ERROR);
$_REQUEST['ajax'] = 1;
/* 应用根目录 */
define('APP_ROOT', dirname(__FILE__));
define('ROOT_PATH', dirname(APP_ROOT));
include(ROOT_PATH . '/eccore/ecmall.php');

/* 定义配置信息 */
ecm_define(ROOT_PATH . '/data/config.inc.php');

/* 启动ECMall */
ECMall::startup(array(
	'default_app'   =>  'index',
	'default_act'   =>  'index',
	'app_root'      =>  APP_ROOT.'/mobile',
	'external_libs' =>  array(
	ROOT_PATH . '/includes/global.lib.php',
	ROOT_PATH . '/includes/libraries/time.lib.php',
	ROOT_PATH . '/includes/ecapp.base.php',
	ROOT_PATH . '/includes/plugin.base.php',
	ROOT_PATH . '/app/frontend.base.php',
	APP_ROOT  . '/mobile/mobile.base.php',
	),
));
