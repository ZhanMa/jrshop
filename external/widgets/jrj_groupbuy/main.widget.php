<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Jrj_groupbuyWidget extends BaseWidget
{
    var $_name = 'jrj_groupbuy';
	var $_ttl  = 1800;
    var $_num  = 6;

	function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        $data =false;
        if($data === false)
        {
			if (!empty($this->options['amount']) && intval($this->options['amount']) <=0){$this->options['amount'] = 1;}
            
			import('init.lib');
			$init = new init_widget();
			$groupbuy_list=$init->groupbuy($this->options['amount']);
			
			$groupbuy_mod=&m('groupbuy');
			foreach ($groupbuy_list as $key => $value){
				$sql = "SELECT store_name FROM " . DB_PREFIX ."store as s LEFT JOIN {$groupbuy_mod->table} AS gb ON gb.store_id = s.store_id WHERE gb.group_id=".$value['group_id'];
				$groupbuy_list[$key]['store_name'] = $groupbuy_mod->getOne($sql);
			}
			
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
