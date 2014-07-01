<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Tcz_home_goodsWidget extends BaseWidget
{
    var $_name = 'tcz_home_goods';
	var $_ttl  = 1800;
    var $_num  = 5;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$recom_mod =& m('recommend');
			$goods_list= $recom_mod->get_recommended_goods($this->options['img_recom_id_1'],intval($this->options['amount_txt']),true,$this->options['img_cate_id_1']);
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
					$goods_listx[$k]['w2']=$whtl[0]+4;
					$goods_listx[$k]['h2']=$whtl[1]+4;
					$goods_listx[$k]['t']=$whtl[2];
					$goods_listx[$k]['l']=$whtl[3];
				}
			}
			$keywords_1=explode(' ',$this->options['kw_1']);
			sort($goods_list);
			foreach($goods_list as $key=>$val)
			{
				$goods_list[$key]['gjz']=$keywords_1[$key];
				$goods_mod=&m('goods');
				$ginfo=$goods_mod->get(array('conditions'=>'goods_id='.$val['goods_id'],'fields'=>'market_price'));
				if($ginfo['market_price']>0){
					$goods_list[$key]['discount']= round(($val['price']/$ginfo['market_price'])*10,1);	
				}else{
					$goods_list[$key]['discount']=0;	
				}
			}
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
			$scategory_mod =& m('scategory');
			$stroe_cate_id=$this->options['store_cate_id']?$this->options['store_cate_id']:0;
        	$cate_ids = $scategory_mod->get_descendant($stroe_cate_id);
        	
			$condition_id=implode(',',$cate_ids);
			
        	$condition_id && $condition_id =' AND cate_id IN(' . $condition_id . ')';
			$store_mod=&m('store');
			if(!$condition_id){
				$condition_id = '';
			}
			$store=$store_mod->find(array('conditions'=>'recommended=1 '.$condition_id,'fields'=>'store_name,store_logo,address,sort_order','join'    => 'belongs_to_user,has_scategory','order'=>'sort_order'));
			foreach($store as $ke=>$va)
			{
				$member_mod=&m('member');
				$member=$member_mod->get(array('conditions'=>'user_id='.$va['store_id'],'fields'=>'portrait,user_name'));
				$store[$ke]['user_name']=$member['user_name'];
				empty($va['store_logo'])&&$store[$ke]['store_logo']=Conf::get('default_store_logo');	
			}
			
			$data = array(
				'goods_list'=>$goods_list,
				'goods_listx'=>$goods_listx,
				'images'    =>$images,
				'model_name'=>$this->options['model_name'],
				'keywords'  =>$keywords,
				'store'     =>$store,
				'model_id'   =>mt_rand()
			);
			//print_r($this->get_scategory_list());
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
        $this->assign('gcategories', $this->_get_gcategory_options(1));
		$this->assign('scategories',$this->get_scategory_list());
    }

}

?>
