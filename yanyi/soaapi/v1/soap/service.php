<?php
ini_set("soap.wsdl_cache_enabled", "0");
include_once './../config.php';

//设定允许进行操作的action数组
$class = 'service';
$act_arr = array('login','getUserInfo','register','payByJinCaiCard','getMyGoodsList','verifyPhoneNum','resetPassword','execAccountChange','modifyPassWord','modifyNickName','getMyCommentList','editUserInfo','getActivityList','getActivity','getUserAction','signUp','getCommentList','getCollect','feedback','getConsigneeList','operatConsignee','modifyUserData','getVersionInfo','getAddrAndPostalCode','getCus');
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'login';

//http://local.rc.com/soaapi/soap/user.php?act=modifyPassWord&oldPassWord=123456&newPassWord=admin123 示例

//判断一下是否是允许进行的操作
if (!in_array($action, $act_arr))
{
	//返回false
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
else if($action == 'info')
{
	$arr=array('idserve'=>$data->idserve);
}
else if($action=='employee')
{
	$arr=array('token'=>$data->token,
	'pageSize'=>$data->pageSize,
	'pageIndex'=>$data->pageIndex
	);
}else if($action == 'index'){
	$arr=array(
	'pageSize'=>$data->pageSize,
	'pageIndex'=>$data->pageIndex,
	'lat'=>$data->lat,
	'lng'=>$data->lng,
	);
}else if($action == 'employee_add')
{
	$arr=array(
	'token'=>$data->token,
	'employee_name'=>$data->employee_name,
	'sex'=>$data->sex,
	'job_number'=>$data->job_number,
	'mobile'=>$data->mobile,
	'id_number'=>$data->id_number,
	'mark'=>$data->mark,
	'head_img'=>isset($_FILES['head_img'])?$_FILES['head_img']['tmp_name']:'',
	);
}

else if($action == 'employee_edit')
{
	$data->head_img=isset($_FILES['head_img'])?$_FILES['head_img']['tmp_name']:'';
	$arr=array(
	'data'=>$data,
	);
}

else if($action == 'employee_drop')
{
	$arr=array(
		'token'=>$data->token,
		'idemployee'=>$data->idemployee,
	);
}else if($action == 'figureorder')
{
	$arr=array(
		'token'=>$data->token,
		'pageSize'=>$data->pageSize,
		'pageIndex'=>$data->pageIndex
	);
}else if($action == 'applyserver')
{
	$arr=array(
		'data'=>$data,
	);
}else if($action == 'qrcode')
{
	$arr=array(
		'idemployee'=>$data->idemployee,
	);
	
}
else if($action == 'servedetail')
{
	$data->newheadImg=isset($_FILES['newheadImg'])?$_FILES['newheadImg']['tmp_name']:'';
	$arr=array(
		'data'=>$data,
	);

}
else if($action == 'PassengerList')
{
	$arr=array('token'=>$data->token,
	'pageSize'=>$data->pageSize,
	'pageIndex'=>$data->pageIndex
	);
}else if($action == 'figureorderlog')
{
	$arr=array('token'=>$data->token,
	'pageSize'=>$data->pageSize,
	'pageIndex'=>$data->pageIndex
	);
}else if($action == 'figureadd')
{
	
	
	
	$arr=array(
		'data'=>$data,
	);
	
	
	
}

/*获得服务点的预约顾客*/
elseif ($action == 'getCus')
{
	$arr=array(
	'token'=>$data->token,
	'pageSize'=>$data->pageSize,
	'pageIndex'=>$data->pageIndex,
	'status'=>$data->status,
	);
}
$rs = getSoapClients($class, $action, $arr);
die($rs);	



