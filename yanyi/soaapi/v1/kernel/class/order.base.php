<?php

!defined('ROOT_PATH') && exit('Forbidden');

/**
 *    订单类型基类
 *
 *    @author    yhao.bai
 *    @usage    none
 */
class BaseOrder extends Object
{
    var $_mod_order;
    var $_mod_orderextm;
    var $_mod_orderfigure;
    var $_mod_ordergoods;
    var $_mod_orderlog;
    var $_mod_ordercron;
    var $_mod_orderpmt;
    var $_errors;

    function __construct(){
    	$this->_mod_order       = &m("order");
    	$this->_mod_orderextm   = &m('orderextm');
    	$this->_mod_orderfigure = &m('orderfigure');
    	$this->_mod_ordergoods  = &m('ordergoods');
    	$this->_mod_orderlog    = &m('orderlog');
    	$this->_mod_ordercron   = &m("ordercron");
    	$this->_mod_orderpmt    = &m("orderpmt");
    }
   
    /**
     *    获取订单类型名称
     *
     *    @author    yhao.bai
     *    @return    string
     */
    function get_name()
    {
        return $this->_name;
    }

    /**
     *    获取订单详情
     *
     *    @author    yhao.bai
     *    @param     int $order_id
     *    @param     array $order_info
     *    @return    array
     */
    function get_order_detail($order_id, $order_info)
    {
        if (!$order_id)
        {
            return array();
        }

        /* 订单基本信息 */
        $data['order_info'] =   $order_info;

        return array('data' => $data, 'template' => 'normalorder.view.html');
    }


    /**
     *    响应支付通知
     *
     *    @author    yhao.bai
     *    @param     int    $order_id
     *    @param     array  $notify_result
     *    @return    bool
     */
    function respond_notify($order_id, $notify_result)
    {
        $where = "order_id = '{$order_id}'";
        $data = array('status' => $notify_result['target']);
        switch ($notify_result['target'])
        {
            case ORDER_ACCEPTED:
                $where .= ' AND status=' . ORDER_PENDING;   //只有待付款的订单才会被修改为已付款
                $data['pay_time']   =   gmtime();
                

                $goods_list = $this->_get_goods_list($order_id);
                               
                $order_info = $this->_order_info($order_id);
                
                if($order_info['status'] != ORDER_PENDING ) return;
                
                
                $_Finfo = $this->_mod_orderfigure->get("order_id='{$order_id}'");
       
                $has_figure = 0;
                
                /**********付款完成，写入服务点分成********************/
                if($_Finfo && !in_array($_Finfo['figure'], array("-1", "-2"))){
                	
                	$figurelog_mod=m('figureorderlog');

                	$figurelog_mod->addlog($order_id,$_Finfo['serviceid']);
                	
                	$has_figure = 1;
                }
                /**********end********************/
                
                /**********付款完成送优惠相关********************/
                $pmts = $this->_get_order_pmt($order_id);
                
                $sends = array();
                foreach((array)$pmts as $key => $val){
                	if($val["sendtype"] && $order_info['buyer_id']){
	                	$sends[] = array(
                				'order_sn'   =>  $order_info['order_sn'],
                				'uid'        =>  $order_info['buyer_id'],
                				'sendtype'   =>  $val['sendtype'],
                				'msg'        =>  $val['pmt_desc'],
                			);
                	}
                }
                
                if($sends){
                	$promotion =& f('promotion');
                	$promotion->realSend($sends);
                }
                
                /**********end********************/
                  
                /**********付款完成写入同步队列******************
                $cron = array(
                		'is_scales' => $has_figure,
                		'fabric_id' => 0,
                );
                
                $this->_mod_ordercron->add();
                **********************************************/
                
                /**********付款完成设计师，送酷特币********************/
                foreach($goods_list as $key => $val){
                	if($val["cst_source"] > 0){
                		$num = pointTurnNum("user_order_reward");
                		$cate = $val["cst_source"] == "1" ? 'sheji_order' : 'jiepai_order';
                		$msg = "用户购买商品送酷特币,比例：100:$num";
                		setCoin($val['cst_author'], ceil(($val['price']*$val["quantity"])*($num/100)), 'add', $cate, "system", $msg);
                	}
                }
                /**********end********************/
                
                /**********付款完成用户，送积分********************/
                $num = pointTurnNum("order_reward");
                $msg = "下订单送积分,订单号:{$order_info["order_sn"]},比例：100:$num";
                if($order_info['buyer_id']){
                	setPoint($order_info['buyer_id'], ceil($order_info['goods_amount']*($num/100)), 'add', "order_reward", 'system', $msg);
                }
                /**********end********************/
                
            break;
            case ORDER_SHIPPED:
                $where .= ' AND status=' . ORDER_ACCEPTED;  //只有等待发货的订单才会被修改为已发货
                $data['ship_time']  =   gmtime();
            break;
            case ORDER_FINISHED:
                $where .= ' AND status=' . ORDER_SHIPPED;   //只有已发货的订单才会被自动修改为交易完成
                $data['finished_time'] = gmtime();
            break;
            case ORDER_CANCLED:                             //任何情况下都可以关闭
                /* 加回商品库存 待定状态 */
               // $model_order->change_stock('+', $order_id);
            break;
        }
        
        return $this->_mod_order->edit($where, $data);
    }


    /**
     *    生成订单号
     *
     *    @author    yhao.bai
     *    @return    string
     */
    function _gen_order_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $timestamp = gmtime();
        $y = date('y', $timestamp);
        $z = date('z', $timestamp);
        $order_sn = $y . str_pad($z, 3, '0', STR_PAD_LEFT) . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $orders = $this->_mod_order->find("order_sn='{$order_sn}'");
        if (empty($orders))
        {
            /* 否则就使用这个订单号 */
            return $order_sn;
        }

        /* 如果有重复的，则重新生成 */
        return $this->_gen_order_sn();
    }
	
    /**
     *    获取商品列表
     *
     *    @author    yhao.bai
     *    @param     int $order_id
     *    @return    array
     */
    function _get_goods_list($order_id)
    {
        if (!$order_id)
        {
            return array();
        }

        return $this->_mod_ordergoods->find("order_id='{$order_id}'");
    }

    /**
     * 获取订单优惠信息
     * @author yhao.bai
     * @param  int $order_id
     * @return arr
     */
    function _get_order_pmt($order_id){
    	if (!$order_id)
    	{
    		return array();
    	}
    	
    	return $this->_mod_orderpmt->find("order_id='{$order_id}'");
    }
    /**
     *    获取扩展信息
     *
     *    @author    yhao.bai
     *    @param     int $order_id
     *    @return    array
     */
    function _get_order_extm($order_id)
    {
        if (!$order_id)
        {
            return array();
        }

        return $this->_mod_orderextm->get($order_id);
    }

    /**
     *    获取订单操作日志
     *
     *    @author    yhao.bai
     *    @param     int $order_id
     *    @return    array
     */
    function _get_order_logs($order_id)
    {
        if (!$order_id)
        {
            return array();
        }

        return $this->_mod_orderlog->find("order_id = {$order_id}");
    }

    /**
     *    处理订单基本信息,返回有效的订单信息数组
     *
     *    @author    yhao.bai
     *    @param     array $order
     *    @param     array $cart
     *    @return    array
     */
    function _handle_order_info($order, $cart)
    {
        /* 默认都是待付款 */
        $order_status = ORDER_PENDING;
        
		if($cart['amount'] == 0){
			$order_status = ORDER_ACCEPTED;
		}
		
        /* 买家信息 */
        //$visitor     =& env('visitor');
        $user_id     =  $order['user_info']['user_id'];
        $user_name   =  $order['user_info']['user_name'];
        
        $payment = $order['payment'];
        if(empty($payment)){
        	$this->_error('没有选择支付方式');
        	return false;
        }

        $order_sn = $this->_gen_order_sn();
        
        $invoice = $order["invoice"];
        
       /*  if($invoice && $invoice['invoiceneed']){
        	$baseinfo['invoiceneed'] = $invoice['invoiceneed'];
        	$baseinfo['invoicetitle'] = $invoice['invoicetitle'];
        	$baseinfo['invoicetype'] = $GLOBALS['__ECLANG__']['invoicetype'][$invoice['invoicetype']];
        	$baseinfo['invoicecontent'] = $GLOBALS['__ECLANG__']['invoicecontent'][$invoice['invoicecontent']];
        } */
        
        /* 返回基本信息 */
        return array(
        	'source_from'   => 'app',
            'order_sn'      =>  $order_sn,
        	'out_trade_sn'  =>  '',
            'type'          =>  $this->_name,
            'extension'     =>  $this->_name,
            'seller_id'     =>  0,
            'seller_name'   =>  '',
            'buyer_id'      =>  $user_id,
            'buyer_name'    =>  addslashes($user_name),
            'buyer_email'   =>  $order['user_info']['email'],
            'status'        =>  $order_status,
            'add_time'      =>  gmtime(),
            'surplus'       =>  0,
            'goods_amount'  =>  $cart['goods_amount'],
            'discount'      =>  $cart['discount'],
            'anonymous'     =>  0,
            'postscript'    =>  $order['postscript'],
        	'order_amount'  =>  $cart['amount'], 
    		'payment_id'    =>  $payment['payment_id'],
    		'payment_name'  =>  $payment["payment_name"],
    		'payment_code'  =>  $payment["payment_code"],
    		'coin'          =>  $order['coin'],
    		'point'         =>  $order['point'],
    		'coin_fee'      =>  $cart['coin_fee'],
    		'point_fee'     =>  $cart['point_fee'],
    		'coupon_fee'    =>  $cart['coupon_fee'],
        	'figure_fee'    =>  $cart['figure_fee'],
        	'invoiceneed'   =>  $invoice['invoiceneed'],
        	'invoicetitle'  =>  $invoice['invoicetitle'],
        	'invoicetype'   =>  $invoice['invoicetype'] ,
        	'invoicecontent' => $invoice['invoicecontent'] ,
        );
    }

    /**
	 *    处理收货人信息，返回有效的收货人信息
	 *
	 *    @author    yhao.bai
	 *    @param     array $order
	 *    @param     array $cart
	 *    @return    array
	 */
	function _handle_consignee_info($order, $cart)
	{
		$address = $order['address'];
		
		/* 验证收货人信息填写是否完整 */
		$consignee_info = $this->_valid_consignee_info($address);
		
		if (!$consignee_info)
		{
			return false;
		}
	
		if (!$order['shipping']['shipping_id'])
		{
			$this->_error('shipping_required');
		
			return false;
		}
		
		return array(
				'consignee'     =>  $consignee_info['consignee'],
				'region_id'     =>  $consignee_info['region_id'],
				'region_name'   =>  $consignee_info['region_name'],
				'address'       =>  $consignee_info['address'],
				'zipcode'       =>  $consignee_info['zipcode'],
				'phone_tel'     =>  $consignee_info['phone_tel'],
				'phone_mob'     =>  $consignee_info['phone_mob'],
				'shipping_id'   =>  $order['shipping']['shipping_id'],
				'shipping_name' =>  addslashes($order['shipping']['shipping_name']),
				'shipping_fee'  =>  $cart['shipping_fee'],
		);
	}
	
	
	/**
	 *    验证收货人信息是否合法
	 *
	 *    @author    yhao.bai
	 *    @param     array $consignee
	 *    @return    void
	 */
	function _valid_consignee_info($consignee)
	{
		if (!$consignee['consignee'])
		{
			$this->_error('consignee_empty');
	
			return false;
		}
		if (!$consignee['region_id'])
		{
			$this->_error('region_empty');
	
			return false;
		}
		if (!$consignee['address'])
		{
			$this->_error('address_empty');
	
			return false;
		}
		if (!$consignee['phone_tel'] && !$consignee['phone_mob'])
		{
			$this->_error('phone_required');
	
			return false;
		}
	
		return $consignee;
	}
	
	/**
	 *    处理订单量体数据,返回有效的量体数据
	 *
	 *    @author    yhao.bai
	 *    @param     array $figure
	 */
	function _handle_figure_info($figure){
		//unset($figure['service']);
// print_exit($figure);
		$data = $this->_valid_figure_info($figure);
	
		if (isset($data['error']) || $data['error'] == 1)
		{
			return $data;
		}
		return array(
				'figure_name' =>  $data['figure_name'],
				'lw ' =>  $data['lw'],
				'xw ' =>  $data['xw'],
				'zyw ' =>  $data['zyw'],
				'tw ' =>  $data['tw'],
				'stw ' =>  $data['stw'],
				'zjk ' =>  $data['zjk'],
				'yxc ' =>  $data['yxc'],
				'zxc ' =>  $data['zxc'],
				'qjk ' =>  $data['qjk'],
				'hyc ' =>  $data['hyc'],
				'yw ' =>  $data['yw'],
				'td' =>  $data['td'],
				'hyg ' =>  $data['hyg'],
				'qyg' =>  $data['qyg'],
				'kk' =>  $data['kk'],
				'hyjc ' =>  $data['hyjc'],
				'qyj' =>  $data['qyj'],
				'tgw ' =>  $data['tgw'],
				'zkc ' =>  $data['zkc'],
				'ykc' =>  $data['ykc'],
				'xiw ' =>  $data['xiw'],
				'body_type_19 ' =>  $data['body_type_19'],
				'body_type_20 ' =>  $data['body_type_20'],
				'body_type_24 ' =>  $data['body_type_24'],
				'body_type_25 ' =>  $data['body_type_25'],
				'body_type_26 ' =>  $data['body_type_26'],
				'body_type_3 ' =>  $data['body_type_3'],
				'body_type_2000 ' =>  $data['body_type_2000'],
				'styleLength ' =>  $data['styleLength'],
				'realname' => $data['realname'],
				'mobile'  => $data['mobile'],
				'region_name' => $data['region_name'],
				'region_id' => $data['region_id'],
				'address' => $data['address'],
				'retime' => strtotime($data['retime']),
				'serviceid' => $data['service'],
				'figure'  => $data['figure'],
		);
	}
	
	/*验证量体数据*/
	function _valid_figure_info($figure){
		global $json;
		if($figure['figure'] > 0){
			if(!$figure['figure_name']){
				return array('error'=>1,'msg'=>'量体名称不能为空');
			}
			
			if(!$figure['lw']){
				return array('error'=>1,'msg'=>'量体lw不合法');
			}
			if(!$figure['xw']){
				return array('error'=>1,'msg'=>'量体xw不合法');
			}
			if(!$figure['zyw']){
				return array('error'=>1,'msg'=>'量体zyw不合法');
			}
			if(!$figure['tw']){
				return array('error'=>1,'msg'=>'量体tw不合法');
			}
			if(!$figure['stw']){
				return array('error'=>1,'msg'=>'量体stw不合法');
			}
			if(!$figure['zjk']){
				return array('error'=>1,'msg'=>'量体zjk不合法');
			}
			if(!$figure['yxc']){
				return array('error'=>1,'msg'=>'量体yxc不合法');
			}
			if(!$figure['zxc']){
				return array('error'=>1,'msg'=>'量体zxc不合法');
			}
			if(!$figure['qjk']){
				return array('error'=>1,'msg'=>'量体qjk不合法');
			}
			if(!$figure['hyc']){
				return array('error'=>1,'msg'=>'量体hyc不合法');
			}
			if(!$figure['yw']){
				return array('error'=>1,'msg'=>'量体yw不合法');
			}
			if(!$figure['td']){
				return array('error'=>1,'msg'=>'量体td不合法');
			}
			/* if(!$figure['hyg']){
				return array('error'=>1,'msg'=>'量体hyg不合法');
			}
			if(!$figure['qyg']){
				return array('error'=>1,'msg'=>'量体qyg不合法');
			} 
			if(!$figure['kk']){
				return array('error'=>1,'msg'=>'量体kk不合法');
			}*/
			if(!$figure['hyjc']){
				return array('error'=>1,'msg'=>'量体hyjc不合法');
			}
			if(!$figure['qyj']){
				return array('error'=>1,'msg'=>'量体qyj不合法');
			}
			if(!$figure['tgw']){
				return array('error'=>1,'msg'=>'量体tgw不合法');
			}
			if(!$figure['zkc']){
				return array('error'=>1,'msg'=>'量体zkc不合法');
			}
			if(!$figure['ykc']){
				return array('error'=>1,'msg'=>'量体ykc不合法');
			}
			/* if(!$figure['xiw']){
				return array('error'=>1,'msg'=>'量体xiw不合法');
			} */
			
			if(!$figure['body_type_19']){
				return array('error'=>1,'msg'=>'量体body_type_19不合法');
			}
			if(!$figure['body_type_20']){
				return array('error'=>1,'msg'=>'量体body_type_20不合法');
			}
			if(!$figure['body_type_24']){
				return array('error'=>1,'msg'=>'量体body_type_24不合法');
			}
			if(!$figure['body_type_25']){
				return array('error'=>1,'msg'=>'量体body_type_25不合法');
			}
			if(!$figure['body_type_26']){
				return array('error'=>1,'msg'=>'量体body_type_26不合法');
			}
			
			if(!$figure['body_type_3']){
				return array('error'=>1,'msg'=>'量体body_type_3不合法');
			}
			
			if(!$figure['body_type_2000']){
				return array('error'=>1,'msg'=>'量体body_type_2000不合法');
			}
			
			if(!$figure['styleLength']){
				return array('error'=>1,'msg'=>'量体styleLength不合法');
			}
			
			if(!$figure['part_label_10130']){
				return array('error'=>1,'msg'=>'量体part_label_10130不合法');
			}
				
			if(!$figure['part_label_10131']){
				return array('error'=>1,'msg'=>'part_label_10131');
			}
				
			/* if(!$figure['part_label_10725']){
				return array('error'=>1,'msg'=>'量体part_label_10725不合法');
			}
				
			if(!$figure['part_label_10726']){
				return array('error'=>1,'msg'=>'量体part_label_10726不合法');
			}
			 */
			if(!$figure['service']){
				return array('error'=>1,'msg'=>'量体数据没有服务点');
			}
		}else{
		
			if(!in_array($figure['figure'], array("-1","-2"))){
				return array('error'=>1,'msg'=>'figure参数错误');
			}
			if(!$figure['realname']){
				return array('error'=>1,'msg'=>'请填写真实姓名');
			}

			if(!$figure['mobile']){
				return array('error'=>1,'msg'=>'请填写手机号码');
			}
			
			if(!$figure['region_id']){
				return array('error'=>1,'msg'=>'region_id不合法');
			}
						
			if(!$figure['region_name']){
				return array('error'=>1,'msg'=>'region_name不合法');
			}
			
			if(!$figure['retime']){
				return array('error'=>1,'msg'=>'请选择预约时间');
			}
			
			if(!$figure['address']){
				return array('error'=>1,'msg'=>'请填写详细地址');
			}
			if(!$figure['service']){
				return array('error'=>1,'msg'=>'请选择服务点');
			}	

			$ntime = date("Ymd");
			$rtime = date("Ymd",strtotime($figure['retime']));
			
			if($rtime <= $ntime){
				return array('error'=>1,'msg'=>'您选择的时间不能进行量体');
			}
		}
		
		return $figure;
	}
	
	/**
	 * 保存促销信息
	 * @author yhao.bai
	 * @param  arr $order
	 * @return boolean
	 */
	function _save_order_pmt($order, $order_id){
		$pmts = array();
		foreach((array)$order['coupons'] as $key => $val){
			$pmt = array(
					'order_id'   => $order_id,
					'goods_id'   => 0,
					'pmt_type'   => "coupon",
					'pmt_amount' => $val["money"],
					'pmt_tag'    => "优惠券",
					'pmt_desc'   => $val['cpn_name'],
					'sendtype'   => '',
			);
			$pmts[] = $pmt;
		}
		
		foreach((array)$order['promotions'] as $key => $val){
			$pmt = array(
					'order_id'   => $order_id,
					'goods_id'   => 0,
					'pmt_type'   => $val['type'],
					'pmt_amount' => $val["money"],
					'pmt_tag'    => $val['gift'] ? "送赠品" : '折扣',
					'pmt_desc'   => $val['msg'],
					'sendtype'   => $val['sendtype']
			);
			$pmts[] = $pmt;
		}
		
		if(empty($pmts)) return true;
		
		return $this->_mod_orderpmt->add(addslashes_deep($pmts));
		
	}
	
	/**
	 *    返回订单基本信息
	 *
	 *    @author    yhao.bai
	 *    @param     array $order_id
	 */
	function _order_info($order_id){
		if(!$order_id){
			return ;
		}
		return $this->_mod_order->get_info($order_id);
	}
	
	/* 设置错误信息 */
	function _error($msg, $obj=''){
		$this->_errors = $msg;
	}
}

?>