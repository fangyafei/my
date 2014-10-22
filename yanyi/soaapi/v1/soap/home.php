<?php
ini_set("soap.wsdl_cache_enabled", "0");
include_once './../config.php';

//设定允许进行操作的action数组
$class = 'Goods';
$act_arr = array('getHome');
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'getHome';

//判断一下是否是允许进行的操作
if (!in_array($action, $act_arr))
{
	//返回false
}

//获取首页
if ($action == 'getHome')
{
	$arr = array(
			'parameter'=>''
	);

	$rs = getSoapClients($class, $action, $arr);

	die($rs);
	

}
else
{
	echo "Lack of method ?action";
}



?>