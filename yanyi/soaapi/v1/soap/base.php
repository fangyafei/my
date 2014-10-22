<?php
	
	ini_set("soap.wsdl_cache_enabled", "0");
	include_once './../config.php';
	
	//设定允许进行操作的action数组
	$class = 'Base';
	$act_arr = array('getGoodsDetail','getGoodsList','getAllCustomList','indexInfo','getGoodsList1','getCount'); 
	
	$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'getGoodsDetail'; //默认一个执行的方法
	
	//判断一下是否是允许进行的操作
	if (!in_array($action, $act_arr))
	{
		return false;
	}
	
	/*获取首页广告轮播图*/
	if ($action == 'indexInfo')
	{
		$adv = include_once ROOT_PATH.'/data/page_config/app.index.config.php';
		$index_adv = $adv['widgets'];
		if (!$index_adv)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'请求数据失败');
			echo $json->encode($arr); die;
		}
		echo $json->encode($index_adv); die;
		/* $arr = array("ad"=>$index_adv);
		$rs = getSoapClients($class, $action, $arr); */
		//die($rs);
	}
	
	/*获取用户各项统计数据*/
	elseif ($action == 'getCount')
	{
		$data = _g();
		$token      = $data->token;
		if (!$token)
		{
			$arr = array('statusCode'=>2,'msg'=>'用户尚未登录');
			echo $json->encode($arr); die;
		}
		 $arr = array('token'=>$token);
		 $rs = getSoapClients($class, $action, $arr); 
		die($rs);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	?>