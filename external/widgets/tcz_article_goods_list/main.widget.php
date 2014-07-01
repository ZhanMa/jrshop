<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_article_goods_listWidget extends BaseWidget
{
    var $_name = 'tcz_article_goods_list';
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
			$brand_mod = &m('brand');
			$brands=$brand_mod->find();
			$goods_list=array();
			$model_name=array();
			for($i=1;$i<=4;$i++){
				$recom_mod =& m('recommend');
				$goods_list[$i]= $recom_mod->get_recommended_goods($this->options['img_recom_id_'.$i], intval($this->options['amount_'.$i]), true, $this->options['img_cate_id_'.$i]);
				
			}
			for($i=1;$i<=3;$i++){
				$model_name[$i]=$this->options['model_name_'.$i];	
			}
			$data = array(
			    'model_name' => $model_name,
                'goods_list' => $goods_list,
				'tname'      =>$this->options['tname'],
				'tlink'      =>$this->options['tlink'],
				'rand'       =>rand(),
				'brands'       => $brands,
				'b_logo'  =>$this->options['b_logo'],
			);
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
