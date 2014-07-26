<?php
class OrderApp extends MobileApp{
	function orders(){
		if(!$this->visitor->has_login){
			$this->error(108,'请登录后购买');
		}
		$page = $this->reqdata->page?intval($this->reqdata->page):0;
		$limit = $this->reqdata->pagesize?intval($this->reqdata->pagesize):15;
		$start = $page*$limit;
		$file = array('status','order_sn','add_time_from','add_time_to');
		foreach ($file as $f){
			if(isset($this->reqdata->$f)){
				$_GET[$f] = $this->reqdata->$f;
			}
		}
		
		$model_order =& m('order');
		!$_GET['type'] && $_GET['type'] = 'all';
		$con = array(
				array(      //按订单状态搜索
						'field' => 'status',
						'name'  => 'type',
						'handler' => 'order_status_translator',
				),
				array(      //按店铺名称搜索
						'field' => 'seller_name',
						'equal' => 'LIKE',
				),
				array(      //按下单时间搜索,起始时间
						'field' => 'add_time',
						'name'  => 'add_time_from',
						'equal' => '>=',
						'handler'=> 'gmstr2time',
				),
				array(      //按下单时间搜索,结束时间
						'field' => 'add_time',
						'name'  => 'add_time_to',
						'equal' => '<=',
						'handler'=> 'gmstr2time_end',
				),
				array(      //按订单号
						'field' => 'order_sn',
				),
		);
		$conditions = $this->_get_query_conditions($con);
		/* 查找订单 */
		$orders = $model_order->findAll(array(
				'conditions'    => "buyer_id=" . $this->visitor->get('user_id') . "{$conditions}",
				'fields'        => 'this.*',
				'count'         => true,
				'limit'         => "$start,$limit",
				'order'         => 'add_time DESC',
				'include'       =>  array(
						'has_ordergoods',       //取出商品
				),
		));
		foreach ($orders as $key1 => $order)
		{
			foreach ($order['order_goods'] as $key2 => $goods)
			{
				empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
			}
		}
		$count = $model_order->getCount();
		$list = array();
		$stats = array(20=>'已购买',40=>'已使用');
		foreach ($orders as $order){
			$item['order_id'] = $order['order_id'];
			$item['order_sn'] = $order['order_sn'];
			$item['add_time'] = date('Y-m-d H:i:s',$order['add_time']);
			$item['finished'] = $order['status']==40?1:0;
			$item['status'] = $stats[$order['status']];
			$item['order_sn'] = $order['order_sn'];
			$item['order_amount'] = $order['order_amount'];
			$item['seller_name'] = $order['seller_name'];
			
			$item['evaluation_status'] = $order['evaluation_status'];
			foreach ($order['order_goods'] as $key2 => $goods){
				$item['goods_name'] = $goods['goods_name'];
				$item['goods_id'] = $goods['goods_id'];
				$item['goods_image'] = SITE_URL.'/'.$goods['goods_image'];
				$item['goods_count'] = $goods['quantity'];
				$item['goods_price'] = $goods['price'];
			}
			$list[] = $item;
			
		}
		$this->success(array('count'=>$count,'list'=>$list));
	}
	
	function view(){
		if(!$this->visitor->has_login){
			$this->error(108,'请登录后购买');
		}
		
		$order_id = intval($this->reqdata->order_id);
		if(!$order_id){
			$this->error(115,'订单id错误');
		}
		$model_order =& m('order');
		
		$order_info = $model_order->get(array(
				'fields'        => "*, order.add_time as order_add_time",
				'conditions'    => "order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'),
				'join'          => 'belongs_to_store',
		));
		if(!$order_info){
			$this->error(115,'订单id错误');
		}
		$order_type =& ot($order_info['extension']);
		$order_detail = $order_type->get_order_detail($order_id, $order_info);
		$goods = array_pop($order_detail['data']['goods_list']);
		$f = array('order_id','order_sn','seller_id','seller_name','buyer_id','buyer_name','buyer_email','order_amount','region_name','address','zipcode','tel','im_qq','im_ww','store_name');
		$info = array();
		foreach ($f as $fname){
			$info[$fname] = $order_info[$fname];
		}
		$info['add_time'] = date('Y-m-d H:i:s',$order_info['add_time']);
		$info['store_logo'] = SITE_URL.'/'.$order_info['store_logo'];
		$info['goods_name'] = $goods['goods_name'];
		$info['goods_id'] = $goods['goods_id'];
		$info['goods_image'] = SITE_URL.'/'.$goods['goods_image'];
		$info['goods_count'] = $goods['quantity'];
		$info['goods_price'] = $goods['price'];
		$this->success(array('info'=>$info));
		
	}
	
	function evaluate()
	{
		if(!$this->visitor->has_login){
			$this->error(108,'请登录后评价');
		}
		$order_id = isset($this->reqdata->order_id) ? intval($this->reqdata->order_id) : 0;
		if (!$order_id)
		{
			$this->error(115,'订单id错误');
	
		}
	
		/* 验证订单有效性 */
		$model_order =& m('order');
		$order_info  = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
		if (!$order_info)
		{
			$this->error(115,'订单id错误');
		}
		if ($order_info['status'] != ORDER_FINISHED)
		{
			/* 不是已完成的订单，无法评价 */
			$this->error(124,'订单还未完成无法评价');
	
			return;
		}
		if ($order_info['evaluation_status'] != 0)
		{
			/* 已评价的订单 */
			$this->error(123,'该订单已完成评价');
	
			return;
		}
		$model_ordergoods =& m('ordergoods');
		$goods_list = $model_ordergoods->find("order_id={$order_id}");
		$firstgoods = array_shift($goods_list);
		
		$_POST['evaluations'] = array($firstgoods['default_spec']=>array('evaluation'=>$this->reqdata->evaluation,'comment'=>$this->reqdata->comment));
		
		$evaluations = array();
		/* 写入评价 */
		foreach ($_POST['evaluations'] as $rec_id => $evaluation)
		{
			if ($evaluation['evaluation'] <= 0 || $evaluation['evaluation'] > 3)
			{
				$this->error(122,'评价等级错误');

				return;
			}
			switch ($evaluation['evaluation'])
			{
				case 3:
					$credit_value = 1;
					break;
				case 1:
					$credit_value = -1;
					break;
				default:
					$credit_value = 0;
					break;
			}
			$evaluations[intval($rec_id)] = array(
					'evaluation'    => $evaluation['evaluation'],
					'comment'       => $evaluation['comment'],
					'credit_value'  => $credit_value
			);
		}
		
		foreach ($evaluations as $rec_id => $evaluation)
		{
			$model_ordergoods->edit("rec_id={$rec_id} AND order_id={$order_id}", $evaluation);
			$goods_url = SITE_URL . '/' . url('app=goods&id=' . $goods_list[$rec_id]['goods_id']);
			$goods_name = $goods_list[$rec_id]['goods_name'];
			$this->send_feed('goods_evaluated', array(
					'user_id'   => $this->visitor->get('user_id'),
					'user_name'   => $this->visitor->get('user_name'),
					'goods_url'   => $goods_url,
					'goods_name'   => $goods_name,
					'evaluation'   => Lang::get('order_eval.' . $evaluation['evaluation']),
					'comment'   => $evaluation['comment'],
					'images'    => array(
							array(
									'url' => SITE_URL . '/' . $goods_list[$rec_id]['goods_image'],
									'link' => $goods_url,
							),
					),
			));
		}

		/* 更新订单评价状态 */
		$model_order->edit($order_id, array(
				'evaluation_status' => 1,
				'evaluation_time'   => gmtime()
		));

		/* 更新卖家信用度及好评率 */
		$model_store =& m('store');
		$model_store->edit($order_info['seller_id'], array(
				'credit_value'  =>  $model_store->recount_credit_value($order_info['seller_id']),
				'praise_rate'   =>  $model_store->recount_praise_rate($order_info['seller_id'])
		));

		/* 更新商品评价数 */
		$model_goodsstatistics =& m('goodsstatistics');
		$goods_ids = array();
		foreach ($goods_list as $goods)
		{
			$goods_ids[] = $goods['goods_id'];
		}
		$model_goodsstatistics->edit($goods_ids, 'comments=comments+1');
		$this->success(array('info'=>'评价成功'));
	}
	
	function finish(){
		if(!$this->visitor->has_login){
			$this->error(108,'请登录后评价');
		}
		$order_id = isset($this->reqdata->order_id) ? intval($this->reqdata->order_id) : 0;
		if (!$order_id)
		{
			$this->error(115,'订单id错误');
		
		}
		$model_order =& m('order');
		$model_order->edit($order_id, array(
				'status' => ORDER_FINISHED
				
		));
		$this->success();
	}
	
	function comments(){
		$goods_id = $this->reqdata->goods_id;
		$page = $this->reqdata->page?intval($this->reqdata->page):0;
		$limit = $this->reqdata->pagesize?intval($this->reqdata->pagesize):15;
		$start = $page*$limit;
		if(!$goods_id){
			$this->error(106,'商品id错误');
		}
		$order_goods_mod =& m('ordergoods');
		
		$comments = $order_goods_mod->find(array(
				'conditions' => "goods_id = '$goods_id' AND evaluation_status = '1'",
				'join'  => 'belongs_to_order',
				'fields'=> 'buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation',
				'count' => true,
				'order' => 'evaluation_time desc',
				'limit' => "$start,$limit",
		));
		
		
		$count = $order_goods_mod->getCount();
		
		$this->success(array('list'=>$comments,'count'=>$count));
	}
}