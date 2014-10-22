<?php

/**
 *    定制商品
 */
class DisGoods extends BaseGoods
{
	
    function __construct($param)
    {
        /* 初始化 */
        $param['_is_material']  = true;
        $param['_name']         = 'dis';
        $param['_order_type']   = 'normal';
        
        parent::__construct($param);
    }
    
    
    function add($post)
    {
    	//简单过滤传过来的基体款ID
    	$goodsids = explode(",", $post["goods_id"]);
    	$spec    = explode(",", $post['spec']);
    	$specs = array();
    	foreach($goodsids as $key => $val){
    		$goodsids[$key] = intval($val);
    		$specs[$val] = $spec[$key];
    	}
    	
    	if(count($goodsids) != count($specs)){
    		return array('code'=>1,'msg'=>'参数错误');
    	}
    	
    	$this->_link_mod =& m("links");
    	$_dis_mod = &m("dissertation");
    	$links = $this->_link_mod->find(array(
    			"conditions" => "d_id = '{$post["disid"]}' AND ". db_create_in($goodsids, "c_id"),
    			'fields'     => 'c_id',
    	));
    	
    	if (!$links){
			return array('code'=>1,'msg'=>'未匹配到相关的基本款');
    	}
    	
    	$disinfo = $_dis_mod->get_info($post['disid']);
    	
    	if(empty($disinfo)){
    		return array('code'=>1,'msg'=>'参数错误');
    	}
    	
    	$cs =& cs();
    	$gcategory = $cs->_get_gcategory();
    	 
    	
    	/* 事件开始 */
    	$transaction = $this->_cart->beginTransaction();
    	

    	foreach($links as $key => $val){
    		
    		$_Bdata = $this->_base_info(intval($val["c_id"]));
    		
    		$_Cdata = $this->_cart_info($val['c_id']);
    		
    		$parsCode = $cs->parsing_code_base($val["c_id"]);
    		
    		if($parsCode['error']){
    			return array('code'=>1,'msg'=>$parsCode['msg']);
    		}
    		
    		$items = current($parsCode['data']);
    		
    		$_Gdata = $this->_group_info($val['c_id'], $items);
    		
    		if(empty($_Gdata['oFabric'])){
    			return array('code'=>1,'msg'=>'该商品没有匹配到面料信息!');
    		}
    		
    		
    		/********************************检查库存 ************************************/
    		$buyNum = 1;
    		if($_Cdata){
    			foreach($_Cdata as $_ck => $_cv){
    				$buyNum += $_cv['quantity'];
    			}
    		}
    		
    		$_checkStore = $this->_check_store($_Bdata['cst_store'], $buyNum);
    		
    		if(!$_checkStore){
    			return array('code'=>1,'msg'=>'基本款库存不足');
    		}
    		
    		$parsCode = $cs->parsing_code_base($val["c_id"]);
    		
    		$_checkFabricStore = $this->_check_fabric_store(array(
    				'fabric'   => $_Gdata['oFabric']['part_name'],
    				'fabric_m' => $_Gdata['consumption']['fabric_m'],
    				'rec_id'   => 0,
    				'buy_num'  => 1,
    				'store'    => $_Gdata['oFabric']['part_number'],
    				'user_id'  => $post['user_id'],
    		));
    		
    		if(!$_checkFabricStore){
    			return array('code'=>1,'msg'=>'面料库存不足');
    		}
    		
    		$price  = $_Bdata['service_fee']; //商品价格
    		
    		/********************************计算价格**********************************/
    		//面料价格
    		$price += $_Gdata['oFabric']['price'] * $_Gdata['consumption']['fabric_m'];
    		 
    		//里料价格
    		$price += $_Gdata['iFabric']['price'] * $_Gdata['consumption']['lining_m'];
    		 
    		 
    		//工艺费
    		foreach((array)$_Gdata['process'] as $_pk => $_pv){
    			$price += $_pv['price'];
    		}
    		
    		if(!$this->checkSpec($specs[$val["c_id"]], $_Bdata['cst_cate'])){
    			$this->_cart->rollback();
    			return array('code'=>1,'msg'=>'尺码错误');
    		}
    		
    		/* 用G+新数据 与Gd进行对比库存*/
    		  
    		/* 生成入库数据 */
    		$cData = array(
    				'goods_id'   => $_Bdata["cst_id"],
    				'goods_name' => $_Bdata["cst_name"],
    				'goods_sn'   => 'customs_'.$_Bdata["cst_id"],
    				'source_id'  => $post["disid"],
    				'cst_author' => $_Bdata['cst_author'],
    				'cst_source' => $_Bdata['cst_source'],
    				'cst_source_id' => $_Bdata['cst_source_id'],
    				'goods_image'  => $_Bdata["cst_dis_image"],
    				'cst_cate'  => $_Bdata["cst_cate"],
    				'items'      => serialize($_Gdata),
    				'price'      => round($price,0),
//     				'session_id' => SESS_ID,
    				'user_id'    => $post['user_id'],
    				'quantity'   => 1,
    				'is_diy'     => 0,
    				'specification' => $specs[$val["c_id"]],
    				'type'       => $this->_name,
    				'source_title' => $disinfo['title'],
    				'type_name'    => $_Bdata["cst_cate"] ? $gcategory[$_Bdata["cst_cate"]]['cate_name'] : '',
    				'goods_weight' => 10,
    				'fabric'     => $_Gdata['oFabric']['part_name'],
    		);
    		
    		$res = $this->_cart->add($cData);
    		
    		if(!$res){
	    		return array('code'=>1,'msg'=>'加入购物车失败!未知错误!');
    		}
    	}
    	
    	$this->_cart->commit($transaction);
    	
    	return true;
    }
    
    
    function update($post){
    	 
    	$_Cdata = $this->_cart_info($post['goods_id']);
    	
    	
    	$_Gdata = $this->_group_info($post["goods_id"]);
    	
    	
    	/* 组件库存验证 */
    	$where = "session_id = '".SESS_ID."' AND rec_id = '{$post['id']}'";
    	$where .= $this->_visitor->get("user_id") ? " AND user_id = '".$this->_visitor->get("user_id")."'" : '';
    	
    	$res = $this->_cart->edit($where, array('quantity' => $post["num"]));
    	if(!$res)
    	{
    		$this->_error("update_error");
    	}
    	return $res;
    }
    
    
    function drop($post){
    	$droped_rows = $this->_cart->drop("rec_id='{$post['id']}' AND type='{$this->_name}' AND session_id='" . SESS_ID ."'");
    	if (!$droped_rows)
    	{
    		$this->_error("drop_error");
    		return false;
    	}
    	return true;
    }
    
    function reset(){
    	return true;
    }
}

?>