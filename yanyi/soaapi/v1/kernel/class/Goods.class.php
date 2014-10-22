<?php
class Goods{
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
   * 获取订单详情
   */
  public function getOrder($token,$orderId)
  {
  	global $json;
  	$user_info = getUserInfo($token);
  	if (!$user_info)
  	{
  		$arr = array('statusCode'=>1,'msg'=>'查无此人');
  		return $json->encode($arr);
  	}
  	
//   var_dump('aa');exit;
  	$userId = $user_info['user_id'];
  	$model_order =& m('order');
  	$order_info = $model_order->get(array(
  			'fields'        => "*, order.add_time as order_add_time",
  			'conditions'    => "order_id={$orderId} AND buyer_id=" .$userId,
  			'join'          => 'belongs_to_store',
  	));
  	
  	 $order_type =& ot($order_info['extension']);
  	 $order_detail = $order_type->get_order_detail($orderId, $order_info);
//   	 var_dump($order_detail);exit;
  	 $arr = array();
  	 $arr['id'] = $order_info['order_id'];
  	 $arr['totalPrice'] = $order_info['order_amount'];
  	 $arr['logisticsCost '] = $order_detail['data']['order_extm']['shipping_fee'];
  	 $arr['Discount']    = $order_info['discount'];
  	 $arr['Remark']     = '';
  	 /* $arr['Consignee'] = $order_info['buyer_id'];
  	 $arr['cartItemList'] = $order_detail['data']['goods_list'];
  	 $arr['orderCode']	= $order_info['order_sn'];
  	 $arr['orderMoney'] = $order_info['order_amount'];
  	 $arr['orderStatus'] = $order_info['status'];
  	 $arr['orderTime']  = $order_info['order_add_time'];
  	 if ($order_detail['data']['order_extm']['shipping_id'] == 1)
  	 {
  	 	$arr['Delivery']   = 0 ;
  	 }
  	 else 
  	 {
  	 	$arr['Delivery']   = 1 ;
  	 }
  	 $arr['payUrl']  = '';
  	 $arr['payWay']  = $order_detail['data']['payment_info']['payment_id']; */
  	 
  	 
  	return $json->encode($arr);
  }
	
  public function  getThemeType()
  {
  	
  }
  /**
   * 获取主题分类列表的数据
   */
  public function getThemeList($type)
  {
  	global $json;
  	$cat=array(
            '1'=>'婚庆系列',
            '2'=>'校园系列',
            '3'=>'职场系列',
            '4'=>'休闲系列',
            '5'=>'儿童系列',
            '6'=>'明星同款',
        );
  	
  	$config = ROOT_PATH . '/data/dissertation/config.php';
  	if(is_file($config)){
  		$cats = include_once($config);
  		foreach ($cat as $k =>$v){
  			$cats[$v]['id']=$k;
  		}
  	}
//  print_exit($cats);
  	$dis_mod = m('dissertation');
  	$conditions = " AND cat LIKE '{$cat[$type]}' ";
  	$data = $dis_mod->find(array(
  			'conditions' => '1=1 '.$conditions,
  			'limit' => 20,
  			'order' => "is_hot DESC, sort_order ASC, add_time DESC",
  			'count' => true,
  	));
  	$ids='';
  	$like_mod = & m('like');
//  print_exit($data);
  	foreach ($data as $k=>$v){
  		$comments=$this->get_comments($v['id'], 3);
  		$data[$k]['comment']=count($comments);
  		$data[$k]['comment_list']=$comments;
  		$likes=$like_mod->findAll(array(
  				'conditions' => " like_id = '{$v['id']}' AND cate = 'dissertation' ",
  		));
  		$data[$k]['likes'] = count($likes);
  		
  	}
  	return  $json->encode($data);
  }
  
  /**
   * 获取主题详细信息
   */
  public function getThemeDetail($id,$token)
  {
  	global $json;
  	$arr = array();
  	
  	include_once ROOT_PATH.'/includes/constants.base.php';
  	$dis_mod = m('dissertation');
  	$info = $dis_mod->get_info($id);
  	if (!$info) 
  	{
  		$arr = array('statusCode'=>1,'msg'=>'该主题不存在');
	   	return $json->encode($arr);
  	}
  	
  	/*查看是否喜欢*/
  	if (!$token)
  	{
  		$arr['islove'] = 0;
  	}
  	else
  	{
  		$user_info = getUserInfo($token);
  		if (!$user_info)
  		{
  			$arr = array('statusCode'=>1,'msg'=>'查无此人');
  			return $json->encode($arr);
  		}
  		$user_id = $user_info['user_id'];
  		$rs = getLikeByUid($user_id, $info['id'], 'dissertation');
  		if ($rs)
  		{
  			$arr['islove'] = 1;
  		}
  		else
  		{
  			$arr['islove'] = 0;
  		}
  	}
  	
  	/*从中间表取出和改主题相关的基本款*/
  	$_link_mod =& m("links");
  	$links = $_link_mod->find(array(
  			"conditions" => "d_id = '{$id}'",
  	));
  	$link = array();
  	foreach($links as $key => $val){
  		$link[] = $val['c_id'];
  	}
  	$cus_mod = & m('customs');
  	$customs = $cus_mod->find(array(
  			'fields' =>	'cst_id as id,cst_name as name,cst_price as price,cst_market as oldprice,cst_dis_image as image,cst_description as des,cst_cate',
  			'conditions' => $this->db_create_in($link, "cst_id"),
  	));
  	$selected = 0;
  	$total    = 0;
  	$market   = 0;
  	foreach($customs as $key => $val)
  	{
  		$selected += 1;
  		$total += $val['price'];
  		$market += $val["cst_maket"];
  		
  		/*取得基本款对应的标准码*/
  		$customs[$key]['biaoZhunMa'] = Constants::$sizelParent[$val['cst_cate']];
  	}
  
  	/*取得该主题下的前4个评论*/
  	$m = &m('comments');
  	$comment = $m->find(array(
  			'conditions' => "comment_id = '{$id}' AND cate = 'dis'",
  			'limit' => '4',
  			'order' => "add_time DESC",
  			'count' => true,
  	));
  	
  	$arr['id'] = $id;
  	$arr['name'] = $info['title'];
  	$arr['price'] = $total;
  	$arr['desc']  = $info['brief'];
  	$arr['content']  = $info['content'];
  	$arr['likes'] = $info['likes'];;
  	$arr['image'] = $info['middle_img'];
  	$arr['out_url'] = M_SITE_URL."/index.php/dissertation-info-".$id.".html";
  	$arr['list'] = $customs;
  	$arr['comment'] = $comment;
  	return $json->encode($arr);
  	
  }
  
  /**
   * 创建像这样的查询: "IN('a','b')";
   *
   * @access   public
   * @param    mix      $item_list      列表数组或字符串,如果为字符串时,字符串只接受数字串
   * @param    string   $field_name     字段名称
   * @author   wj
   *
   * @return   void
   */
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
  			foreach ($item_list as $k=>$v)
  			{
  				$item_list[$k] = intval($v);
  			}
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
  
  public function get_comments($id=0,$limit=3)
  {
       $conditions = " AND comment_id = '{$id}' AND cate = 'dis'";
       $comment_mod =& m('comments');
       $return = $comment_mod->find(array(
               'conditions' => " 1 = 1 ".$conditions,
               'limit'      => $limit,
       ));
       return $return;
   }
  
   /**
    * 对主题喜欢的操作
    */
   public function loveTheme($id,$token)
   {
	   	global $json;
	   	global $incSet;
	   	$cate = 'zhuti_like';
	   	$user_info = getUserInfo($token);
	   	if (!$user_info)
	   	{
	   		$arr = array('statusCode'=>1,'msg'=>'用户不存在');
	   		return $json->encode($arr);
	   	}
	   	$user_id = $user_info['user_id'];
	   	
	   	$res = setLike($user_id, $id, $cate,'pc');
	   	
	   	if ($res['err'] == 1){
	   		$arr = array( 'statusCode'=>1,'msg'=>$res['msg']);
	   	}else{
	   		/* 喜欢成功加积分 */
	   		$p_num = $incSet[$cate];
	   		setPoint($user_id,$p_num,'add',$cate);
	   		$arr = array( 'statusCode'=>0,'msg'=>"喜欢成功");
	   	}
	   	return $json->encode($arr);
   }
   
   /**
    * 主题发表评论
    */
   public function addThemeComment($token,$content,$id)
   {
	   	global $json;
	   	global $incSet;
	   	$cate = "series_comment";
	   	$user_info = getUserInfo($token);
	   	if (!$user_info)
	   	{
	   		$arr = array('statusCode'=>1,'msg'=>'用户不存在');
	   		return $json->encode($arr);
	   	}
	   	$user_id = $user_info['user_id'];
	   	$m = m('comments');
	   	$count = $m->_count(array(
	   			'conditions' => "cate= 'dis' AND uid = '{$user_id}' AND comment_id = '{$id}'"
	   	));
	   	if($count)
	   	{
	   		$arr = array('statusCode'=>1,'msg'=>'已经评论过了');
	   		return $json->encode($arr);
	   	}
	   	
	   	$res = setComment($user_id, 0, $id, 'dis', $content);
	   	if (!$res) 
	   	{
	   		$arr = array('statusCode'=>1,'msg'=>'意外评论 请重试');
	   	}
	   	else 
	   	{
	   		$p_num = $incSet[$cate];
	   		setPoint($user_id,$p_num,'add',"series_comment",$author = 'system',$msg = '',$way = 'pc');
	   		$arr = array('statusCode'=>0,'msg'=>'评论成功');
	   	}
	   	return $json->encode($arr);
   }
   
   /**
    * 获取主题评论过
    */
   public function getThemeComment($id,$pageSize,$pageIndex)
   {
   		global $json;
	   	if($pageIndex<1)
	   	{
	   		$pageIndex = 1;
	   	}
	   	$m = &m('comments');
	   	$list = $m->find(array(
	   			'conditions' => "comment_id = '{$id}' AND cate = 'dis'",
	   			'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
	   			'order' => "add_time DESC",
	   			'count' => true,
	   	));
	   	/*判断有无下一页*/
	   	$pageNext = $pageIndex + 1;
	   	$list_next = $m->find(array(
	   			'conditions' => "comment_id = '{$id}' AND cate = 'dis'",
	   			'limit' => ($pageSize * ($pageNext-1)) . ','. $pageSize,
	   			'order' => "add_time DESC",
	   			'count' => true,
	   	));
	   	
	   	/*格式化用户信息*/
	   	if ($list)
	   	{
	   		foreach ($list as $k=>$v)
	   		{
	   			$user_info = getUinfoByUid($v['uid']);
	   			$list[$k]['user_name'] = $user_info['user_name'];
	   			$av = $user_info['avatar'];
	   			if (substr($av,0,4) != 'http')
	   			{
	   				$av = SITE_URL.$av;
	   			}
	   			$list[$k]['avatar']    = $av;
	   		}
	   	}
	   	if ($list_next)
	   	{
	   		$hasNext = true;
	   	}
	   	else
	   	{
	   		$hasNext = false;
	   	}
	   	
	   	$arr = array("hasNext"=>$hasNext,"list"=>$list);
	   	return $json->encode($arr);
   }
   
   /**
    * 获取基本款推荐列表
    */
   public function getRecGoodsList()
   {
	   	global $json,$db,$ecs;
	   	 
	   	$cate_id1 = 7;//西装
	   	$cate_id2 = 4;//西裤
	   	$cate_id3 = 2;//衬衫
	   	$customs_mod = m("customs");
	   	
	   	$list1 = $customs_mod->find(array(
	   			'conditions'	=>	"cst_cate=3 AND is_rec=1 AND is_active=1",
	   			'limit'			=>  '5',
	   	));
	   	/*取基本款的收藏量 */
	   	foreach ($list1 as $k=>$v)
	   	{
	   		$sql = "SELECT COUNT(*) FROM " . $ecs->table("collect") . " WHERE type='customs' AND item_id={$v['cst_id']}";
	   		$count = $db->getOne($sql);
	   		$list1[$k]['collect_num'] =  $count;
	   	}
	   	
	   	$list2 = $customs_mod->find(array(
	   			'conditions'	=>	"cst_cate=2000 AND is_rec=1 AND is_active=1",
	   			'limit'			=>  '5',
	   	));
	   	/*取基本款的收藏量 */
	   	foreach ($list2 as $k=>$v)
	   	{
	   		$sql = "SELECT COUNT(*) FROM " . $ecs->table("collect") . " WHERE type='customs' AND item_id={$v['cst_id']}";
	   		$count = $db->getOne($sql);
	   		$list2[$k]['collect_num'] =  $count;
	   	}
	   	
	   	$list3 = $customs_mod->find(array(
	   			'conditions'	=>	"cst_cate=4000 AND is_rec=1 AND is_active=1",
	   			'limit'			=>  '5',
	   	));
	   	/*取基本款的收藏量 */
	   	foreach ($list3 as $k=>$v)
	   	{
	   		$sql = "SELECT COUNT(*) FROM " . $ecs->table("collect") . " WHERE type='customs' AND item_id={$v['cst_id']}";
	   		$count = $db->getOne($sql);
	   		$list3[$k]['collect_num'] =  $count;
	   	}
	   	
	   	
	   	$arr[] = $list1;
	   	$arr[] = $list2;
	   	$arr[] = $list3;
	   	return $json->encode($arr);
   }
   
  /**
   * 获取分类[分页]
   * 
   * @param  int $pageSize
   * @param  int $pageIndex
   * @return array
   * @author liliang
   */
	public function getCategoryList() {
		global $json;
		
		$g_mod = m('customs');
		$categoryList = $g_mod->get_cate_g();
		return $json->encode( array ('categoryList' => $categoryList ));
	}
	
	/**
	 * 获取基本款全部列表
	 *
	 * @param  int $cateId
	 * @return array
	 */
	public function getGoodsList($type,$pageSize,$pageIndex)
	{
		global $json,$db,$ecs;
	
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		if($type)
		{
			$conditions = "1=1 AND cst_cate = '$type' AND is_active=1 ";
			$customs_mod = m("customs");
			$customs_list = $customs_mod->find(array(
					"conditions"	=>	$conditions,
					"fields"		=>	"cst_id,cst_name,cst_image,cst_description,cst_store,cst_likes",
					'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
					'order' => "cst_id desc",
			));
			/*判断有无下一页*/
			$sql = "SELECT COUNT(*) FROM ". $ecs->table("customs") . "WHERE ".$conditions;
			$counts = $db->getOne($sql);
			$page_count = ceil($counts/$pageSize);
			if ($pageIndex < $page_count)
			{
				$hasNext = true;
			}
			else 
			{
				$hasNext = false;
			}
		}
	
		$arr = array("hasNext"=>$hasNext,"goodslist"=>$customs_list);
		return $json->encode($arr);
	}
	
	/**
	 * 获取基本款全部列表
	 *
	 * @param  int $cateId
	 * @return array
	 */
	public function getGoodsListNew($type,$pageSize,$pageIndex)
	{
		global $json,$db,$ecs;
	
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		if($type)
		{
			$conditions = "1=1 AND cst_cate = '$type' AND is_active=1 ";
			$customs_mod = m("customs");
			$customs_list = $customs_mod->find(array(
					"conditions"	=>	$conditions,
					"fields"		=>	"cst_id,cst_name,cst_image,cst_description,cst_store,cst_likes",
					'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
					'order' => "cst_id desc",
					'index_key' => '',
			));
			/*判断有无下一页*/
			$sql = "SELECT COUNT(*) FROM ". $ecs->table("customs") . "WHERE ".$conditions;
			$counts = $db->getOne($sql);
			$page_count = ceil($counts/$pageSize);
			if ($pageIndex < $page_count)
			{
				$hasNext = true;
			}
			else
			{
				$hasNext = false;
			}
		}
	
		$arr = array("hasNext"=>$hasNext,"goodslist"=>$customs_list);
		return $json->encode($arr);
	}
	
	/**
	 * 获取基本款的推荐列表
	 */
	public function getRelGoods($id)
	{
		global $json;
		$nums = 12;
		$link_mod = m('links');
		$customs_mod = m('customs');
		$links = $link_mod->findAll(array(
				"conditions" => "c_id = '{$id}'",
		));
		if(!empty($links)){
			 
			foreach ($links as $row){
				$dId[] = $row['d_id'];
			}
			 
			$lks = $link_mod->findAll(array(
					"conditions" => db_create_in($dId,'d_id')
			));
			foreach ($lks as $r){
				$cId[] = $r['c_id'];
			}
			$cst = $customs_mod->findAll(array(
					'fields' => 'cst_name,cst_price,cst_image',
					"conditions" => db_create_in($cId,'cst_id')
			));
		
		}else{
			$cst = $customs_mod->findAll(array(
			//                "conditions" => " 1 = 1",
					'fields' => 'cst_name,cst_price,cst_image',
					"order"      => "RAND()",
					"limit"      => $nums,
			));
		}
		return $json->encode($cst);
	}
	
	/**
	 * 获取基本款详情
	 *
	 * @param  int $goodsId
	 * @return array
	 * @author hai
	 */
	public function getGoodsDetail($cstId)
	{
		global $json,$db,$ecs;
		include_once ROOT_PATH.'/includes/constants.base.php';
		if ($cstId)
		{
			$customs_mod = m("customs");
			$customs_info = $customs_mod->get($cstId);
			$customs_info['biaoZhunMa'] = Constants::$sizelParent[$customs_info['cst_cate']];
			
		}
	
		return $json->encode($customs_info);
	}
	
	/**
	 * 添加基本款的收藏
	 * @param string $token
	 * @param int $cstId
	 */
	public function addCstCollect($token,$cstId)
	{
		
		global $json,$db,$ecs,$incSet;
		$cate = "shoucang";
		$userInfo = getUserInfo($token);
		if (!$userInfo)
		{
			$arr = array( 'statusCode'=>0,'msg'=>'找不该用户');
			return $json->encode($arr);
		}
		$user_id = $userInfo['user_id'];
		
		/* 验证要收藏的基本款是否存在 */
		$model_customs = m('customs');
// 		var_dump($model_customs);exit;
		$customs_info  = $model_customs->get(array(
				"conditions" => 'cst_id='.$cstId,
				));
		if (empty($customs_info))
		{
			$arr = array( 'statusCode'=>1,'msg'=>'该基本款不存在');
			return $json->encode($arr);
		}
		
		/*添加收藏之前判断是否已经收藏过了*/
		$collect_mod = m('collect');
		$sql = "SELECT * FROM ".$ecs->table('collect')." WHERE user_id=$user_id AND item_id=$cstId AND type='customs' ";
		if ($db->getOne($sql, true) > 0)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'该基本款已收藏');
			return $json->encode($arr);
		}
		
		
		$time = time();
		$sql = "INSERT INTO ".$ecs->table('collect')." (`user_id`,`type`,`item_id`,`add_time`) VALUES ('$user_id','customs','$cstId','$time')";
		/*添加收藏*/
		if ($db->query($sql))
		{
			$p_num = $incSet[$cate];
			setPoint($user_id,$p_num,'add',$cate);
			$arr = array('statusCode'=>0,'msg'=>'基本款收藏成功');
		}
		else 
		{
			$arr = array('statusCode'=>1,'msg'=>'未知错误');
		}
		
		return $json->encode($arr);
	}
	
	/**
	 * 删除基本款收藏
	 * @param string $token
	 * @param int $cstId
	 */
	public function delCstCollect($token,$cstId)
	{
		global $json,$db,$ecs;
		$userInfo = getUserInfo($token);
		if (!$userInfo)
		{
			$arr = array( 'statusCode'=>0,'msg'=>'找不该用户');
			return $json->encode($arr);
		}
		$user_id = $userInfo['user_id'];
		
		$sql = "DELETE FROM ".$ecs->table('collect')." WHERE user_id='$user_id' AND item_id='$cstId' AND type='customs' ";
		if($db->query($sql))
		{
			$arr = array( 'statusCode'=>0,'msg'=>'删除成功');
		}
		else
		{
			$arr = array( 'statusCode'=>1,'msg'=>'删除失败');
		}
		
		return $json->encode($arr);
	}
	
	/**
	 * 获取基本款收藏列表
	 * @param stirng $token
	 */
	public function getCstCollect($token)
	{
		global $json,$db,$ecs;
		$userInfo = getUserInfo($token);
		if (!$userInfo)
		{
			$arr = array( 'statusCode'=>0,'msg'=>'找不该用户');
			return $json->encode($arr);
		}
		$user_id = $userInfo['user_id'];
		
		$sql = "SELECT * FROM ".$ecs->table('collect')." WHERE user_id='$user_id' AND type='customs' ";
		$cst_list = $db->getAll($sql);
		
		$arr = array();
		$customs_mod = m('customs');
		/*根据item_id获取基本款的信息*/
		
		foreach ($cst_list as $cst)
		{
			$temp = array();
			$customs_info = $customs_mod->get($cst['item_id']);
			if ($customs_info)
			{
				/*统计基本款收藏的次数*/
				$cst_id = $customs_info['cst_id'];
				$sql = "SELECT COUNT(*) FROM ".$ecs->table('collect')." WHERE type='customs' AND item_id=$cst_id ";
				$collectnum = $db->getOne($sql);
				
				$temp['id'] = $cst_id;
				$temp['image'] = $customs_info['cst_image'];
				$temp['cst_name'] = $customs_info['cst_name'];
				$temp['price']  = $customs_info['cst_price'];
				$temp['commentnum'] = $customs_info['cst_comment'];
				$temp['collectnum'] = $collectnum;
				$temp['add_time']   = $cst['add_time'];
				$arr[] = $temp;
			}
		}
		return $json->encode($arr);
	}
	
	/**
	 * 喜欢基本款
	 */
	public function loveCus($token,$id)
	{
		global $json;
		global $incSet;
		$cate = "dingzhi_like";
		$userInfo = getUserInfo($token);
		if (!$userInfo)
		{
			$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
			return $json->encode($arr);
		}
		$user_id = $userInfo['user_id'];
		 
		$res = setLike($user_id, $id, $cate,'pc');
		 
		if ($res['err'] == 1){
			$arr = array( 'statusCode'=>1,'msg'=>$res['msg']);
		}else{
			/* 喜欢成功加积分 */
			$p_num = $incSet[$cate];
			setPoint($user_id,$p_num,'add',$cate);
			$arr = array( 'statusCode'=>0,'msg'=>"喜欢成功");
		}
		 
		return $json->encode($arr);
	}
	
	
	
	
	
	
	
	
}

?>