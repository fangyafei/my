<?php
ini_set("soap.wsdl_cache_enabled", "0");
class User
{
	var $wdwl_url = '';
	var $error = '';
	var $token = '';

  /**
   * 构造函数
   * @param string $username
   *  可设置当前用户
   * @access protected
   * @return void
   */
  function __construct() {
	  //
  }

  /**
   * 设置参数
   */
  public function set($key, $value) {
    $this->$key = $value;
  }
  
  /**
   * 获取参数
   */
  public function get($key) {
    return isset($this->$key) ? $this->$key : NULL;
  }

  /**
   * 调试信息
   */
  public function message($message) {
  }
 /**
   * 取消订单
   * @param string $name
   * @param string $pwd
   * @access protected
   * @return void
   */
   public function orderCancel($order_id){		
   	
		global $json,$db,$ecs;
		
		$operation = 'cancel';
		
		$sql = "SELECT * FROM " . $ecs->table('order_info') .
		" WHERE order_id = $order_id " ;
		
		$order = $db->getRow($sql);
		if($order)
		{
			if ($order['order_status'] == 2) {
				$return['success'] = 'true';
				$return['state'] = 5;
			}else{ 
			/* 检查能否操作 */
			$operable_list = $this->operable_list($order);
// 			return $operable_list;
			if (!isset($operable_list[$operation]))
			{
				$return['success'] = 'false';
				$return['state'] = 2;
			}
		
			$order_id = $order['order_id'];
		
			/* 标记订单为“取消”，记录取消原因 */
			$cancel_note = '手机端操作';
			
			if ($db->autoExecute($ecs->table('order_info'), array('order_status' => 2, 'to_buyer' => $cancel_note), 'UPDATE',"order_id = '$order_id'") > 0){
				/* 记录log */
				$this->order_action($order['order_sn'], 2, $order['shipping_status'], 0, $cancel_note);
				
				/* 退还用户余额、积分、红包 */
				$this->return_user_surplus_integral_bonus($order);
				$return['success'] = 'true';
				$return['state'] = 1;
			}else{
				$return['success'] = 'false';
				$return['state'] = 3;
			}

		}
		}
		else
		{
			$return['success'] = 'false';
				$return['state'] = 4;
		}
		return $json->encode($return);
	}
	/**
	 * 退回余额、积分、红包（取消、无效、退货时），把订单使用余额、积分、红包设为0
	 * @param   array   $order  订单信息
	 */
	public function return_user_surplus_integral_bonus($order)
	{
		global $db,$ecs;
		/* 处理余额、积分、红包 */
		if ($order['user_id'] > 0 && $order['surplus'] > 0)
		{
			$surplus = $order['money_paid'] < 0 ? $order['surplus'] + $order['money_paid'] : $order['surplus'];
			$this->log_account_change($order['user_id'], $surplus, 0, 0, 0, sprintf('手机取消-订单号：', $order['order_sn']),139);
			$db->query("UPDATE ". $ecs->table('order_info') . " SET `order_amount` = '0' WHERE `order_id` =". $order['order_id']);
		}
	
		if ($order['user_id'] > 0 && $order['integral'] > 0)
		{
			$this->log_account_change($order['user_id'], 0, 0, 0, $order['integral'], sprintf('手机取消-订单号：', $order['order_sn']),139);
		}
		/* 修改订单 */
		$arr = array(
				'bonus_id'  => 0,
				'bonus'     => 0,
				'integral'  => 0,
				'integral_money'    => 0,
				'surplus'   => 0
		);
		return $db->autoExecute($ecs->table('order_info'),$arr, 'UPDATE', "order_id = '{$order['order_id']}'");
	}
	
	/**
	 * 记录订单操作记录
	 *
	 * @access  public
	 * @param   string  $order_sn           订单编号
	 * @param   integer $order_status       订单状态
	 * @param   integer $shipping_status    配送状态
	 * @param   integer $pay_status         付款状态
	 * @param   string  $note               备注
	 * @param   string  $username           用户名，用户自己的操作则为 buyer
	 * @return  void
	 */
	public function order_action($order_sn, $order_status, $shipping_status, $pay_status, $note = '', $username = null, $place = 0)
	{
		global $db,$ecs;

		$sql = 'INSERT INTO ' . $ecs->table('order_action') .
		' (order_id, action_user, order_status, shipping_status, pay_status, action_place, action_note, log_time) ' .
		'SELECT ' .
		"order_id, '$username', '$order_status', '$shipping_status', '$pay_status', '$place', '$note', '" .(time() - date('Z')) . "' " .
		'FROM ' . $ecs->table('order_info') . " WHERE order_sn = '$order_sn'";
		$db->query($sql);
	}
	/**
	 * 返回某个订单可执行的操作列表，包括权限判断
	 * @param   array   $order      订单信息 order_status, shipping_status, pay_status
	 * @param   bool    $is_cod     支付方式是否货到付款
	 * @return  array   可执行的操作  confirm, pay, unpay, prepare, ship, unship, receive, cancel, invalid, return, drop
	 * 格式 array('confirm' => true, 'pay' => true)
	 */
	public function operable_list($order)
	{
		/* 取得订单状态、发货状态、付款状态 */
		$os = $order['order_status'];
		$ss = $order['shipping_status'];
		$ps = $order['pay_status'];
		/* 取得订单操作权限 */
		$actions = $_SESSION['action_list'];
		if ($actions == 'all')
		{
			$priv_list  = array('os' => true, 'ss' => true, 'ps' => true, 'edit' => true);
		}
		else
		{
			$actions    = ',' . $actions . ',';
			$priv_list  = array(
					'os'    => strpos($actions, ',order_os_edit,') !== false,
					'ss'    => strpos($actions, ',order_ss_edit,') !== false,
					'ps'    => strpos($actions, ',order_ps_edit,') !== false,
					'edit'  => strpos($actions, ',order_edit,') !== false
			);
		}
	
		/* 取得订单支付方式是否货到付款 */
		$payment = $this->payment_info($order['pay_id']);
		$is_cod  = $payment['is_cod'] == 1;
	
		/* 根据状态返回可执行操作 */
		$list = array();
		if (0 == $os)
		{
			/* 状态：未确认 => 未付款、未发货 */
			if ($priv_list['os'])
			{
				$list['confirm']    = true; // 确认
				$list['invalid']    = true; // 无效
				$list['cancel']     = true; // 取消
				if ($is_cod)
				{
					/* 货到付款 */
					if ($priv_list['ss'])
					{
						$list['prepare'] = true; // 配货
						$list['split'] = true; // 分单
						$list['new_split'] = true; // 分单
					}
				}
				else
				{
					/* 不是货到付款 */
					if ($priv_list['ps'])
					{
						$list['pay'] = true;  // 付款
					}
				}
			}
		}
		elseif (1 == $os || 5 == $os || 6 == $os)
		{
			/* 状态：已确认 */
			if (0 == $ps)
			{
				/* 状态：已确认、未付款 */
				if (0 == $ss || 3 == $ss)
				{
					/* 状态：已确认、未付款、未发货（或配货中） */
					if ($priv_list['os'])
					{
						$list['cancel'] = true; // 取消
						$list['invalid'] = true; // 无效
						//$list['to_delivery'] = true; // 无效
						$list['new_split'] = true; // 分单
						$list['split'] = true; // 分单
					}
					if ($is_cod)
					{
						/* 货到付款 */
						if ($priv_list['ss'])
						{
							if (0 == $ss)
							{
								$list['prepare'] = true; // 配货
							}
							$list['split'] = true; // 分单
							$list['new_split'] = true; // 分单
							$list['to_delivery'] = true; // 分单
						}
					}
					else
					{
						/* 不是货到付款 */
						if ($priv_list['ps'])
						{
							$list['pay'] = true; // 付款
						}
					}
				}
				/* 状态：已确认、未付款、发货中 */
				elseif (5 == $ss || 4 == $ss)
				{
					// 部分分单
					if (6 == $os)
					{
						$list['split'] = true; // 分单
					}
					$list['to_delivery'] = true; // 去发货
				}
				else
				{
					/* 状态：已确认、未付款、已发货或已收货 => 货到付款 */
					if ($priv_list['ps'])
					{
						$list['pay'] = true; // 付款
					}
					if ($priv_list['ss'])
					{
						if (1 == $ss)
						{
							$list['receive'] = true; // 收货确认
						}
						$list['unship'] = true; // 设为未发货
						if ($priv_list['os'])
						{
							$list['return'] = true; // 退货
						}
					}
				}
			}
			else
			{
				/* 状态：已确认、已付款和付款中 */
				if (0 == $ss || 3 == $ss)
				{
					/* 状态：已确认、已付款和付款中、未发货（配货中） => 不是货到付款 */
					if ($priv_list['ss'])
					{
						if (0 == $ss)
						{
							$list['prepare'] = true; // 配货
						}
						$list['split'] = true; // 分单
						$list['new_split'] = true; // 分单
					}
					if ($priv_list['ps'])
					{
						$list['unpay'] = true; // 设为未付款
						if ($priv_list['os'])
						{
							$list['cancel'] = true; // 取消
						}
					}
				}
				/* 状态：已确认、未付款、发货中 */
				elseif (5 == $ss || 4 == $ss)
				{
					// 部分分单
					if (6 == $os)
					{
						$list['split'] = true; // 分单
						$list['new_split'] = true; // 分单
					}
					$list['to_delivery'] = true; // 去发货
				}
				else
				{
					/* 状态：已确认、已付款和付款中、已发货或已收货 */
					if ($priv_list['ss'])
					{
						if (1 == $ss)
						{
							$list['receive'] = true; // 收货确认
						}
						if (!$is_cod)
						{
							$list['unship'] = true; // 设为未发货
						}
					}
					if ($priv_list['ps'] && $is_cod)
					{
						$list['unpay']  = true; // 设为未付款
					}
					if ($priv_list['os'] && $priv_list['ss'] && $priv_list['ps'])
					{
						$list['return'] = true; // 退货（包括退款）
					}
				}
			}
		}
		elseif (2 == $os)
		{
			/* 状态：取消 */
			if ($priv_list['os'])
			{
				$list['confirm'] = true;
			}
			if ($priv_list['edit'])
			{
				$list['remove'] = true;
			}
		}
		elseif (3 == $os)
		{
			/* 状态：无效 */
			if ($priv_list['os'])
			{
				$list['confirm'] = true;
			}
			if ($priv_list['edit'])
			{
				$list['remove'] = true;
			}
		}
		elseif (4 == $os)
		{
			/* 状态：退货 */
			if ($priv_list['os'])
			{
				$list['confirm'] = true;
			}
		}
	
		/* 售后 */
		$list['after_service'] = true;
	
		return $list;
	}
  /**
   * 会员登录
   * @param string $name
   * @param string $pwd
   * @access protected
   * @return void
   */
   public function UserLogin($name,$pass){		
		global $json,$db,$ecs;
		
		//要连表进行验证
		$sql = "SELECT COUNT(*) FROM ".$ecs->table('users')." WHERE 1";
		$count = $db->getOne($sql);

		$arr = array('name'=>$name, 'pass'=>md5($pass), 'nun'=>$count, 'status'=>'ok');

		return $json->encode($arr);
	}

  /**
   * 会员注册
   * @param string $name
   * @param string $pwd
   * @param string $email
   * @access protected
   * @return void
   */
   public function UserRegister($name,$pwd,$email){		
		$arr = array('uname'=>$name, 'pwd'=>md5($pwd), 'email'=>$email, 'status'=>'ok');
		return $json->encode($arr);
	}

  /**
   * 获取会员信息
   * @param string $name
   * @access protected
   * @return void
   */
   public function getUserInfo($name){		
		$arr = array('uname'=>$name, 'mobile'=>'13146294015', 'status'=>'ok');
		return $json->encode($arr);
	}

  /**
   * 获取首页需要的数据
   * @param string $name
   * @access protected
   * @return void
   */
   public function getIndexInfo($name,$pwd){		
		$arr = array('uname'=>$name, 'pwd'=>$pwd, 'status'=>'ok');
		return $json->encode($arr);
	}
   public function getActivityList($pagesize,$pageindex){
		global $json,$db,$ecs;
		$pageindex = $pageindex - 1;
		$pagenum = $pagesize * $pageindex;
		$pagesizes = $pagesize + 1;
		$sql  = "SELECT `activity_id`,`image_url`,`title`,`add_time`,`address`,`description` FROM " . $ecs->table('fabactivity') ." order by sort_order desc limit ".$pagenum.",".$pagesizes;
		$row = $db->getAll($sql);
		if($row[$pagesize]){
			array_pop($row);
			$data['hasNext'] = true;
		}else{
			$data['hasNext'] = false;
		}
		foreach($row as $key=>$value){
			$actList[$key]['id'] = $value['activity_id'];
			if($value['image_url']){
				$actList[$key]['imgUrl'] = IMG_PREFIX.$value['image_url'];
			}else{
				$actList[$key]['imgUrl'] ='';
			}
			$actList[$key]['theme'] = $value['title'];
			$actList[$key]['time'] = $value['add_time'];
			$actList[$key]['address'] = $value['address'];
			$actList[$key]['description'] = $value['description'];
		}
		$data['actList'] = $actList;
		return $json->encode($data);
   }
   public function getActivity($activityId){		
		global $json,$db,$ecs;
		$sql  = "SELECT `activity_id`,`image_url`,`title`,`add_time`,`address`,`description` FROM " . $ecs->table('fabactivity') ."WHERE `activity_id` = ".$activityId;
		$row = $db->getRow($sql);
		if(!$row){
			$activity = array();
		}else{
			$activity['id'] = $row['activity_id'];
			if($row['image_url']){
				$activity['imgUrl'] = IMG_PREFIX.$row['image_url'];
			}else{
				$activity['imgUrl'] ='';
			}
			$activity['theme'] = $row['title'];
			$activity['time'] = $row['add_time'];
			$activity['address'] = $row['address'];
			$activity['description'] = $row['description'];
		}
		$data['activity'] = $activity;
		return $json->encode($data);
   }
   public function signUp($token,$phoneNum,$activityId){		
		global $json,$db,$ecs;
		$sql = "SELECT user_id FROM ".$ecs->table("member") . " WHERE user_token = '{$token}'";
		$userId = $db->getOne($sql);

		$sql = "SELECT * FROM ".$ecs->table('fabactivity_signup')." WHERE user_id=".$userId." AND activity_id=".$activityId;
		$mysignUp = $db->getRow($sql);
		if($mysignUp){
			$arr['statusCode'] = 2;
			$arr['msg'] = '你已报名';
			return $json->encode($arr);
		}else{
			$sql = "INSERT INTO ".$ecs->table('fabactivity_signup')." (`activity_id`, `user_id`, `phone`, `add_time`) VALUES (".$activityId.", ".$userId." , ".$phoneNum." ,  ".time().")";
			if($db->query($sql)){
				$arr['statusCode'] = 0;
				$arr['msg'] = '报名成功';
				return $json->encode($arr);
			}else{
				$arr['statusCode'] = 1;
				$arr['msg'] = '报名失败';
				return $json->encode($arr);
			}
		}
	}
   public function getOrderData($myorder,$cart_goods)
   {	
   		global $json,$db,$ecs;
   		
   		$order		=	unserialize($myorder);
   		$cart_goods	=	unserialize($cart_goods);
   		//return $order['shop_id'];
   		$order['mendian_id']=$this->get_shopids($order['mendian_id']);//根据店名获取商店ID   		
   		
   		//配送方式名称
   		$shipping = $this->shipping_info($order['shipping_id']);
   		$order['shipping_name'] = addslashes($shipping['shipping_name']);
   		//支付方式名称
   		$payment  = $this->payment_info($order['pay_id']);
   		$order['pay_name'] = addslashes($payment['pay_name']);
   		
   		//判断支付方式
   		if($payment['pay_code'] !='cod' && $shipping['shipping_code']=='cac'){
   			$dant='freedom';
   		}else{
   			$dant='';
   		}
   		$consignee=array();
   		$consignee['country']	=	$order['country'];
   		$consignee['province']	=	$order['province'];
   		$consignee['city']		=	$order['city'];
   		$consignee['district']	=	$order['district'];
   		
   		if($cart_goods){
   			
   		}
   		else{ 
   			return $json->encode(array('message'=>'购物车中没有商品','success'=>false,'status'=>4));
   		}
   		
   		/* 订单中的总额 */
   		$total = $this->order_fee($order, $cart_goods, $consignee,$dant);
   		$order['bonus']        = $total['bonus'];
   		$order['goods_amount'] = $total['goods_price'];
   		$order['discount']     = $total['discount'];
   		$order['surplus']      = $total['surplus'];
   		$order['tax']          = $total['tax'];
   		$order['shipping_fee'] = $total['shipping_fee'];
   		$order['insure_fee']   = $total['shipping_insure'];
   		$order['pay_fee'] 	   = $total['pay_fee'];
   		$order['cod_fee'] 	   = $total['cod_fee'];
   		$order['order_amount'] = number_format($total['amount'], 2, '.', '');
   		
   		//return $json->encode(array('order'=>$order,'success'=>false));
   		//return 1;
   		
   		$user_info = $this->user_info($order['user_id']);
   		//return $payment;
   		/* 如果全部使用余额支付，检查余额是否足够 
   		
   			
   			if($order['surplus'] >0) //余额支付里如果输入了一个金额
   			{
   				$order['order_amount'] = $order['order_amount'] + $order['surplus'];   				
   				$order['surplus_js'] = $order['surplus']; //用于计算
   				$order['surplus'] = 0;
   				
   			}else{ 
   				return $json->encode(array('message'=>'余额不能为空','success'=>false,'status'=>1));
   			}*/
   			
   			//return $user_info['user_money'];
   		if($order['pay_id']==7){
   			if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line']))
   			{
   					
   				return $json->encode(array('message'=>'余额不足','success'=>false,'status'=>2));
   			}else{
   				$order['surplus'] = $order['order_amount'];
   				$order['order_amount'] = 0;
   			}
   		}elseif($order['pay_id']==3){
   			//货到付款
   		}elseif($order['pay_id']==5){
   			//到店支付
   		}else{
   			return $json->encode(array('message'=>'支付方式错误','success'=>false,'status'=>101));
   			
   		}
   			
   		
   		//return $order['order_amount'];
   		/* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
   		if ($order['order_amount'] <= 0)
   		{
   			$order['order_status'] = 1;
   			$order['confirm_time'] = $this->gmtime();
   			$order['pay_status']   = 2;
   			$order['pay_time']     = $this->gmtime();
   			$order['order_amount'] = 0;
   		}
   		$order['integral_money']   = $total['integral_money'];
   		$order['integral']         = $total['integral'];
   		$order['is_iphone']        = 1;  //手机端提交的
   		
   	   //return $json->encode(array('order'=>$order));
   		
   		/* 插入订单表 */
   		$error_no = 0;
   		do
   		{
   			$order['order_sn'] = $this->get_order_sner(); //获取新订单号
   			$db->autoExecute($ecs->table('order_info'), $order, 'INSERT');
   		
   			$error_no = $db->errno();
   		
   			if ($error_no > 0 && $error_no != 1062)
   			{
   				//die($db->errorMsg());
   				return $json->encode(array('message'=>$db->errorMsg(),'success'=>false));
   			}
   		}
   		while ($error_no == 1062); //如果是订单号重复则重新提交数据
   		
   		$new_order_id = $db->insert_id();
   		$order['order_id'] = $new_order_id;
   		if($cart_goods){ 
   			foreach($cart_goods as $k=>$v){ 
   				/* 插入订单商品 */
   				$sql = "INSERT INTO " . $ecs->table('order_goods') . "( " .
   						"order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".
   						"goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id)values('$new_order_id','$v[goods_id]',
   						'$v[goods_name]','$v[goods_sn]','$v[product_id]','$v[goods_number]','$v[market_price]','$v[goods_price]','$v[goods_attr]',
   						'$v[is_real]','$v[extension_code]','$v[parent_id]','$v[is_gift]','$v[goods_attr_id]')";
   				$db->query($sql);
   				 
   			}
   			
   		}
   		
   		
   		/* 处理余额*/
   		if ($order['user_id'] > 0 && $order['surplus'] > 0)
   		{
   			$this->log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf('订单号：', $order['order_sn']));
   		}
   			//return 334343;
   			   $this->change_order_goods_storage($order['order_id'], true, 1);
   			 // return 2222;
   			
   		/* 插入支付日志 */
   		$order['log_id'] = $this->insert_pay_log($new_order_id, $order['order_amount'], 0);
   		
   		//返回订单号、金额、门店ID、会员ID
   		$list=array();
   		$list['order_sn']		=	$order['order_sn'];
   		$list['order_amount']	=	$total['amount'];
   		$list['shop_id']		=	$order['mendian_id'];
   		$list['user_id']		=	$order['user_id'];
   		
		$arr 	= array('data'=>$list,'success'=>true);

		return $json->encode($arr);
	}
   public function getBookSeats($myorder,$cart_goods,$infolist)
	{
		global $json,$db,$ecs;
		 
		$order		=	unserialize($myorder);
		$cart_goods	=	unserialize($cart_goods);
		$infolist	=	unserialize($infolist);
		//配送方式名称
		$shipping = $this->shipping_info($order['shipping_id']);
		$order['shipping_name'] = addslashes($shipping['shipping_name']);
		//支付方式名称
		$payment  = $this->payment_info($order['pay_id']);
		$order['pay_name'] = addslashes($payment['pay_name']);
		 
		//判断支付方式
		if($payment['pay_code'] !='cod' && $shipping['shipping_code']=='cac'){
			$dant='freedom';
		}else{
			$dant='';
		}
		$consignee=array();
		$consignee['country']	=	$order['country'];
		$consignee['province']	=	$order['province'];
		$consignee['city']		=	$order['city'];
		$consignee['district']	=	$order['district'];
		 
		if($order['yufu_money']){
	
		}
		else{
			return $json->encode(array('message'=>'订座预付金额不能为空','success'=>false,'status'=>5));
		}
		
		if($cart_goods){
		
		}
		else{
			return $json->encode(array('message'=>'购物车中没有商品','success'=>false,'status'=>4));
		}
		 
		/* 订单中的总额 */
		$total = $this->order_fee($order, $cart_goods, $consignee,$dant);
		$order['bonus']        = $total['bonus'];
		$order['goods_amount'] = $total['goods_price'];
		$order['discount']     = $total['discount'];
		$order['surplus']      = $total['surplus'];
		$order['tax']          = $total['tax'];
		$order['shipping_fee'] = $total['shipping_fee'];
		$order['insure_fee']   = $total['shipping_insure'];
		$order['pay_fee'] 	   = $total['pay_fee'];
		$order['cod_fee'] 	   = $total['cod_fee'];
		$order['order_amount'] = number_format($total['amount'], 2, '.', '');
		 
		//return $json->encode(array('order'=>$order,'success'=>false));
		//return 1;
		 
		$user_info = $this->user_info($order['user_id']);
		//return $payment;
		/* 如果全部使用余额支付，检查余额是否足够 */
		if ($payment['pay_code'] == 'balance')
		{
	
			if($order['surplus'] >0) //余额支付里如果输入了一个金额
			{
				$order['order_amount'] = $order['order_amount'] + $order['surplus'];
				$order['surplus_js'] = $order['surplus']; //用于计算
				$order['surplus'] = 0;
					
			}else{
				return $json->encode(array('message'=>'余额不能为空','success'=>false,'status'=>1));
			}
	
			//return $user_info['user_money'];
			if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line']))
			{
					
				return $json->encode(array('message'=>'余额不足','success'=>false,'status'=>2));
			}
			 
			//判读输入余额是否等于商品余额
			if($order['order_amount']==$order['surplus_js']){
				$order['surplus'] = $order['order_amount'];
				$order['order_amount'] = 0;
			}else
			{
				$json->encode(array('message'=>'输入余额不等于商品总额','success'=>false,'status'=>3));
			}
		}
		//return $order['order_amount'];
		/* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
		if ($order['order_amount'] <= 0)
		{
			$order['order_status'] = 1;
			$order['confirm_time'] = $this->gmtime();
			$order['pay_status']   = 2;
			$order['pay_time']     = $this->gmtime();
			$order['order_amount'] = 0;
		}
		$order['integral_money']   = $total['integral_money'];
		$order['integral']         = $total['integral'];
		 
		//return $json->encode(array('order'=>$cart_goods,'success'=>false));
		
		//订座数据信息
		$info = array(
				'shop_id'         => $order['mendian_id'],
				'add_time'        => $order['add_time'],
				//'time_1'		  => $_SESSION['time_1'],
				//'time_2'		  => $_SESSION['time2'],
				'user_id'         => $order['user_id'],
				//'session_id'      => SESS_ID,
				'ren_num'         => $infolist['ren_num'],
				'sex'			  => $infolist['sex_r'],
				'mobile'		  => $order['mobile'],
				'telphone'		  => $order['tel'],
				'status'          => 1,
				'yufu_money'      => $order['surplus'],
				'sit_group_id1'         => $infolist['sit_group_id1'],
				'sit_group_id2'         => $infolist['sit_group_id2'],
				'sit_group_id3'         => $infolist['sit_group_id3'],
				'sit_group_id4'         => $infolist['sit_group_id4'],
				'sit_group_id5'         => $infolist['sit_group_id5'],
				'sit_group_id6'         => $infolist['sit_group_id6'],
				'sit_group_id7'         => $infolist['sit_group_id7'],
				'sit_group_id8'         => $infolist['sit_group_id8'],
				'daotian_time'          => $order['refuelTime1'],
				//'xianshi'		        => $infolist['xianshi'],
				'lianxiren'		        => $order['consignee']
		);
		
		
		//return $info;
		$db->autoExecute($ecs->table('shop_sit_log'), $info, 'INSERT');
		
		$shop_log_id= $db->insert_id();
		$order['shop_id']=$shop_log_id;
		 
		/* 插入订单表 */
		$error_no = 0;
		do
		{
			$order['order_sn'] = $this->get_order_sner(); //获取新订单号
			$db->autoExecute($ecs->table('order_info'), $order, 'INSERT');
			 
			$error_no = $db->errno();
			 
			if ($error_no > 0 && $error_no != 1062)
			{
				//die($db->errorMsg());
				return $json->encode(array('message'=>$db->errorMsg(),'success'=>false));
			}
		}
		while ($error_no == 1062); //如果是订单号重复则重新提交数据
		 
		$new_order_id = $db->insert_id();
		$order['order_id'] = $new_order_id;
		if($cart_goods){
			foreach($cart_goods as $k=>$v){
				/* 插入订单商品 */
				$sql = "INSERT INTO " . $ecs->table('order_goods') . "( " .
						"order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".
						"goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id)values('$new_order_id','$v[goods_id]',
						'$v[goods_name]','$v[goods_sn]','$v[product_id]','$v[goods_number]','$v[market_price]','$v[goods_price]','$v[goods_attr]',
						'$v[is_real]','$v[extension_code]','$v[parent_id]','$v[is_gift]','$v[goods_attr_id]')";
				$db->query($sql);
	
			}
	
		}
		 
		 
		/* 处理余额*/
		if ($order['user_id'] > 0 && $order['surplus'] > 0)
		{
			$this->log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf('订单号：', $order['order_sn']));
		}
		//return 11;
		 $this->change_order_goods_storage($order['order_id'], true, 1);
		// return 2222;
	
		/* 插入支付日志 */
		$order['log_id'] = $this->insert_pay_log($new_order_id, $order['order_amount'], 0);
		 
		//返回订单号、金额、门店ID、会员ID
		$list=array();
		$list['order_sn']		=	$order['order_sn'];
		$list['order_amount']	=	$total['amount'];
		$list['shop_id']		=	$order['mendian_id'];
		$list['user_id']		=	$order['user_id'];
		 
		$arr 	= array('data'=>$list,'success'=>true);
	
		return $json->encode($arr);
	}
	
   public function shipping_info($shipping_id)
	{
		global $db,$ecs;
		$sql = 'SELECT * FROM ' . $ecs->table('shipping') .
		" WHERE shipping_id = '$shipping_id' " .
		'AND enabled = 1';
	
		return $db->getRow($sql);
	}
	public function payment_info($pay_id)
	{
		global $db,$ecs;
		$sql = 'SELECT * FROM ' . $ecs->table('payment') .
		" WHERE pay_id = '$pay_id' AND enabled = 1";
	
		return $db->getRow($sql);
	}
	public function get_order_sn()
	{
		/* 选择一个随机的方案 */
		mt_srand((double) microtime() * 1000000);
	
		return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', 0);
	}

   public function Hotdish($cat_id,$xtype=0,$PageId,$type,$ResName){		
		global $json,$db,$ecs;
		
		//要连表进行验证
		$sql = "SELECT cat_id, cat_name FROM ".$ecs->table('category')." WHERE parent_id=".$cat_id;
		$row = $db->getAll($sql);

		$caixi=array();
		foreach($row as $k=>$v){
			$caixi[$k]['caixiName']=$v['cat_name'];
			$caixi[$k]['catId']    =$v['cat_id'];

		}
		$res=array('success'=>'true','state'=>1);

		$cat=$this->get_children($cat_id);

		$sqlc = "SELECT g.goods_sn, g.goods_name, g.xf_select, g.imglist_phone FROM  " .$ecs->table('goods'). " AS g WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ".$cat." AND g.xf_select = '$xtype' AND g.imglist_phone !='' ORDER by g.sort_order ASC,g.goods_id DESC LIMIT 16";
		$result = $db->getAll($sqlc);
		
		$cailist=array();
	
		foreach($result as $k=>$v){
			$cailist[$k]['index'] = $k;
			$cailist[$k]['Code']  = $v['goods_sn'];
			$cailist[$k]['Name']  = $v['goods_name'];
			$cailist[$k]['URL']   = $v['imglist_phone'];
			$cailist[$k]['xf_select']   = $v['xf_select'];
		}

		$arr = array('PageId'=>$PageId,'type'=>$type,'ResName'=>$ResName,
					 'RES'=>$res,'CaiXiList'=>$caixi,'ReCaiList'=>$cailist);
		
		return $json->encode($arr);
	}
	 public function Colddish($cat_id,$xtype=0,$PageId,$type,$ResName){		
		global $json,$db,$ecs;
		
		//要连表进行验证
		$sql = "SELECT cat_id, cat_name FROM ".$ecs->table('category')." WHERE parent_id=".$cat_id;
		$row = $db->getAll($sql);

		$caixi=array();
		foreach($row as $k=>$v){
			$caixi[$k]['caixiName']=$v['cat_name'];
			$caixi[$k]['catId']=$v['cat_id'];

		}
		$res=array('success'=>'true','state'=>1);

		$cat=$this->get_children($cat_id);

		$sqlc = "SELECT g.goods_sn ,g.goods_name,g.imglist_phone FROM  " .$ecs->table('goods'). " AS g WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ".$cat." AND g.xf_select = '$xtype' AND g.imglist_phone !='' ORDER by g.sort_order ASC,g.goods_id DESC LIMIT 16";
		$result = $db->getAll($sqlc);
		
		$cailist=array();
	
		foreach($result as $k=>$v){
			$cailist[$k]['index'] = $k;
			$cailist[$k]['Code']  = $v['goods_sn'];
			$cailist[$k]['Name']  = $v['goods_name'];
			$cailist[$k]['URL']   = $v['imglist_phone'];
		}

		$arr = array('PageId'=>$PageId,'type'=>$type,'ResName'=>$ResName,
				     'RES'=>$res,'CaiXiList'=>$caixi,'ReCaiList'=>$cailist);
		
		return $json->encode($arr);
	}
	public function Drinks($cat_id,$xtype=0,$PageId,$type,$ResName){		
		global $json,$db,$ecs;
		
		//要连表进行验证
		$sql = "SELECT cat_id, cat_name FROM ".$ecs->table('category')." WHERE parent_id=".$cat_id;
		$row = $db->getAll($sql);

		$caixi=array();
		foreach($row as $k=>$v){
			$caixi[$k]['caixiName']=$v['cat_name'];
			$caixi[$k]['catId']=$v['cat_id'];

		}
		$res=array('success'=>'true','state'=>1);



		$cat=$this->get_children($cat_id);

		$sqlc = "SELECT g.goods_sn ,g.goods_name,g.imglist_phone FROM  " .$ecs->table('goods'). " AS g WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ".$cat." AND g.xf_select = '$xtype' AND g.imglist_phone !='' LIMIT 16";
		$result = $db->getAll($sqlc);
		
		$cailist=array();
	
		foreach($result as $k=>$v){
			$cailist[$k]['index'] = $k;
			$cailist[$k]['Code']  = $v['goods_sn'];
			$cailist[$k]['Name']  = $v['goods_name'];
			$cailist[$k]['URL']   = $v['imglist_phone'];
		}

		$arr = array('PageId'=>$PageId,'type'=>$type,'ResName'=>$ResName,
					 'RES'=>$res,'CaiXiList'=>$caixi,'ReCaiList'=>$cailist);
		
		return $json->encode($arr);
	}
		public function Pastry($cat_id,$xtype=0,$PageId,$type,$ResName){		
		global $json,$db,$ecs;
		
		//要连表进行验证
		$sql = "SELECT cat_id, cat_name FROM ".$ecs->table('category')." WHERE parent_id=".$cat_id;
		$row = $db->getAll($sql);

		$caixi=array();
		foreach($row as $k=>$v){
			$caixi[$k]['caixiName']=$v['cat_name'];
			$caixi[$k]['catId']=$v['cat_id'];

		}
		$res=array('success'=>'true','state'=>1);



		$cat=$this->get_children($cat_id);

		$sqlc = "SELECT g.goods_sn ,g.goods_name,g.imglist_phone FROM  " .$ecs->table('goods'). " AS g WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ".$cat." AND g.xf_select = '$xtype' AND g.imglist_phone !='' ORDER by g.sort_order ASC,g.goods_id DESC LIMIT 16";
		$result = $db->getAll($sqlc);
		
		$cailist=array();
	
		foreach($result as $k=>$v){
			$cailist[$k]['index'] = $k;
			$cailist[$k]['Code']  = $v['goods_sn'];
			$cailist[$k]['Name']  = $v['goods_name'];
			$cailist[$k]['URL']   = $v['imglist_phone'];
		}

		$arr = array('PageId'=>$PageId,'type'=>$type,'ResName'=>$ResName,
					 'RES'=>$res,'CaiXiList'=>$caixi,'ReCaiList'=>$cailist);
		
		return $json->encode($arr);
	}
	public function semi_manufact($cat_id,$xtype,$PageId,$type,$ResName){		
		global $json,$db,$ecs;
		
		//要连表进行验证
		$sql = "SELECT cat_name FROM ".$ecs->table('category')." WHERE parent_id=".$cat_id;
		$row = $db->getAll($sql);

		$caixi=array();
		foreach($row as $k=>$v){
			$caixi[$k]['caixiName']=$v['cat_name'];
		}
		$res=array('success'=>'true','state'=>1);



		$cat=$this->get_children($cat_id);

		$sqlc = "SELECT g.goods_sn ,g.goods_name,g.imglist_phone FROM  " .$ecs->table('goods'). " AS g WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ".$cat." AND g.xf_select = '$xtype' LIMIT 16";
		$result = $db->getAll($sqlc);
		
		$cailist=array();
	
		foreach($result as $k=>$v){
			$cailist[$k]['index'] = $k;
			$cailist[$k]['Code']  = $v['goods_sn'];
			$cailist[$k]['Name']  = $v['goods_name'];
			$cailist[$k]['URL']   = $v['imglist_phone'];
		}

		$arr = array('PageId'=>$PageId,'type'=>$type,'ResName'=>$ResName,
					 'RES'=>$res,'CaiXiList'=>$caixi,'ReCaiList'=>$cailist);
		
		return $json->encode($arr);
	}
	public function wcake($cat_id,$xtype,$PageId,$type,$ResName){		
		global $json,$db,$ecs;
		
		//要连表进行验证
		$sql = "SELECT cat_name FROM ".$ecs->table('category')." WHERE parent_id=".$cat_id;
		$row = $db->getAll($sql);

		$caixi=array();
		foreach($row as $k=>$v){
			$caixi[$k]['caixiName']=$v['cat_name'];
		}
		$res=array('success'=>'true','state'=>1);



		$cat=$this->get_children($cat_id);

		$sqlc = "SELECT g.goods_sn ,g.goods_name,g.imglist_phone FROM  " .$ecs->table('goods'). " AS g WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ".$cat." AND g.xf_select = '$xtype' LIMIT 16";
		$result = $db->getAll($sqlc);
		
		$cailist=array();
	
		foreach($result as $k=>$v){
			$cailist[$k]['index'] = $k;
			$cailist[$k]['Code']  = $v['goods_sn'];
			$cailist[$k]['Name']  = $v['goods_name'];
			$cailist[$k]['URL']   = $v['imglist_phone'];
		}

		$arr = array('PageId'=>$PageId,'type'=>$type,'ResName'=>$ResName,
					 'RES'=>$res,'CaiXiList'=>$caixi,'ReCaiList'=>$cailist);
		
		return $json->encode($arr);
	}
	public function get_shopids($ResName){ 
		global $db,$ecs;
		$sql = "SELECT shop_id FROM ".$ecs->table('shop')." WHERE shop_name='$ResName'";
		$shop_id = $db->getOne($sql);
		
		return $shop_id;
	}
	public function cp_show($ResName='',$caixiName=0){		
		global $json,$db,$ecs;		
		//要连表进行验证
		
		$shop_id = $this->get_shopids($ResName);
		
		if(empty($shop_id)){
			$res=array('success'=>'false','state'=>0);
		}
		else{		
		
		$res=array('success'=>'true','state'=>1);
		//$cat=$this->get_children($cat_id);

		$sqlc = "SELECT g.goods_sn ,g.shop_ids,g.goods_name,g.imglist_phone,g.shop_price,g.market_price,g.promote_price,g.promote_start_date,g.promote_end_date FROM  " .$ecs->table('goods'). " AS g WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1";
		$result = $db->getAll($sqlc);
		
		$cailist=array();
	
		foreach($result as $k=>$row){	
			$vcr='error';
			$pieces = @explode(",", $row['shop_ids']);
			foreach($pieces as $val){
				if($val==$shop_id){					
					$vcr='right';					
				}
			}
			//$vcr='right';

			//return $vcr;
			if($vcr=='right'){
			
					$cailist[$k]['Code']  = $row['goods_sn'];
					$cailist[$k]['Name']  = $row['goods_name'];

					

					/* 修正促销价格 */
					if ($row['promote_price'] > 0)
					{
						$promote_price = $this->bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
					}
					else
					{
						$promote_price = 0;
					}
					$row['promote_price']	  =  intval($promote_price);

					if($row['promote_price']>0){
						$cailist[$k]['Price']=$row['promote_price'];
					}else{
						$cailist[$k]['Price']=intval($row['shop_price']);
					}


					$cailist[$k]['URL']   = $row['imglist_phone'];
					
				
			}

			//return $cailist;
			}

		}
		$arr = array('ResName'=>$ResName,'caixiName'=>$caixiName,
					 'RES'=>$res,'data'=>$cailist);
		
		return $json->encode($arr);
		//return $cailist;
		
	}
	public function region_list($region){		
		global $json,$db,$ecs;
		
		//要连表进行验证
		$sql = "SELECT region_id,region_name FROM ".$ecs->table('region')." WHERE parent_id=0";
		$row = $db->getAll($sql);

		$list=array();
		foreach($row as $k=>$v){
			$list[$k]['Province'] = $v['region_name'];
			$list[$k]['City']     = $this->get_region_city($v['region_id']);
		}

		$res=array('success'=>'true','state'=>1);

		$arr = array('RES'=>$res,'data'=>$list);
		
		return $json->encode($arr);
	}
	public function shop_list($Province,$City){
		global $json,$db,$ecs;
		$pid = $this->get_region_id($Province,1);
		$cid = $this->get_region_id($City,2);		
		//die;
		if(empty($pid) || empty($cid)){
			$res=array('success'=>'false','state'=>0);
			$arr=array('RES'=>$res);
		}else{

			$res=array('success'=>'true','state'=>1);
			$sql = "SELECT shop_name,address,is_best,shop_id,goods_thumb,map_x,map_y FROM ".$ecs->table('shop')." WHERE is_show=1 AND province=".$pid." AND city=".$cid.' ORDER BY shop_id DESC';
			$row = $db->getAll($sql);
			$list=array();
			foreach($row as $k=>$v){
				$list[$k]['ResName']	= $v['shop_name'];
				$list[$k]['ResAddress'] = $v['address'];
				$list[$k]['recommend']	= $v['is_best']?'true':'false';
				$list[$k]['AdUrl']		= 'mobile/shop.php?step=info&id='.$v['shop_id'];
				$list[$k]['URL']		= $v['goods_thumb'];
				$list[$k]['longitude']		= $v['map_x'];
				$list[$k]['dimensionality']	= $v['map_y'];

			}
			$arr = array('Province'=>$Province,'City'=>$City,'RES'=>$res,
					 'data'=>$list);
		}
	

		return $json->encode($arr);

	}
	public function shop_detail($detail){
		global $json,$db,$ecs;

		$sql = "SELECT g.shop_name,g.shop_id,g.map_x,g.map_y FROM ".$ecs->table('shop')."AS g WHERE g.is_show = 1";
			
		$result = $db->getAll($sql);

		
		$list=array();
		 if ($result !== false)
		{
			
			 /* 获得商品的销售价格 */
			 foreach($result as $k=>$row){

				$list[$k]['Name']				= $row['shop_name'];
				$list[$k]['Id']				    = $row['shop_id'];
				$list[$k]['Map_x']			    = $row['map_x'];		
				$list[$k]['Map_y']			    = $row['map_y'];
			 
			 }
				

			$res=array('success'=>'true','state'=>1);

		}else{
		$res=array('success'=>'false','state'=>0);
		
		}
		
		$arr = array('RES'=>$res,'Detail'=>$list);
		return $json->encode($arr); 
		//return '1111';

	}
	/**
	 * $cat_id 菜品的分类
	 * $xtype 外卖还是堂食
	 */
	public function getCaiList($cat_id=0, $xtype){		
		global $json,$db,$ecs;		
		$cat = $this->get_children($cat_id);
		$sqlc = "SELECT g.goods_id, g.goods_sn ,g.goods_name,g.imglist_phone FROM  " .$ecs->table('goods'). 
			    " AS g WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ".$cat." AND g.xf_select = '$xtype'";
		$result  = $db->getAll($sqlc);
		
		$cailist = array();
	
		foreach($result as $k=>$v){
			$cailist[$k]['index'] = $k;
			$cailist[$k]['Code']  = $v['goods_sn'];
			$cailist[$k]['Name']  = $v['goods_name'];
			$cailist[$k]['URL']   = $v['imglist_phone'];
		}
		$arr = array('CaiList'=>$cailist);	
		
		return $json->encode($arr);
	}

	/**
	 * 得到新订单号
	 * @return  string
	 */
	public function get_order_sner()
	{
		/* 选择一个随机的方案 */
		mt_srand((double) microtime() * 1000000);

		return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	}

	public function ac_detail($Code,$Name){
		global $json,$db,$ecs;

		$sql = "SELECT g.goods_name,g.goods_sn,g.taste, g.xf_select,g.is_real,g.extension_code,g.goods_brief,g.goods_id,g.shop_price,g.market_price,g.promote_price,g.promote_start_date,g.promote_end_date,g.imgshow_phone FROM ".$ecs->table('goods')."AS g WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.goods_sn='$Code' AND g.goods_name='$Name'";
		$row = $db->getRow($sql);
		$list=array();
		 if ($row !== false)
		{
			
			 /* 获得商品的销售价格 */
			 
		$list['Name']				= $row['goods_name'];
		$list['Code']				= $row['goods_sn'];
        $list['OldPrice']			= intval($row['market_price']);		
		/* 修正促销价格 */
        if ($row['promote_price'] > 0)
        {
            $promote_price = $this->bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }
        $row['promote_price']	  =  intval($promote_price);

		if($row['promote_price']>0){
			$list['NewPrice']=$row['promote_price'];
		}else{
			$list['NewPrice']=intval($row['shop_price']);
		}
		$list['Taste']		  =  $row['taste'];
		$list['Introduce']	  =  $row['goods_brief'];
		$list['URL']		  =  $row['imgshow_phone'];
		$list['goods_id']	  =  $row['goods_id'];
		$list['is_real']	  =  $row['is_real'];
		$list['extension_code']	  =  $row['extension_code'];
		$list['xf_select']	  =  $row['xf_select'];
		
		$list['goods_attrs']  = $this->get_goodsattr($row['goods_id']) ;

		$res=array('success'=>'true','state'=>1);

		}else{
		$res=array('success'=>'false','state'=>0);
		
		}
		
		$arr = array('CaiPinContent'=>$list,'RES'=>$res);
		return $json->encode($arr); 

	}
	function get_goodsattr($gid){ 
		
		global $db,$ecs;
		
		$sql = "SELECT goods_attr_id,attr_value FROM ".$ecs->table('goods_attr')." WHERE goods_id='$gid'";
		$row = $db->getAll($sql);
		foreach($row as $k=>$v){
			
			$row[$k]['product_id']=$this->get_productid($v['goods_attr_id'],$gid);
		}
		return $row;
		
	}
	function get_productid($attr_id,$gid){ 
		
		if(empty($attr_id))return false;
		global $db,$ecs;
		
		$sql = "SELECT product_id FROM ".$ecs->table('products')." WHERE goods_id='$gid' and goods_attr='$attr_id'";
		$row = $db->getOne($sql);
		foreach($row as $k=>$v){
				
			$row[$k]['products']=$row;
		}
		return $row;
		
	}
	function bargain_price($price, $start, $end)
	{
		if ($price == 0)
		{
			return 0;
		}
		else
		{
			$time = $this->gmtime();
			if ($time >= $start && $time <= $end)
			{
				return $price;
			}
			else
			{
				return 0;
			}
		}
	}
	function gmtime()
	{
		return (time() - date('Z'));
	}
	public function get_region_id($name,$stri){
	
		global $json,$db,$ecs;
		if($stri==1){
			$stri=' AND parent_id =0';
		}else{
			$stri=' AND parent_id !=0';
		}
		$sql = "SELECT region_id FROM ".$ecs->table('region')." WHERE region_name='$name'".$stri; 
		$row = $db->getOne($sql);
		return $row;
	}
	
	public function get_region_city($id){
		global $json,$db,$ecs;
		$sql = "SELECT region_id,region_name FROM ".$ecs->table('region')." WHERE parent_id=".$id;
		$row = $db->getAll($sql);
		$list=array();
		foreach($row as $k=>$v){
			$list[$k]['CityName']=$v['region_name'];
		}
		return $list;
	}
	public function get_children($cat = 0)
	{
		return 'g.cat_id ' . $this->db_create_in(array_unique(array_merge(array($cat), array_keys($this->cat_list($cat, 0, false)))));
	}
	public function db_create_in($item_list, $field_name = '')
	{
		if (empty($item_list))
		{
			return $field_name . " IN ('') ";
		}
		else
		{
			if (!is_array($item_list))
			{
				$item_list = explode(',', $item_list);
			}
			$item_list = array_unique($item_list);
			$item_list_tmp = '';
			foreach ($item_list AS $item)
			{
				if ($item !== '')
				{
					$item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
				}
			}
			if (empty($item_list_tmp))
			{
				return $field_name . " IN ('') ";
			}
			else
			{
				return $field_name . ' IN (' . $item_list_tmp . ') ';
			}
		}
	}
	public function cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
	{	
		global $db,$ecs;
		static $res = NULL;

    if ($res === NULL)
    {
       
            $sql = "SELECT c.cat_id, c.cat_name, c.measure_unit, c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children ".
                'FROM ' . $ecs->table('category') . " AS c ".
                "LEFT JOIN " . $ecs->table('category') . " AS s ON s.parent_id=c.cat_id ".
                "GROUP BY c.cat_id ".
                'ORDER BY c.parent_id, c.sort_order ASC';
            $res = $db->getAll($sql);

            $sql = "SELECT cat_id, COUNT(*) AS goods_num " .
                    " FROM " . $ecs->table('goods') .
                    " WHERE is_delete = 0 AND is_on_sale = 1 " .
                    " GROUP BY cat_id";
            $res2 = $db->getAll($sql);

            $sql = "SELECT gc.cat_id, COUNT(*) AS goods_num " .
                    " FROM " . $ecs->table('goods_cat') . " AS gc , " . $ecs->table('goods') . " AS g " .
                    " WHERE g.goods_id = gc.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 " .
                    " GROUP BY gc.cat_id";
            $res3 = $db->getAll($sql);

            $newres = array();
            foreach($res2 as $k=>$v)
            {
                $newres[$v['cat_id']] = $v['goods_num'];
                foreach($res3 as $ks=>$vs)
                {
                    if($v['cat_id'] == $vs['cat_id'])
                    {
                    $newres[$v['cat_id']] = $v['goods_num'] + $vs['goods_num'];
                    }
                }
            }

            foreach($res as $k=>$v)
            {
                $res[$k]['goods_num'] = !empty($newres[$v['cat_id']]) ? $newres[$v['cat_id']] : 0;
            }
            
      
    }

    if (empty($res) == true)
    {
        return $re_type ? '' : array();
    }

    $options = $this->cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

    $children_level = 99999; //大于这个分类的将被删除
    if ($is_show_all == false)
    {
        foreach ($options as $key => $val)
        {
            if ($val['level'] > $children_level)
            {
                unset($options[$key]);
            }
            else
            {
                if ($val['is_show'] == 0)
                {
                    unset($options[$key]);
                    if ($children_level > $val['level'])
                    {
                        $children_level = $val['level']; //标记一下，这样子分类也能删除
                    }
                }
                else
                {
                    $children_level = 99999; //恢复初始值
                }
            }
        }
    }

    /* 截取到指定的缩减级别 */
    if ($level > 0)
    {
        if ($cat_id == 0)
        {
            $end_level = $level;
        }
        else
        {
            $first_item = reset($options); // 获取第一个元素
            $end_level  = $first_item['level'] + $level;
        }

        /* 保留level小于end_level的部分 */
        foreach ($options AS $key => $val)
        {
            if ($val['level'] >= $end_level)
            {
                unset($options[$key]);
            }
        }
    }

   
        

        return $options;
    
	}
	public function cat_options($spec_cat_id, $arr)
	{
    static $cat_options = array();

    if (isset($cat_options[$spec_cat_id]))
    {
        return $cat_options[$spec_cat_id];
    }

    if (!isset($cat_options[0]))
    {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = array();
       
            while (!empty($arr))
            {
                foreach ($arr AS $key => $value)
                {
                    $cat_id = $value['cat_id'];
                    if ($level == 0 && $last_cat_id == 0)
                    {
                        if ($value['parent_id'] > 0)
                        {
                            break;
                        }

                        $options[$cat_id]          = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id']    = $cat_id;
                        $options[$cat_id]['name']  = $value['cat_name'];
                        unset($arr[$key]);

                        if ($value['has_children'] == 0)
                        {
                            continue;
                        }
                        $last_cat_id  = $cat_id;
                        $cat_id_array = array($cat_id);
                        $level_array[$last_cat_id] = ++$level;
                        continue;
                    }

                    if ($value['parent_id'] == $last_cat_id)
                    {
                        $options[$cat_id]          = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id']    = $cat_id;
                        $options[$cat_id]['name']  = $value['cat_name'];
                        unset($arr[$key]);

                        if ($value['has_children'] > 0)
                        {
                            if (end($cat_id_array) != $last_cat_id)
                            {
                                $cat_id_array[] = $last_cat_id;
                            }
                            $last_cat_id    = $cat_id;
                            $cat_id_array[] = $cat_id;
                            $level_array[$last_cat_id] = ++$level;
                        }
                    }
                    elseif ($value['parent_id'] > $last_cat_id)
                    {
                        break;
                    }
                }

                $count = count($cat_id_array);
                if ($count > 1)
                {
                    $last_cat_id = array_pop($cat_id_array);
                }
                elseif ($count == 1)
                {
                    if ($last_cat_id != end($cat_id_array))
                    {
                        $last_cat_id = end($cat_id_array);
                    }
                    else
                    {
                        $level = 0;
                        $last_cat_id = 0;
                        $cat_id_array = array();
                        continue;
                    }
                }

                if ($last_cat_id && isset($level_array[$last_cat_id]))
                {
                    $level = $level_array[$last_cat_id];
                }
                else
                {
                    $level = 0;
                }
            }
          
        
        $cat_options[0] = $options;
    }
    else
    {
        $options = $cat_options[0];
    }

    if (!$spec_cat_id)
    {
        return $options;
    }
    else
    {
        if (empty($options[$spec_cat_id]))
        {
            return array();
        }

        $spec_cat_id_level = $options[$spec_cat_id]['level'];

        foreach ($options AS $key => $value)
        {
            if ($key != $spec_cat_id)
            {
                unset($options[$key]);
            }
            else
            {
                break;
            }
        }

        $spec_cat_id_array = array();
        foreach ($options AS $key => $value)
        {
            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
                ($spec_cat_id_level > $value['level']))
            {
                break;
            }
            else
            {
                $spec_cat_id_array[$key] = $value;
            }
        }
        $cat_options[$spec_cat_id] = $spec_cat_id_array;

        return $spec_cat_id_array;
    }
	}
	/**
	 * 获得订单中的费用信息
	 *
	 * @access  public
	 * @param   array   $order
	 * @param   array   $goods
	 * @param   array   $consignee
	 * @param   bool    $is_gb_deposit  是否团购保证金（如果是，应付款金额只计算商品总额和支付费用，可以获得的积分取 $gift_integral）
	 * @return  array
	 */
	public function order_fee($order, $goods, $consignee,$dant=null)
	{
	    /* 初始化订单的扩展code */
	    if (!isset($order['extension_code']))
	    {
	        $order['extension_code'] = '';
	    }
	
	    if ($order['extension_code'] == 'group_buy')
	    {
	        //$group_buy = group_buy_info($order['extension_id']);
	    }
	
	    $total  = array('real_goods_count' => 0,
	                    'gift_amount'      => 0,
	                    'goods_price'      => 0,
	                    'market_price'     => 0,
	                    'discount'         => 0,
	                    'pack_fee'         => 0,
	                    'card_fee'         => 0,
	                    'shipping_fee'     => 0,
	                    'shipping_insure'  => 0,
	                    'integral_money'   => 0,
	                    'bonus'            => 0,
	                    'surplus'          => 0,
	                    'cod_fee'          => 0,
	                    'pay_fee'          => 0,
	                    'tax'              => 0);
	    $weight = 0;
	
	    /* 商品总价 */
	    foreach ($goods AS $val)
	    {
	        /* 统计实体商品的个数 */
	        if ($val['is_real'])
	        {
	            $total['real_goods_count']++;
	        }
	
	        $total['goods_price']  += $val['goods_price'] * $val['goods_number'];
	        $total['market_price'] += $val['market_price'] * $val['goods_number'];
	    }
	
	    $total['saving']    = $total['market_price'] - $total['goods_price'];
	    $total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;
	
	    $total['goods_price_formated']  = ($total['goods_price']);
	    $total['market_price_formated'] = ($total['market_price']);
	    $total['saving_formated']       = ($total['saving']);
	
	    
	    $total['discount_formated'] = ($total['discount']);
	
	    
	    $total['tax_formated'] = ($total['tax']);
	
	    
	
	
	    /* 配送费用 */
	    $shipping_cod_fee = NULL;
	
	    if ($order['shipping_id'] > 0 && $total['real_goods_count'] > 0)
	    {
	        $region['country']  = $consignee['country'];
	        $region['province'] = $consignee['province'];
	        $region['city']     = $consignee['city'];
	        $region['district'] = $consignee['district'];
	        $shipping_info = $this->shipping_area_info($order['shipping_id'], $region);
	
	        if (!empty($shipping_info))
	        {
	            
	            // 查看购物车中是否全为免运费商品，若是则把运费赋为零zoutj
	           // $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('cart') . " WHERE  `session_id` = '" . SESS_ID. "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";
	            //$shipping_count = $GLOBALS['db']->getOne($sql);
	
	           //todo $total['shipping_fee'] = ($shipping_count == 0 AND $weight_price['free_shipping'] == 1) ?0 :  shipping_fee($shipping_info['shipping_code'],$shipping_info['configure'], $weight_price['weight'], $total['goods_price'], $weight_price['number']);
				
			//echo '';die;
				if($order['wort']==1){
					$total['shipping_fee']=0;
	
				}elseif($dant=='freedom'){
					$total['shipping_fee']=0;
	
				}else{
					$total['shipping_fee']=$this->get_region_price($consignee['district']);
	
				}
	
	           
	                $total['shipping_insure'] = 0;//zoutj
	            
	
	            if ($shipping_info['support_cod'])
	            {
	                $shipping_cod_fee = $shipping_info['pay_fee'];
	            }
	        }
	    }
	
	    $total['shipping_fee_formated']    = ($total['shipping_fee']);
	    $total['shipping_insure_formated'] = ($total['shipping_insure']);
	
	    // 购物车中的商品能享受红包支付的总额
	    //$bonus_amount = compute_discount_amount();
	    $bonus_amount=0;
	    // 红包和积分最多能支付的金额为商品总额
	    $max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;
	
	    /* 计算订单总额 */
	    if ($order['extension_code'] == 'group_buy')
	    {
	        $total['amount'] = $total['goods_price'];
	    }
	    else
	    {
	        $total['amount'] = $total['goods_price'] - $total['discount'] + $total['tax'] + $total['pack_fee'] + $total['card_fee'] +
	            $total['shipping_fee'] + $total['shipping_insure'] + $total['cod_fee'];
	        /*zoutj
	        if($total['amount'] > $order['yufu_money']){
	        	$total['amount'] = $total['amount'];
	        }else{
	        	$total['amount'] += $order['yufu_money'];
	        }*/
	
	        // 减去红包金额
	        $use_bonus        = min($total['bonus'], $max_amount); // 实际减去的红包金额
	        if(isset($total['bonus_kill']))
	        {
	            $use_bonus_kill   = min($total['bonus_kill'], $max_amount);
	            $total['amount'] -=  $price = number_format($total['bonus_kill'], 2, '.', ''); // 还需要支付的订单金额
	        }
	
	        $total['bonus']   = $use_bonus;
	        $total['bonus_formated'] = ($total['bonus']);
	
	        $total['amount'] -= $use_bonus; // 还需要支付的订单金额
	        $max_amount      -= $use_bonus; // 积分最多还能支付的金额
	
	    }
	
	    /* 余额 */
	    $order['surplus'] = $order['surplus'] > 0 ? $order['surplus'] : 0;
	    if ($total['amount'] > 0)
	    {
	        if (isset($order['surplus']) && $order['surplus'] > $total['amount'])
	        {
	            $order['surplus'] = $total['amount'];
	            $total['amount']  = 0;
	        }
	        else
	        {
	            $total['amount'] -= floatval($order['surplus']);
	        }
	    }
	    else
	    {
	        $order['surplus'] = 0;
	        $total['amount']  = 0;
	    }
	    $total['surplus'] = $order['surplus'];
	    $total['surplus_formated'] = ($order['surplus']);
	
	    /* 积分 */
	    $order['integral'] = $order['integral'] > 0 ? $order['integral'] : 0;
	    if ($total['amount'] > 0 && $max_amount > 0 && $order['integral'] > 0)
	    {
	        $integral_money = ($order['integral']);
	
	        // 使用积分支付
	        $use_integral            = min($total['amount'], $max_amount, $integral_money); // 实际使用积分支付的金额
	        $total['amount']        -= $use_integral;
	        $total['integral_money'] = $use_integral;
	        $order['integral']       = ($use_integral);
	    }
	    else
	    {
	        $total['integral_money'] = 0;
	        $order['integral']       = 0;
	    }
	    $total['integral'] = $order['integral'];
	    $total['integral_formated'] = ($total['integral_money']);
	
	   
	
	    $se_flow_type =  '';
	    
	    /* 支付费用 */
	    if (!empty($order['pay_id']) && ($total['real_goods_count'] > 0 || $se_flow_type != CART_EXCHANGE_GOODS))
	    {
	        $total['pay_fee']      = $this->pay_fee($order['pay_id'], $total['amount'], $shipping_cod_fee);
	    }
	
	    $total['pay_fee_formated'] = ($total['pay_fee']);
	
	    $total['amount']           += $total['pay_fee']; // 订单总额累加上支付费用
	    $total['amount_formated']  = ($total['amount']);
	
	    /* 取得可以得到的积分和红包 */
	    if ($order['extension_code'] == 'group_buy')
	    {
	        $total['will_get_integral'] = 0;//zoutj
	    }
	    elseif ($order['extension_code'] == 'exchange_goods')
	    {
	        $total['will_get_integral'] = 0;
	    }
	    else
	    {
	        $total['will_get_integral'] = $order['integral'];//zoutj
	    }
	  //  $total['will_get_bonus']        = $order['extension_code'] == 'exchange_goods' ? 0 : price_format(get_total_bonus(), false);
	    $total['formated_goods_price']  = ($total['goods_price']);
	    $total['formated_market_price'] = ($total['market_price']);
	    $total['formated_saving']       = ($total['saving']);
	
	    
	
	    return $total;
	}
	public function pay_fee($payment_id, $order_amount, $cod_fee=null)
	{
		$pay_fee = 0;
		$payment = $this->payment_info($payment_id);
		//$rate    = ($payment['is_cod'] && !is_null($cod_fee)) ? $cod_fee : $payment['pay_fee'];
		$rate    = $payment['pay_fee'];
	
		if (strpos($rate, '%') !== false)
		{
			/* 支付费用是一个比例 */
			$val     = floatval($rate) / 100;
			$pay_fee = $val > 0 ? $order_amount * $val /(1- $val) : 0;
		}
		else
		{
			$pay_fee = floatval($rate);
		}
	
		return round($pay_fee, 2);
	}
	/**
	 * 取得某配送方式对应于某收货地址的区域信息
	 * @param   int     $shipping_id        配送方式id
	 * @param   array   $region_id_list     收货人地区id数组
	 * @return  array   配送区域信息（config 对应着反序列化的 configure）
	 */
	public function shipping_area_info($shipping_id, $region_id_list)
	{
		global $db,$ecs;
		$sql = 'SELECT s.shipping_code, s.shipping_name, ' .
				's.shipping_desc, s.insure, s.support_cod, a.configure ' .
				'FROM ' . $ecs->table('shipping') . ' AS s, ' .
				$ecs->table('shipping_area') . ' AS a, ' .
				$ecs->table('area_region') . ' AS r ' .
				"WHERE s.shipping_id = '$shipping_id' " .
				'AND r.region_id ' . $this->db_create_inst($region_id_list) .
				' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1';
		$row = $db->getRow($sql);
	
		if (!empty($row))
		{
			$shipping_config = $this->unserialize_config($row['configure']);
			if (isset($shipping_config['pay_fee']))
			{
				if (strpos($shipping_config['pay_fee'], '%') !== false)
				{
					$row['pay_fee'] = floatval($shipping_config['pay_fee']) . '%';
				}
				else
				{
					$row['pay_fee'] = floatval($shipping_config['pay_fee']);
				}
			}
			else
			{
				$row['pay_fee'] = 0.00;
			}
		}
	
		return $row;
	}
	
	public function db_create_inst($item_list, $field_name = '')
	{
	    if (empty($item_list))
	    {
	        return $field_name . " IN ('') ";
	    }
	    else
	    {
	        if (!is_array($item_list))
	        {
	            $item_list = explode(',', $item_list);
	        }
	        $item_list = array_unique($item_list);
	        $item_list_tmp = '';
	        foreach ($item_list AS $item)
	        {
	            if ($item !== '')
	            {
	                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
	            }
	        }
	        if (empty($item_list_tmp))
	        {
	            return $field_name . " IN ('') ";
	        }
	        else
	        {
	            return $field_name . ' IN (' . $item_list_tmp . ') ';
	        }
    }
	}
	/**
	 * 处理序列化的支付、配送的配置参数
	 * 返回一个以name为索引的数组
	 *
	 * @access  public
	 * @param   string       $cfg
	 * @return  void
	 */
	public function unserialize_config($cfg)
	{
		if (is_string($cfg) && ($arr = unserialize($cfg)) !== false)
		{
			$config = array();
	
			foreach ($arr AS $key => $val)
			{
				$config[$val['name']] = $val['value'];
			}
	
			return $config;
		}
		else
		{
			return false;
		}
	}
	//获取地区价格
	public function get_region_price($region_id)
	{
		global $db,$ecs;
		$sql = "SELECT region_price ".
				"FROM " .$ecs->table('region'). " WHERE region_id =  '$region_id' LIMIT 1";
		$row = $db->getOne($sql);
		return $row;
	
	}
	/**
	 * 取得用户信息
	 * @param   int     $user_id    用户id
	 * @return  array   用户信息
	 */
	public function user_info($user_id)
	{
		global $db,$ecs;
		$sql = "SELECT * FROM " . $ecs->table('users') .
		" WHERE user_id = '$user_id'";
		$user = $db->getRow($sql);
	
		unset($user['question']);
		unset($user['answer']);
	
		/* 格式化帐户余额 */
		if ($user)
		{
			//        if ($user['user_money'] < 0)
				//        {
				//            $user['user_money'] = 0;
				//        }
					$user['formated_user_money'] = ($user['user_money']);
					$user['formated_frozen_money'] = ($user['frozen_money']);
		}
	
		return $user;
	}
	/**
	 * 获得当前格林威治时间的时间戳
	 *
	 * @return  integer
	 */
	public function gmtimest()
	{
		return (time() - date('Z'));
	}
	/**
	 * 记录帐户变动
	 * @param   int     $user_id        用户id
	 * @param   float   $user_money     可用余额变动
	 * @param   float   $frozen_money   冻结余额变动
	 * @param   int     $rank_points    等级积分变动
	 * @param   int     $pay_points     消费积分变动
	 * @param   string  $change_desc    变动说明
	 * @param   int     $change_type    变动类型：参见常量文件
	 * @return  void
	 */
	public function log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER)
	{
		global $db,$ecs;
		/* 插入帐户变动记录 */
		$account_log = array(
				'user_id'       => $user_id,
				'user_money'    => $user_money,
				'frozen_money'  => $frozen_money,
				'rank_points'   => $rank_points,
				'pay_points'    => $pay_points,
				'change_time'   => $this->gmtimest(),
				'change_desc'   => $change_desc,
				'change_type'   => $change_type
		);
		$db->autoExecute($ecs->table('account_log'), $account_log, 'INSERT');
	
		//更新用户信息
		$sql = "UPDATE " . $ecs->table('users') .
		" SET user_money = user_money + ('$user_money')," .
		" frozen_money = frozen_money + ('$frozen_money')," .
		" rank_points = rank_points + ('$rank_points')," .
		" pay_points = pay_points + ('$pay_points')" .
		" WHERE user_id = '$user_id' LIMIT 1";
		$db->query($sql); 
	}
	/**
	 * 改变订单中商品库存
	 * @param   int     $order_id   订单号
	 * @param   bool    $is_dec     是否减少库存
	 * @param   bool    $storage     减库存的时机，1，下订单时；0，发货时；
	 */
	public function change_order_goods_storage($order_id, $is_dec = true, $storage = 0)
	{
		global $db,$ecs;
		/* 查询订单商品信息 */
		switch ($storage)
		{
			case 0 :
				$sql = "SELECT goods_id, SUM(send_number) AS num, MAX(extension_code) AS extension_code, product_id FROM " . $ecs->table('order_goods') .
				" WHERE order_id = '$order_id' AND is_real = 1 GROUP BY goods_id, product_id";
				break;
	
			case 1 :
				$sql = "SELECT goods_id, SUM(goods_number) AS num, MAX(extension_code) AS extension_code, product_id FROM " . $ecs->table('order_goods') .
				" WHERE order_id = '$order_id' AND is_real = 1 GROUP BY goods_id, product_id";
				break;
		}
	
		$res = $db->getAll($sql);
		//return $res;
		foreach ($res as $k=>$row)
		{
			if ($row['extension_code'] != "package_buy")
			{	
				if ($is_dec)
				{ 
					
				   $this->change_goods_storage($row['goods_id'], $row['product_id'], - $row['num']);
				}
				else
				{ 
					//return 3333;
					$this->change_goods_storage($row['goods_id'], $row['product_id'], $row['num']);
				}
				$db->query($sql);
			}
			else
			{	//return 3333;
				$sql = "SELECT goods_id, goods_number" .
						" FROM " . $ecs->table('package_goods') .
						" WHERE package_id = '" . $row['goods_id'] . "'";
				$res_goods = $db->getAll($sql);
				foreach ($res_goods as $k=>$row_goods)
				{
					$sql = "SELECT is_real" .
							" FROM " . $ecs->table('goods') .
							" WHERE goods_id = '" . $row_goods['goods_id'] . "'";
					$real_goods = $db->query($sql);
					$is_goods = $db->fetchRow($real_goods);
	
					if ($is_dec)
					{
						$this->change_goods_storage($row_goods['goods_id'], $row['product_id'], - ($row['num'] * $row_goods['goods_number']));
					}
					elseif ($is_goods['is_real'])
					{
						$this->change_goods_storage($row_goods['goods_id'], $row['product_id'], ($row['num'] * $row_goods['goods_number']));
					}
				}
			}
		}
	
	}
	/**
	 * 商品库存增与减 货品库存增与减
	 *
	 * @param   int    $good_id         商品ID
	 * @param   int    $product_id      货品ID
	 * @param   int    $number          增减数量，默认0；
	 *
	 * @return  bool               true，成功；false，失败；
	 */
	function change_goods_storage($good_id, $product_id, $number = 0)
	{
		global $db,$ecs;
		if ($number == 0)
		{
			return true; // 值为0即不做、增减操作，返回true
		}
	
		if (empty($good_id) || empty($number))
		{
			return false;
		}
	
		$number = ($number > 0) ? '+ ' . $number : $number;
		//return 4545454;
		/* 处理货品库存 */
		
		$products_query = true;
		if (!empty($product_id))
		{
			
			$sql = "UPDATE " . $ecs->table('products') ."
			SET product_number = product_number$number
			WHERE goods_id = '$good_id'
			AND product_id = '$product_id'
			LIMIT 1";
			//return $sql;
			$products_query = $db->query($sql);
			//return $products_query;
			
			
		}
		
		/* 处理商品库存 */
		$sql = "SELECT goods_number FROM " . $ecs->table('goods') ."		
		WHERE goods_id = '$good_id'
		LIMIT 1";
		$gnumber = $db->getOne($sql);
		//return $gnumber;
		$mynumber=abs($number);
		
		//return $mynumber;
		if($gnumber>=$mynumber){
			/* 处理商品库存 */
			$sql = "UPDATE " . $ecs->table('goods') ."
			SET goods_number = goods_number$number
			WHERE goods_id = '$good_id'
			LIMIT 1";
			$query = $db->query($sql);
			//return 2222;
			if ($query && $products_query)
			{
				//return 1111;
					return true;
				}
				else
				{
				//return 2222;
					return false;
				}
			
		}
		
		
	}
	
	/**
	 * 将支付LOG插入数据表
	 *
	 * @access  public
	 * @param   integer     $id         订单编号
	 * @param   float       $amount     订单金额
	 * @param   integer     $type       支付类型
	 * @param   integer     $is_paid    是否已支付
	 *
	 * @return  int
	 */
	public function insert_pay_log($id, $amount, $type = PAY_SURPLUS, $is_paid = 0)
	{
		global $db,$ecs;
		$sql = 'INSERT INTO ' .$ecs->table('pay_log')." (order_id, order_amount, order_type, is_paid)".
				" VALUES  ('$id', '$amount', '$type', '$is_paid')";
		$db->query($sql);
	
		return $db->insert_id();
	}
	

	
	public function getUserId($user_token){
		global $db, $ecs;
		$sql = "SELECT user_id FROM ".$ecs->table("member") . " WHERE user_token = '{$user_token}'";
		return $db->getOne($sql);
	}
}

