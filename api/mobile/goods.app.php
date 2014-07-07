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
	
	
	function goodsinfo(){
		$gid = intval($this->reqdata->id);
		if(!$gid){
			$this->error(106,'未获取到id');
		}
		$m = m('goods');
		$goodsinfo =  $m->get_info($gid);
		
		if(!$goodsinfo){
			$this->error(106,'未获取到id');
		}
		$goods = array();
		$fileds = array('goods_id','store_id','goods_name','brand','price','market_price','orders');
		foreach ($fileds as $file){
			$goods[$file] = $goodsinfo[$file];
		}
		$spect = $goodsinfo['_specs'][0];
		$goods['stock'] = $spect['stock'];
		$imgs = $goodsinfo['_images'];
		foreach ($imgs as $img){
			$goods['imgs'][] = array('image_url'=>SITE_URL.'/'.$img['$img'],'thumb'=>SITE_URL.'/'.$img['thumbnail']);
		}
		$intro = $goodsinfo['description'];
		$r = preg_match_all("/<p[^>]*>(.*?)<\/p>/i", $intro,$formats);
		
		$goods['description'] = array();
		if(!($r&&$formats[1])){
			$intro = strip_tags($intro);
			$intro = html_entity_decode(trim($intro));
			$goods['description'][] = array('isimg'=>0,'imgurl'=>'','txt'=>$intro);
		}else{
			foreach ($formats[1] as $item){
				$rs = preg_match_all("/<img.*?src=[\"']([^'\"]*)[\"'].*?>/i", $item,$getimgs);
				if(!($rs&&$getimgs[1])){
					$txt = strip_tags($item);
					$txt = trim($txt);
					if($txt){
						$txt = html_entity_decode($txt);
						$goods['description'][] = array('isimg'=>0,'imgurl'=>'','txt'=>$txt);
					}
				}else{
					$src = $getimgs[1][0];
					if(strpos($src,'http')===false){
						$src = SITE_URL.'/'.$src;
					}
					$goods['description'][] = array('isimg'=>1,'imgurl'=>$src,'txt'=>'');
				}	
			}
		}
		$this->success(array('goods'=>$goods));
	}
}