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
	
	function search(){
		$word = $this->reqdata->word;
		$page = $this->reqdata->page?intval($this->reqdata->page):0;
		$limit = $this->reqdata->pagesize?intval($this->reqdata->pagesize):15;
		$start = $page*$limit;
		
		if(strlen($word)<1){
			$this->error(113,'搜索内容为空');
		}
		
		$conditions = " g.if_show = 1 AND g.closed = 0 AND s.state = 1"; // 上架且没有被禁售，店铺是开启状态,
		$conditions .= " AND  (g.goods_name like '%" . $word . "%' or g.tags like '%" . $word . "%')";
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
	
	function buy(){
		if(!$this->visitor->has_login){
			$this->error(108,'请登录后购买');
		}
		
		$gid = intval($this->reqdata->id);
		$quantity = intval($this->reqdata->num);
		$quantity = $quantity?$quantity:1;
		if(!$gid){
			$this->error(106,'为获取到商品id');
		}
		$mgoods = m('goods');
		$goodsinfo = $mgoods->get_info($gid);
		if(!$goodsinfo)
		{
			$this->error(106,'商品id错误');
			
		}
		
		$spec_id   = $goodsinfo['default_spec'];
		
		/* 是否有商品 */
		$spec_model =& m('goodsspec');
		$spec_info  =  $spec_model->get(array(
				'fields'        => 'g.store_id, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image, gs.spec_1, gs.spec_2, gs.stock, gs.price',
				'conditions'    => $spec_id,
				'join'          => 'belongs_to_goods',
		));
		
		
		
		if($this->visitor->get('manage_store')){
			$this->error(107,'商家店铺不能购买商品！');
		}
		
		
		
		/* 检查是否绑定手机 */
		$user = $this->visitor->get();
		$user_mod =& m('member');
		$info = $user_mod->get_info($user['user_id']);
		if(!$info['safephone']){
			$this->error(109,'请先绑定手机');
		}
		$getaddress = $info['safephone'];
		
		/* 12小时内相同物品只能购买一次*/
		
		$tmp = time() - 12*3600;
		$ordergoods_mod = m('ordergoods');
		$conditions = ' AND goods_id = '.$spec_id.' AND add_time > '.$tmp." and  buyer_id=".$user['user_id'];
		/* $ordergoods = $ordergoods_mod->find(array(
		 'conditions' => '1=1' . $conditions,
				'join'  => 'belongs_to_order',
		)); */
		$sql ="SELECT * FROM ecm_order o JOIN ecm_order_goods og
        using(order_id) where goods_id = ".$spec_info['goods_id']."
		        and  o.add_time > $tmp and  o.buyer_id=".$user['user_id'];
		$ordergoods = $ordergoods_mod->getOne($sql);
		
		if(!empty($ordergoods)){
			//$this->error(110,'12小时内相同物品只能购买一次');
		}
		/* 是否添加过 */
		//         $model_cart =& m('cart');
		//         $item_info  = $model_cart->get("spec_id={$spec_id} AND session_id='" . SESS_ID . "'");
		//         if (!empty($item_info))
			//         {
			//             $this->json_error('goods_already_in_cart');
		
			//             return;
			//         }
		
		if ($quantity > $spec_info['stock'])
		{
			$this->error(111,'库存不足');
		
		}
		
		$spec_1 = $spec_info['spec_name_1'] ? $spec_info['spec_name_1'] . ':' . $spec_info['spec_1'] : $spec_info['spec_1'];
		$spec_2 = $spec_info['spec_name_2'] ? $spec_info['spec_name_2'] . ':' . $spec_info['spec_2'] : $spec_info['spec_2'];
		
		$specification = $spec_1 . ' ' . $spec_2;
		
		/* 将商品加入购物车 */
		$cart_item = array(
				'user_id'       => $this->visitor->get('user_id'),
				'session_id'    => $this->visitor->token,
				'store_id'      => $spec_info['store_id'],
				'spec_id'       => $spec_id,
				'goods_id'      => $spec_info['goods_id'],
				'goods_name'    => addslashes($spec_info['goods_name']),
				'specification' => addslashes(trim($specification)),
				'price'         => $spec_info['price'],
				'quantity'      => $quantity,
				'goods_image'   => addslashes($spec_info['default_image']),
		);
		$store_mod = & m('store');
		$store_info = $store_mod->get($spec_info['store_id']);
		$goods_info = array(
				'items'     =>  array($cart_item),    //商品列表
				'quantity'  => 1,          //商品总量
				'amount'    =>  $spec_info['price'],          //商品总价
				'store_id'  =>  $spec_info['store_id'],          //所属店铺
				'store_name'=>  $store_info['store_name'],       //店铺名称
				'type'      =>  'material', //商品类型
				'otype'     =>  'normal',   //订单类型
				'allow_coupon'  => true,    //是否允许使用优惠券
		);
		
		/* 根据商品类型获取对应的订单类型 */
		$goods_type =& gt($goods_info['type']);
		$order_type =& ot($goods_info['otype']);
		
		$msg_code = $order_type->_gen_msg_code();
		$goods_info['msg_code'] = $msg_code;
		
		$poatdata['spec_id'] = $spec_id;
		$poatdata['quantity'] = $quantity;
		
		/* 将这些信息传递给订单类型处理类生成订单(你根据我提供的信息生成一张订单) */
		$order_id = $order_type->submit_order(array(
				'goods_info'    =>  $goods_info,      //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
				'post'          =>  $poatdata,           //用户填写的订单信息
		));
		
		
		if (!$order_id)
		{
			$this->error($order_type->get_error());
			
		}
		
		$model_order =& m('order');
		
		/* 减去商品库存 */
		$model_order->change_stock('-', $order_id);
		
		/* 获取订单信息 */
		$order_info = $model_order->get($order_id);
		
		/* 发送事件 */
		$feed_images = array();
		foreach ($goods_info['items'] as $_gi)
		{
			$feed_images[] = array(
					'url'   => SITE_URL . '/' . $_gi['goods_image'],
					'link'  => SITE_URL . '/' . url('app=goods&id=' . $_gi['goods_id']),
			);
		}
		$this->send_feed('order_created', array(
				'user_id'   => $this->visitor->get('user_id'),
				'user_name' => addslashes($this->visitor->get('user_name')),
				'seller_id' => $order_info['seller_id'],
				'seller_name' => $order_info['seller_name'],
				'store_url' => SITE_URL . '/' . url('app=store&id=' . $order_info['seller_id']),
				'images'    => $feed_images,
		));
		
		$buyer_address = $this->visitor->get('email');
		$model_member =& m('member');
		$member_info  = $model_member->get($goods_info['store_id']);
		$seller_address= $member_info['email'];
		
		/* 发送给买家下单通知 */
		$buyer_mail = get_mail('tobuyer_new_order_notify', array('order' => $order_info));
		$this->_mailto($buyer_address, addslashes($buyer_mail['subject']), addslashes($buyer_mail['message']));
		
		/* 发送给卖家新订单通知 */
		$seller_mail = get_mail('toseller_new_order_notify', array('order' => $order_info));
		$this->_mailto($seller_address, addslashes($seller_mail['subject']), addslashes($seller_mail['message']));
		
		/* 更新下单次数 */
		$model_goodsstatistics =& m('goodsstatistics');
		$goods_ids = array();
		foreach ($goods_info['items'] as $goods)
		{
			$goods_ids[] = $goods['goods_id'];
		}
		$model_goodsstatistics->edit($goods_ids, 'orders=orders+1');
		
		$msg = '【96018】尊敬的金融街用户，您在'.$store_info['store_name'].'购买的'. addslashes($spec_info['goods_name']) .',订单号：'.$order_info['order_sn'].',商品券密码为'.$msg_code.'。无需预约。持本券到商家进行认证即可【96018金融街生活在线】';
		
		import('HTTP_SDK');
		$cpid = 'jinrongjiewuye';
		$cppsw = '123456';
		$engine = HTTP_SDK::getInstance($cpid,$cppsw);
		$rusult = $engine->pushMt($getaddress,'1', $msg,  0);
		$this->success(array('info'=>'购买成功'));
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
			$goods['imgs'][] = array('image_url'=>SITE_URL.'/'.$img['image_url'],'thumb'=>SITE_URL.'/'.$img['thumbnail']);
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