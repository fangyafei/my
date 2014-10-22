<?php
ini_set("soap.wsdl_cache_enabled", "0");
include_once './../config.php';

//设定允许进行操作的action数组
$class = 'User';
$act_arr = array('ac_detail');
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'UserLogin';

//判断一下是否是允许进行的操作
if (!in_array($action, $act_arr))
{
	return false;
}

//菜品详情
if ($action == 'ac_detail')
{
	$Code = _g('Code','str');
	$Name = _g('Name','str');
	
	$res_rc  = array('Code'=>$Code,
					 'Name'=>$Name);
	//echo $Name.$Code;die;
	$rs = getSoapClients($class, $action, $res_rc);
	die($rs);
}
elseif ($action == 'UserRister')
{
	/*Email	String	可选	正常可用的email地址
	userName	String	必填	用户名，4-16个字母或数字
	passWord	String	必填	密码，6-16个字母或数字*/

	$Email = _g('Email','str');
	$userName = _g('userName','str');
	$passWord = _g('passWord','str');

	/* 测试数据 */
// 	$Email = 'm323lg919@163.com';
// 	$userName = 'x4xqw4x4';
// 	$passWord = 'admin123';
	
	/*email 合法性*/
	if ($Email){
	    if (!is_email($Email)){
	        echo 'email errore';
	    }
	}
	
	/* 过滤 用户名特殊字符 */
	if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $userName))
	{
		echo 'email errore';
	}
	
	$arr = array(
			'post_email'=>$Email,
			'post_username'=>$userName,
			'password'=>$passWord
	);
	$rs = getSoapClients($class, $action, $arr);
	die($rs);
	
}
elseif ($action == 'getUserInfo')
{
}
else
{
	$name = 'lirp';
	$pass = '123456';
	$arr = array(
			'name'=>$name,
			'pass'=>$pass
	);
	$rs = getSoapClients($class, $action, $arr);
	die($rs);
}



?>