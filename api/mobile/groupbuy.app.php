<?php
ini_set('date.timezone','Asia/Shanghai');
class GroupbuyApp extends MobileApp{
	function adv(){
		$db = m('groupbuy');
		$time = time();
		$sql = "SELECT * FROM ecm_groupbuy where state=1 and start_time<$time and end_time> $time order by group_id desc limit 1";
		$gbuy = $db->getRow($sql);
		
		if($gbuy){
			$gid = $gbuy['goods_id'];
			$name = $gbuy['group_name'];
			$goodsinfo = m('goods')->get_info($gid);
			$info = unserialize($gbuy['spec_price']);
			$a = array_pop($info);
			extract($a);
			$this->success(array('nothing'=>0,'servertime'=>time(),'endtime'=>$gbuy['end_time'],'goods_id'=>$gid,'goods_price'=>$goodsinfo['price'],'group_id'=>$gbuy['group_id'],'group_price'=>$price,'name'=>$name,'image'=>SITE_URL.'/'.$goodsinfo['default_image']));
		}else{
			$this->success(array('nothing'=>1));
		}
	}
	
	function servertime(){
		
		$this->success(array('servertime'=>time()));
	}
	
	function glist(){
		$page = $this->reqdata->page?intval($this->reqdata->page):0;
		$limit = $this->reqdata->pagesize?intval($this->reqdata->pagesize):15;
		$start = $page*$limit;
		
		$groupbuy_mod = &m('groupbuy');
		$groupbuy_list = $groupbuy_mod->find(array(
				'conditions'    => 'gb.state ='. GROUP_ON .' AND gb.end_time>' . time(),
				'fields'        => 'gb.group_name,gb.spec_price,gb.min_quantity,gb.store_id,gb.state,gb.end_time,g.default_image,default_spec,s.store_name,g.price,gb.start_time',
				'join'          => 'belong_store, belong_goods',
				'limit'         => $start.','.$limit,
				'count'         => true,   //允许统计
				'order'         => 'group_id desc',
		));
		if ($ids = array_keys($groupbuy_list))
		{
			$quantity = $groupbuy_mod->get_join_quantity($ids);
		}
		
		$list = array();
		
		
		foreach ($groupbuy_list as $key=> $item){
			$spect = unserialize($item['spec_price']);
			$defaultspect = array_shift($spect);
			$g['join_quantity'] = empty($quantity[$key]['quantity']) ? 0 : $quantity[$key]['quantity'];
			$g['group_name'] = $item['group_name'];
			$g['group_num'] = $item['min_quantity'];
			$g['start_time'] = (int)$item['start_time'];
			$g['start_time_str'] = date('Y-m-d H:i:s',$item['start_time']);
			$g['end_time'] = (int)$item['end_time'];
			$g['end_time_str'] = date('Y-m-d H:i:s',$item['end_time']);
			$g['now_time'] = time();
			$g['default_image'] =  SITE_URL.'/'.$item['default_image'];
			$g['grounp_price'] = $defaultspect['price'];
			$g['org_price'] = $item['price'];
			$list[] = $g;
		}
		$count = $groupbuy_mod->getCount();
		$this->success(array('list'=>$list,'count'=>$count));
	}
}