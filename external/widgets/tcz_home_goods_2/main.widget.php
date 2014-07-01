<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Tcz_home_goods_2Widget extends BaseWidget
{
    var $_name = 'tcz_home_goods_2';
	var $_ttl  = 1800;
    var $_num  = 20;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$recom_mod =& m('recommend');
			$goods_list= $recom_mod->get_recommended_goods($this->options['img_recom_id_1'],8, true, $this->options['img_cate_id_1']);
			$goods_listx= $recom_mod->get_recommended_goods($this->options['img_recom_id'], intval($this->options['amount']), true, $this->options['img_cate_id']);
			$whtlall=explode(',',$this->options['whtl']);
			sort($goods_listx);
			if($goods_listx)
			{
				foreach($goods_listx as $k=>$v)
				{
					$whtl=explode(' ',$whtlall[$k]);
					$goods_listx[$k]['w']=$whtl[0];
					$goods_listx[$k]['h']=$whtl[1];
					$goods_listx[$k]['h2']=$whtl[1]+4;
					$goods_listx[$k]['w3']=$whtl[0]+4;
					$goods_listx[$k]['h3']=$whtl[1]+4;
					$goods_listx[$k]['t']=$whtl[2];
					$goods_listx[$k]['l']=$whtl[3];
					$path=explode('small_',$v['default_image']);
					$pathx=$path[0].'f_small_'.$path[1];
					if(file_exists($pathx)){
						$goods_listx[$k]['default_image']=$path[0].'f_small_'.$path[1];
					}else{
						$goods_listx[$k]['default_image']=$v['default_image'];
					}
				}
			}
			sort($goods_list);
			$brand_mod = &m('brand');
			
			$conditions = '';
			if($this->options['brand_tag'])
			{
				$conditions .= " and tag='" . $this->options['brand_tag'] ."'";
			}

			$brand=$brand_mod->find(array('conditions'=>'if_show=1 and recommended = 1' . $conditions, 'fields'=>'brand_name,brand_logo,tag','order'=>'sort_order asc'));
			$keywords=explode(' ',$this->options['kw']);
			$images=$this->options['images'];
			for($i=0;$i<20;$i++)
			{
				$cl=explode(' ',$images[$i]['ad_cl_url']);
				$images[$i]['width']=$cl[0];
				$images[$i]['height']=$cl[1];
				$images[$i]['left']=$cl[2];
				$images[$i]['top']=$cl[3];	
			}
			
			$data = array(
				'goods_list'=>$goods_list,
				'goods_listx'=>$goods_listx,
				'images'    =>$images,
				'model_name'=>$this->options['model_name'],
				'keywords'  =>$keywords,
				'brand'     =>$brand,
				'brand_tag' =>$this->options['brand_tag'],
				'model_id'        =>mt_rand()
			);
			
        	$cache_server->set($key, $data,$this->_ttl);
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
						'ad_cl_url'  => $input['ad_cl_url'][$i]
                    );
                }
            }
        }
        var_dump($result);
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
        $this->assign('gcategories', $this->_get_gcategory_options(1));
    }

}

?>
