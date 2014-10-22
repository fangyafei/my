<?php

!defined('ROOT_PATH') && exit('Forbidden');

/**
 *    商品类型基类
 *
 *    @author    Garbin
 *    @usage    none
 */
class BaseGoods extends Object
{
    var $_is_material;  // 是否实体商品，支付接口可能需要用到
    var $_name;         // 商品类型的名称
    var $_order_type;   // 对应的订单类型
	var $_error = '';
	var $_visitor;
	var $_cart;
	var $_custom;
    function __construct($params)
    {
    	//$this->_visitor =& env('visitor', new UserVisitor());
    	$this->_cart = &m("mobcart");
    	$this->_custom = &m("customs");
        $this->BaseGoods($params);
    }
    function BaseGoods($params)
    {
        if (!empty($params))
        {
            foreach ($params as $key => $value)
            {
                $this->$key = $value;
            }
        }
    }

    /**
     *    获取对应订单类型实例
     *
     *    @author    Garbin
     *    @param     array $params
     *    @return    void
     */
    function get_order_type()
    {
        return $this->_order_type;
    }

    /**
     *    获取类型名称
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function get_name()
    {
        return $this->_name;
    }

    /**
     *    是否是实体商品
     *
     *    @author    Garbin
     *    @return    void
     */
    function is_material()
    {
        return $this->_is_material;
    }
    
	function _group_info($goods_id, $items){
    	/* 获取组件数据 */
 	
    	$res = array(
    			'oFabric'     => array(), //面料 
    			'iFabric'     => array(), //里料
    			'oData'       => array(), //其它组件
    			'process'     => array(), //工艺
    			'consumption' => array(), //单耗
    			'oCode'       => $items,
    		);
    	
    	$cs =& cs();
    	$groups = $cs->parsing_code($goods_id, $items);
    
    	if($groups['error']){
    		return array('code'=>1,'msg'=>$groups['msg']);
    	}
    	
    	foreach($groups['data'] as $key => $val){
    		// 取里料
    		if(in_array($val['t_id'], Constants::$materialParent)){
    			//unset($groups[$key]);
    			$res['iFabric'] = $val;
    		}
    		
    		// 取面料
    		if(in_array($val['t_id'], Constants::$fabricsParent)){
    			//unset($groups[$key]);
    			$res['oFabric'] = $val;
    		}
    		
    	}
    	
    	$res['oData']       = $groups['data'];
    	$res['process']     = $groups['process'];
    	$res['consumption'] = $groups['consumption'];
    	
    	return $res;
    }
    
    function _figure_body($height, $weight){
    	$m = 0;
    	if($height > 191 || ($weight > 101 && $weight < 120)){
    		$m = 1.5;
    	}
    	

    	if($height > 200 || $weight > 121){
    		$m = 2;
    	}
    	
    	return $m;
    }
    
    function _base_info($goods_id){
    	/* 获取基本款数据 */
    	$data = array(
    			'goods_id'   => $goods_id,
    			'goods_name' => "模拟商品1",
    			'goods_sn'	 => "TEST001",
    			'price'      => "2800",
    			'is_sale'    => 1,
    	);
    	
    	/* 基本款不存在 */
    	if(!$data){
    		$this->_error('diy_goods_not_exist');
    		return false;
    	}
    	
    	/* 检查基本款是否上架销售 */
    	if(!$data['is_sale']){
    		$this->_error('diy_goods_not_sale');
    		return false;
    	}
    	
    	$data = $this->_custom->get_info($goods_id);
    	
    	return $data;
    }
    
    function _cart_info($goods_id,$user_id,$rec_id = 0)
    {
    	$conditions = "goods_id = '{$goods_id}' AND user_id = '$user_id' ";

    	if($rec_id){
    		$conditions .= " AND rec_id = '{$rec_id}'";
    	}
    	$item = $this->_cart->find(array(
    				'conditions' => $conditions,
    			));
	 
    	return $item;
    }
    /**
     * 检查库存
     */
    function _check_store($store, $buyNum){
    	 
    	if($store < $buyNum){
    		return false;
    	}
    	return true;
    }
    
    /**
     * 检查组件库存
     * @return boolean
     */
    function _check_fabric_store($params){
    	 
    	$list = $this->_cart_fabric($params['fabric'],$params['user_id']);
    	$buyNum = $params['buy_num']*$params['fabric_m'];
    	 
    	foreach($list as $key => $val){
    		if($params['rec_id'] != $val['rec_id']){
    			$item = unserialize($val['items']);
    			$buyNum += $val['quantity']*$item['consumption']['fabric_m'];
    		}
    	}
    	 
    	if($params['store'] < $buyNum){
    		
    		return false;
    	}
    	 
    	return true;
    }
    
    /**
     *
     * @param str $fabric 面料
     * @return array
     */
    function _cart_fabric($fabric,$user_id){
    	 
    	$conditions = "fabric = '{$fabric}' AND user_id = '".$user_id."'";
    	 
    	$item = $this->_cart->find(array(
    			'conditions' => $conditions,
    	));
    
    	return $item;
    }
    
    //检查尺码是否合法
    function checkSpec($spec){
    	return true;
    }
    function _error($msg, $obj=""){
    	$this->error = $msg;
    }
    
    function get_error(){
    	return $this->error;
    }
    
    
}


?>