<?php

/**
 *    定制商品 
 *    copy xiaobai  -----  add by liliang
 */
class DiyGoods extends BaseGoods
{
	
	function __construct($param)
    {
        $this->DiyGoods($param);
    }
    
    function DiyGoods($param)
    {
        /* 初始化 */
        $param['_is_material']  = true;
        $param['_name']         = 'diy';
        $param['_order_type']   = 'normal';
        
        /* 引入定制模型 */
        	
        /* 引入组件模型 */
        
        parent::__construct($param);
    }
	

    
    function add($post)
    {
    	$_Bdata = $this->_base_info(intval($post["goods_id"]));
    	if(!$_Bdata){
    		return false;
    	}
   		if($post['is_diy']==0){
    		if(!$this->checkSpec($post['spec'])){
    			return array('code'=>1,'msg'=>'不是标准码');
    		}
    			
    	}else{
    		if(!$post['height'] || !$post['weight']){
    			return array('code'=>1,'msg'=>'没有填写身高和体重');
    		 }
    	}
	
    	if(!$post['items']){
    		return array('code'=>1,'msg'=>'item不能为空');
    	}
    	
    	/*购物车商品*/
    	$_Cdata = $this->_cart_info($post["goods_id"],$post["user_id"]);
    	
    	/*组件信息*/
    	$_Gdata = $this->_group_info($post["goods_id"], $post['items']);
  		
    	if(isset($_Gdata['code']) && $_Gdata['code'] == 1){
    		return $_Gdata;
    	}
    	
    	if(empty($_Gdata['oFabric'])){
    		return array('code'=>1,'msg'=>'该商品没有匹配到面料信息!');
    	}
    	/********************************检查库存 ************************************/
    	$buyNum = 1;
    	if($_Cdata){
    		foreach($_Cdata as $key => $val){
    			$buyNum += intval($val['quantity']);
    		}
    	}
    	$_checkStore = $this->_check_store($_Bdata['cst_store'], $buyNum);
    	if(!$_checkStore){
    		return array('code'=>1,'msg'=>'基本款库存不足');
    	}
    	
    	/*组件库存（只检查面料库存）*/
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
    	
    	/********************************组件信息*************************************/
    	$price  = $_Bdata['service_fee']; //商品价格
    	$oFee = 0;
    	
    	
    
    	/********************************计算价格**********************************/
    	//面料价格
    	$price += $_Gdata['oFabric']['price'] * $_Gdata['consumption']['fabric_m'];
    	
    	//里料价格
    	$price += $_Gdata['iFabric']['price'] * $_Gdata['consumption']['lining_m'];
    	 
    	
    	//工艺费
    	foreach((array)$_Gdata['process'] as $key => $val){
    		$price += $val['price'];
    	}
    	 
    	//身高体重超出部份费用
    	if($post['is_diy'] == 1){
    		$oFee = $this->_figure_body($post['height'], $post['weight']);
    		//$oFee 值为 0,  1.5, 2
    		if($oFee){
    			$price +=  $_Gdata['oFabric']['price'] * $oFee;
    		}
    	}
    	/*四舍五入先去掉*/
    	$price = round($price,0);
    	if($post['total'] != $price){
    		return array('code'=>1,'msg'=>'金额不正确');
    	}
    	$cs =& cs();
    	$gcategory = $cs->_get_gcategory('',1);
    	/* 用G+新数据 与Gd进行对比库存*/

    	/* 生成入库数据 */
    	$cData = array(
    			'goods_id'   => $_Bdata["cst_id"],
    			'goods_name' => $_Bdata["cst_name"],
    			'goods_sn'   => $_Bdata["goods_sn"],
    			'cst_author' => $_Bdata['cst_author'],
    			'cst_source' => $_Bdata['cst_source'],
    			'cst_source_id' => $_Bdata['cst_source_id'],
    			'cst_cate'  => $_Bdata["cst_cate"],
    			'items'      => serialize($_Gdata),
    			'price'      => $price,
     			/*'session_id' => SESS_ID,*/
    			'user_id'    => $post['user_id'],
    			'quantity'   => 1,
    			'type'       => $this->_name,
    			'goods_image'=> "/custom/{$_Bdata['cst_id']}/{$_Gdata['oFabric']['part_name']}/10004/{$post['imgcode']}.jpg",
    			'is_diy'     => $post['is_diy'],
    			'specification' => $post['spec'],
    			'height'     => $post['height'],
    			'weight'     => $post['weight'],
    			'type_name'    => $_Bdata["cst_cate"] ? $gcategory[$_Bdata["cst_cate"]]['cate_name'] : '',
    			'goods_weight' => $_Bdata['cst_weight'],
    			'emb_con'    => addslashes($post['emb_con']),
    			'fabric'     => $_Gdata['oFabric']['part_name'],
    			'cst_cate'   => $_Bdata["cst_cate"]
    	);
    	$res = $this->_cart->add($cData);
    	
    	if(!$res)
    	{
    		return array('code'=>1,'msg'=>'添加购物车失败');
    	}
    	return $res;
    }
    
    /**
     * 购物商品修改操作
     */
    function reset($post)
    {
    	/****商品基本信息***/
	   	$_Bdata = $this->_base_info(intval($post["goods_id"]));
	   	 
	   	if(!$_Bdata){
	   		return array('code'=>1,'msg'=>'基本款不存在');
	   	}
	   	
	   	if($post['is_diy']==0){
	   		if(!$this->checkSpec($post['spec'], $_Bdata['cst_cate'])){
	   			return array('code'=>1,'msg'=>'尺码错误!');
	   		}
	   	}else{
	   		if(!$post['height'] || !$post['weight']){
	   			return array('code'=>1,'msg'=>'没有填写身高或者体重');
	   		}
	   	}
	   	
	   	if(!$post['items']){
	   		return array('code'=>1,'msg'=>'item为空');
	   	}
	   	
	   	$_Cdata = $this->_cart_info($post["goods_id"],$post['user_id']);
		
	   	$has = false;
	   	foreach($_Cdata as $key => $val){
	   		if($val['rec_id'] == $post['rec_id']){
	   			$has = true;
	   		}
	   	}

	   	if(!$_Cdata || !$has){
	   		return array('code'=>1,'msg'=>'非法操作!');
	   	}
	   	
	   	
   		/* 组件信息 */
    	$_Gdata = $this->_group_info($post["goods_id"], $post['items']);
    	
    	if(isset($_Gdata['code']) && $_Gdata['code'] == 1){
    		return $_Gdata;
    	}
    	
    	if(empty($_Gdata['oFabric'])){
    		return array('code'=>1,'msg'=>'该商品没有匹配到面料信息!');
    	}
    	
    	/********************************检查库存 ************************************/

    	$buyNum = 1;
    	$fabricNum = $_Gdata['consumption']['fabric_m'];
    	
    	/*面料库存*/
    	$_checkFabricStore = $this->_check_fabric_store(array(
    				'fabric'   => $_Gdata['oFabric']['part_name'],
    				'fabric_m' => $_Gdata['consumption']['fabric_m'],
    				'rec_id'   => $post['rec_id'],
    				'buy_num'  => 1,
    				'store'    => $_Gdata['oFabric']['part_number'],
    			));
    	
    	if(!$_checkFabricStore){
    		return array('code'=>1,'msg'=>'面料库存不足');
    	}
    	
    	/********************************组件信息*************************************/
    	$price  = $_Bdata['service_fee']; //商品价格
  		$oFee = 0;

    	/********************************计算价格**********************************/
    	//面料价格
    	$price += $_Gdata['oFabric']['price'] * $_Gdata['consumption']['fabric_m'];

    	//里料价格
    	$price += $_Gdata['iFabric'] ? $_Gdata['iFabric']['price'] * $_Gdata['consumption']['lining_m'] : 0;
    	
    	//工艺费
    	foreach((array)$_Gdata['process'] as $key => $val){
    		$price += $val['price'];
    	}
    	
    	//身高体重超出部份费用
    	if($post['is_diy'] == 1){
	    	$oFee = $this->_figure_body($post['height'], $post['weight']);
	    	//$oFee 值为 0,  1.5, 2

	    	if($oFee){
	    		$price +=  $_Gdata['oFabric']['price'] * $oFee;
	    	}
    	}
    	
    	$price = round($price,0);
    	
    	if($post['total'] != $price){
    		return array('code'=>1,'msg'=>'金额不正确!');
    	}
	   	
	   	/* 生成入库数据 */
    	$cData = array(
    		'items'      => serialize($_Gdata),
    		'price'      => $price,
    		'goods_image'=> "/custom/{$_Bdata['cst_id']}/{$_Gdata['oFabric']['part_name']}/10004/{$post['imgcode']}.jpg",
    		'is_diy'     => $post['is_diy'],
    		'specification' => $post['spec'],
    		'height'     => $post['height'],
    		'weight'     => $post['weight'],
    		'emb_con'    => addslashes($post['emb_con']),
    		'fabric'     => $_Gdata['oFabric']['part_name'],
    	);
	   	
	   	$where = " rec_id = '{$post['rec_id']}'";
	   	
	   	$res = $this->_cart->edit($where, $cData);
	   	
	   	if(!$res)
	   	{
	   		$this->_error("add_to_cart_error");
	   	}
	   	
	   	return $res;
    }
    
    /**
     * 修改购物车商品数量
     */
     function update($post){
    	 
        $_Bdata = $this->_base_info(intval($post["goods_id"]));
    	
    	if(!$_Bdata){
    		return array('code'=>1,'msg'=>'基本款不存在');
    	}

        /* 购物车商品 */
    	$_Cgoods = $this->_cart_info($post["goods_id"],$post['user_id']);
    	
    	$item = array();
    	foreach($_Cgoods as $key => $val){
    		if($val['rec_id'] == $post['id']){
    			$item = unserialize($val['items']);
    		}
    	}
    	/* 组件信息 */
    	$_Gdata = $this->_group_info($post["goods_id"], $item['oCode']);
    	 
    	if(isset($_Gdata['code']) && $_Gdata['code'] == 1){
    		return $_Gdata;
    	}
    	if(empty($_Gdata['oFabric'])){
    		return array('code'=>1,'msg'=>'该商品没有匹配到面料信息!');
    	}
    	
    	/********************************检查库存 ************************************/
    	$buyNum  = 1;
	    if($_Cgoods){
	    		foreach($_Cgoods as $key => $val){
	    			if($val['rec_id'] == $post['id']){
	    				$buyNum  += $post["num"];
	    			}else{
	    				$buyNum += $val['quantity'];
	    			}
	    		}
	    }
    	
    	$_checkStore = $this->_check_store($_Bdata['cst_store'], $buyNum);

    	if(!$_checkStore){
    		return array('code'=>1,'msg'=>'基本款库存不足');
    	}
    	
    	$_checkFabricStore = $this->_check_fabric_store(array(
    			'fabric'   => $_Gdata['oFabric']['part_name'],
    			'fabric_m' => $_Gdata['consumption']['fabric_m'],
    			'rec_id'   => $post['id'],
    			'buy_num'  => $post['num'],
    			'store'    => $_Gdata['oFabric']['part_number'],
    	));
    	
    	if(!$_checkFabricStore){
    		return array('code'=>1,'msg'=>'面料库存不足');
    	}
    	
    	
    	/********************************检查End ************************************/
    	
    	$where = "rec_id = '{$post['id']}'";
    	$where .= $post['user_id'] ? " AND user_id = '".$post['user_id']."'" : '';
    	
    	$res = $this->_cart->edit($where, array('quantity' => $post["num"]));
    	
    	if(!$res)
    	{
    		return array('code'=>1,'msg'=>'修改数量失败!未知原因');
    	}
    	
    	return $res;
   }
    
   function drop($post)
   {
	   	$droped_rows = $this->_cart->drop("rec_id='{$post['id']}' ");
	   	if (!$droped_rows)
	   	{
	   		
	   		return false;
	   	}
   		return true;
   }
}

?>