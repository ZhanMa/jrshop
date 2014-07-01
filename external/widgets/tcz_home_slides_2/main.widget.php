<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Tcz_home_slides_2Widget extends BaseWidget
{
    var $_name = 'tcz_home_slides_2';
	var $_ttl  = 1800;

	function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
           	if (empty($this->options['amount']) || intval($this->options['amount']) <=0){
				$this->options['amount'] = 5;
			}
			
			$recom_mod =& m('recommend');
			$goods_list= $recom_mod->get_recommended_goods($this->options['img_recom_id'], intval($this->options['amount']), true, $this->options['img_cate_id']);
			foreach($goods_list as $k=>$v){
				if($v['market_price']>0){
				$goods_list[$k]['discount']=number_format(($v['price']/$v['market_price'])*10,1);
				}else{
					$goods_list[$k]['discount']=0;
				}
			}

			$data = array(
			    'model_name' => $this->options['model_name'],
                'goods_list' => $goods_list,
				'model_id'   =>mt_rand()
			);
        	
			$cache_server->set($key, $data, $this->_ttl);
        }

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
