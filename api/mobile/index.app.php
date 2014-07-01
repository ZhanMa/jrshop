<?php
class  IndexApp extends MobileApp{
	function index(){
		$this->show_warning('gogogo');
	}
	
	function adv(){
		$slide = m('mobileslide');
		
		$list = $slide->find(array('order' => "sort_order ASC"));
		$ads = array();
		foreach ($list as $k=>$v){
			$v['image'] = SITE_URL.'/'.$v['image'];
			$ads[]= $v;
		}
		$this->success(array('advs'=>$ads));
	}
	
	function newgoods(){
		$this->reqdata->num = intval($this->reqdata->num);
		$num = $this->reqdata->num?$this->reqdata->num:15;
		$goods_mod =& m('goods');
		$goods = $goods_mod->find(array(
				'limit' => $num,
				'order' => "add_time DESC",
				'fields'=>"goods_id,goods_name,default_image,price"
		));
		$ret = array();
		foreach ($goods as $v){
			$v['default_image'] = SITE_URL.'/'.$v['default_image'];
			$ret[] = $v;
		}
		$this->success(array('goods'=>$ret));
	}
}