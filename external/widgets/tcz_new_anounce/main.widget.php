<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_new_anounceWidget extends BaseWidget
{
    var $_name = 'tcz_new_anounce';
    var $_ttl  = 86400;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
            import('init.lib');
			$init = new init_widget();
			$article_a=$init->get_article($this->options['a_cate_id_a'],$this->options['ar_cate']);
			$data = array(
			   'article_a'      => $article_a,
			   'module_name_a'       => $this->options['module_name_a'],
			   'module_name_b'       => $this->options['module_name_b'],
			   'ad_image_url'  => $this->options['ad_image_url'],
               'ad_link_url'   => $this->options['ad_link_url'],
			);
            $cache_server->set($key, $data, $this->_ttl);
        }
		//print_r($data);
        return $data;
    }
	
	function get_config_datasrc()
    {
        $a_mod = &m('acategory');
        $this->assign('acategories', $a_mod->find());
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
