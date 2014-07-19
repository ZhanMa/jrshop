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
			$item['status'] = $stats[$order['status']];
			$item['order_sn'] = $order['order_sn'];
			$item['order_amount'] = $order['order_amount'];
			$item['seller_name'] = $order['seller_name'];
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
}