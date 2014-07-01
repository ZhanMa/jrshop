<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Tcz_rankWidget extends BaseWidget
{
    var $_name = 'tcz_rank';
	var $_ttl  = 1800;

    function _get_data()
    {
		// 不能设置缓存，如设置的话，会影响频道页的数据
		$con = $_GET['cate_id'] ? intval($_GET['cate_id']) : 0;
		$num = $this->options['num'] ? $this->options['num'] : 15;
		import('init.lib');
		$init = new init_widget();
		$data=$init->rank($con,$num);
		$goods_mod=&m('goods');
		$static_mod=&m('goodsstatistics');
		$data = $data ? $data : $goods_mod->getAll("SELECT * FROM  {$goods_mod->table} g  JOIN {$static_mod->table} s ON g.goods_id=s.goods_id ORDER BY s.sales DESC LIMIT ".$num);	

        return $data;
    }	
	function parse_config($input)
    {
        return $input;
    }
}

?>
