<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Jrj_complexWidget extends BaseWidget
{
    var $_name = 'jrj_complex';
	var $_ttl  = 1800;
    var $_num  = 5;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        //$data = false;
        if($data === false)
        {
        	$data = array(
        			'model_name'=>$this->options['model_name'],
        			'model_id'        =>mt_rand(),
        			'leftimg'	=> $this->options['leftimg'],
        			'centerimg'	=> $this->options['centerimg'],
        			'tabs'	=>$this->options['tabs'],
        	);
        	
        	if(is_array($this->options['tabs'])){
        		foreach ($this->options['tabs'] as $key=>$tab){
        			$cate_select=$tab['tab_cate_id_1']?$tab['tab_cate_id_1']:1;
        			 
        			$recom_mod =& m('recommend');
        			$count = ($key==0)?6:8;
        			$goods_list= $recom_mod->get_recommended_goods($tab['tab_recom_id_1'],$count, true,$cate_select);
//         			var_dump($goods_list);
//         			foreach($goods_list as $k=>$v)
//         			{
//         				$goods_mod=&m('goods');
//         				$get_store_id[$k]=$goods_mod->get(array('conditions'=>'goods_id='.$v['goods_id'],'fields'=>'store_id,market_price'));
//         				$store_id[$k]=$get_store_id[$k]['store_id'];
//         				$store_mod=&m('store');
//         				$regionx=$store_mod->get(array('conditions'=>'store_id='.$store_id[$k],'fields'=>'region_id'));
//         				$region_mod =& m('region');
//         				$avvr=$region_mod->get(array('conditions'=>'region_id='.$regionx['region_id'],'fields'=>'region_name'));
//         				$goods_list[$k]['region_name']=$avvr['region_name'];
//         				$goods_list[$k]['region_id']=$avvr['region_id'];
//         				$goods_list[$k]['market_price']=$get_store_id[$k]['market_price'];
//         				if($goods_list[$k]['market_price']!=0){
//         					$goods_list[$k]['discount']=round(($v['price']/$goods_list[$k]['market_price'])*10,1);
//         				}else{
//         					$goods_list[$k]['discount']=0;
//         				}
//         			}
        			$data['goods_list'][] = $goods_list;
        		}
        	}
        	
        	$amount_2=$this->options['amount_2']?$this->options['amount_2']:6;
        	$store_mod=&m('store');
        	$store=$store_mod->find(array(
        			'conditions'=>'1=1',
        			'fields' => 'store_id, store_name, store_logo, praise_rate, user_name',
                	'join' => 'belongs_to_user',
        			'limit'=>$amount_2,
        			'order'=>'store_id DESC'));
        	$goods_mod =& m('goods');
        	foreach ($store as $key => $s)
        	{
        		$store[$key]['goods_count'] = $goods_mod->get_count_of_store($s['store_id']);
        		empty($s['store_logo']) && $store[$key]['store_logo'] = Conf::get('default_store_logo');
        	}
        	$data['store'] = $store;

        	$keywords=explode(' ',$this->options['kw']);
        	$data['keywords'] = $keywords;
        	
        	$gcategory_mod = & m('gcategory');
        	$gcategory = $gcategory_mod->find(array(
        		'conditions'=>'1=1 and parent_id = 0 and store_id = 0',
        		'order'=>'sort_order ASC'
        	));
        	$goods_mod=&m('goods');
        	$static_mod=&m('goodsstatistics');
        	$rank = array();
        	
        	foreach ($gcategory as $key=>$value){
        		$rank[] = $goods_mod->getAll("SELECT * FROM  {$goods_mod->table} g  JOIN {$static_mod->table} s ON g.goods_id=s.goods_id WHERE cate_id_1 = {$key} ORDER BY s.sales DESC LIMIT 5");
        	}
        	
        	$data['rank'] = $rank;
        	$data['gcates'] = $gcategory;
        	$cache_server->set($key, $data,$this->_ttl);
        }
        return $data;
    }

    function parse_config($input)
    {
    	$result['leftimg'] = array();
    	$num    = isset($input['ad_leftlink_url']) ? count($input['ad_leftlink_url']) : 0;
    	if ($num > 0)
    	{
    		$images = $this->_upload_image($num,'left');
    		for ($i = 0; $i < $num; $i++)
    		{
    			if (!empty($images[$i]))
				{
    				$input['ad_leftimage_url'][$i] = $images[$i];
				}
    			if (!empty($input['ad_leftimage_url'][$i])){
    				$result['leftimg'][] = array(
    				'ad_leftimage_url' => $input['ad_leftimage_url'][$i],
    						'ad_leftlink_url'  => $input['ad_leftlink_url'][$i]
    				);
    			}
			}
		}
		$result['centerimg'] = array();
		$num    = isset($input['ad_centerlink_url']) ? count($input['ad_centerlink_url']) : 0;
		if ($num > 0)
		{
			$images = $this->_upload_image($num,'center');
			for ($i = 0; $i < $num; $i++)
			{
			if (!empty($images[$i]))
			{
			$input['ad_centerimage_url'][$i] = $images[$i];
			}
			if (!empty($input['ad_centerimage_url'][$i])){
				$result['centerimg'][] = array(
					'ad_centerimage_url' => $input['ad_centerimage_url'][$i],
					'ad_centerlink_url'  => $input['ad_centerlink_url'][$i]
	    				);
				}
			}
		}
		$result['tabs']= array();
		$num    = isset($input['tab_name']) ? count($input['tab_name']) : 0;
		if ($num > 0)
		{
			for($i=0;$i<$num;$i++){
				if(!empty($input['tab_recom_id_1'][$i])){
					$result['tabs'][] = array(
							'tab_name' => $input['tab_name'][$i],
							'tab_recom_id_1' => $input['tab_recom_id_1'][$i],
							'tab_cate_id_1'  => $input['tab_cate_id_1'][$i]
					);
				}
			}
		}
		$result['model_name'] = $input['model_name'];
		$result['amount_2'] = $input['amount_2'];
		$result['kw'] = $input['kw'];
		
		return $result;
    }

	function _upload_image($num,$type)
    {
        import('uploader.lib');

        $images = array();
        for ($i = 0; $i < $num; $i++)
        {
            $file = array();
            foreach ($_FILES['ad_'.$type.'image_file'] as $key => $value)
            {
                $file[$key] = $value[$i];
            }

            if ($file['error'] == UPLOAD_ERR_OK)
            {
                $uploader = new Uploader();
                $uploader->allowed_type(IMAGE_FILE_TYPE);
                $uploader->addFile($file);
                $uploader->root_dir(ROOT_PATH);
                $images[$i] = $uploader->save('data/files/mall/template', $uploader->random_filename());
            }
        }

        return $images;
    }
    
	function get_scategory_list(){
		$scategory_mod=&m('scategory');
		$scategories=$scategory_mod->find(array('conditions'=>'parent_id=0'));	
		$resuilt=array();
		foreach($scategories as $k=>$v){
			$resuilt[$v['cate_id']]=$v['cate_name'];	
		}
		return $resuilt;
	}
	function get_config_datasrc()
    {
         // 取得推荐类型
        $this->assign('recommends', $this->_get_recommends());

        // 取得一级商品分类
        $this->assign('gcategories', $this->_get_gcategory_options(3));
    }

}

?>
