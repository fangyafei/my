<?php
	ini_set("soap.wsdl_cache_enabled", "0");
	include_once './../config.php';
	
	//设定允许进行操作的action数组
	$class = 'Webserver';
	$act_arr = array('login','register');
	$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'login,register,sendCode';
	
	//http://local.rc.com/soaapi/soap/user.php?act=modifyPassWord&oldPassWord=123456&newPassWord=admin123 示例
	
	//判断一下是否是允许进行的操作
	if (!in_array($action, $act_arr))
	{
		//返回false
	}
	
	//会员登录
	if ($action == 'login')
	{
		/*userName	String	必填	用户名，4-16个字母或数字
		  passWord	String	必填	密码，6-16个字母或数字*/
		
		$userName = _g('phoneNum','str');
		$passWord = _g('passWord','str');
		/* 参数验证 */
		if (empty($userName) || empty($passWord))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'手机号码和密码不能为空');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'user_name'=>$userName,
				'password'=>$passWord
		);
// 	print_exit($arr);
		$rs = getSoapClients($class, $action, $arr);
	
		die($rs);
	}
	
	elseif ($action == 'register') //默认手机号码注册
	{
		/*Email	String	可选	正常可用的email地址
		userName	String	必填	用户名，4-16个字母或数字
		passWord	String	必填	密码，6-16个字母或数字*/
	
		$phoneNum = _g('phoneNum','str');
		$passWord = _g('passWord','str');
		
		$nickname = _g('nickname','str');
		
		/*检测手机合法性*/
		if(!preg_match('/^1[3458][0-9]{9}$/',$phoneNum)){
	       $arr = array( 'statusCode'=>1,'msg'=>'手机号码不正确，请重新输入');
			echo $json->encode($arr); die;
	   	}
	   	if(empty($passWord)){
	   		$arr = array( 'statusCode'=>1,'msg'=>'密码不能为空');
	   		
	   		echo $json->encode($arr); die;
	   	}
		
		$arr = array(
				'mobile'=>$phoneNum,
				'password'=>$passWord,
				'nickname'=>$nickname,				
		);
		
		$rs = getSoapClients($class, $action, $arr);
		die($rs);	
	}
	
	elseif ($action == 'sendCode')
	{
		$data = _g();
		$phoneNum		= $data->phoneNum;
		/*检测手机合法性*/
		if(!preg_match('/^1[3458][0-9]{9}$/',$phoneNum)){
			$arr = array( 'statusCode'=>1,'msg'=>'手机号码不正确，请重新输入');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'mobile'=>$phoneNum,
		);
		
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	
	
	else
	{
		echo "Lack of method ?action";
	}
	
	

?>