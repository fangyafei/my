<?php
	
	ini_set("soap.wsdl_cache_enabled", "0");
	include_once './../config.php';
	
	//设定允许进行操作的action数组
	$class = 'Goods';
	$act_arr = array('getGoodsData','getGoodsList','getCategoryList','queryGoodsList','getGoodsDetail','operateCollect','getCollect','juxtaposeToOth','getOrder','getThemeType','getThemeList','loveTheme','getThemeDetail','saveDesign','addThemeComment','getThemeComment','getRecGoodsList','getGoodsDetail','addCstCollect','delCstCollect','delCstCollect','getCstCollect','loveCus','getRelGoods','getGoodsListNew'); //示例，开发时换成实际的方法名，以及陆续补充在这个数组里
	
	
	
	$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'getGoodsData'; //默认一个执行的方法
	
	//判断一下是否是允许进行的操作
	if (!in_array($action, $act_arr)){
		return false;
	}
	
	//获取某个商品的数据
	
	if ($action == 'getGoodsData'){
		$data= _g();
	}
	
	/*获取分类列表*/
	elseif ($action == 'getCategoryList'){
		global $json;
		
		$arr = array();
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取某个基本款的详细信息*/
	elseif ($action == 'getGoodsDetail')
	{
		$cstId = _g("cstId","int");
	
		/*参数验证*/
		if (empty($cstId))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'该基本款不存在');
			echo $json->encode($arr); die;
		}
	
		$arr = array('cstId'=>$cstId);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取基本款的全部列表*/
	elseif($action == 'getGoodsList')
	{
		$data = _g();
		$type = $data->type;
		$pageSize = $data->pageSize;
		$pageIndex = $data->pageIndex;
		/* 参数验证 */
		if (empty($type))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'请先选择分类');
			echo $json->encode($arr); die;
		}
		$arr = array(
				'type' =>$type,
				'pageSize'	=> $pageSize,
				'pageIndex'	=> $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取基本款的全部列表*/
	elseif($action == 'getGoodsListNew')
	{
		$data = _g();
		$type = $data->type;
		$pageSize = $data->pageSize;
		$pageIndex = $data->pageIndex;
		/* 参数验证 */
		if (empty($type))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'请先选择分类');
			echo $json->encode($arr); die;
		}
		$arr = array(
				'type' =>$type,
				'pageSize'	=> $pageSize,
				'pageIndex'	=> $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取基本款的相关列表*/
	elseif ($action == 'getRelGoods')
	{
		$data = _g();
		$id = $data->id;
		/* 参数验证 */
		if (!$id)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'id is empty');
			echo $json->encode($arr); die;
		}
		$arr = array(
				'id' =>$id,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取基本款的推荐列表*/
	elseif ($action == 'getRecGoodsList')
	{
	
		$arr = array();
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取订单详情*/
	elseif ($action == 'getOrder')
	{
		$data = _g();
		$token = $data->token;
		$orderId = $data->orderId;
		if(empty($token))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'token not empty');
		
			echo $json->encode($arr); die;
		}
		if (empty($orderId))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'orderId not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'token' 		=>$token,
				'orderId'        =>$orderId,
		);
		
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取主题分类*/
	elseif ($action == 'getThemeType')
	{
		
		$cat=array(
				'1'=>'婚庆系列',
				'2'=>'校园系列',
				'3'=>'职场系列',
				'4'=>'休闲系列',
				'5'=>'儿童系列',
				'6'=>'明星同款',
		);
		
		echo $json->encode($cat);die();
	}
	
	/*获取主题下的基本款列表*/
	elseif ($action == 'getThemeList')
	{
		$data = _g();
		$type = $data->type;
		if (!$type)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'type not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'type' => $type,
		);
		
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取某个主题的详情*/
	elseif ($action == 'getThemeDetail')
	{
		$data = _g();
		$id = $data->id;
		if (!isset($data->token))
		{
			$token = '';
		}
		else 
		{
			$token = $data->token;
		}
		
		if (!$id)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'id' => $id,
				'token'=>$token,
				
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
		
	}
	/*对主题喜欢的操作*/
	elseif ($action == 'loveTheme')
	{
		$data = _g();
		$id = $data->id;
		$token = $data->token;
		if (!$token)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		if (!$id)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'id' => $id,
				'token'=>$token,
		);
		
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*发表主题评论*/
	elseif ($action == 'addThemeComment')
	{
		$data = _g();
		$content = $data->content;
		$token = $data->token;
		$id    = $data->id;
		if (!$token)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		if (!$content)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'content not empty');
			echo $json->encode($arr); die;
		}
		if (!$id)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		$arr = array(
				'token' => $token,
				'content'=>$content,
				'id' => $id,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取主题评论列表*/
	elseif ($action == 'getThemeComment')
	{
		$data = _g();
		$id = $data->id;
		$pageSize = $data->pageSize;
		$pageIndex = $data->pageIndex;
		if (!$id)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageSize)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageIndex)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'pageIndex not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'id' => $id,
				'pageSize' => $pageSize,
				'pageIndex' => $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*添加基本款的收藏*/
	elseif ($action == 'addCstCollect')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
			$cstId  = $data->cstId;
		}
		
		if (!$token)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		
		if (!$cstId)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'cstId not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'token' => $token,
				'cstId' => $cstId,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*取消收藏*/
	elseif ($action == 'delCstCollect')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
			$cstId  = $data->cstId;
		}
		
		if (!$token)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		
		if (!$cstId)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'cstId not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'token' => $token,
				'cstId' => $cstId,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取基本款收藏列表*/
	elseif ($action == 'getCstCollect')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
		}
		
		if (!$token)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'token' => $token,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*基本款喜欢接口*/
	elseif ($action == "loveCus")
	{
		$data = _g();
		if ($data)
		{
			$id  	  = $data->id;
			$token    = $data->token;
		}
		if (!$id)
		{
	
			$arr = array('statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		if (!$token)
		{
	
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		$arr = array
		(
				'id' => $id,
				'token'   => $token,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	

?>