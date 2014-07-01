<?php

/**
 * 商品分类挂件
 *
 * @return  array   $category_list
 */
class Tcz_new_gcategory_listWidget extends BaseWidget
{
    var $_name = 'tcz_new_gcategory_list';
	var $_ttl  = 1800;

    function _get_data()
    {
		$cache_server =& cache_server();
        $key = $this->_get_cache_id();
        $data = $cache_server->get($key);
        if($data === false)
        {
			$cate_id_1=$this->options['img_cate_id_2']?$this->options['img_cate_id_2']:0;
			$gcate_mod=&m('gcategory');
			$gcategory_kk=$gcate_mod->get(array(
				'conditions'=>'cate_id='.$cate_id_1,
			));
			$recom_mod =& m('recommend');
			$goods_list= $recom_mod->get_recommended_goods($this->options['img_recom_id_1'], intval($this->options['amount_1']), true, $this->options['img_cate_id_1']);
			import('init.lib');
			$init=new search();
			$gcategory=$init->get_all_category_tree($cate_id_1);
			$scategory_mod =& m('scategory');
			$stroe_cate_id=$this->options['store_cate_id']?$this->options['store_cate_id']:0;
			$amount_2=$this->options['amount_2']?$this->options['amount_2']:6;
			$cate_ids = $scategory_mod->get_descendant($stroe_cate_id);
			$condition_id=implode(',',$cate_ids);
			$condition_id && $condition_id =' AND cate_id IN(' . $condition_id . ')';
			$store_mod=&m('store');
			if($stroe_cate_id>0){
				$store=$store_mod->find(array('conditions'=>'recommended=1 '.$condition_id,'fields'=>'store_name,store_logo,address,sort_order','join'    => 'belongs_to_user,has_scategory','limit'=>$amount_2,'order'=>'sort_order'));
			}
			
			$data=array(
				'store'=>$store,
				'goods_list'=>$goods_list,
				'gcategory'=>$gcategory,
				'add_class'=>$cate_id_1,
				'cate_name'=>$gcategory_kk['cate_name'],
				'cate_id'  =>$gcategory_kk['cate_id']
			);
			//print_r($data);
        	$cache_server->set($key, $data, $this->_ttl);
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
	function get_scategory_list(){
		$scategory_mod=&m('scategory');
		$scategories=$scategory_mod->find(array('conditions'=>'parent_id=0'));	
		$resuilt=array();
		foreach($scategories as $k=>$v){
			$resuilt[$v['cate_id']]=$v['cate_name'];	
		}
		return $resuilt;
	}
	function parse_config($input)
    {

        return $input;
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
