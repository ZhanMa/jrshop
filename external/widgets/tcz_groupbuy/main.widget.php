<?php

/**
 * 推荐团购挂件
 *
 * @return  array
 */
class Tcz_groupbuyWidget extends BaseWidget
{
    var $_name = 'tcz_groupbuy';
    var $_ttl  = 1800;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
		
        if($data === false)
        {
			if (!empty($this->options['amount']) && intval($this->options['amount']) <=0){$this->options['amount'] = 1;}
            
			import('init.lib');
			$init = new init_widget();
			$groupbuy_list=$init->groupbuy($this->options['amount']);
			$data = array(
			   'model_name'    => $this->options['model_name'],
			   'groupbuy_list' => $groupbuy_list
			);			
            
			$cache_server->set($key, $data, $this->_ttl);
        }
        return $data;
    }
}

?>
