<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_cart_guessWidget extends BaseWidget
{
    var $_name = 'tcz_cart_guess';
    var $_ttl  = 86400;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
           	if (empty($this->options['amount']) || intval($this->options['amount']) <=0)
			{
				$this->options['amount'] = 5;
			}
			import('init.lib');
			$init = new init_widget();
			$data=$init->cart_guess($this->options['img_recom_id'],$this->options['amount'],$this->options['img_cate_id'],$this->options['model_name']);;
        }
		//print_r($data);
        return $data;
    }
	
	function get_config_datasrc()
    {
         // 取得推荐类型
        $this->assign('recommends', $this->_get_recommends());

        // 取得一级商品分类
        $this->assign('gcategories', $this->_get_gcategory_options(1));
    }

    function parse_config($input)
    {
       return $input;
    }
	

    
}
?>
