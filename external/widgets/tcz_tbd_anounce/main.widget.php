<?php

/**
 * 公告栏挂件
 *
 * @return  array
 */
class Tcz_tbd_anounceWidget extends BaseWidget
{
    var $_name = 'tcz_tbd_anounce';
    var $_ttl  = 86400;

    function _get_data()
    {
        $cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$article_a=$this->get_article($this->options['a_cate_id_a'],$this->options['ar_cate']);
			$data = array(
			   	'article_a'      => $article_a,
			   	'module_name'       => $this->options['module_name_a'],
			   	'ad_image_url'  => $this->options['ad_image_url'],
            	'ad_link_url'   => $this->options['ad_link_url'],
			);
            $cache_server->set($key, $data, $this->_ttl);
        }
		//print_r($data['article_a']);
        return $data;
    }
	/*home article*/
	function get_article($cate_id,$ar_cate){
        $article_mod =& m('article');
		$ar_cate=explode(' ',$ar_cate);
		$article_a = $article_mod->find(array('conditions'=>'cate_id='.intval($cate_id).' AND if_show = 1','limit' =>5,'order'=>'sort_order desc'));
		$count=count($article_a);
		if($count>0){
			$i=0;
			foreach($article_a as $k=>$v){
				$article_a[$k]['ar_cate']=$ar_cate[$i];
				$i++;
			}
		}
		return $article_a;
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
