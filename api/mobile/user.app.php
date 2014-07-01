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