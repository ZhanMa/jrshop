<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class tcz_tbd_spWidget extends BaseWidget
{
    var $_name = 'tcz_tbd_sp';
    var $_ttl  = 86400;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data ==false)
        {
			import('init.lib');
			$init = new init_widget();
			$data=$init->tejia($this->options['title'],$this->options['link']);
            
			$cache_server->set($key, $data, $this->_ttl);
        }
		//print_r($data);
        return $data;
    }

    function parse_config($input)
    {
       return $input;
    }
    
}
?>
