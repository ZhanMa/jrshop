<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Tcz_home_re_store_2Widget extends BaseWidget
{
    var $_name = 'tcz_home_re_store_2';
	var $_ttl  = 1800;
    var $_num  = 35;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$amount_1 = (!empty($this->options['amount_1']) && intval($this->options['amount_1']) >0) ? intval($this->options['amount_1']) : 8;
			$amount_2 = (!empty($this->options['amount_2']) && intval($this->options['amount_2']) >0) ? intval($this->options['amount_2']) : 24;
			
			$store_mod=&m('store');
			$storex=$store_mod->find(array('fields'=>'store_name,store_id','order'=>'add_time desc','limit'=>$amount_1));
			$storey=$store_mod->find(array('conditions'=>'recommended=1','order'=>'sort_order desc,add_time asc','limit'=>$amount_2));
			foreach($storey as $k=>$v){
				empty($v['store_logo'])&&$storey[$k]['store_logo']=Conf::get('default_store_logo');	
			}
			$data = array(
				'store'=>$storex,
				'storey'=>$storey,
			);
			//print_r($data['storey']);
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
