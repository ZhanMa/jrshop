<?php

/**
 * 商品分类挂件
 *
 * @return  array   $category_list
 */
class Tcz_search_gcategory_listWidget extends BaseWidget
{
    var $_name = 'tcz_search_gcategory_list';
 	var $_ttl  = 1800;

    function _get_data()
    {
		// 此处不需加缓存，加的话，会影响不同频道页面的分类数据
		$data = array();
		$cate_id_1=$_GET['id']?$_GET['id']:'';
		
		if(!empty($cate_id_1))
		{
			$channel_mod 	= &af('channels');
			$channel = $channel_mod->getOne($cate_id_1);
			
			$cate_id_1 = $channel['cate_id'];
			
			$gcate_mod=&m('gcategory');
			$gcategory_kk=$gcate_mod->get(array(
				'conditions'=>'cate_id='.$cate_id_1,
			));
			
			import('init.lib');
			$init=new search();
			$gcategory=$init->get_all_category_tree($cate_id_1);
				
			$data=array(
				'gcategory'=>$gcategory,
				'add_class'=>$cate_id_1,
				'cate_name'=>$gcategory_kk['cate_name'],
				'cate_id'  =>$gcategory_kk['cate_id']
			);
		}
        return $data;
    }
	function get_parent_id($cate_id){
		$gcate_mod=&m('gcategory');
		$gcategory=$gcate_mod->get(array(
			'conditions'=>'cate_id='.$cate_id,
		));
		if($gcategory['parent_id']==0){

		}else{
			$id=$gcategory['parent_id'];
			$gcategory=$this->get_parent_id($id);		
		}
		return $gcategory;
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


}

?>
