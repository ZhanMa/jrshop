<?php
class MobileApp extends ApiApp{
	var $user_mod;
	
	function __construct()
	{
		$this->UcApp();
	}
	

	function UcApp()
	{
		parent::__construct();
		$this->appdir   = ROOT_PATH . '/';
		$this->user_mod =& m('member');
	}
	
	function index(){
		echo 'index';
	}
	
	function getslide(){
		header('Content-type: application/json');
		$callback = $_REQUEST ['callback'];
		$mobileslide_mod =& m('mobileslide');
		$slides = $mobileslide_mod->find(array(
				'order' => "sort_order ASC"
		));
		$i = 0;
		foreach ($slides as $key=>$val){
			$str .='<li><img src="'.SITE_URL.'/'.$val['image'].'"/></li>';
			$i++;
		}
		$arr['text'] = $str;
		$arr['count'] = $i;
		$json = json_encode($arr);
		echo $callback . "(" . $json . ")";	
	}
	
	function getnewgoods(){
		header('Content-type: application/json');
		$callback = $_REQUEST ['callback'];
		$goods_mod =& m('goods');
		$goods = $goods_mod->find(array(
				'limit' => "20",
				'order' => "add_time DESC"
		));
		$i = 0;
		foreach ($goods as $key=>$val){
			$str .='<li><a goodsid="'.$val['goods_id'].'"><img src="'.SITE_URL.'/'.$val['default_image'].'"/></a><br/>'.$val['price'].'</li>';
			$i++;
		}
		$arr['text'] = $str;
		$arr['count'] = $i;
		$json = json_encode($arr);
		echo $callback . "(" . $json . ")";
	}
	
	function getnewstore(){
		header('Content-type: application/json');
		$callback = $_REQUEST ['callback'];
		$store_mod =& m('store');
		$goods = $store_mod->find(array(
				'limit' => "20",
				'order' => "add_time DESC"
		));
		$i = 0;
		foreach ($goods as $key=>$val){
			$str .='<li><img src="'.SITE_URL.'/'.$val['store_logo'].'"/></li>';
			$i++;
		}
		$arr['text'] = $str;
		$arr['count'] = $i;
		$json = json_encode($arr);
		echo $callback . "(" . $json . ")";
	}
	
	function getgoodsinfo(){
		$goods_id = $_GET ['goods_id'];
		if(empty($goods_id)){
			exit();
		}
		$goods_mod =& m('goods');
		$goods = $goods_mod->find(array(
				'limit' => "20",
				'order' => "add_time DESC"
		));
	}
}