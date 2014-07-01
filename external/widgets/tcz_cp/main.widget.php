<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_cpWidget extends BaseWidget
{
    var $_name = 'tcz_cp';
    var $_ttl  = 86400;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data ==false)
        {
			$data=array(
				'copyright'    =>$this->options['copyright'],
			);
			
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
