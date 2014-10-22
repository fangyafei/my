<?php
class Session{
// 	public $config = array('name'=>'','path'=>'','domain'=>'','expire'=>'','use_trans_sid'=>'','use_cookies'=>'','type'=>'');
	// session管理函数
	function Session($name = array(),$value = '') {
// 		if(!$name){
// 			$name = $this->config;
// 		}
// 		if(isset($_REQUEST[C('VAR_SESSION_ID')])){//SESSION 共享
// 			session_id($_REQUEST[C('VAR_SESSION_ID')]);
// 		}elseif(isset($name['id'])) {
// 			session_id($name['id']);
// 		}
// 		ini_set('session.auto_start', 0);//session.auto_start = on时，执行 session_start() 将产生新的 session_id
		//session文件中会有session_name元素
// 		if(isset($name['name'])) session_name($name['name']);
		//SESSION文件存储路径
// 		if(isset($name['path'])) session_save_path($name['path']);
		//域名
// 		if(isset($name['domain'])) ini_set('session.cookie_domain', $name['domain']);
		//生存周期
// 		if(isset($name['expire'])) ini_set('session.gc_maxlifetime', $name['expire']);
//		SESSION也是用COOKIE，一但浏览器禁用了，就需要用URL传递
// 		if(isset($name['use_trans_sid'])) ini_set('session.use_trans_sid', $name['use_trans_sid']?1:0);
// 		if(isset($name['use_cookies'])) ini_set('session.use_cookies', $name['use_cookies']?1:0);

		// 启动session,session初始化在session_start 之前调用
		session_start();
	}
	
	function setSession($userInfo){
		$_SESSION[SESS_KEY_NAME] = $userInfo;
	}
	
	function getSession(){
		return $_SESSION[SESS_KEY_NAME] ;
	}

	function getValue($key){
		return $_SESSION[SESS_KEY_NAME][$key] ;
	}
	
	function setValue($key,$value){
		return $_SESSION[SESS_KEY_NAME][$key] = $value ;
	}
	
	function destory(){
// 		session_write_close()
// 		session_regenerate_id();
// 		unset($_SESSION[$name]);
		session_unset();
		session_destroy();
	}
			

	
}
?>