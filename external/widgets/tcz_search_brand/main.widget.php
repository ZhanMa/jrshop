<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_search_brandWidget extends BaseWidget
{
    var $_name = 'tcz_search_brand';
    var $_ttl  = 86400;
	
	function _get_data()
	{
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$brand_mod=&m('brand');
			$tag=$this->options['tag'];
			$brand=$brand_mod->find(array('conditions'=>'tag='.'"'.$tag.'"','limit'=>$this->options['amount']));
			$data=array(
				'brand'=>$brand,
			);
			//print_r($data);
			$cache_server->set($key, $data,$this->_ttl);
        }
        return $data;
	}
	function get_config_datasrc()
	{
			
	}
	
	function parse_config($input)
	{
		return $input;
	}
		

    
}
?>
