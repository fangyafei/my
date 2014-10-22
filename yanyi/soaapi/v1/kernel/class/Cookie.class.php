<?php
class Cookie{
	// Cookie 设置、获取、删除
	function cookie($name, $value='', $option=null) {
	}
	
	function setCook($userInfo){
		$t = time() + COOKIE_EXPIRE;
		setcookie('saveuser',$userInfo['username'],$t,COOKIE_PATH,COOKIE_DOMAIN);
		foreach($userInfo as $k=>$v){
// 			if($k == 'password')continue;
// 			if($k == 'pwd')continue;
// 			if($k == 'ps')continue;
			setcookie($k,$v,$t,COOKIE_PATH,COOKIE_DOMAIN);
		}
	}
	
	function getCook(){
		return $_COOKIE;
	}
	
	function getValue($key){
		return $_COOKIE[$key];
	}
	
	function setValue($key,$value){
		setcookie($key,$value,time() + 3600,COOKIE_PATH,COOKIE_DOMAIN);
	}
	
	function destory(){
		foreach($_COOKIE as $k=>$v){
			if($k == 'saveuser')continue;
			setcookie($k,$v,time() - 3600,COOKIE_PATH,COOKIE_DOMAIN);
			unset($_COOKIE[$k]);
		}
	}
	
}
?>