<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_search_historyWidget extends BaseWidget
{
    var $_name = 'tcz_search_history';
    var $_ttl  = 86400;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			import('init.lib');
			$init = new init_widget();
			$data=$init->_get_goods_history(9);
			
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
