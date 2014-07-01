<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_tbd_goods_listWidget extends BaseWidget
{
    var $_name = 'tcz_tbd_goods_list';
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
			$brand_mod = &m('brand');
			$brands=$brand_mod->find(array('conditions'=>'if_show=1 and recommended = 1','order'=>'sort_order asc'));
			$goods_list=array();
			$model_name=array();
			for($i=1;$i<=4;$i++){
				$recom_mod =& m('recommend');
				$goods_list[$i]= $recom_mod->get_recommended_goods($this->options['img_recom_id_'.$i], intval($this->options['amount_'.$i]), true, $this->options['img_cate_id_'.$i]);
				$keywords=explode(' ',$this->options['kw_'.$i]);
				sort($goods_list[$i]);
				foreach($goods_list[$i] as $k=>$v){
					$goods_list[$i][$k]['keyword']=$keywords[$k];
				}
				
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
				'mt_rand'=>mt_rand(),
				'ad_image_url'  => $this->options['ad_image_url'],
            	'ad_link_url'   => $this->options['ad_link_url'],
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
        $image = $this->_upload_image();
        if ($image)
        {
            $input['ad_image_url'] = $image;
        }

        return $input;
    }
	function _upload_image()
    {
        import('uploader.lib');
        $file = $_FILES['ad_image_file'];
        if ($file['error'] == UPLOAD_ERR_OK)
        {
            $uploader = new Uploader();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->addFile($file);
            $uploader->root_dir(ROOT_PATH);
            return $uploader->save('data/files/mall/template', $uploader->random_filename());
        }

        return '';
    }
	

    
}
?>
