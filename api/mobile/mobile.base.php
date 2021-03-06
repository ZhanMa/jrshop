<?php

/**
 * api控制器基类
*/
class MobileApp extends FrontendApp
{
	var $reqdata = array();
	function __construct(){
		if(!defined('TEST_ROOT')){
			header('Content-type: text/json');
		}
		
		$this->reqdata = $_REQUEST['datas'];
		$test = json_decode($this->reqdata);
		if(!$test && get_magic_quotes_gpc()){
			$this->reqdata = stripslashes_deep($this->reqdata);
		}
		
		$this->reqdata = json_decode($this->reqdata);
		if(!$this->reqdata){
			$this->error(101,"未获取到参数");
		}
		if(!$this->reqdata->appid){
			$this->error(104);
		}
		parent::__construct();
	}
	
	function _init_visitor()
	{
		$this->visitor = env('visitor',new MobileVisitor($this->reqdata->appid,$this->reqdata->token));
		
	}
	
	function _init_session(){
		define('SESS_ID', time());
	}
	
	function success($val=''){
		$ret = array('code'=>200,'done'=>true);
		if($val&&is_array($val)){
			$ret = array_merge($ret,$val);
		}
		echo json_encode($ret);
		exit();
	}
	
	/**
	 * 0 未知错误
	 * 200 成功
	 * 101 未获取到datas
	 * 102 用户名密码不能为空
	 * 103 用户名或密码错误
	 * 104 未获取到appid
	 * 105 未获取到catid
	 * 106 商品id错误
	 * 107 店家不能购买商品
	 * 108 需要登录后操作
	 * 109 未绑定手机
	 * 110 相同物品12小时内只能购买一次
	 * 111 库存不足
	 * 112 店铺id错误
	 * 113 搜索至少2个字符
	 * 114 修改密码失败
	 * 115 订单id错误
	 * 116 未获取到团购id
	 * 117 手机号码错误
	 * 118 手机号已被注册
	 * 119 短信发送失败
	 * 120 注册信息错误
	 * 121 短信验证码错误
	 * 122 参数错误
	 * 123 已经评价
	 * 124 订单状态错误
	 * @param unknown $msg
	 * @param number $code
	 */
	function error($code=0,$msg,$val=array()){
		$e = array('code'=>$code,'msg'=>$msg,'done'=>false);
		if($val){
			$e = array_merge($e,$val);
		}
		echo json_encode($e);
		exit();
	}
	
	/**
	 * 执行退出操作
	 */
	function _do_logout()
	{
		$this->visitor->logout();
	}
	
	function log($data){
		if(is_array($data)){
			$data = json_encode($data);
		}
		$data.='  ::'.date('H:i:s').'\n';
		file_put_contents(ROOT_PATH.'/log/'.date('Y-m-d').'.txt', $data);
	}
}

/**
 *    api访问者
 */
class MobileVisitor extends BaseVisitor
{
	var $_info_key = 'user_info';
	var $appid = '';
	var $token = '';
	function __construct($appid,$token){
		$this->appid = intval($appid);
		$this->token = addslashes($token);
		$this->BaseVisitor();
	}
	
	function BaseVisitor()
	{
		$usertoken = m('usertoken');
		$tokeninfo = array();
		if($this->appid && $this->token){

			$tokeninfo = $usertoken->get(array('conditions'=>" appid='$this->appid' and token='$this->token' "));
		}
		
		if ($tokeninfo)
		{
			$uid = $tokeninfo['uid'];
			$this->info['user_id'] = $uid;
			$this->has_login = true;
			$mod_user =& m('member');
			$userinfo = $mod_user->get(array(
					'conditions'    => "user_id = '{$uid}'",
					'join'          => 'has_store',                 //关联查找看看是否有店铺
					'fields'        => 'user_id, user_name, reg_time, last_login, last_ip, store_id',
			)); 
			$userinfo2 = $this->get_detail();
			$userinfo = array_merge($userinfo,$userinfo2);
			unset($userinfo['password']);
			if($userinfo){
				$this->info         = $userinfo;
				$this->has_login    = true;
			}else{
				$this->has_login    = false;
			}
			
		}
		if(!$this->has_login){
			$this->info         = array(
					'user_id'   => 0,
					'user_name' => Lang::get('guest')
			);
			$this->has_login    = false;
		}
		
		
	}
	function assign($user_info)
	{
		$this->info   =   $user_info;
	}

	/**
	 *    登出
	 *
	 *    @author    Garbin
	 *    @return    void
	 */
	function logout()
	{
		if($this->appid && $this->token){
			$usertoken = m('usertoken');
			
			$usertoken->db->query("update ecm_user_token set token='' where appid=$this->appid and token = '$this->token'");
		}
	}
}

?>