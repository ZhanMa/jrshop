<?php
class UserApp extends MobileApp{
	function login(){
		if($this->reqdata->username&&$this->reqdata->password){
			$ms =& ms();
			$appid = $this->reqdata->appid;
            $user_id = $ms->user->auth($this->reqdata->username, $this->reqdata->password);
			if(!$user_id){
				$this->error(103,'用户名或密码错误');
			}
			$token = md5(md5($this->reqdata->username.time()).'kuff');
			$db = m('usertoken');
			$db->db->query("replace into ecm_user_token(uid,appid,token)value($user_id,$appid,'$token')");
			
			$this->_do_login($user_id);
			$this->visitor->info['token'] = $token;
			$this->success((array)$this->visitor->info);
		}else{
			$this->error(102,'用户名或密码不能为空');
		}
	}
	
	function resetpwd(){
		$mobile = $this->reqdata->mobile;
		$mobilecode = $this->reqdata->mobilecode;
		if(!preg_match("/^[1][3-8]\d{9}$/",$mobile)){
			$this->error(117,'手机号错误');
		}
		$memberdb = m('member');
		
		$user = $memberdb->get(array('conditions'=>" safephone='$mobile' "));
		if(!$user){
			$this->error(117,'不存在该手机号的用户');
		}
		
		$user_id = $user['user_id'];
		$db = m('mobilecode');
		$oldcode = $db->get(array('conditions'=>"mobile='$mobile'"));
		if(!$oldcode){
			$this->error(121,'手机验证码错误');
		}
		if($oldcode['code']!=$mobilecode){
			$this->error(121,'手机验证码错误');
		}
		$ms =& ms(); //连接用户中心
		$result = $ms->user->edit($user_id, '', array(
				'password' => $mobile
		),1);
		if($result){
			$this->success(array('info'=>"尊敬的用户您的密码已成功重置为您的手机号,用户名".$user['user_name']));
		}else{
			$this->error(0,'修改失败');
		}
		
		
	}
	
	function bindphone(){
		if(!$this->visitor->has_login){
			$this->error(108,'请登录');
		}
		$user_id = $this->visitor->get('user_id');
		$mobile = $this->reqdata->mobile;
		$mobilecode = $this->reqdata->mobilecode;
		if(!preg_match("/^[1][3-8]\d{9}$/",$mobile)){
			$this->error(117,'手机号错误');
		}
		$ms =& ms(); //连接用户中心
		$r = $ms->user->check_phone($user_id,$mobile);
		if(!$r){
			$this->error(118,'手机号已被注册');
		}
		$db = m('mobilecode');
		$oldcode = $db->get(array('conditions'=>"mobile='$mobile'"));
		if(!$oldcode){
			$this->error(121,'手机验证码错误');
		}
		if($oldcode['code']!=$mobilecode){
			$this->error(121,'手机验证码错误');
		}
		$result = $ms->user->edit($user_id, '', array(
				'safephone' => $mobile
		),1);
		if($result){
			$this->success(array('info'=>'绑定成功'));
		}else{
			$this->error(0,'绑定失败');
			
		}
		
	}
	
	function changepwd(){
		if(!$this->visitor->has_login){
			$this->error(108,'请登录');
		}
		$orig_password = trim($this->reqdata->oldpwd);
		if(empty($orig_password)){
			$this->error(104,'请输入原密码');
		}
		$new_password = trim($this->reqdata->newpwd);
		if(empty($new_password)){
			$this->error(104,'请输入新密码');
		}
		$uid = $this->visitor->get('user_id');
		/* 修改密码 */
		$ms =& ms();    //连接用户系统
		$result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
				'password'  => $new_password
		));
		if (!$result)
		{
			$error = $ms->user->get_error();
			/* 修改不成功，显示原因 */
			$this->error(114,$error[0]['msg']);
		
		}else{
			$db = m('usertoken');
			$db->db->query("delete from ecm_user_token where uid=$uid");
			$this->success();
		}
	}
	
	function logout(){
		if($this->visitor->has_login){
			$this->_do_logout();
		}
		$this->success();
	}
	
	function register(){
		$mobile = $this->reqdata->mobile;
		$mobile = trim($mobile);
		$user_name = $this->reqdata->username;
		$password = $this->reqdata->password;
		$email = $this->reqdata->email;
		$code = $this->reqdata->mobilecode;
		$passlen = strlen($password);
		$user_name_len = strlen($user_name);
		if(!preg_match("/^[1][3-8]\d{9}$/",$mobile)){
			$this->error(117,'手机号错误');
		}
		if ($user_name_len < 3 || $user_name_len > 25)
		{
			$this->error(120,'用户名长度3-25字符');
			return;
		}
		if ($passlen < 6 || $passlen > 20)
		{
			$this->error(120,'密码长度6-20字符');
			return;
		}
		if (!is_email($email))
		{
			$this->error(120,'邮箱错误');
		
			return;
		}
		
		$ms =& ms(); //连接用户中心
		$r = $ms->user->check_phone(1,$mobile);
		if(!$r){
			$this->error(118,'手机号已被注册');
		}
		$db = m('mobilecode');
		$oldcode = $db->get(array('conditions'=>"mobile='$mobile'"));
		if(!$oldcode){
			$this->error(121,'手机验证码错误');
		}
		if($oldcode['code']!=$code){
			$this->error(121,'手机验证码错误');
		}
		$user_id = $ms->user->register($user_name, $password, $email);
		if (!$user_id)
		{
			$this->error(120,$ms->user->get_error());
		
			return;
		}
		$result = $ms->user->edit($user_id, $password, array(
				'safephone' => $mobile
		));
		$this->success('注册成功');
	}
	
	function sendcode(){
		$mobile = $this->reqdata->mobile;
		$sendtype = $this->reqdata->type;
		$sendtype = $sendtype?$sendtype:'register';
		$mobile = trim($mobile);
		if(!preg_match("/^[1][3-8]\d{9}$/",$mobile)){
			$this->error(117,'手机号错误('.$mobile.')');
		}
		$getaddress = $mobile;
		$ms =& ms();
		if($sendtype=='register'){
			$r = $ms->user->check_phone(1,$mobile);
			if(!$r){
				$this->error(118,'手机号已被注册');
			}
		}elseif($sendtype=='resetpwd'){
			$memberdb = m('member');
			$user = $memberdb->get(array('condition'=>" safephone='$mobile' "));
			if(!$user){
				$this->error(117,'不存在该手机号的用户');
			}
		}elseif($sendtype=='bindphone'){
			if(!$this->visitor->has_login){
				$this->error(108,'请登录后再绑定手机');
			}
			$r = $ms->user->check_phone($this->visitor->get('user_id'),$mobile);
			if(!$r){
				$this->error(118,'手机号已被注册');
			}
		}else{
			$this->error(122,'type值错误');
		}
		
		
		$code = $this->make_code();
		import('HTTP_SDK');
		$cpid = 'jinrongjiewuye';
    	$cppsw = '123456';
    	$engine = HTTP_SDK::getInstance($cpid,$cppsw);
    	if($sendtype=='register'){
    		$content = '【96018】欢迎您注册金融街生活在线，请输入以下验证码完成注册，您的验证码为'.$code.'，请您在十分钟内进行提交，您正在用手机绑定您的账户，为了保护您的账号安全，请勿泄露。【96018金融街生活在线】';
    	}else{
    		$content = '【96018】尊敬的用户您的短信验证码为'.$code.'，为了保护您的账号安全，请勿泄露。【96018金融街生活在线】';
    	}
    	
		$rusult = $engine->pushMt($getaddress,'1', $content,  0);
		
		$db = m('mobilecode');
		$oldcode = $db->get(array('conditions'=>"mobile='$mobile'"));
		
		if($oldcode){
			$db->edit($oldcode['id'],array('`code`'=>$code));
		}else{
			$db->add(array('mobile'=>$mobile,'`code`'=>$code));
		}
		$this->success('发送成功');
		
	}
	
	function make_code()
	{
		$chars = '23456789';
		$code = '';
		for ( $i = 0; $i < 6; $i++)
		{
		$code .= $chars[ mt_rand(0, strlen($chars) - 1) ];
		}
		return $code;
	}
	
	function getinfo(){
		$token = '';
		if($this->visitor->has_login){
			$token = $this->reqdata->token;
			$this->visitor->info['haslogin'] = 1;
		}else{
			$this->visitor->info['haslogin'] = 0;
		}
		
		$this->visitor->info['token'] = $token;
		$this->success((array)$this->visitor->info);
	}
	
	function message(){
		if(!$this->visitor->has_login){
			$this->error(108,'请登录后评价');
		}
		$user_id = $this->visitor->get('user_id');
		$starttime = $this->reqdata->starttime;
		$endtime = $this->reqdata->endtime;
		$page = $this->reqdata->page?intval($this->reqdata->page):0;
		$limit = $this->reqdata->pagesize?intval($this->reqdata->pagesize):15;
		$start = $page*$limit;
		$condition = '';
		if($starttime){
			$starttime = strtotime($starttime);
			$condition.= 'and addtime>='.$starttime;
		}
		if($endtime){
			$endtime = strtotime($endtime);
			$endtime = $endtime + 24*60*60;
			$condition.= 'and addtime>='.$starttime;
		}
		$model_message =& m('message');
		$messages = $model_message->find(array(
				'fields'        =>'this.*',
				'conditions'    => 'parent_id=0 '.$condition,
				'count'         => true,
				'limit'         => "$start,$limit",
				'order'         => 'last_update DESC',
		));
		$count = $model_message->getCount();
		if (!empty($messages))
		{
			foreach ($messages as $key => $message)
			{
				$messages[$key]['new'] = (($message['from_id'] == $user_id && $message['new'] == 2)||($message['to_id'] == $user_id && $message['new'] == 1 )) ? 1 : 0; //判断是否是新消息
				$subject = $this->removecode($messages[$key]['content']);
				$subject = str_replace('点击购买', '', $subject);
				$messages[$key]['content'] = htmlspecialchars($subject);
				$message['from_id'] == MSG_SYSTEM && $messages[$key]['user_name'] = Lang::get('system_message'); //判断是否是系统消息
			}
		}
		$this->success(array('list'=>$messages,'count'=>$count));
	}
}