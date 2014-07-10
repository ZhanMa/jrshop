<?php
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
}