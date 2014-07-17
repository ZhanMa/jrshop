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
}