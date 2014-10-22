<?php
ini_set("soap.wsdl_cache_enabled", "0");
include_once './../config.php';

//设定允许进行操作的action数组
$class = 'club';
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
}else if($action=='noticeuser')
{
	$arr=array(
	'token'=>$data->token,
	'targetid'=>$data->targetid,
	'state'=>$data->state
	);
}else if($action=='fansList')
{
	$arr=array(
	'userid'=>$data->userid,
	'pageSize'=>$data->pageSize,
	'pageIndex'=>$data->pageIndex,
	'token' => isset($data->token)?$data->token:''
	);
}else if($action=='noticeList')
{
	$arr=array(
	'userid'=>$data->userid,
	'pageSize'=>$data->pageSize,
	'pageIndex'=>$data->pageIndex,
	'token' => isset($data->token)?$data->token:''
	);
}
else if($action=='getUserInfo')
{
	$arr=array(
	'data'=>$data,
	);
	
	
}
else if($action=='setUserInfo')
{
	$arr=array(
	'data'=>$data,
	);
}
else if($action=='feedback')
{
	$arr=array(
	'token'=>$data->token,
	'content'=>$data->content,
	);
}
else if($action=='messageList')
{
	$arr=array(
	'token'=>$data->token,
	'pageSize'=>$data->pageSize,
	'pageIndex'=>$data->pageIndex
	);
}
else if($action=='checkVersion')
{
	$arr=array(
	'devicetype'=>$data->devicetype,
	'version'=>$data->version,
	
	);
}
else if($action=='serverRate')
{
	$arr=array(
	'token'=>$data->token,
	'serverid'=>$data->serverid,
	'star'=>$data->star,
	'desc'=>$data->desc,
	);
}

else if($action=='RatingInfo')
{
	$arr=array(

	'serverid'=>$data->serverid,
	);
}

else if($action=='RatingList')
{
	$arr=array(
	'serverid'=>$data->serverid,
	'type'=>$data->type,
	'pageSize'=>$data->pageSize,
	'pageIndex'=>$data->pageIndex
	);
}

else if($action=='getregionlist')
{
	$arr=array(
		'parent_id'=>$data->parent_id
	);
}



$rs = getSoapClients($class, $action, $arr);
die($rs);	



