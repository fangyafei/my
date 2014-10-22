<?php
ini_set("soap.wsdl_cache_enabled", "0");
include_once './../config.php';


/*订餐接口
*Hotdish	--热菜系列
*Colddish	--凉菜系列
*Drinks		--酒水系列
*Pastry		--糕点系列
*/

//设定允许进行操作的action数组
$class = 'User';
$act_arr = array('Hotdish','Colddish','Drinks','Pastry','getUserPasswd');
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'UserLogin';



//判断一下是否是允许进行的操作
if (!in_array($action, $act_arr))
{
	//返回false
	return false;
}

$ResName	= _g('ResName','str');
$PageId		= _g('PageId','int');
$type		= _g('type','str');
$xtype		= _g('xtype','int');
$cat_id		= _g('cat_id','int');


//会员登录
if ($action == 'Hotdish')
{
	$res_rc  = array('cat_id'=>$cat_id,
					 'xtype'=>$xtype,
					 'PageId'=>$PageId,
					 'type'=>$type,
					 'ResName'=>$ResName);	
	$rs = getSoapClients($class, $action, $res_rc);
	die($rs);
}
elseif($action == 'Colddish'){
	$res_rc  = array('cat_id'=>$cat_id,
					 'xtype'=>$xtype, 
					 'PageId'=>$PageId,
					 'type'=>$type,
					 'ResName'=>$ResName);	
	
	$rs = getSoapClients($class, $action, $res_rc);
	die($rs);

}
elseif($action == 'Drinks'){
	$res_rc  = array('cat_id'=>$cat_id,
					 'xtype'=>$xtype,
					 'PageId'=>$PageId,
					 'type'=>$type,
					 'ResName'=>$ResName);	
	
	$rs = getSoapClients($class, $action, $res_rc);
	die($rs);

}
elseif($action == 'Pastry'){
	$res_rc  = array('cat_id'=>$cat_id,
					 'xtype'=>$xtype,
					 'PageId'=>$PageId,
					 'type'=>$type,
					 'ResName'=>$ResName);	
	
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
	print_r($rs);
}



?>