<?php
	
	ini_set("soap.wsdl_cache_enabled", "0");
	include_once './../config.php';
	
	//设定允许进行操作的action数组
	$class = 'Order';
	$act_arr = array('getOrderData','cancelOrder', 'getOrderList', 'getOrder', 'submitOrder','getFigure','getService','getIsdiy','resOrder'); //示例，开发时换成实际的方法名，以及陆续补充在这个数组里
	$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'getOrderData'; //默认一个执行的方法
	
	//判断一下是否是允许进行的操作
	if (!in_array($action, $act_arr))
	{
		return false;
	}
	
	//获取订单数据
	if ($action == 'getOrderData')
	{
		
	}
	elseif($action == "getOrderList")
	{
		$data= _g();
	
		$user_token = $data->token;
		$condition = $data->condition;
		//$user_token = 1;
		//$condition = 0;
		if(empty($user_token))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'token not empty');
		
			echo $json->encode($arr); die;
		}
		
		/* 参数验证 */
		if (!isset($condition)){
			$arr = array( 'statusCode'=>1,'msg'=>'condition not empty');
		
			echo $json->encode($arr); die;
		}
		
		$userId = getSoapClients("User", "getUserId", array('user_token' => $user_token));
		//$userId = 28;
		if(empty($userId))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'token is fault');
		
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'userId' => $userId,
				'condition' => $condition
		);
		
		$rs = getSoapClients($class, $action, $arr);
		
		//die(print_R(json_decode($rs,true)));
		die($rs);
		
	}
	
	elseif($action == "getOrder")
	{
		$data= _g();
		$orderId = intval($data->orderId);
		//$orderId = 2;
		if(empty($orderId)){
			$arr = array( 'statusCode'=>1,'msg'=>'orderId not empty');
			
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'orderId' => $orderId,
		);
		
		$rs = getSoapClients($class, $action, $arr);
		
		die($rs);
	}
	elseif($action == "submitOrder")
	{
		$data= _g();
		$token      = $data->token;                        //用户token
		$invoiceneed = $data->invoiceneed;				   //是否需要索要发票 0 不需要  1需要
// 		$consigneeId= $data->consigneeId;                  //收货信息ID
		$shipping   = 5;
		$payment    = 1;

		if (!isset($data->remark))
		{
			$remark = "";
		}
		else
		{
			$remark = trim($data->remark);
		}
		
		/*发票*/
		if (!in_array($invoiceneed, array(0,1)))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'invoiceneed is 0 or 1');
			echo $json->encode($arr); die;
		}
		
		/*发票类型*/
		if (!isset($data->invoicetype))
		{
			$invoicetype = "";
		}
		else
		{
			$invoicetype = trim($data->invoicetype);
			if (!in_array($invoicetype, array('公司','个人')))
			{
				$arr = array( 'statusCode'=>1,'msg'=>"invoicetype is only 公司 or 个人");
				echo $json->encode($arr); die;
			}
		}
		/*发表内容*/
		if (!isset($data->invoicecontent))
		{
			$invoicecontent = "";
		}
		else
		{
			$invoicecontent = trim($data->invoicecontent);
			if (!in_array($invoicecontent, array('服装')))
			{
				$arr = array( 'statusCode'=>1,'msg'=>'invoicecontent is only 服装');
				echo $json->encode($arr); die;
			}
		}
		/*发票抬头*/
		if (!isset($data->invoicetitle))
		{
			$invoicetitle = "";
		}
		else
		{
			$invoicetitle = trim($data->invoicetitle);
		}
		/*如果invoiceneed是1 invoicetype和invoicecontent必须传值*/
		if ($invoiceneed == 1)
		{
			if (!$invoicetype || !$invoicecontent)
			{
				$arr = array( 'statusCode'=>1,'msg'=>'你选择了索要发票 所以发票内容和发票类型必须填写');
				echo $json->encode($arr); die;
			}
		}
		/*详细地址*/
		if (!isset($data->address))
		{
			$address = "";
		}
		else
		{
			$address = trim($data->address);
		}
		/*-1 上门量体 -2就近服务点量体*/
		if (!isset($data->figure))
		{
			$figure = 0;
		}
		else
		{
			$figure = intval($data->figure);
		}
		/*手机号码*/
		if (!isset($data->mobile))
		{
			$mobile = "";
		}
		else
		{
			$mobile = $data->mobile;
		}
		/*真实姓名*/
		if (!isset($data->realname))
		{
			$realname = "";
		}
		else
		{
			$realname = trim($data->realname);
		}
		/*region-id*/
		if (!isset($data->region_id))
		{
			$region_id = "";
		}
		else
		{
			$region_id = trim($data->region_id);
		}
		/*收货地址*/
		if (!isset($data->region_name))
		{
			$region_name = "";
		}
		else
		{
			$region_name = trim($data->region_name);
		}
		/*预约时间*/
		if (!isset($data->retime))
		{
			$retime = "";
		}
		else
		{
			$retime = trim($data->retime);
		}
		/*服务点id*/
		if (!isset($data->service))
		{
			$service = "";
		}
		else
		{
			$service = trim($data->service);
		}
		
		
		/*必填参数*/
	 	if(empty($token)){
	 		$arr = array( 'statusCode'=>1,'msg'=>'token not empty');
	 		echo $json->encode($arr); die;
	 	}
	 	
	 	
	 	/* if(empty($consigneeId)){
	 		$arr = array( 'statusCode'=>1,'msg'=>'consigneeId not empty');
	 		echo $json->encode($arr); die;
	 	} */
// echo $mobile;exit;		
		$arr = array(
				'token'   => $token,     //会员
// 				'consigneeId' => $consigneeId,   //收货信息ID
				'shipping'    => $shipping,      //配送方式
				'payment'     => $payment,       //支付方式
		        'remark'      => $remark,        //备注
		        'address'     => $address,
				'figure'      => $figure,
				'mobile'      => $mobile,
				'realname'    => $realname,
				'region_id'   => $region_id,
				'region_name' => $region_name,
				'retime'      => $retime,
				'service'     => $service,
				'invoiceneed' => $invoiceneed,
				'invoicetype' => $invoicetype,
				'invoicecontent' => $invoicecontent,
				'invoicetitle' => $invoicetitle,
		);
// print_exit($arr);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	elseif($action == "cancelOrder"){
		$data= _g();
		
		$user_token = $data->token;
		$order_id = $data->orderId;
		// 	$pageSize = $data->pageSize;
		// 	$pageIndex = $data->pageIndex;
		//$user_token = 1;
		//$order_id = 1;
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
		
		//$userId = 28;
		if(empty($userId))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'token is fault');
		
			echo $json->encode($arr); die;
		}
		
		if(empty($order_id))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'order_id is fault');
			
			echo $json->encode($arr); die;
		}
		$arr = array(
		// 			'pageSize'=>$pageSize,
		// 			'pageIndex'=>$pageIndex,
				'userId' => $userId,
				'orderId' => $order_id
				
		);
		
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获得会员的量体数据*/
	elseif ($action == 'getFigure')
	{
		$data = _g();
		if ($data)
		{
			$token    = $data->token;
		}
		if (!$token)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		$arr = array
		(
				'token'   => $token,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*根据regionid获得 对应的服务点*/
	elseif ($action == 'getService')
	{
		$data = _g();
		if ($data)
		{
			$id    = $data->id;
		}
		if (!$id)
		{
	
			$arr = array('statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		$arr = array
		(
				'id'   => $id,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*查询购物车中是否存在我要量体的选项*/
	elseif ($action == 'getIsdiy')
	{
		$data= _g();
		$token      = $data->token;
		
		if (!$token)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		$arr = array
		(
				'token'   => $token,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	elseif ($action == 'resOrder')
	{
		$data= _g();
		$order_id      = $data->order_id;
		$notify_result      = $data->notify_result;
		if (!$order_id)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'order_id not empty');
			echo $json->encode($arr); die;
		}
		if (!$notify_result)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'notify_result not empty');
			echo $json->encode($arr); die;
		}
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	else
	{
		echo "Lack of method ?action";
	}
?>