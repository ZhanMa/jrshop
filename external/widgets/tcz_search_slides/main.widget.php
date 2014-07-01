<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Tcz_search_slidesWidget extends BaseWidget
{
    var $_name = 'tcz_search_slides';
	var $_ttl  = 1800;
    var $_num  = 5;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$gcate_id=$_GET['cate_id'];
			if(!empty($gcate_id)){
				$gcate_mod=&m('gcategory');
				$gcategory=$gcate_mod->get(array(
					'conditions'=>'cate_id='.$gcate_id,
				));
			}
			
			$data = array(
		   		'model_id'=>mt_rand(),
		   		'ads'=> $this->options,
		  	 	'cate_name_1'=>$gcategory['cate_name'],
		   		'cate_id'    =>$gcate_id
			);
        	
			$cache_server->set($key, $data,$this->_ttl);
        }
        return $data;
    }

    function parse_config($input)
    {
        $result = array();
        $num    = isset($input['ad_link_url']) ? count($input['ad_link_url']) : 0;
        if ($num > 0)
        {
            $images = $this->_upload_image($num);
            for ($i = 0; $i < $num ; $i++)
            {
                if (!empty($images[$i]))
                {
                    $input['ad_image_url'][$i] = $images[$i];
                }
    
                if (!empty($input['ad_image_url'][$i]))
                {
                    $result[] = array(
                        'ad_image_url' => $input['ad_image_url'][$i],
                        'ad_link_url'  => $input['ad_link_url'][$i],
                        'ad_title' => $input['ad_title'][$i],
						'ad_cate' => $input['ad_cate'][$i]
                    );
                }
            }
        }

        return $result;
    }

    function _upload_image($num)
    {
        import('uploader.lib');

        $images = array();
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
}

?>
