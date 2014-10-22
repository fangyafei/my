<?php

/**
 *    定制订单类型，需要量体
 *
 *    @author    yhao.bai
 */
class DiyOrder extends BaseOrder
{
    var $_name = 'diy';
 
    function __construct(){
		parent::__construct();
    }
    
    /**
     *    查看订单
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
    
    	/* 获取商品列表 */
    	$data['goods_list'] =   $this->_get_goods_list($order_id);
    
    	/* 配送信息 */
    	$data['order_extm'] =   $this->_get_order_extm($order_id);
    
    	/* 支付方式信息 */
    	if ($order_info['payment_id'])
    	{
    		$payment_model      =& m('payment');
    		$payment_info       =  $payment_model->get("payment_id={$order_info['payment_id']}");
    		$data['payment_info']   =   $payment_info;
    	}

    	/* 订单操作日志 */
    	$data['order_logs'] =   $this->_get_order_logs($order_id);
    
    	return array('data' => $data);
    }

    function submit($data){
    	
    	extract($data);
    	
    	/* 订单基本信息 */
    	$baseinfo = $this->_handle_order_info($_order, $_cart);
//   print_exit($baseinfo); 
		if(!$baseinfo){
			
			return array('stateCode'=>1,'oInfo'=>'订单基本信息不合法');
		}
		
    	/* 处理订单收货人信息 */
    	$consignee_info = $this->_handle_consignee_info($_order, $_cart);
// print_exit($consignee_info);

    	if (!$consignee_info)
    	{
    		/* 收货人信息验证不通过 */
    		return array('stateCode'=>1,'oInfo'=>'收获地址不合法');
    	}

    	/* 处理量体数据 */
    	$figure_info = $this->_handle_figure_info($_order['figure']);
    	if (isset($figure_info['error']) || $figure_info['error']==1)
    	{
    		/* 量体数据验证不通过 */
    		return array('stateCode'=>1,'oInfo'=>$figure_info['msg']);
    		exit;
    	}
    
    	/* 保存订单信息 */
    	$order_id = $this->_mod_order->add($baseinfo);
    
    	if (!$order_id)
    	{
    		/* 保存基本信息失败 */
    
    		return array('stateCode'=>1,'oInfo'=>'保存订单失败');
    	}
    
    	/* 保存收货地址信息 */
    	$consignee_info['order_id'] = $order_id;
    
    	$res = $this->_mod_orderextm->add($consignee_info);
    
    	if (!$res)
    	{
    		/* 保存地址失败 */
    		return array('stateCode'=>1,'oInfo'=>'保存收货地址失败');
    	}
    
    
    	/* 保存量体数据 */
    	$figure_info["order_id"] = $order_id;
    	$res = $this->_mod_orderfigure->add($figure_info);
    
    	if (!$res)
    	{
    		/* 保存量体数据失败 */
    		return array('stateCode'=>1,'oInfo'=>'保存量体失败');
    	}
    
    	/* 保存促销信息 */
    	$res = $this->_save_order_pmt($_order, $order_id);
    	
    	if(!$res){
    		/* 保存优惠活动失败 */
    		return array('stateCode'=>1,'oInfo'=>'保存量体失败');
    	}
    	
    	/* 保存商品数据 */
    	foreach ($_cart['goods_list'] as $key => $value)
    	{
    		$goods_items[] = array(
    				'order_id'      =>  $order_id,
    				'goods_id'      =>  $value['goods_id'],
    				'goods_name'    =>  $value['goods_name'],
    				'spec_id'       =>  $value['spec_id'],
    				'specification' =>  $value['specification'],
    				'price'         =>  $value['price'],
    				'quantity'      =>  $value['quantity'],
    				'goods_image'   =>  $value['goods_image'],
    				'source_id'     =>  $value['source_id'],
    				'cst_cate'      =>  $value['cst_cate'],
    				'source_title'  =>  $value['source_title'],
    				'type_name'     =>  $value['type_name'],
    				'cst_author'    => 	$value['cst_author'],
    				'cst_source'    => 	$value['cst_source'],
    				'cst_source_id' => 	$value['cst_source_id'],
    				'is_diy' 		=> 	$value['is_diy'],
    				'height' 		=> 	$value['height'],
    				'weight' 		=> 	$value['weight'],
    				'emb_con'		=>  $value['emb_con'],
    				'fabric'		=>  $value['fabric'],
    				'items'         =>  $value['items'],
    		);
    	}
    	$res = $this->_mod_ordergoods->add(addslashes_deep($goods_items)); //防止二次注入
    
    	if (!$res)
    	{
    		/* 保存商品数据 */
    		return array('stateCode'=>1,'oInfo'=>'保存商品失败');
    	}
    	$user_id     =  $_order['user_info']['user_id'];
    	$tag = md5($user_id.'RCTAILOR');
    	$pay_url = "http://m.rctailor.com/index.php/buyer_order-pay-$order_id-$user_id-$tag.html";
    	return array('order_id' => $order_id, 'order_sn' => $baseinfo['order_sn'],'pay_url'=>$pay_url);
    
    }
}

?>