<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Jrj_re_storeWidget extends BaseWidget
{
    var $_name = 'jrj_re_store';
	var $_ttl  = 1800;
    var $_num  = 35;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();

        $data = $cache_server->get($key);
        //$data = false;
        if($data === false)
        {
			$data = array(
        			'model_name'=>$this->options['model_name'],
        			'model_id'        =>mt_rand(),
        	);
			//获取店铺分类
			$scategory_mod = & m('scategory');
			$scategory = $scategory_mod->find(array(
				"conditions" => 'parent_id = 0',
                'order' => 'sort_order, cate_id',
            ));
			$store_mod = & m('store');
			//获取各分类下店铺
			foreach ($scategory as $key=>$scate){
				$store_list[] = $store_mod->find(array(
					"conditions" => 'state = 1 AND cate_id ='.$scate['cate_id'],
					'order' => 'sort_order',
					'join'  => 'has_scategory'
				));
			}
			array_unshift($scategory,array('cate_id'=>0,'cate_name'=>'热门商家'));
			//插入推荐店铺
			$re_store=$store_mod->find(array('conditions'=>'recommended=1','order'=>'sort_order desc,add_time asc'));
			array_unshift($store_list, $re_store);
			$data['scategory'] = $scategory;
			$data['store_list'] = $store_list;
        	$cache_server->set($key, $data, $this->_ttl);
        }
        return $data;
    }

    function parse_config($input)
    {
        return $input;
    }
}

?>
