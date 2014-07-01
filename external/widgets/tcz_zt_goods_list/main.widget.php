<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_zt_goods_listWidget extends BaseWidget
{
    var $_name = 'tcz_zt_goods_list';
    var $_ttl  = 86400;

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
				$goods_image_mod=&m('goodsimage');
				$goods_image=$goods_image_mod->get(array(
					'conditions'=>'goods_id='.$v['goods_id']
				));
				$goods_list[$k]['default_image']=$goods_image['thumbnail'];
				$goods_list[$k]['big_image']=$goods_image['image_url'];
				$pr=explode('.',$v['price']);
				$goods_list[$k]['price']=$v['price'];
			}
			$model_name=$this->options['model_name'];
			$letter_name=$this->options['letter_name'];	
			$more_name=$this->options['more_name'];	
			$more_link=$this->options['more_link'];	
			$keyword=explode(' ',$this->options['kw']);
			
			$data = array(
			    'model_name' => $model_name,
                'goods_list' => $goods_list,
				'keywords'   =>$keyword,
				'letter_name'=>$letter_name,
				'more_name'=>$more_name,
				'more_link'=>$more_link
			);
			
			$cache_server->set($key, $data,$this->_ttl);
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
