<?php

class StorelistApp extends MallbaseApp
{
    function index()
    {
        //导航
        $this->assign('navs', $this->_get_navs());
        $_curlocal = array(
                array(
                'text' => Lang::get('index'),
                'url'  => 'index.php',),
                array(
                'text' => '品牌墙',
                'url'  => '',),
            );
        $this->assign('_curlocal', $_curlocal);
        $recommended_stores = $this->_recommended_stores(5);
        $this->assign('recommended_stores', $recommended_stores);
        $recommended_brands = $this->_recommended_brands(10);
        $this->assign('recommended_brands', $recommended_brands);
        
        $storelist = array();
        $store_mod = & m('store');
        
        $scategories = $this->get_scategory_list();
        $stores = array();
        foreach($scategories as $key=>$val){
        	$store_arr = $store_mod->find(array("conditions" => 'state = 1 AND cate_id = '.$key,'join'  => 'has_scategory','order'=>'sort_order desc,add_time asc'));
        	$storelist[$key]['tag'] = $val;
        	$storelist[$key]['count'] = count($store_arr);
        	$storelist[$key]['list'] = $store_arr;
        }
        
        $this->assign('storelist', $storelist);

        $this->_config_seo('title', '品牌墙');
        $this->display('storelist.index.html');
    }

    function _recommended_brands($num)
    {
        $brand_mod =& m('brand');
        $brands = $brand_mod->find(array(
            'conditions' => 'recommended = 1 AND if_show = 1',
            'order' => 'sort_order',
            'limit' => '0,' . $num));
        return $brands;
    }

    function _recommended_stores($num)
    {
        $store_mod =& m('store');
        $goods_mod =& m('goods');
        $stores = $store_mod->find(array(
            'conditions'    => 'recommended=1 AND state = 1',
            'order'         => 'sort_order',
            'join'          => 'belongs_to_user',
            'limit'         => '0,' . $num,
        ));
        foreach ($stores as $key => $store){
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');
            $stores[$key]['goods_count'] = $goods_mod->get_count_of_store($store['store_id']);
        }
        return $stores;
    }
    
    function get_scategory_list(){
    	$scategory_mod=&m('scategory');
    	$scategories=$scategory_mod->find(array('conditions'=>'parent_id=0'));
    	$resuilt=array();
    	foreach($scategories as $k=>$v){
    		$resuilt[$v['cate_id']]=$v['cate_name'];
    	}
    	return $resuilt;
    }
}

?>
