<?php

/**
 *    购物车控制器，负责会员购物车的管理工作，她与下一步售货员的接口是：购物车告诉售货员，我要买的商品是我购物车内的商品
 *
 *    @author    Garbin
 */

class CartApp extends MallbaseApp
{
    /**
     *    列出购物车中的商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        $store_id = isset($_GET['store_id']) ? intval($_GET['store_id']) : 0;
        $carts = $this->_get_carts($store_id);
        $this->_curlocal(
            LANG::get('cart')
        );
        $this->_config_seo('title', Lang::get('confirm_goods') . ' - ' . Conf::get('site_title'));

        if (empty($carts))
        {
            $this->_cart_empty();

            return;
        }

        $this->assign('carts', $carts);
        $this->display('cart.index.html');
    }

    /**
     *    放入商品(根据不同的请求方式给出不同的返回结果)
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
        $spec_id   = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity   = isset($_GET['quantity']) ? intval($_GET['quantity']) : 0;
        if (!$spec_id || !$quantity)
        {
            return;
        }

        /* 是否有商品 */
        $spec_model =& m('goodsspec');
        $spec_info  =  $spec_model->get(array(
            'fields'        => 'g.store_id, g.goods_id, g.goods_name, g.spec_name_1, g.spec_name_2, g.default_image, gs.spec_1, gs.spec_2, gs.stock, gs.price',
            'conditions'    => $spec_id,
            'join'          => 'belongs_to_goods',
        ));

        if (!$spec_info)
        {
            $this->json_error('no_such_goods');
            /* 商品不存在 */
            return;
        }

        if($this->visitor->get('manage_store')){
        	$this->json_error('商家店铺不能购买商品！');
        	
        	return;
        }
        
        /* 如果是自己店铺的商品，则不能购买 */
        if ($this->visitor->get('manage_store'))
        {
            if ($spec_info['store_id'] == $this->visitor->get('manage_store'))
            {
                $this->json_error('can_not_buy_yourself');

                return;
            }
        }

        /* 检查是否绑定手机 */
        $user = $this->visitor->get();
        $user_mod =& m('member');
        $info = $user_mod->get_info($user['user_id']);
        if(!$info['safephone']){
        	//$this->show_message('safephone_empty','phone','index.php?app=member&act=phone');
        	$this->json_error(-1);
        	return;
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
        $mysql = $sql;
		if(!empty($ordergoods)){
			$this->json_error('12小时内相同物品只能购买一次',array('sql'=>$mysql));
			
			return;
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
            $this->json_error('no_enough_goods');
            return;
        }

        $spec_1 = $spec_info['spec_name_1'] ? $spec_info['spec_name_1'] . ':' . $spec_info['spec_1'] : $spec_info['spec_1'];
        $spec_2 = $spec_info['spec_name_2'] ? $spec_info['spec_name_2'] . ':' . $spec_info['spec_2'] : $spec_info['spec_2'];

        $specification = $spec_1 . ' ' . $spec_2;

        /* 将商品加入购物车 */
        $cart_item = array(
            'user_id'       => $this->visitor->get('user_id'),
            'session_id'    => SESS_ID,
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
        
        /* 将这些信息传递给订单类型处理类生成订单(你根据我提供的信息生成一张订单) */
        $order_id = $order_type->submit_order(array(
        		'goods_info'    =>  $goods_info,      //商品信息（包括列表，总价，总量，所属店铺，类型）,可靠的!
        		'post'          =>  $_POST,           //用户填写的订单信息
        ));
        
        
        if (!$order_id)
        {
        	$this->json_error($order_type->get_error());
        	return;
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
        
//         /* 添加并返回购物车统计即可 */
//         $cart_model =&  m('cart');
//         $cart_model->add($cart_item);
//         $cart_status = $this->_get_cart_status();

//         /* 更新被添加进购物车的次数 */
//         $model_goodsstatistics =& m('goodsstatistics');
//         $model_goodsstatistics->edit($spec_info['goods_id'], 'carts=carts+1');

        //执行购买并发送短信
        
        
        $this->json_result(array('sql'=>$mysql), '短信已成功发送,请注意查收');
    }

    /**
     *    丢弃商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function drop()
    {
        /* 传入rec_id，删除并返回购物车统计即可 */
        $rec_id = isset($_GET['rec_id']) ? intval($_GET['rec_id']) : 0;
        if (!$rec_id)
        {
            return;
        }

        /* 从购物车中删除 */
        $model_cart =& m('cart');
        $droped_rows = $model_cart->drop('rec_id=' . $rec_id . ' AND session_id=\'' . SESS_ID . '\'', 'store_id');
        if (!$droped_rows)
        {
            return;
        }

        /* 返回结果 */
        $dropped_data = $model_cart->getDroppedData();
        $store_id     = $dropped_data[$rec_id]['store_id'];
        $cart_status = $this->_get_cart_status();
        $this->json_result(array(
            'cart'  =>  $cart_status['status'],                      //返回总的购物车状态
            'amount'=>  $cart_status['carts'][$store_id]['amount']   //返回指定店铺的购物车状态
        ),'drop_item_successed');
    }

    /**
     *    更新购物车中商品的数量，以商品为单位，AJAX更新
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function update()
    {
        $spec_id  = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
        $quantity = isset($_GET['quantity'])? intval($_GET['quantity']): 0;
        if (!$spec_id || !$quantity)
        {
            /* 不合法的请求 */
            return;
        }

        /* 判断库存是否足够 */
        $model_spec =& m('goodsspec');
        $spec_info  =  $model_spec->get($spec_id);
        if (empty($spec_info))
        {
            /* 没有该规格 */
            $this->json_error('no_such_spec');
            return;
        }

        if ($quantity > $spec_info['stock'])
        {
            /* 数量有限 */
            $this->json_error('no_enough_goods');
            return;
        }

        /* 修改数量 */
        $where = "spec_id={$spec_id} AND session_id='" . SESS_ID . "'";
        $model_cart =& m('cart');

        /* 获取购物车中的信息，用于获取价格并计算小计 */
        $cart_spec_info = $model_cart->get($where);
        if (empty($cart_spec_info))
        {
            /* 并没有添加该商品到购物车 */
            return;
        }

        $store_id = $cart_spec_info['store_id'];

        /* 修改数量 */
        $model_cart->edit($where, array(
            'quantity'  =>  $quantity,
        ));

        /* 小计 */
        $subtotal   =   $quantity * $cart_spec_info['price'];

        /* 返回JSON结果 */
        $cart_status = $this->_get_cart_status();
        $this->json_result(array(
            'cart'      =>  $cart_status['status'],                     //返回总的购物车状态
            'subtotal'  =>  $subtotal,                                  //小计
            'amount'    =>  $cart_status['carts'][$store_id]['amount']  //店铺购物车总计
        ), 'update_item_successed');
    }

    /**
     *    获取购物车状态
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_cart_status()
    {
        /* 默认的返回格式 */
        $data = array(
            'status'    =>  array(
                'quantity'  =>  0,      //总数量
                'amount'    =>  0,      //总金额
                'kinds'     =>  0,      //总种类
            ),
            'carts'     =>  array(),    //购物车列表，包含每个购物车的状态
        );

        /* 获取所有购物车 */
        $carts = $this->_get_carts();
        if (empty($carts))
        {
            return $data;
        }
        $data['carts']  =   $carts;
        foreach ($carts as $store_id => $cart)
        {
            $data['status']['quantity'] += $cart['quantity'];
            $data['status']['amount']   += $cart['amount'];
            $data['status']['kinds']    += $cart['kinds'];
        }

        return $data;
    }

    /**
     *    购物车为空
     *
     *    @author    Garbin
     *    @return    void
     */
    function _cart_empty()
    {
        $this->display('cart.empty.html');
    }

    /**
     *    以购物车为单位获取购物车列表及商品项
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_carts($store_id = 0)
    {
        $carts = array();

        /* 获取所有购物车中的内容 */
        $where_store_id = $store_id ? ' AND cart.store_id=' . $store_id : '';

        /* 只有是自己购物车的项目才能购买 */
        $where_user_id = $this->visitor->get('user_id') ? " AND cart.user_id=" . $this->visitor->get('user_id') : '';
        $cart_model =& m('cart');
        $cart_items = $cart_model->find(array(
            'conditions'    => 'session_id = \'' . SESS_ID . "'" . $where_store_id . $where_user_id,
            'fields'        => 'this.*,store.store_name',
            'join'          => 'belongs_to_store',
        ));
        if (empty($cart_items))
        {
            return $carts;
        }
        $kinds = array();
        foreach ($cart_items as $item)
        {
            /* 小计 */
            $item['subtotal']   = $item['price'] * $item['quantity'];
            $kinds[$item['store_id']][$item['goods_id']] = 1;

            /* 以店铺ID为索引 */
            empty($item['goods_image']) && $item['goods_image'] = Conf::get('default_goods_image');
            $carts[$item['store_id']]['store_name'] = $item['store_name'];
            $carts[$item['store_id']]['amount']     += $item['subtotal'];   //各店铺的总金额
            $carts[$item['store_id']]['quantity']   += $item['quantity'];   //各店铺的总数量
            $carts[$item['store_id']]['goods'][]    = $item;
        }

        foreach ($carts as $_store_id => $cart)
        {
            $carts[$_store_id]['kinds'] =   count(array_keys($kinds[$_store_id]));  //各店铺的商品种类数
        }

        return $carts;
    }
}

?>
