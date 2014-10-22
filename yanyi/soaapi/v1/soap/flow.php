<?php
	
	ini_set("soap.wsdl_cache_enabled", "0");
	include_once './../config.php';
	
	//设定允许进行操作的action数组
	$class = 'Flow';
	$act_arr = array('getCartData','getGoodsList','operateCart','getCart','operateCartList','addCart','getConsigneeList','getPayment','getShipping','getCart','delCart','addAddress','editAddress','delAddress','updateCart','myDesignList','myphotoList','addComment','getCommentList','createAlbum','addtoAlbum','photoList','getAlbum','getAlbumDetail','getUserList','kukeIndex','addPhoto','kukeInfo','addImg',"loveJiePai","loveSheJi","editAlbum","delAlbum","setCover","delPhoto",'kukejiepai','addSheji','clearCart','getBasis','setDefAddr','test'); //示例，开发时换成实际的方法名，以及陆续补充在这个数组里
	$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'getCartData'; //默认一个执行的方法
	
	//判断一下是否是允许进行的操作
	if (!in_array($action, $act_arr))
	{
		return false;
	}
	
	//获取会员的购物中的数据
	if ($action == 'getCartData')
	{
	}
	
	/**
	 * 放入商品--添加购物车(根据不同的请求方式给出不同的返回结果)
	 */
	elseif ($action == 'addCart')
	{
		$data = _g();
		$types = array("diy", "dis");
		$goodsId =  $data->goodsId;
		$token	= $data->token;
		//$is_diy = intval($data->is_diy);/*是否需要量体  0不需要量体  1需要量体*/
		//$items	= $data->items;/*对应组件组合*/
		//$imgcode	= $data->imgcode;/*图片地址*/
		//$emb_con	= $data->emb_con;/*刺绣内容*/
		//$total	= $data->total;/*计算的总价格*/
		
		/*选填数据验证开始----------------------------------------------*/
		
		if (!isset($data->is_diy))
		{
			$is_diy = 0;
		}
		else
		{
			$is_diy = intval($data->is_diy);
		}
		
		if (!isset($data->item))
		{
			$items = '';
		}
		else
		{
			$items = $data->item;
		}
		
		if (!isset($data->imgcode))
		{
			$imgcode = '';
		}
		else
		{
			$imgcode = $data->imgcode;
		}
		
		if (!isset($data->total))
		{
			$total = '';
		}
		else
		{
			$total = $data->total;
		}
		
		if (!isset($data->type))
		{
			$type = "diy";
		}
		else 
		{
			$type = trim($data->type);
		}
		
		/*主题id 如果不是从主题系列添加的基本款  则disid为0*/
		if (!isset($data->disid))
		{
			$disid = 0;
		}
		else
		{
			$disid = trim($data->disid);
		}
		/*刺绣内容*/
		if (!isset($data->emb_con))
		{
			$emb_con = "";
		}
		else
		{
			$emb_con = trim($data->emb_con);
		}
		/*如果是大于0的数值 说明此商品已经在购物车中存在,这次操作是修改  如果是0则说明是首次添加购物车*/
		if (!isset($data->rec_id))
		{
			$rec_id = 0;
		}
		else
		{
			$rec_id = intval($data->rec_id);
		}
		/*身高*/
		if (!isset($data->height))
		{
			$height = 0;
		}
		else
		{
			$height = intval($data->height);
		}
		/*体重*/
		if (!isset($data->weight))
		{
			$weight = 0;
		}
		else
		{
			$weight = intval($data->weight);
		}
		/*标准码数据*/
		if (!isset($data->spec))
		{
			$spec = 0;
		}
		else
		{
			$spec = trim($data->spec);
		}
		/*基本款数量 1.0只能为1*/
		if (!isset($data->number))
		{
			$number = 1;
		}
		else 
		{
			$number = intval($data->number);
		}
		/*选填验证结束-------------------------------------------------*/
		
		/*如果选了标准码 也就是$is_dir为0 但是spec为空 则不允许通过*/
		if ($is_diy == 0)
		{
			if (!$spec)
			{
				$arr = array( 'statusCode'=>1,'msg'=>'请选择标准尺码');
				echo $json->encode($arr); die;
			}
		}
		elseif ($is_diy == 1)
		{
			if (!$height || !$weight)
			{
				$arr = array( 'statusCode'=>1,'msg'=>'身高和体重必填');
				echo $json->encode($arr); die;
			}
		}
		
		/*验证非空参数*/
		if (!$goodsId)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'goodsId not empty');
			echo $json->encode($arr); die;
		}
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		/* if (!$items)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'items not empty');
			echo $json->encode($arr); die;
		}
		if (!$imgcode)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'imgcode not empty');
			echo $json->encode($arr); die;
		}
		if (!$total)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'total not empty');
			echo $json->encode($arr); die;
		} */
		if ($is_diy !=0 && $is_diy != 1)
		{
			$arr = array('statusCode'=>1,'msg'=>'is_diy is error');
			echo $json->encode($arr); die;
		}
		
			
		/*检查类型参数是否正确*/
		if(!in_array($type, $types))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'参数错误');
			echo $json->encode($arr); die;
		}
		

		$arr = array(
				'type'	 =>	$type,
				'goodsId'=> $goodsId,
				'token'  => $token,
				'rec_id' => $rec_id,
				'height' => $height,
				'weight' => $weight,
				'spec'   => $spec,
				'number' => $number,
				'is_diy' => $is_diy,
				'items'  => $items,
				'imgcode'=> $imgcode,
				'emb_con'=> $emb_con,
				'total'  => $total,
				'disid'  => $disid,
				);
// print_exit($arr);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
		
	}
	
	/**
	 * 删除购物车商品--删除购物车(根据不同的请求方式给出不同的返回结果)
	 */
	elseif ($action == 'delCart')
	{
		$data = _g();
		$recId		= $data->recId;
		$token      = $data->token;
		if (!$token)
		{
			$arr = array('statusCode'=>2,'msg'=>'用户尚未登录');
			echo $json->encode($arr); die;
		}
		
		if (!isset($data->type))
		{
			$type = "diy";
		}
		else 
		{
			$type = $data->type;
		}
		
		if (!$recId)
		{
			$arr = array('statusCode'=>3,'msg'=>'请先选择商品');
			echo $json->encode($arr); die;
		}
	
		$arr = array(
				'recId'	 => $recId,
				'token'  => $token,
				'type'	 => $type,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	
	}
	
	/**
	 * 清空购物车商品--
	 */
	elseif ($action == 'clearCart')
	{
		$data = _g();
		$token      = $data->token;
		if (!$token)
		{
			$arr = array('statusCode'=>2,'msg'=>'用户尚未登录');
			echo $json->encode($arr); die;
		}
	
		if (!isset($data->type))
		{
			$type = "diy";
		}
		else
		{
			$type = $data->type;
		}
	
		$arr = array(
				'token'  => $token,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	
	}
	
	/*获取购物车列表*/
	elseif ($action == 'getCart')
	{
		$data = _g();
		$token = $data->token;
		$pageSize = $data->pageSize;
		$pageIndex = $data->pageIndex;
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageSize)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageIndex)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
		$arr = array("token"=>$token,'pageSize'=>$pageSize,'pageIndex'=>$pageIndex);
		
		$rs = getSoapClients($class, $action,$arr);
		die($rs);
	}
	
	/**
	 * 修改购物车商品的数量
	 */
	elseif ($action == 'updateCart')
	{
		$data = _g();
		$types = array("diy", "normal");
		$recId = intval($data->recId);
		$goodsId 	= intval($data->goodsId);
		$num     = intval($data->num);/*num是商品现在的总数量 而不是增加的数量*/
		$token      = $data->token;
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'用户尚未登录');
			echo $json->encode($arr); die;
		}
		
		if (!isset($data->type))
		{
			$type = "diy";
		}
		else 
		{
			$type = $data->type;
		}
		
		if (!$num)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'缺少参数num');
			echo $json->encode($arr); die;
		}
		if (!$goodsId)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'缺少参数goodsId');
			echo $json->encode($arr); die;
		}
		if (!$recId)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'缺少参数recId');
			echo $json->encode($arr); die;
		}
			
		/*检查类型参数是否正确*/
		if(!in_array($type, $types))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'type参数错误');
			echo $json->encode($arr); die;
		}
	
	
		$arr = array(
				'type'	 =>	$type,
				'num' => $num,
				'goodsId'=> $goodsId,
				'token'  => $token,
				'recId'  => $recId,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	
	}
	
	/*获取收获地址*/
	elseif ($action == 'getConsigneeList')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
		}
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'用户尚未登录');
			echo $json->encode($arr); die;
		}
		$arr = array("token"=>$token);
		
		$rs = getSoapClients($class, $action,$arr);
		die($rs);
	}
	
	/*添加收获地址*/
	elseif ($action == 'addAddress')
	{
		$data = _g();
		
		if ($data)
		{
			$token = $data->token;
		}
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'用户尚未登录');
			echo $json->encode($arr); die;
		}
		$consignee = $data->consignee;
		$region_id = $data->region_id;
		$region_name = $data->region_name;
		$address   = $data->address;
		$phone_tel = $data->phone_tel;
		$phone_mob = $data->phone_mob;
		$email     = $data->email;
		
		if (!isset($data->al_name))
		{
			$al_name = "";
		}
		else
		{
			$al_name = $data->al_name;
		}
		
		if (!isset($data->zipcode))
		{
			$zipcode = "";
		}
		else
		{
			$zipcode = $data->zipcode;
		}
		
		
		$arr = array(
				"token"=>$token,
				'consignee'=>$consignee,
				'region_id'=>$region_id,
				'region_name'=>$region_name,
				'al_name'	=> $al_name,
				'address'	=> $address,
				'zipcode'	=> $zipcode,
				'phone_tel'	=> $phone_tel,
				'phone_mob'	=> $phone_mob,
				'email'		=> $email,
				);
	
		$rs = getSoapClients($class, $action,$arr);
		die($rs);
	}
	
	/*修改收获地址*/
	elseif ($action == 'editAddress')
	{	
		error_reporting(11);
		$data = _g();
	
		if ($data)
		{
			$token = $data->token;
		}
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'用户尚未登录');
			echo $json->encode($arr); die;
		}
		$consignee = $data->consignee;
		$region_id = $data->region_id;
		$region_name = $data->region_name;
		$address   = $data->address;
		$phone_tel = $data->phone_tel;
		$phone_mob = $data->phone_mob;
		$email     = $data->email;
		$addr_id   = $data->addr_id;
		
		if (!isset($data->al_name))
		{
			$al_name = "";
		}
		else
		{
			$al_name = $data->al_name;
		}
		
		if (!isset($data->zipcode))
		{
			$zipcode = "";
		}
		else
		{
			$zipcode = $data->zipcode;
		}
	
		$arr = array(
				"token"=>$token,
				'consignee'=>$consignee,
				'region_id'=>$region_id,
				'region_name'=>$region_name,
				'al_name'	=> $al_name,
				'address'	=> $address,
				'zipcode'	=> $zipcode,
				'phone_tel'	=> $phone_tel,
				'phone_mob'	=> $phone_mob,
				'email'		=> $email,
				'addr_id'	=> $addr_id,
		);
	
		$rs = getSoapClients($class, $action,$arr);
		die($rs);
	}
	
	/*删除收获地址*/
	elseif ($action == 'delAddress')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
			$addr_id = $data->addr_id;
		}
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'用户尚未登录');
			echo $json->encode($arr); die;
		}
		
		if (!$addr_id)
		{
			$arr = array('statusCode'=>1,'msg'=>'请先选择收获地址');
			echo $json->encode($arr); die;
		}
		
		$arr = array("token"=>$token,"addr_id"=>$addr_id);
		
		$rs = getSoapClients($class, $action,$arr);
		die($rs);
	}
	/*获取支付方式*/
	elseif ($action == 'getPayment')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
		}
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'用户尚未登录');
			echo $json->encode($arr); die;
		}
		
		$arr = array("token"=>$token);
		
		$rs = getSoapClients($class, $action,$arr);
		die($rs);
	}
	
	/*获取配送方式*/
	elseif ($action == 'getShipping')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
		}
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array("token"=>$token);
		
		$rs = getSoapClients($class, $action,$arr);
		die($rs);
	}
	

	/*他的设计列表*/
	elseif ($action == 'myDesignList')
	{
		$data = _g();
		if ($data)
		{
			$uid = $data->uid;
			$pageSize = $data->pageSize;
			$pageIndex = $data->pageIndex;
		}
		
		if (!$uid)
		{
			$arr = array('statusCode'=>1,'msg'=>'uid not empty');
			echo $json->encode($arr); die;
		}
		
		if (!$pageSize)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
		
		if (!$pageIndex)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageIndex not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array
		(
				'uid' => $uid,
				'pageSize' => $pageSize,
				'pageIndex' => $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	
	}
	
	/*他的街拍列表*/
	elseif ($action == 'myphotoList')
	{
		$data = _g();
		if ($data)
		{
			$uid = $data->uid;
			$pageSize = $data->pageSize;
			$pageIndex = $data->pageIndex;
		}
	
		if (!$uid)
		{
			$arr = array('statusCode'=>1,'msg'=>'uid not empty');
			echo $json->encode($arr); die;
		}
	
		if (!$pageSize)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
	
		if (!$pageIndex)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageIndex not empty');
			echo $json->encode($arr); die;
		}
	
		$arr = array
		(
				'uid' => $uid,
				'pageSize' => $pageSize,
				'pageIndex' => $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	
	}
	
	/*添加街拍*/
	elseif ($action == 'addPhoto')
	{
		$data = _g();
		
		if ($data)
		{
			$token = $data->token;
			$title = $data->title;
			if (!$token)
			{
				$arr = array('statusCode'=>1,'msg'=>'token not empty');
				echo $json->encode($arr); die;
			}
			if (!$_FILES['imgCode'])
			{
				$arr = array('statusCode'=>1,'msg'=>'imgCode not empty');
				echo $json->encode($arr); die;
			}
			if (!$title)
			{
				$arr = array('statusCode'=>1,'msg'=>'title not empty');
				echo $json->encode($arr); die;
			}
			/*描述*/
			if (!isset($data->desc))
			{
				$desc = $data->desc;
			}
			else 
			{
				$desc = '';
			}
		}
					
		$arr = array
		(
				'token' => $token,
				'desc' => $desc,
				'imgCode' => $_FILES['imgCode'],
				'title' => $title,
		);
// 	print_exit($arr);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*酷客基地街拍详情页发评论*/
	elseif ($action == 'addComment')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
			$id    = $data->id;//图片id----不是相册id
			$content = $data->content;
		}
		
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		if (!$id)
		{
			$arr = array('statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		if (!$content)
		{
			$arr = array('statusCode'=>1,'msg'=>'content not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array
		(
				'token' => $token,
				'id' => $id,
				'content' => $content,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*酷客基地 街拍详情页评论获取*/
	elseif ($action == 'getCommentList')
	{
		$data = _g();
		if ($data)
		{
			$id    = $data->id;//图片id----不是相册id
			$pageSize = $data->pageSize;
			$pageIndex = $data->pageIndex;
		}
		
		if (!$id)
		{
			$arr = array('statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageSize)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageIndex)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageIndex not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array
		(
			'id' => $id,
			'pageSize' => $pageSize,
			'pageIndex' => $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*创建酷吧*/
	elseif ($action == 'createAlbum')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
			$title = $data->title;
			$desc  = $data->desc;
		}
		
		if (!$token)
		{

			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		if (!$title)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'title not empty');
			echo $json->encode($arr); die;
		}
		if (!$desc)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'desc not empty');
			echo $json->encode($arr); die;
		}		
		
		$arr = array
		(
				'token' => $token,
				'title' => $title,
				'desc'  => $desc,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*添加设计图片*/
	elseif ($action == 'addSheji')
	{
		$data = _g();
		
		if ($data)
		{
			$token = $data->token;
			$title = $data->title;
			$base_info = $data->base_info;
			if (!$token)
			{
				$arr = array('statusCode'=>1,'msg'=>'token not empty');
				echo $json->encode($arr); die;
			}
			if (!$_FILES['imgCode'])
			{
				$arr = array('statusCode'=>1,'msg'=>'imgCode not empty');
				echo $json->encode($arr); die;
			}
			if (!$title)
			{
				$arr = array('statusCode'=>1,'msg'=>'title not empty');
				echo $json->encode($arr); die;
			}
			/*描述*/
			if (!$base_info)
			{
				$arr = array('statusCode'=>1,'msg'=>'base_info not empty');
				echo $json->encode($arr); die;
			}
		}
			
		$arr = array
		(
				'token' => $token,
				'base_info' => $base_info,
				'imgCode' => $_FILES['imgCode'],
				'title' => $title,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取酷吧列表*/
	elseif ($action == 'getAlbum')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
			$pageSize = $data->pageSize;
			$pageIndex = $data->pageIndex;
			
		}
		
		if (!$token)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageSize)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageIndex)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'pageIndex not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array
		(
				'token' => $token,
				'pageSize' => $pageSize,
				'pageIndex' => $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*个人设计 添加到酷吧*/
	elseif ($action == 'addtoAlbum')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
			$album_id = $data->album_id;//酷吧id
			$photo_id = $data->photo_id;//个人设计 图片id	
		}
		
		if (!$token)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		if (!$album_id)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'album_id not empty');
			echo $json->encode($arr); die;
		}
		if (!$photo_id)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'photo_id not empty');
			echo $json->encode($arr); die;
		}
		$arr = array
		(
				'token' => $token,
				'album_id' => $album_id,
				'photo_id' => $photo_id,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取相册详情*/
	elseif ($action == 'getAlbumDetail')
	{
		$data = _g();
		if ($data)
		{
			$token = $data->token;
			$album_id = $data->album_id;//酷吧id
		}
		
		if (!$token)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		if (!$album_id)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'album_id not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array
		(
				'token' => $token,
				'album_id' => $album_id,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*修改相册信息*/
	elseif ($action == 'editAlbum')
	{
		$data = _g();
		
		if ($data)
		{
			$token = $data->token;
			$title = $data->title;
			$albumId = $data->albumId;
			if (!$token)
			{
				$arr = array('statusCode'=>1,'msg'=>'token not empty');
				echo $json->encode($arr); die;
			}
			if (!$albumId)
			{
				$arr = array('statusCode'=>1,'msg'=>'albumId not empty');
				echo $json->encode($arr); die;
			}
			if (!$_FILES['imgCode'])
			{
				$arr = array('statusCode'=>1,'msg'=>'imgCode not empty');
				echo $json->encode($arr); die;
			}
			if (!$title)
			{
				$arr = array('statusCode'=>1,'msg'=>'title not empty');
				echo $json->encode($arr); die;
			}
			if (!isset($data->desc))
			{
				$desc = $data->desc;
			}
			else
			{
				$desc = '';
			}
		}
			
		$arr = array
		(
				'token' => $token,
				'desc' => $desc,
				'imgCode' => $_FILES['imgCode'],
				'title' => $title,
				'albumId' => $albumId,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*删除街拍相册*/
	elseif ($action == 'delAlbum')
	{
		$data = _g();
		if ($data)
		{
			$id = $data->id;
		}
		
		if (!$id)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array
		(
				'id' => $id,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*删除图片*/
	elseif ($action == 'delPhoto')
	{
		$data = _g();
		if ($data)
		{
			$id = $data->id;
		}
	
		if (!$id)
		{
	
			$arr = array('statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
	
		$arr = array
		(
				'id' => $id,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*设置某种图片街拍相册封面*/
	elseif ($action == 'setCover')
	{
		$data = _g();
		if ($data)
		{
			$id = $data->id;
		}
	
		if (!$id)
		{
	
			$arr = array('statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
	
		$arr = array
		(
				'id' => $id,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	
	
	
	/*获取酷客列表*/
	elseif ($action == 'getUserList')
	{	
		$data = _g();
		if ($data)
		{
			$pageSize = $data->pageSize;
			$pageIndex = $data->pageIndex;
		}
		
		if (!$pageSize)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageIndex)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'pageIndex not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array
		(
				'pageSize' => $pageSize,
				'pageIndex' => $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取晒库页面数据*/
	elseif ($action == 'kukeIndex')
	{
		$data = _g();
		if ($data)
		{
			$type      = $data->type;
			$pageIndex = $data->pageIndex;
			$pageSize  = $data->pageSize;
		}
		
		if (!$type)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'type not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageIndex)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'pageIndex not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageSize)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array
		(
				'type' => $type,
				'pageSize' => $pageSize,
				'pageIndex' => $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取晒库详细页数据*/
	elseif ($action == 'kukeInfo')
	{
		$data = _g();
		if ($data)
		{
			$picId  = $data->picId;
		}
		if (!$picId)
		{
		
			$arr = array('statusCode'=>1,'msg'=>'picId not empty');
			echo $json->encode($arr); die;
		}
		$arr = array
		(
				'picId' => $picId,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*上传图片接口*/
	elseif ($action == 'addImg')
	{
		$data = _g();
		if ($data)
		{
			$imgCode  = $data->imgCode;
			$token    = $data->token;
		}
		$arr = array
		(
				'imgCode' => $imgCode,
				'token'   => $token,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*街拍喜欢接口*/
	elseif ($action == "loveJiePai")
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
	
	/*设计喜欢接口*/
	elseif ($action == "loveSheJi")
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
	
	/*获取街拍相册列表*/
	elseif ($action == 'getAlbum')
	{
		$data = _g();
		if ($data)
		{
			$token    = $data->token;
			$pageSize = $data->pageSize;
			$pageIndex = $data->pageIndex;
		}
		if (!$token)
		{
			$arr = array('statusCode'=>1,'msg'=>'token not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageSize)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageSize not empty');
			echo $json->encode($arr); die;
		}
		if (!$pageIndex)
		{
			$arr = array('statusCode'=>1,'msg'=>'pageIndex not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array
		(
				'token'   => $token,
				'pageSize' => $pageSize,
				'pageIndex' => $pageIndex,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*获取定制页面详情*/
	elseif ($action == 'getBasis')
	{
		$data = _g();
		if ($data)
		{
			$id = $data->id;
		}
		if (!$id)
		{
			$arr = array('statusCode'=>1,'msg'=>'id not empty');
			echo $json->encode($arr); die;
		}
		
		$arr = array(
				'id' => $id,
		);
		/* $url = SITE_URL."/index.php/custom-get_basis-{$id}.html";
		$arr = file_get_contents($url); */
// 		echo $json->encode($arr); die;
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	/*设置收货地址为默认收货地址*/
	elseif ($action == 'setDefAddr')
	{
		$data = _g();
		if ($data)
		{
			$id = $data->id;
			$token = $data->token;
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
		
		$arr = array(
				'id' => $id,
				'token' => $token,
		);
		$rs = getSoapClients($class, $action, $arr);
		die($rs);
	}
	
	elseif ($action == 'test')
	{
		echo site_url();exit;
	}
	
	
	
	
	
	
	
	
	
	
	?>