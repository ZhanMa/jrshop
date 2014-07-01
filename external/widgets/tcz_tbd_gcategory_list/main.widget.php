<?php

/**
 * 商品分类挂件
 *
 * @return  array   $category_list
 */
class Tcz_tbd_gcategory_listWidget extends BaseWidget
{
    var $_name = 'tcz_tbd_gcategory_list';
    var $_ttl  = 86400;


    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$this->options['amount'] = intval($this->options['amount']);
			
			import('init.lib');
			$init = new init_widget();
			$data=$init->gcategory($this->options['amount']);
			
			$position[9] = '-148';
			foreach($data['gcategory'] as $key=>$val){
				$data['gcategory'][$key]['top'] = isset($position[$key]) ? $position[$key] : -$key*37;
			}
			$cache_server->set($key, $data, $this->_ttl);
        }
        return $data;
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
