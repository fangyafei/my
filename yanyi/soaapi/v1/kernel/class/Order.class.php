<?php
class Order{
	var $wdwl_url = '';
	var $error = '';
	var $token = '';

  function __construct() {

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
   * 用户订单列表
   * @param unknown_type $userId
   * @param unknown_type $condition
   */
  public function getOrderList($userId, $condition)
  {
  	global $db, $ecs, $json;
  		
  		$where = "1";
  		$where = " o.buyer_id = '{$userId}'";
		if($condition == 1)
  		{
  			$where .= " AND o.status = 11";
  		}
  		$sql = "SELECT o.order_id, o.order_sn, o.payment_id, o.order_amount, o.status, o.add_time,
  				a.consignee, a.phone_mob, a.phone_tel, a.region_id, a.address, a.zipcode, a.shipping_name,a.shipping_id,  
  				g.*
  				 FROM " . $ecs->table("order") . " AS o ".
  		" LEFT JOIN " . $ecs->table("order_extm"). " AS a ON a.order_id = o.order_id " .
  		" LEFT JOIN " . $ecs->table("order_goods"). " AS g ON g.order_id = o.order_id ".
  		" WHERE ".$where." order by o.add_time DESC";
  		
  		//return $sql;
  		$orderList = $db->getAll($sql);
  		$orders = array();
  		
  		foreach($orderList as $key => $val)
  		{
  			$payWay = $this->format_payment($val["payment_id"]);
  			
  			$self = explode('自提', $val['shipping_name']);
  			
  			if($val["status"] == 20){
  				$s = 0;
  			}
  			elseif($val['status'] == 11){
  				$s = 1;
  			}
  			elseif($val['status'] == 0){
  				$s = 2;
  			}elseif($val['status'] == 40){
  				$s = 3;
  			}elseif($val['status'] == 30){
  				$s = 4;
  			}
  			
  			$orders['orderList'][$val["order_id"]]["id"] = $val["order_id"];
  			$orders['orderList'][$val["order_id"]]["orderCode"] = $val["order_sn"];
  			$orders['orderList'][$val["order_id"]]["orderMoney"] = $val["order_amount"];
  			$orders['orderList'][$val["order_id"]]["orderStatus"] = $s;
  			$orders['orderList'][$val["order_id"]]["orderTime"] = $val["add_time"];
  			$orders['orderList'][$val["order_id"]]["delivery"] = count($self) > 1 ? 1 : 0;
  			$orders['orderList'][$val["order_id"]]["payUrl"] = '';
  			$orders['orderList'][$val["order_id"]]["payWay"] = $payWay;
  			$orders['orderList'][$val["order_id"]]["Consignee"]['name'] = $val["consignee"];
  			$orders['orderList'][$val["order_id"]]["Consignee"]['phone'] = empty($val["phone_mob"]) ? $val["phone_tel"] : $val["phone_mob"];
  			$orders['orderList'][$val["order_id"]]["Consignee"]["cityID"] = $val["region_id"];
  			$orders['orderList'][$val["order_id"]]["Consignee"]["address"] = $val["address"];
  			$orders['orderList'][$val["order_id"]]["Consignee"]["zipCode"] = $val["zipcode"];
  			
  			/*处理商品图像*/
  			if (substr($val['goods_image'], 0,4) != 'http')
  			{
  				$val['goods_image'] = site_url().$val['goods_image'];
  			}
  				
  			$goods = array(
  					'goodsId' => $val["goods_id"],
  					'price'  => $val["price"],
  					'quantity' => $val["quantity"],
  					'goods_image' => $val['goods_image'],
  					'comment'  => $val['comment'],
  					'type_name' => $val['type_name'],
  					'is_diy'   => $val['is_diy'],
  					'height'  => $val['height'],
  					'weight'  => $val['weight'],
  					'emb_con' => $val['emb_con'],
  			);
  			$orders['orderList'][$val["order_id"]]["cartItemList"][] = $goods;
  		}
  		return $json->encode($orders);
  }
  
  /**
   * 订单详情
   * @param unknown_type $orderId
   */
  public function getOrder($orderId)
  {
  	global $db, $ecs, $json;
  	$sql = "SELECT o.order_id, o.order_sn, o.discount, o.postscript, o.order_amount, o.goods_amount, o.payment_id, o.status, o.add_time,
  				a.consignee, a.phone_mob, a.shipping_fee, a.shipping_name, a.shipping_id, a.phone_tel, a.region_id, a.address, a.zipcode,
  				g.*
  				 FROM " . $ecs->table("order") . " AS o ".
  	  				 " LEFT JOIN " . $ecs->table("order_extm"). " AS a ON a.order_id = o.order_id " .
  	  				 " LEFT JOIN " . $ecs->table("order_goods"). " AS g ON g.order_id = o.order_id ".
  	  				 " WHERE o.order_id = '{$orderId}'";
  	
  	$orderList = $db->getAll($sql);
  	$orders = array();
  	foreach($orderList as $key => $val)
  	{
  		$payWay = $this->format_payment($val["payment_id"]);
  		
  		$self = explode('自提', $val['shipping_name']);
  		
  		$orders['totalPrice'] = $val["goods_amount"];
  		$orders['logisticsCost'] = $val["shipping_fee"];
  		$orders['discount'] = $val["discount"];
  		$orders['remark'] = $val["postscript"];
  		$orders['Order']["id"] = $val["order_id"];
  		$orders['Order']["orderCode"] = $val["order_sn"];
  		$orders['Order']["orderMoney"] = $val["order_amount"];
  		
  		if($val["status"] == 20){
  			$s = 0;
  		}
  		elseif($val['status'] == 11){
  			$s = 1;
  		}
  		elseif($val['status'] == 0){
  			$s = 2;
  		}elseif($val['status'] == 40){
  			$s = 3;
  		}elseif($val['status'] == 30){
  			$s = 4;
  		}
  		
  		$orders['Order']["orderStatus"] = $s;
  		$orders['Order']["orderTime"] = $val["add_time"];
  		$orders['Order']["delivery"] = count($self) > 1 ? 1 : 0;
  		$orders['Order']["payUrl"] = '';
  		$orders['Order']["payWay"] = $payWay;
  		$orders['Order']["Consignee"]['name'] = $val["consignee"];
  		$orders['Order']["Consignee"]['phone'] = empty($val["phone_mob"]) ? $val["phone_tel"] : $val["phone_mob"];
  		$orders['Order']["Consignee"]["cityID"] = $val["region_id"];
  		$orders['Order']["Consignee"]["address"] = $val["address"];
  		$orders['Order']["Consignee"]["zipCode"] = $val["zipcode"];
  		
  		/*处理商品图像*/
  		if (substr($val['goods_image'], 0,4) != 'http')
  		{
  			$val['goods_image'] = site_url().$val['goods_image'];
  		}
  			
  		$goods = array(
  				'goodsId' => $val["goods_id"],
  				'price'  => $val["price"],
  				'quantity' => $val["quantity"],
  				'goods_image' => $val['goods_image'],
  				'comment'  => $val['comment'],
  				'type_name' => $val['type_name'],
  				'is_diy'   => $val['is_diy'],
  				'height'  => $val['height'],
  				'weight'  => $val['weight'],
  				'emb_con' => $val['emb_con'],
  				
  				
  		);
  		$orders['Order']["cartItemList"][] = $goods;
  		
  	}
  	return $json->encode($orders);
  }
	
  /**
   *    以购物车为单位获取购物车列表及商品项
   *
   *    @author    copy yhao.bai  add by liang.li
   *    @return    void
   */
  function _cart_main($user_id)
  {
  	$carts = array();
  
  	$where_user_id = "user_id=" . $user_id;
  
  	$cart_model =& m('mobcart');
  
  	$cart_items = $cart_model->find(array(
  			'conditions'    => $where_user_id
  	));
  	 
  	if (empty($cart_items))
  	{
  		return array('goods_list' => array(), 'amount' => 0);
  	}
  
  	$amount    = 0;
  	$goods_num = 0;
  	$weight    = 0;
  	foreach ($cart_items as $item)
  	{
  
  		/* 小计 */
  		$item['subtotal']   = $item['price'] * $item['quantity'];
  
  		/* 总计 */
  		$amount += $item['subtotal'];
  		$goods_num += $item['quantity'];
  		$weight += $item['quantity']*$item['goods_weight'];
  		$carts[] = $item;
  	}
  
  	return array("goods_list" => $carts, "amount" => $amount, 'goods_num' => $goods_num, 'weight' => $weight);
  }
	/**
     * @author copy yaho.bai  add by liang.li
     * @desc 初始化订单信息
     * @return array
     */
    function _order_info($order){
    	global $json;
    	//$order = isset($_SESSION['_order']) ? $_SESSION['_order'] : array();
    	
    	import("address.lib");
    	import("shipping.lib");
    	$_mod_figure = &m("figure");
    	$_mod_pay = &m("payment");
    	$_mod_ship = &m("shipping");
    	$user_id = $order['user_info']['user_id'];
    	/* 量体数据  */
    	if($order['figure']['figure'] > 0){
    		$order['figure'] = array();
    		$data = $_mod_figure->get(array('conditions' => "userid='$user_id'"));
    		$data['figure'] = $data["idfigure"];
    		$data['service'] = $data['idserve'];
    		$order['figure'] = $data['figure'] ? $data : array();
    	}
    	elseif ($order['figure']['figure'] == -1 || $order['figure']['figure']== -2)
    	{
    		$data['figure'] = $order['figure']['figure'];
    		$data['address'] = $order['figure']['address'];
    		$data['service'] = $order['figure']['service'];
    		$data['mobile'] = $order['figure']['mobile'];
    		$data['realname'] = $order['figure']['realname'];
    		$data['region_id'] = $order['figure']['region_id'];
    		$data['region_name'] = $order['figure']['region_name'];
    		$data['retime'] = $order['figure']['retime'];
    		$order['figure'] = $data['figure'] ? $data : array();
    	}
//   print_exit($order);
    	/* 默认地址 */
    	/*app端默认只有登录才能下单*/
    	$def_addr = $order['user_info']['def_addr'];/*会员的默认收货地址*/
    	$address = new Address($user_id);
    	$_def = $address->defAddress($def_addr);
    	if (!$_def)
    	{
    		$arr = array('statusCode'=>1,'msg'=>'此会员还没有填写收货地址');
    		return json_encode($arr);
    	}
    	$order['address'] = $_def;
//   print_exit($order);
    	/*配送*/
    	if($user_id && $_def)
    	{
	    	$shipping = new Shipping($_def["region_id"]);

	    	/*app端支付和配送是固定的*/
	    	//$ship_id = $this->visitor->get('def_ship');
	    			
	    	$shipping = $_mod_ship->get_info($order['shipping']);
	    			 
	    	if($shipping)
	    	{
	    		$order['shipping'] = $shipping;
	    	}
    	}
    	
    	/*支付*/
    	if($user_id){
	    	//$pay_id = $this->visitor->get('def_pay');
	    	/*app端支付和配送都是默认*/
	    	$payment = $_mod_pay->get_info($order['payment']);
	    	if($payment){
	    		$order['payment'] = $payment;
	    	}
    	}
    	
    	
    	
    	/*优惠券*/
    	if(!$order['coupons']){
    		$order['coupons'] = array();
    	}
    	
    	/*酷特币*/
    	if(!$order['coin']){
    		$order['coin'] = 0;
    	}
    	
    	/*积分*/
    	if(!$order['point']){
    		$order['point'] = 0;
    	}
    	
    	return $order;
    }
    
    /**
     * @author copy yhao.bai  add by liang.li
     * @return arr
     */
    function _total($order){
    	 
    	import("shipping.lib");
    	 
    	$_pmt =& f('promotion');
    	 
    	/* 购物车信息 */
    	$result = array(
    			'goods_list'    => array(),
    			'goods_amount'  => 0,
    			'goods_num'     => 0,
    			'figure_fee'    => 0,
    			'shiping_fee'   => 0,
    			'discount'      => 0,
    			'integral'      => 0,
    			'amount'        => 0,
    			'coin_fee'      => 0,
    			'point_fee'     => 0,
    			'coupon_fee'    => 0,
    			'is_diy'        => 0,
    			'pmts'          => array(),
    	);
	
    	$user_id = $order['user_info']['user_id'];
    	$main    = $this->_cart_main($user_id);
    	
    	if(empty($main['goods_list'])){
    		 
    		return $result;
    	}

    	$result['user_id'] = $user_id;
    	
    	$is_diy = 0;
    	 
    	foreach($main['goods_list'] as $key => $val){
    		if($val['is_diy'] == 1){
    			$is_diy = 1;
    		}
    	}
    
    	$order['is_diy'] = $is_diy;
    	$result['is_diy'] = $is_diy;
    
    	$result['goods_list']   = $main['goods_list'];
    	$result['amount'] = $result['goods_amount'] = $main['amount'];
    	$result['goods_num']    = $main['goods_num'];
    	 
    	$pmts = array();
    
    	$pmts = $_pmt -> orderPromotion($user_id,$main['amount']);
    	$order['promotions'] = $pmts;
    
    	$result['pmts'] = $pmts;
    	 
    	$address  = $order["address"];
    	//$payment  = $_order["payment"];
    	$shipping = $order['shipping'];
    	 
    	$figure   = $order['figure'];
    	//$invoice  = $_order['invoice'];
    	$coupons  = $order['coupons'];
    	 
    	$coin     = $order['coin'];
    	 
    	$point    = $order['point'];
    	 
    	if($figure){
    		if($figure["figure"] == "-1"){
    			//暂时固定量体费用80 ，待后台优化
    			$result["figure_fee"] = 0;
    			 
    			$result['amount']+= $result["figure_fee"];
    		}
    	}
    	 
    	if($address && $shipping){
    		//实例 配送类
    		$ship = new Shipping($address["region_id"]);
    
    		//根据配送地区取出配送区域及费用
    		$_ship = $ship->shipInfo($shipping["shipping_id"]);
    
    		$fp = $_ship['ainfo']['first_price']; //首重费用
    		$sp = $_ship['ainfo']['step_price']; //续重费用
    
    		$fw = $_ship['sinfo']['first_weight']; //首重重量
    		$sw = $_ship['sinfo']['step_weight'];  //续重重量
    
    		//首重费用
    		$result['shipping_fee'] = $fp;
    
    		//计算续重费用
    		if($main['weight'] > $fw){
    			//((商品重量-首重重量)/续重重量)*续重费用
    			$result['shipping_fee'] += round((ceil(($main['weight']-$fw)/$sw))*$sp,2);
    		}
    		$result['amount']+= $result['shipping_fee'];
    		//print_R($_ship);
    	}
    	 
    	if($coin){
    		//比例换算1:1
    		$k = CONF::get("kutebi_use_proportion");
    
    		$result['coin_fee'] = $coin/$k;
    		$result['amount'] -= $result['coin_fee'];
    	}
    	 
    	if($point){
    		//比例换算1:10
    		$p = CONF::get("point_use_proportion");
    		$point_fee = $point/$p;
    		$result['point_fee'] = $point_fee;
    		$result['amount'] -= $result['point_fee'];
    	}
    	 
    	/* 优惠券 */
    	if($coupons){
    		foreach($coupons as $key => $val){
    			$result['coupon_fee'] += $val['money'];
    		}
    		 
    		$result['amount'] -= $result['coupon_fee'];
    	}
    	 
    	/* 优惠促销 */
    	if($order['promotions']){
    		foreach($order['promotions'] as $key => $val){
    			if($val['gift'] == 0){
    				$result['discount'] += $val['money'];
    			}
    		}
    
    		$result['amount'] -= $result['discount'];
    	}
    	 
    	//$_SESSION['_order'] = $order;
    	 
    	$result['amount'] = max(0,$result['amount']);
    	 
    	return $result;
    }
    
    /**
     * 提交订单
     * @param  $user_info    会员信息
     * @param  $goodsList    商品信息
     * @param  $consigneeId  收货信息ID
     * @param  $shipping     配送方式
     * @param  $payment      支付方式
     * @param  $remark       备注
     * @author Ruesin
     */
    public function submitOrder($token, $shipping, $payment,$remark,$address,$figure,$mobile,$realname,$region_id,$region_name,$retime,$service,$invoiceneed,$invoicetype,$invoicecontent,$invoicetitle){
  		global $db, $ecs, $json;
  		$_mod_order = &m("order");
  		$user_info = getUserInfo($token);
  		if (!$user_info)
  		{
  			$arr = array('statusCode'=>1,'msg'=>'查无此人');
  			return $json->encode($arr);
  		}
  		$user_id = $user_info['user_id'];
  		
  		$arr_f = array(
  				'figure'      => $figure,
  				'mobile'      => $mobile,
  				'realname'    => $realname,
  				'region_id'   => $region_id,
  				'region_name' => $region_name,
  				'retime'      => $retime,
  				'service'     => $service,
  				'address'     => $address,
  		);
  		$invoice = array(
  				'invoiceneed' => $invoiceneed,
  				'invoicetype' => $invoicetype,
  				'invoicecontent' => $invoicecontent,
  				'invoicetitle' => $invoicetitle,
  		);
//  print_exit($arr_f);	
  		$order = array('shipping'=>$shipping,'payment'=>$payment,'user_info'=>$user_info,'figure'=>$arr_f,'invoice'=>$invoice);
  		$_order = $this->_order_info($order);
// print_exit($_order);	
  		
  		$aCart = $this->_total($_order);
  		$otype = $aCart['is_diy'] == 1 ? "diy" : "normal";
  		$order_type =& ot_mob($otype);/**/
// print_exit($aCart);
  		if(!$aCart['goods_list']){
  			$arr = array('statusCode'=>1,'msg'=>'购物车没有商品');
  			return $json->encode($arr);
  		}
  		
  		$coupons  = $_order['coupons'];
  		
  		$coin     = isset($_order['coin']) ? $_order['coin'] : 0;
  		 
  		$point 	  = isset($_order['point']) ? $_order['point'] : 0;
  		 
  		$objCou = &f("coupon");
  		 
  		$allow_coin = $this->_use_coin($user_id);
  		 
  		$allow_point = $this->_use_point($user_id);
//echo $allow_coin;exit;		 
  		$coupon_sn = array();
  		
  		/*如果选择了跟量体相关的 并且没有选择历史量体数据  要验证跟量体相关参数必填*/
  		if ($aCart['is_diy'] == 1)
  		{
  			if ($figure == -1 || $figure == -2)
  			{
  				/*预约量体 以下参数比填*/
  			}
  		}
  		
  		$msg = '';
  		 
  		//验证优惠券
  		if(!empty($coupons)){
  			$sns = array();
  			foreach($coupons as $key => $val){
  				$sns[] = $val['sn'];
  			}
  		
  			$gids = array();
  			foreach($aCart['goods_list'] as $key => $val){
  				$gids[]=$val['goods_id'];
  			}
  		
  			$res = $objCou->getCoupon($user_id, implode(',',$sns), $aCart['goods_amount'], implode(",",$gids));
  		
  			if(!empty($res['msg'])){
  				$arr = array('statusCode'=>1,'msg'=>'优惠劵验证失败');
  				return $json->encode($arr);
  			}
  		}

  		if(!empty($msg)){
  			$arr = array('statusCode'=>1,'msg'=>$msg.'优惠劵验证失败');
  			return $json->encode($arr);
  		}
  		 
  		if($coin > $allow_coin){
  			$arr = array('statusCode'=>1,'msg'=>"此次订单最多可用$allow_coin 个酷特币");
  			return $json->encode($arr);
  		}
  		
  		if($coin > $user_info["coin"]){
  			$arr = array('statusCode'=>1,'msg'=>"超出您所持有的酷特币数量");
  			return $json->encode($arr);
  		}
  		 
  		if($point > $allow_point){
  			$arr = array('statusCode'=>1,'msg'=>"此次订单最多可用$allow_point 个积分数");
  			return $json->encode($arr);
  		}
  		 
  		if($point > $user_info["point"]){
  			$arr = array('statusCode'=>1,'msg'=>"超出您所持有的积分数量");
  			return $json->encode($arr);
  		}
// print_exit($_order);
  		/* 事件开始 */
  		$transaction = $_mod_order->beginTransaction();
  		
  		$oInfo = $order_type->submit(array(
  				'_order' => $_order,
  				'_cart'  => $aCart,
  		));

  		
  		if (isset($oInfo['stateCode']) || $oInfo['stateCode'] == 1)
  		{
  			/* 事务回滚 */
  			$_mod_order->rollback();
  			return $json->encode($oInfo);
  		}
  		 
  		/* 提交 */
  		$_mod_order->commit($transaction);
  		$this->_clear($user_id);
//   	print_exit($oInfo);
  		return $json->encode(array('stateCode'=>0,'oInfo'=>$oInfo));
  }
  
	  /**
	   * @author yhao.bai
	   * @desc   此次订单最多使用酷特币数
	   * @return num
	   */
	  function _use_coin($user_id){
	  	$main    = $this->_cart_main($user_id);
	  	return $main['amount'];
	  }
	  
	  /**
	   * @author yhao.bai
	   * @desc   此次订单最多使用酷特币数
	   * @return num
	   */
	  function _use_point($user_id){
	  	$main    = $this->_cart_main($user_id);
	  	return $main['amount'];
	  }
    /**
     *    生成订单号
     *    @author    yhao.bai
     *    @return    string
     */
	function _gen_order_sn(){
	    $mOrder        = &m("order");
		mt_srand((double) microtime() * 1000000);
		$timestamp = gmtime();
		$y = date('y', $timestamp);
		$z = date('z', $timestamp);
		$order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
		$orders = $mOrder->find('order_sn=' . $order_sn);
		if (empty($orders)){
			return $order_sn;
		}	
		return $this->_gen_order_sn();
	}
	
	
	
    /**
    * 获取 商品/订单 金额
    * @param
    * @return
    * @author Ruesin
    */
    function orderAmount($goods,$shipping,$addr,$figures_info){
        import("shipping.lib");
        
        foreach ($goods as $key=>$gls){
            $goods[$key]['subtotal'] = $gls['price'] * $gls['quantity']; //商品小计
            $result["goods_amount"] += $goods[$key]['subtotal'];     //商品总额
            $weight += 1;                                 //订单商品重量//暂时写死//----------------
        }
        $result['goods_list'] = $goods;
        if($figures_info){
            if($figures_info["idfigure"] == "-1"){
                //暂时固定量体费用80 ，待后台优化//---------------------------
                $result["figure_fee"] = 80;
            }
        }
        if($addr && $shipping){
            //实例 配送类
            $ship = new Shipping($addr["region_id"]);
            //根据配送地区取出配送区域及费用
            $_ship = $ship->shipInfo($shipping);
            $fp = $_ship['ainfo']['first_price']; //首重费用
            $sp = $_ship['ainfo']['step_price']; //续重费用
        
            $fw = $_ship['sinfo']['first_weight']; //首重重量
            $sw = $_ship['sinfo']['step_weight'];  //续重重量
            //首重费用
            $result['shipping_fee'] = $fp;
            //计算续重费用//商品总重量
            if( $weight > $fw){
                //((商品重量-首重重量)/续重重量)*续重费用
                $result['shipping_fee'] += round((ceil(($main['weight']-$fw)/$sw))*$sp,2);
            }
        }
        return $result;
    }
    
    function saveOrders($baseinfo,$address,$figure_info,$goodsinfo){
        $mOrder        = &m("order");
        $mOrderextm    = &m('orderextm');
        $mOrderfigure  = &m('orderfigure');
        $mOrdergoods   = &m('ordergoods');
        
        $baseinfo['order_sn']   = $this->_gen_order_sn();
        $baseinfo['status']     = ORDER_PENDING;
        $baseinfo['type']       = 'normal';
        $baseinfo['extension']  = 'normal';
        $baseinfo['add_time'] = time();
        
        /* 保存订单信息 */
        $order_id = $mOrder->add($baseinfo);
        if (!$order_id)return false;
        
        
        /* 保存收货地址信息 */
        $address['order_id'] = $order_id;
        $res = $mOrderextm->add($address);
        if (!$res)return false;
       
        
        /* 保存量体数据 */
        //目前没有传量体地址过来,就用了收货地址的信息
        $figure_info['realname']        =$address['consignee'];
        $figure_info['mobile']          =$address['phone_mob'];
        $figure_info['region_name']     =$address['region_name'];
        $figure_info['region_id']       =$address['region_id'];
        $figure_info['address']         =$address['address'];
        $figure_info['retime']          = 0;//预约时间
        $figure_info['serviceid']       = 0;//服务点
        $figure_info['order_id']    = $order_id;
        
        $res = $mOrderfigure->add($figure_info);
        if (!$res)return false;
        
        /* 保存商品数据 */
        foreach ($goodsinfo as $key => $value){
            
//             $goods_items[] = array(
            $goods_items = array(
                'order_id'      =>  $order_id,
                'goods_id'      =>  $value['goods_id'],
                'goods_name'    =>  $value['goods_name'],
                'spec_id'       =>  $value['spec_id'],
                'specification' =>  $value['specification'],
                'price'         =>  $value['price'],
                'quantity'      =>  $value['quantity'],
                'goods_image'   =>  $value['goods_image'],
            );
            //         $res = $mOrdergoods->add(addslashes_deep($goods_items)); //防止二次注入
            $res = $mOrdergoods->add($goods_items);
            if (!$res)return false;
        }
        //日志表??

        return array('order_id' => $order_id, 'order_sn' => $baseinfo['order_sn'] ,'amount' => $baseinfo['order_amount'] , 'alipy_url'=>'http://www.baidu.com' );
    }
    function _fmtFigure($figures){
        $res['figure'] = $figures['idfigure'];
        $res['figure_name'] = '标准码A';
        $res['lw']      = $res['lw'];
        $res['xw']      = $res['xw'];
        $res['zyw']     = $res['zyw'];
        $res['tw']      = $res['tw'];
        $res['stw']     = $res['stw'];
        $res['zjk']     = $res['zjk'];
        $res['yxc']     = $res['yxc'];
        $res['zxc']     = $res['zxc'];
        $res['qjk']     = $res['qjk'];
        $res['hyc']     = $res['hyc'];
        $res['yw']      = $res['yw'];
        $res['td']      = $res['td'];
        $res['hyg']     = $res['hyg'];
        $res['qyg']     = $res['qyg'];
        $res['kk']      = $res['kk'];
        //$res['hyjc']    = $res['hyjc'];     /没这个字段?
        //$res['qyj']     = $res['qyj'];
        //$res['tgw']     = $res['tgw'];
        //$res['zkc']     = $res['zkc'];
        //$res['ykc']     = $res['ykc'];
        //$res['xiw']     = $res['xiw'];
//         $res['realname']    ='';
//         $res['mobile']      ='';
//         $res['region_name'] ='';
//         $res['region_id']   ='';
//         $res['address']     ='';
//         $res['retime']      ='';
//         $res['serviceid']   ='';
//         $res['order_id']    = '';
        return $res;
    }
    
    
    
    
    ////-----------------------------


    ////*********** Old *****************////
    
    
    public function cancelOrder($userId, $orderId){
      		global $db, $ecs, $json;
      		$sql = "SELECT COUNT(1) FROM " . $ecs->table("order") . " WHERE buyer_id='{$userId}' AND order_id = '{$orderId}'";
      		if(!$db->getOne($sql)){
      		    return $json->encode(array("statusCode" =>1,'msg'=>'此订单信息不存在，请重试！'));
      		}
      		$res = $db->autoExecute($ecs->table("order"), array('status' => 0), "UPDATE", "buyer_id='{$userId}' AND order_id = '{$orderId}'");
      		$val = 0;
      		if(!$res){
      		    $val = 1;
      		}
      		return $json->encode(array("statusCode" => $val));
    }
    
    public function format_payway($payway=0){
        global $db, $ecs;
        $code = '';
        if($payway == 0){
            $code = 'alipay';
        }
        if($payway == 1){
            $code = "card";
        }
    
        if($payway == 2){
            $code = "cod";
        }
    
        return $db->getOne("SELECT payment_id FROM " . $ecs->table("payment") . " WHERE payment_code = '{$code}'");
    }
    
    public function format_payment($pay_id){
        global $db, $ecs;
        $sql = "SELECT payment_code FROM " . $ecs->table("payment") . " WHERE payment_id = '{$pay_id}'";
        //return $sql;
        $info = $db->getRow($sql);
        if($info['payment_code'] == "alipay"){
            return 0;
        }
        if($info['payment_code'] == "card"){
            return 1;
        }
    
        if($info['payment_code'] == "cod"){
            return 2;
        }
        return 0;
    }
    
    public function getShippingId($dev){
        global $db, $ecs;
        $where = " shipping_name not like '%自提%'";
        if($dev == 1){
            $where = " shipping_name like '%自提%'";
        }
        $sql = "SELECT shipping_id, shipping_name FROM ". $ecs->table("shipping") . ' WHERE ' . $where . " limit 1";
    
        return $db->getRow($sql);
    }
    
    /**
     * 获取会员的量体数据
     */
    public function getFigure($token)
    {
    	global $json;
    	$userInfo = getUserInfo($token);
    	if (!$userInfo)
    	{
    		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
    		return $json->encode($arr);
    	}
    	$user_id = $userInfo['user_id'];
    	
    	$figure = &m("figure");
    	$figure = $figure->get(array(
    			'conditions' => "userid='$user_id'",
    	));
    	
    	if ($figure)
    	{
    		return $json->encode($figure);
    	}
    	else
    	{
    		$arr = array( 'statusCode'=>1,'msg'=>'该会员暂无量体数据');
    		return $json->encode($arr);
    	}
    }
    
    /**
     * 
     * @param 根据region_id获得对应的服务点
     */
    public function getService($id)
    {
    	global $json;
    	$_serve = m('serve');
    	$conditions=' and region_id='.$id;
    	$serves = $_serve->find(array(
    			'conditions' => 'serve_type=2 AND state=1 ' . $conditions,
    			'order' => "idserve desc",
    			'count' => true,
    			'fields'=>'idserve,serve_name,mobile,serve_address',
    	));
    	return $json->encode($serves);
    }
    
    /**
     * 查询购物车是否存在我要量体选择
     */
    public function getIsdiy($token)
    {
    	global $json;
    	$userInfo = getUserInfo($token);
    	if (!$userInfo)
    	{
    		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
    		return $json->encode($arr);
    	}
    	$user_id = $userInfo['user_id'];
    	$cart = &m("mobcart");
    	
    	$is_diy = 0;
    	
    	$cart_list = $cart->find(array(
    			'conditions' => 'user_id='.$user_id,
    			'fields'     => 'is_diy',
    			));
    	foreach ($cart_list as $v)
    	{
    		if ($v['is_diy'] == 1)
    		{
    			$is_diy = 1;
    		}
    	}
    	
    	if ($is_diy) {
    		$arr = array('is_diy'=>$is_diy,'msg'=>'订单要传量体相关参数');
    	}
    	else
    	{
    		$arr = array('is_diy'=>$is_diy,'msg'=>'订单不需要传量体相关参数');
    	}
    	return $json->encode($arr);
    }
    
    /**
     * 响应订单
     */
    public function resOrder($order_id,$notify_result)
    {
    	$order_type = "normal";
    	$order_type  = ot_mob($order_type);
    	$order_type->respond_notify($order_id, $notify_result);    //响应通知
    }
    
    
    function _clear($user_id)
    {
    	$mobCart = m('mobcart');
    	$mobCart->drop("user_id='" . $user_id . "'");
    	
    }
    
}



?>