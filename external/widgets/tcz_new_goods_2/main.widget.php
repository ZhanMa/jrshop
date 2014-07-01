<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Tcz_new_goods_2Widget extends BaseWidget
{
    var $_name = 'tcz_new_goods_2';
	var $_ttl  = 1800;
    var $_num  = 5;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$cate_select=$this->options['img_cate_id_1']?$this->options['img_cate_id_1']:1;
			$recom_mod =& m('recommend');
			$goods_list= $recom_mod->get_recommended_goods($this->options['img_recom_id_1'],8, true,$cate_select);
			foreach($goods_list as $k=>$v)
			{
				$goods_mod=&m('goods');
				$get_store_id[$k]=$goods_mod->get(array('conditions'=>'goods_id='.$v['goods_id'],'fields'=>'store_id,market_price'));
				$store_id[$k]=$get_store_id[$k]['store_id'];
				$store_mod=&m('store');
				$regionx=$store_mod->get(array('conditions'=>'store_id='.$store_id[$k],'fields'=>'region_id'));	
				$region_mod =& m('region');
				$avvr=$region_mod->get(array('conditions'=>'region_id='.$regionx['region_id'],'fields'=>'region_name'));
				$goods_list[$k]['region_name']=$avvr['region_name'];
				$goods_list[$k]['region_id']=$avvr['region_id'];
				$goods_list[$k]['market_price']=$get_store_id[$k]['market_price'];
				if($goods_list[$k]['market_price']!=0){
					$goods_list[$k]['discount']=round(($v['price']/$goods_list[$k]['market_price'])*10,1);
				}else{
					$goods_list[$k]['discount']=0;
				}
			}
			if($store_id)
			{
				$store_id=array_unique($store_id);
				foreach($store_id as $key=>$val)
				{
					$store_mod=&m('store');
					$region=$store_mod->get(array('conditions'=>'store_id='.$val,'fields'=>'region_id'));	
					$region_mod =& m('region');
					$regions[$key]=$region_mod->get(array('conditions'=>'region_id='.$region['region_id'],'fields'=>'region_name'));
					$regions[$key]['cate_id']=$cate_select;
				}
			}
			$gcategory_mod=&m('gcategory');
			$gcategory=$gcategory_mod->find(array('conditions'=>'parent_id='.$cate_select,'fields'=>'cate_name'));
			$images=$this->options['images'];
			
			$data = array(
				'regions'   =>$regions,
				'gcategory' =>$gcategory,
				'goods_list'=>$goods_list,
				'images'    =>$images,
				'cate_id'   =>$cate_select,
				'model_name'=>$this->options['model_name'],
				'model_id'        =>mt_rand()
			);
			//print_r($data['goods_list']);
        	$cache_server->set($key, $data, $this->_ttl);
        }

        return $data;
    }

    function parse_config($input)
    {
        $result['images']= array();
        $num    = isset($input['ad_link_url']) ? count($input['ad_link_url']) : 0;
        if ($num > 0)
        {
            $images = $this->_upload_image($num);
            for ($i = 0; $i < $num; $i++)
            {
                if (!empty($images[$i]))
                {
                    $input['ad_image_url'][$i] = $images[$i];
                }
    
	
                if (!empty($input['ad_image_url'][$i]))
                {
                    $result['images'][]= array(
                        'ad_image_url' => $input['ad_image_url'][$i],
                        'ad_link_url'  => $input['ad_link_url'][$i],
						'ad_cl_url'  => $input['ad_cl_url'][$i],
						'ad_title_url'  => $input['ad_title_url'][$i]
                    );
                }
            }
        }
		$input=$input+$result;
        return $input;
    }

    function _upload_image($num)
    {
        import('uploader.lib');

        $images= array();
        for ($i = 0; $i < $num; $i++)
        {
            $file = array();
            foreach ($_FILES['ad_image_file'] as $key => $value)
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
	function get_config_datasrc()
    {
         // 取得推荐类型
        $this->assign('recommends', $this->_get_recommends());

        // 取得一级商品分类
        $this->assign('gcategories', $this->_get_gcategory_options(3));
    }

}

?>
