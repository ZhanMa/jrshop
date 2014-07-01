<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Tcz_home_partnerWidget extends BaseWidget
{
    var $_name = 'tcz_home_partner';
    var $_ttl  = 86400;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			if (empty($this->options['num']) || intval($this->options['num']) <= 0){
            	$this->options['num'] = 10;
        	}
			
            $partner_mod =& m('partner');
			
			$partner = $partner_mod->find(array(
                'conditions' => "store_id = 0",
                'order' => 'sort_order',
                'limit' => $this->options['num'],
            ));
			
			$data = array(
				'model_name' => $this->options['model_name'],
				'partner'    => $partner
			);
            
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
