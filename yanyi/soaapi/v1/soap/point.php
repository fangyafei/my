<?php
ini_set("soap.wsdl_cache_enabled", "0");
include_once './../config.php';

//设定允许进行操作的action数组
$class = 'Point';
$act_arr = array('getMyScores');
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'getMyScores';

//判断一下是否是允许进行的操作
if (!in_array($action, $act_arr))
{
	//返回false
}

//会员登录
if ($action == 'getMyScores')
{
	/*userName	String	必填	用户名，4-16个字母或数字
	  passWord	String	必填	密码，6-16个字母或数字*/
	
	$data= _g();
	
    $user_token = $data->token;
// 	$pageSize = $data->pageSize;
// 	$pageIndex = $data->pageIndex;
   // $user_token = 1;

	if(empty($user_token))
	{
		$arr = array( 'statusCode'=>1,'msg'=>'token not empty');
	
		echo $json->encode($arr); die;
	}	

	/* 参数验证 */
// 	if (empty($pageSize) || empty($pageIndex)){
// 		$arr = array( 'statusCode'=>1,'msg'=>'pageSize or pageIndex not empty');
	
// 		echo $json->encode($arr); die;
// 	}
	
	$userId = getSoapClients("User", "getUserId", array('user_token' => $user_token));
	
	//$userId = 1;
	if(empty($userId))
	{
		$arr = array( 'statusCode'=>1,'msg'=>'token is fault');
	
		echo $json->encode($arr); die;
	}
	
	$arr = array(
// 			'pageSize'=>$pageSize,
// 			'pageIndex'=>$pageIndex,
			'userId' => $userId
	);
	
	
	$rs = getSoapClients($class, $action, $arr);
	
	die($rs);
}
else
{
	echo "Lack of method ?action";
}



?>