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
	
	function goodscat(){
		
		 $fid = intval($this->reqdata->fid); 
		 $fid = $fid?$fid:0;
		 $onlyroot = $this->reqdata->onlyroot?true:false;
		 $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
	     $gcategory = $gcategory_mod->get_list($fid, true);
	     
	     import('tree.lib');
	     $tree = new Tree();
	     if($onlyroot){
	     	$tree->setTree($gcategory, 'cate_id', 'parent_id', 'cate_name');
	     	$data = $tree->getArrayList($fid);
	     	$data = array_values($data);
	     	$this->success(array('cats'=>$data));
	     }
         $gcategories = $gcategory;
         foreach ($gcategory as $val)
         {
             $result = $gcategory_mod->get_list($val['cate_id'], true);
             $result = array_slice($result, 0, $this->options['amount']);
             $gcategories = array_merge($gcategories, $result);
         }
         
         $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
         
         $data = $tree->getArrayList($fid);
        
         $this->success(array('cats'=>$data));
	}
	
	
	function getstore(){
		$this->reqdata->num = intval($this->reqdata->num);
		$num = $this->reqdata->num?$this->reqdata->num:15;
		$store_mod =& m('store');
		$stores = $store_mod->find(array(
				'limit' => "20",
				'conditions'=>"store_logo<>''",
				'order' => "add_time DESC",
				'fields'=>'store_id,store_name,store_logo'
		));
		$res = array();
		foreach ($stores as $v){
			$res[] = array('id'=>$v['store_id'],'name'=>$v['store_name'],'logo'=>SITE_URL.'/'.$v['store_logo']);
		}
		$this->success(array('stores'=>$res));
	}
}