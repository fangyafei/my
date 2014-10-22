<?php
ini_set("soap.wsdl_cache_enabled", "0");
include_once './../config.php';

//设定允许进行操作的action数组
$class = 'experience';
$act_arr = array('test');
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'login';

//http://local.rc.com/soaapi/soap/user.php?act=modifyPassWord&oldPassWord=123456&newPassWord=admin123 示例

//判断一下是否是允许进行的操作
if (!in_array($action, $act_arr))
{
	//返回false
	//return false;
}

$data=_g();

if($action == 'test')
{
	
	$userName = _g('phoneNum','str');
	$passWord = _g('passWord','str');
	$arr = array(
			'user_name'=>$userName,
			'password'=>$passWord
	);
}
elseif($action == 'topad')
{
	$arr=array('type'=>$data->type);
}

$rs = getSoapClients($class, $action, $arr);
die($rs);	



