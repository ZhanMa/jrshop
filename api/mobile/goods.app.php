<?php
class GoodsApp extends MobileApp{
	function goodslist(){
		$cate_id = intval($this->reqdata->catid);
		$page = $this->reqdata->page?intval($this->reqdata->page):0;
		$limit = $this->reqdata->pagesize?intval($this->reqdata->pagesize):15;
		$start = $page*$limit;
		
		if(!$cate_id){
			$this->error(105,'catid错误');
		}
		$gcategory_mod  =& bm('gcategory');
		$layer   = $gcategory_mod->get_layer($cate_id, true);
		$conditions = " g.if_show = 1 AND g.closed = 0 AND s.state = 1"; // 上架且没有被禁售，店铺是开启状态,
		$conditions .= " AND  g.cate_id_{$layer} = '" . $cate_id . "'";
		$goods_mod  =& m('goods');
		$goods_list = $goods_mod->get_list(array(
				'conditions' => $conditions,
				'order'      => 'g.last_update desc',
				'limit'      => $start.','.$limit,
				'count' => true
		));
		
		foreach ($goods_list as $key=>$goods){
			$goods_image_mod=&m('goodsimage');
			$goods_image=$goods_image_mod->get(array(
					'conditions'=>'goods_id='.$goods['goods_id']
			));
			$goods_list[$key]['default_image']=SITE_URL.'/'.$goods_image['thumbnail'];
		}
		$count = $goods_mod->getCount();
		$this->success(array('count'=>$count,'list'=>$goods_list));
	}
}