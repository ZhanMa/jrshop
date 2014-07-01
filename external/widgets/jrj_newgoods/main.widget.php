<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Jrj_newgoodsWidget extends BaseWidget
{
    var $_name = 'jrj_newgoods';
	var $_ttl  = 1800;
    var $_num  = 10;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
        	$article_a=$this->get_article($this->options['a_cate_id_a']);
        	$data = array(
        			'model_name'=>$this->options['model_name'],
        			'model_id'        =>mt_rand(),
        			'article_a'      => $article_a,
        			'ad_image_url'	=>$this->options['ad_image_url'],
        			'ad_link_url'	=>$this->options['ad_link_url'],
        			'tabs'	=>$this->options['tabs']
        	);
        	if(is_array($this->options['tabs'])){
	        	foreach ($this->options['tabs'] as $key=>$tab){
	        		$cate_select=$tab['tab_cate_id_1']?$tab['tab_cate_id_1']:1;
	        		
	        		$recom_mod =& m('recommend');
	        		$goods_list= $recom_mod->get_recommended_goods($tab['tab_recom_id_1'],$this->_num, true,$cate_select);
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
	        		$data['goods_list'][] = $goods_list;
	        	}
        	}
        	$cache_server->set($key, $data,$this->_ttl);
        }
        return $data;
    }

    function parse_config($input)
    {
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
        $image = $this->_upload_image();
        if ($image)
        {
        	$input['ad_image_url'] = $image;
        }
        //$input=array_merge($input,$result);
        $input = $input + $result;
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
        
        $a_mod = &m('acategory');
        $this->assign('acategories', $a_mod->find());
    }

    function get_article($cate_id){
    	$article_mod =& m('article');
    	$article_a = $article_mod->find(array('conditions'=>'cate_id='.intval($cate_id).' AND if_show = 1','limit' =>7,'order'=>'sort_order desc'));
    	return $article_a;
    }
}

?>
