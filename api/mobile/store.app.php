<?php
class StoreApp extends MobileApp{
	function info(){
		$storeid = intval($this->reqdata->id);
		if(!$storeid){
			$this->error(112,'店铺id错误');
		}
		$store_mod  =& m('store');
		$store_info = $store_mod->get_info($storeid);
		$sgrade_mod =& m('sgrade');
		$grades = $sgrade_mod->get_options();
		$info['logo'] = SITE_URL.'/'.$store_info['store_logo'];
		$info['name'] = $store_info['store_name'];
		$info['id'] = $store_info['store_id'];
		$info['address'] = $store_info['address'];
		$info['zipcode'] = $store_info['zipcode'];
		$info['grade'] = $grades[$store_info['sgrade']];
		$info['credit_value'] = $store_info['credit_value'];
		$info['description'] = strip_tags($store_info['description']);
		$goods_mod =& m('goods');
		$info['goods_count'] = $goods_mod->get_count_of_store($storeid);
		$this->success(array('info'=>$info));
	}
	
	function goods(){
		$store_id = intval($this->reqdata->id);
		$page = $this->reqdata->page?intval($this->reqdata->page):0;
		$limit = $this->reqdata->pagesize?intval($this->reqdata->pagesize):15;
		$start = $page*$limit;
		
		if(!$store_id){
			$this->error(112,'店铺id错误');
		}
		$gcategory_mod  =& bm('gcategory');
		
		$conditions = " g.if_show = 1 AND g.closed = 0 AND s.state = 1"; // 上架且没有被禁售，店铺是开启状态,
		$conditions .= " AND  g.store_id = $store_id";
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
		$list = array_values($goods_list);
		$count = $goods_mod->getCount();
		$this->success(array('count'=>$count,'list'=>$list));
	}
	
	
	function favorlist(){
		if(!$this->visitor->has_login){
			$this->error(108,'请先登录');
		}
		$type = empty($this->reqdata->type)    ? 'goods' : trim($this->reqdata->type);
		if ($type == 'goods')
		{
			$this->_list_collect_goods();
		}
		elseif ($type == 'store')
		{
			/* 收藏店铺 */
			$this->_list_collect_store();
		}
	}
	
	function addfavorite()
	{
		if(!$this->visitor->has_login){
			$this->error(108,'请登录后再收藏');
		}
		$type = $this->reqdata->type;
		$item_id = $this->reqdata->id;
		$keyword = '';
		if (!$item_id)
		{
			$this->error('106','未获取到id');
	
			return;
		}
		if ($type == 'goods')
		{
			$this->_add_collect_goods($item_id, $keyword);
		}
		elseif ($type == 'store')
		{
			$this->_add_collect_store($item_id, $keyword);
		}
		$this->success();
	}
	
	function _add_collect_goods($goods_id, $keyword)
	{
		/* 验证要收藏的商品是否存在 */
		$model_goods =& m('goods');
		$goods_info  = $model_goods->get($goods_id);
	
		if (empty($goods_info))
		{
			/* 商品不存在 */
			$this->error(106,'商品id错误');
			return;
		}
		$model_user =& m('member');
		$model_user->createRelation('collect_goods', $this->visitor->get('user_id'), array(
				$goods_id   =>  array(
						'keyword'   =>  $keyword,
						'add_time'  =>  gmtime(),
				)
		));
	
		/* 更新被收藏次数 */
		$model_goods->update_collect_count($goods_id);
	
		$goods_image = $goods_info['default_image'] ? $goods_info['default_image'] : Conf::get('default_goods_image');
		$goods_url  = SITE_URL . '/' . url('app=goods&id=' . $goods_id);
		$this->send_feed('goods_collected', array(
				'user_id'   => $this->visitor->get('user_id'),
				'user_name'   => $this->visitor->get('user_name'),
				'goods_url'   => $goods_url,
				'goods_name'   => $goods_info['goods_name'],
				'images'    => array(array(
						'url' => SITE_URL . '/' . $goods_image,
						'link' => $goods_url,
				)),
		));
	
	
	}
	
	/**
	 *    收藏店铺
	 *
	 *    @author    Garbin
	 *    @param     int    $store_id
	 *    @param     string $keyword
	 *    @return    void
	 */
	function _add_collect_store($store_id, $keyword)
	{
		/* 验证要收藏的店铺是否存在 */
		$model_store =& m('store');
		$store_info  = $model_store->get($store_id);
		if (empty($store_info))
		{
			/* 店铺不存在 */
			$this->error(112,'店铺id错误');
			return;
		}
		$model_user =& m('member');
		$model_user->createRelation('collect_store', $this->visitor->get('user_id'), array(
				$store_id   =>  array(
						'keyword'   =>  $keyword,
						'add_time'  =>  gmtime(),
				)
		));
		$this->send_feed('store_collected', array(
				'user_id'   => $this->visitor->get('user_id'),
				'user_name'   => $this->visitor->get('user_name'),
				'store_url'   => SITE_URL . '/' . url('app=store&id=' . $store_id),
				'store_name'   => $store_info['store_name'],
		));
	
		
	}
	
	
	/**
	 *    列表收藏的商品
	 *
	 *    @author    Garbin
	 *    @return    void
	 */
	function _list_collect_goods()
	{
		$page = $this->reqdata->page?intval($this->reqdata->page):0;
		$limit = $this->reqdata->pagesize?intval($this->reqdata->pagesize):15;
		$start = $page*$limit;
		
		$model_goods =& m('goods');
		
		$collect_goods = $model_goods->find(array(
				'join'  => 'be_collect,belongs_to_store,has_default_spec',
				'fields'=> 'this.*,store.store_name,store.store_id,collect.add_time,goodsspec.price,goodsspec.spec_id',
				'conditions' => 'collect.user_id = ' . $this->visitor->get('user_id'),
				'count' => true,
				'order' => 'collect.add_time DESC',
				'limit' => "$start,$limit",
		));
		
		$list = array();
		foreach ($collect_goods as $key => $goods)
		{
			empty($goods['default_image']) && $collect_goods[$key]['default_image'] = Conf::get('default_goods_image');
			$item['goods_id'] = $goods['goods_id'];
			$item['name'] = $goods['goods_name'];
			$item['price'] = $goods['price'];
			$item['market_price'] = $goods['market_price'];
			$item['img'] = SITE_URL . '/' .$goods['default_image'];
			$list[] = $item;
		}
		$count = $model_goods->getCount();   //获取统计的数据
		$this->success(array('type'=>'goods','count'=>$count,'list'=>$list));
	}
	
	/**
	 *    列表收藏的店铺
	 *
	 *    @author    Garbin
	 *    @return    void
	 */
	function _list_collect_store()
	{
		
		$model_store =& m('store');
		$page = $this->reqdata->page?intval($this->reqdata->page):0;
		$limit = $this->reqdata->pagesize?intval($this->reqdata->pagesize):15;
		$start = $page*$limit;
		$collect_store = $model_store->find(array(
				'join'  => 'be_collect,belongs_to_user',
				'fields'=> 'this.*,member.user_name,collect.add_time',
				'conditions' => 'collect.user_id = ' . $this->visitor->get('user_id') ,
				'count' => true,
				'order' => 'collect.add_time DESC',
				'limit' => "$start,$limit",
		));
		$count = $model_store->getCount();   //获取统计的数据
		$step = intval(Conf::get('upgrade_required'));
		$step < 1 && $step = 5;
		$list = array();
		foreach ($collect_store as $key => $store)
		{
			empty($store['store_logo']) && $collect_store[$key]['store_logo'] = Conf::get('default_store_logo');
			$item['store_id'] = $store['store_id'];
			$item['logo'] = SITE_URL . '/' .$store['store_logo'];
			$item['name'] = $store['store_name'];
			$item['owner']= $store['owner_name'];
			$list[] = $item;
		}
		
		$this->success(array('type'=>'store','count'=>$count,'list'=>$list));
		
	}
}