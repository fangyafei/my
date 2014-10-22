<?php
class Flow{
	var $wdwl_url = '';
	var $error = '';
	var $token = '';
	
	
  function __construct() {
	$this->_init_session();
  }

  /**
   *    初始化Session
   *
   *    @author    Garbin
   *    @param    none
   *    @return    void
   */
  function _init_session()
  {
  	import('session.lib');
  	$db =& db();
  	$this->_session = new SessionProcessor($db, '`ecm_sessions`', '`ecm_sessions_data`', 'ECM_ID');
  	define('SESS_ID', $this->_session->get_session_id());
  	$this->_session->my_session_start();
  }
  /**
   * 获取当前session id
   *
   * @author wj
   * @return string
   */
  function get_session_id()
  {
  	return $this->session_id;
  }
  /**
   * 打开session
   *
   * @author wj
   * @return void
   */
  function my_session_start()
  {
  	session_name($this->session_name); // 自定义session_name
  	session_set_cookie_params(0, $this->session_cookie_path, $this->session_cookie_domain, $this->session_cookie_secure);
  	return session_start();
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
	
  public function addCart($type,$goodsId,$token,$is_diy,$number,$rec_id,$height,$weight,$spec,$items,$imgcode,$emb_con,$total,$disid)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不到用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	
  	
  	/* 根据类型 引用相应的库文件 */
  	$goods = $this->gt($type);
  	$post = array(
  			'goods_id' => $goodsId,
  			'number'   => $number,
  			'disid'    => $disid,
  			'user_id'  => $user_id,
  			'is_diy'   => $is_diy,
  			'spec'     => $spec,
  			'height'   => $height,
  			'weight'   => $weight,
  			'rec_id'   => $rec_id,
  			'items'    => $items,
  			'imgcode'  => $imgcode,
  			'emb_con'  => $emb_con,
  			'total'    => $total
  	);
//   print_exit($post);
  	$res = $rec_id ? $goods->reset($post) : $goods->add($post);

  	/* 商品加入购物车 */
  	if(is_array($res))
  	{
  		$arr = $res;
  	}
  	else
  	{
  		//$main = $this->_cart_main($user_id);
  		$arr = array('code'=>0,'msg'=>'添加购物车成功');
  	}
  	return $json->encode($arr);
  }
  
  /**
   *    获取商品类型对象
   *
   *    @author    Garbin
   *    @param     string $type
   *    @param     array  $params
   *    @return    void
   */
  function &gt($type, $params = array())
  {
  	static $types = array();
  	if (!isset($types[$type]))
  	{
  		/* 加载订单类型基础类 */
  		include_once KERNEL_PATH.'class/goods.base.php';
  		include(KERNEL_PATH . 'class/' . $type . '.gtype.php');
  		$class_name = ucfirst($type) . 'Goods';
  		$types[$type]   =   new $class_name($params);
//   var_dump($types[$type]);exit;
  	}
  
  	return $types[$type];
  }
  
   /**
     *    以购物车为单位获取购物车列表及商品项
     *
     *    @author    copy yhao.bai
     *    edit by liang.li
     */
    function _cart_main($user_id)
    {
    	$carts = array();
    
    	$where_user_id = "user_id= '$user_id' ";
    	$cart_model =& m('mobcart');
    
    	$cart_items = $cart_model->find(array(
    			'conditions'    =>  $where_user_id
    	));
    	if (empty($cart_items))
    	{
    		return array('goods_list' => array(), 'amount' => 0);
    	}
    	$amount    = 0;
    	$goods_num = 0;
    
    	foreach ($cart_items as $item)
    	{
    		/*处理图片缩络图*/
    		if (substr($item['goods_image'],0,4) != 'http')
    		{
    			$item['goods_image'] = SITE_URL.$item['goods_image'];
    		}
    		
    		/* 小计 */
    		$item['subtotal']   = $item['price'] * $item['quantity'];
    
    		/* 总计 */
    		$amount += $item['subtotal'];
    		$goods_num += $item['quantity'];
    		$carts[] = $item;
    	}
    
    	return array("goods_list" => $carts, "amount" => $amount, 'goods_num' => $goods_num);
    }
  
  /**
   * 删除购物车商品
   */
  public function delCart($recId,$token,$type)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>0,'msg'=>'找不到该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	$goods = $this->gt($type);
  	
  	if(!$goods->drop(array('id' => $recId)))
  	{
  		$arr =  array('statusCode'=>1,'msg'=>'删除失败');
  	}
  	else 
  	{
  		$arr =  array('statusCode'=>0,'msg'=>'删除成功');
  	}
  	
  	return $json->encode($arr);
  }
  
  /**
   * 清空购物车
   */
  public function clearCart($token)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>0,'msg'=>'找不到该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	$mobCart = m('mobcart');
  	if ($mobCart->drop("user_id=".$user_id))
  	{
  		$arr = array('msgCode'=>0,'msg'=>'成功');
  	}
  	else 
  	{
  		$arr = array('msgCode'=>1,'msg'=>'失败');
  	}
  	return $json->encode($arr);
  }
  
  /**
   * 
   * @param string $token
   * @param int $pageSize
   * @param int $pageIndex
   * @return json
   */
  public function getCart($token,$pageSize,$pageIndex)
  {
  		global $json;
  		$userInfo = getUserInfo($token);
  		$user_id = $userInfo['user_id'];
  		$main = $this->_cart_main($user_id);
        //$this->_config_seo('title', Lang::get('confirm_goods') . ' - ' . Conf::get('site_title'));
        if (empty($main['goods_list']))
        {
            $arr = array('statusCode'=>1,'msg'=>'购物车无数据');
  			return $json->encode($arr);

        }
        
        return $json->encode($main);
  }
  
  /**
   * 修改购物车商品数量
   */
  public function updateCart($type,$num,$goodsId,$recId,$token)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>0,'msg'=>'找不到用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	/* 根据类型 引用相应的库文件 */
  	$goods = $this->gt($type);
  	$res = $goods->update(array('id' => $recId, "num" => $num, 'goods_id' => $goodsId,'user_id' => $user_id));
  	if(is_array($res))
  	{
  		$arr = $res;
  	}
  	else 
  	{
  		$arr = array('statusCode'=>0,'msg'=>'成功');
  	}
  	
  	return $json->encode($arr);
  }
 
  /**
   * 添加收获地址
   */
  public function addAddress($consignee,$region_id,$region_name,$al_name,$address,$zipcode,$phone_tel,$phone_mob,$email,$token)
  {
  	global $json,$ecs,$db;
//   	import("address.lib");
  	$address_mod = m("address");
//   var_dump($address_mod);exit;
  	$user_info = getUserInfo($token);
  	if (!$user_info)
  	{
  		$arr = array('statusCode'=>1,'msg'=>'查无此人');
  		return $json->encode($arr);
  	}
  	$userId = $user_info['user_id'];
  	$data = array(
				"user_id"=>$userId,
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
  	$add_id = $address_mod->add($data);
  	
  	if ($add_id)
  	{
  		/*添加完收货地址 把默认的收货地址改为当前*/
  		$m = m('member');
  		$m->edit('user_id='.$userId,array('def_addr'=>$add_id));
  		$arr = array('statusCode'=>0,'msg'=>'成功');
  	}
  	else 
  	{
  		$arr = array('statusCode'=>1,'msg'=>'添加地址失败');
  	}
  	return $json->encode($arr);
  	
  }
  
  /**
   * 修改收获地址
   */
  public function editAddress($consignee,$region_id,$region_name,$al_name,$address,$zipcode,$phone_tel,$phone_mob,$email,$token,$addr_id)
  {
  	global $json;
  
  	//import("address.lib");
  	
  	$address_mod = m("address");
  	$user_info = getUserInfo($token);
  	if (!$user_info)
  	{
  		$arr = array('statusCode'=>1,'msg'=>'查无此人');
  		return $json->encode($arr);
  	}
  	$userId = $user_info['user_id'];
  	$data = array(
  			"user_id"=>$userId,
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

  	if ($address_mod->edit("addr_id=".$addr_id,$data) !== false)
  	{
  		/*把该地址设置为默认收货地址*/
  		$m = m('member');
  		$m->edit($userId,array('def_addr'=>$addr_id));
  		$arr = array('statusCode'=>0,'msg'=>'修改地址成功');
  	}
  	else
  	{
  		$arr = array('statusCode'=>1,'msg'=>'修改地址失败');
  	}
  	return $json->encode($arr);
  	 
  }
  
  /**
   * 删除收获地址
   */
  public function delAddress($token,$addr_id)
  {
  	global $json;
  	$user_info = getUserInfo($token);
  	if (!$user_info)
  	{
  		$arr = array('statusCode'=>1,'msg'=>'查无此人');
  		return $json->encode($arr);
  	}
  	
  	//import("address.lib");
  	$address_mod = m("address");
  	if ($address_mod->drop($addr_id))
  	{
  		$arr = array('statusCode'=>0,'msg'=>'删除地址成功');
  	}
  	else 
  	{
  		$arr = array('statusCode'=>1,'msg'=>'删除地址失败');
  	}
  	return $json->encode($arr);
  }
  
  /**
   * 获得收获地址
   */
  public function getConsigneeList($token)
  {
  		global $json,$ecs,$db;
  		//import("address.lib");
  		$address = m("address");
//   	var_dump($address);exit;
  		$user_info = getUserInfo($token);
  		if (!$user_info)
  		{
  			$arr = array('statusCode'=>1,'msg'=>'查无此人');
  			return $json->encode($arr);
  		}
  		$user_id = $user_info['user_id'];
  		$_addr = $address->find(array(
  				'conditions' => "1=1 AND user_id=$user_id",
  				));
	  /*如果用户有默认收获地址方式*/
  		$def_addr = $user_info['def_addr'];
	  	if ($def_addr)
	  	{
	  		foreach ($_addr as $k=>$v)
	  		{
	  			if ($v['addr_id'] == $def_addr)
	  			{
	  				$_addr[$k]['def_addr'] = 1;
	  			}
	  		}
	  	}
  		if($_addr)
  		{
  			return $json->encode($_addr);
  		}
  		
  		return $json->encode(array());
  	
  }
  
  /**
   * 获取支付方式
   * @return array 返回配送方式列表  如果会员有默认支付方式  则在该数组加键def_pay
   */
  public function getPayment($token)
  {
  	global $json;
  	$user_info = getUserInfo($token);
  	if (!$user_info)
  	{
  		$arr = array('statusCode'=>1,'msg'=>'查无此人');
  		return $json->encode($arr);
  	}
  	
  	$def_pay = $user_info['def_pay'];
  	$payment_mod = m("payment");
  	$payment_list = $payment_mod->find(array(
  			"conditions" => "1=1",
  			));
  	/*如果用户有默认支付方式*/
  	if ($def_pay)
  	{
  		foreach ($payment_list as $k=>$v)
  		{
  			if ($v['payment_id'] == $def_pay)
  			{
  				$payment_list[$k]['def_pay'] = 1;
  			}
  		}
  	}
  	
  	return $json->encode($payment_list);
  }
  
  /**
   * 获取配送方式
   * @return array 返回配送方式列表  如果会员有默认配送方式  则在该数组加键def_shipping
   */
  public function getShipping($token)
  {
  	global $json;
  	$user_info = getUserInfo($token);
  	if (!$user_info)
  	{
  		$arr = array('statusCode'=>1,'msg'=>'查无此人');
  		return $json->encode($arr);
  	}
  	 
  	$def_pay = $user_info['def_ship'];
  	$shipping_mod = m("shipping");
  	$shipping_list = $shipping_mod->find(array(
  			"conditions" => "1=1",
  	));
  	/*如果用户有默认支付方式*/
  	if ($def_pay)
  	{
  		foreach ($shipping_list as $k=>$v)
  		{
  			if ($v['shipping_id'] == $def_pay)
  			{
  				$shipping_list[$k]['def_ship'] = 1;
  			}
  		}
  	}
  	 
  	return $json->encode($shipping_list);
  }
  
  /**
   * 获取个人设计
   * @param int $pageSize
   * @param int $pageIndex
   * @param string $token
   * return JSON  
   */
  public function myDesignList($pageSize,$pageIndex,$uid)
  {
  	global $json,$db,$ecs;
  	
  	if($pageIndex<1)
  	{
  		$pageIndex = 1;
  	}
  	$_userphoto_mod =& m('userphoto');
  	$conditions = "cate = 1 AND uid='{$uid}'";
  	$photo_list = $_userphoto_mod->find(array(
  			'conditions' => $conditions,
  			'order' => "add_time desc",		//根据权重 正序
  			'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
  			'count' => true,
  	));
  	if ($photo_list)
  	{
  		foreach ($photo_list as $k=>$v)
  		{
  			$photo_list[$k]['url'] = SITE_URL.'/upload_user_photo/sheji/520x685/'.$v['url'];
  		}
  	}
  	/*判断有无下一页*/
  	$sql = "SELECT COUNT(*) FROM ". $ecs->table("userphoto") . "WHERE ".$conditions;
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
  	
  	$arr = array("hasNext"=>$hasNext,"photo_list"=>$photo_list);
  	return $json->encode($arr);
  	
  	
  }
  
  /**
   * 获取街拍
   * @param int $pageSize
   * @param int $pageIndex
   * @param string $token
   * return JSON
   */
  public function myphotoList($pageSize,$pageIndex,$uid)
  {
  	global $json,$db,$ecs;
  	
  	 
  	if($pageIndex<1)
  	{
  		$pageIndex = 1;
  	}
  	$_userphoto_mod =& m('userphoto');
  	$conditions = "cate = 2 AND uid='{$uid}'";
  	$photo_list = $_userphoto_mod->find(array(
  			'conditions' => $conditions,
  			'order' => "add_time desc",		//根据权重 正序
  			'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
  			'count' => true,
  	));
  	 
  	if ($photo_list)
  	{
  		foreach ($photo_list as $k=>$v)
  		{
  			$photo_list[$k]['url'] = SITE_URL.'/upload_user_photo/jiepai/520x685/'.$v['url'];
  		}
  	}
  	
  	/*判断有无下一页*/
  	$sql = "SELECT COUNT(*) FROM ". $ecs->table("userphoto") . "WHERE ".$conditions;
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
  	$arr = array("hasNext"=>$hasNext,"photo_list"=>$photo_list);
  	return $json->encode($arr);
  }
 
  /**
   * 
   * @param string $token
   * @param string $desc
   * @param Array $imgCode    二进制流  (数组)
   * @param string $title
   */
  public function addPhoto($token,$desc,$imgCode,$title)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
  		return $json->encode($arr);
  	}
  	
  	if (!is_array($imgCode))
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'传入图片列表无效');
  		return $json->encode($arr);
  	}
  	
  	
  	$user_id = $userInfo['user_id'];
  	$m_album = m('album');
  	$m = m('userphoto');
  	$data = array(
  			'add_time'=>time(),
  			'title'=>$title,
  			'description'=>$desc,
  			'uid'=>$user_id,
  			'cate'=>2,
  	);
  	$is_top = 0;
  	$album_id = $m_album->add($data);
  	$j=0;
  	foreach($imgCode['error'] as $k=>$v)
  	{
  		if ($v) {
  			continue;
  		}
  		$img_name = $user_id  . "_" .md5( uniqid() . mt_rand(0,255) ) . ".jpg";
  		$imgpath = '/upload_user_photo/jiepai/';
  		$file['name'] = $imgCode['name'][$k];
  		$file['type'] = $imgCode['type'][$k];
  		$file['tmp_name'] = $imgCode['tmp_name'][$k];
  		$file['error'] = $imgCode['error'][$k];
  		$file['size'] = $imgCode['size'][$k];
  		/*上传原图*/
  		$fileName1 = ROOT_PATH.$imgpath.'original/'.$img_name;
		file_put_contents($fileName1, file_get_contents($file['tmp_name']));
  		/*上传缩络图*/
  		$fileName2 = ROOT_PATH.$imgpath.'235x315/'.$img_name;
  		pro_img_multi($file,235,315,$fileName2);
  		$fileName3 = ROOT_PATH.$imgpath.'520x685/'.$img_name;
  		pro_img_multi($file,520,685,$fileName3);
  		/*图片名称添加数据库*/
  		if($img_name){
  			$data = array(
  					'add_time'=>time(),
  					'url'=>$img_name,
  					'uid'=>$user_id,
  					'cate'=>2,
  					'status'=>1,
  					'album_id'=>$album_id,
  			);
  			$m->add($data);
  			 
  			if(!$is_top){
  				$new_data = array('top_url'=>$img_name);
  				$m_album->edit($album_id,$new_data);
  				$is_top = 1;
  			}
  			$j++;
  		}
  	}
  	
  	$m = m('member');
  	$rs = $m->setInc(" user_id = '{$user_id}' " , 'pic_num',$j);
  	$m = m('album');
  	$rs = $m->setInc(" id = $album_id " , 'pic_num',$j);
  	return $json->encode(array( 'statusCode'=>0,'msg'=>'添加街拍成功'));
  }
  
  /**
   * 上传多张图片
   * @param unknown_type $files
   * @param unknown_type $trends_id
   * @return string
   */
  function multiUpload($files,$trends_id)
  {
  	$file['name'] = $files['name'][0];
  	foreach($files['error'] as $k=>$v)
  	{
  		if ($v) {
  			continue;
  		}
  		$file['name'] = $files['name'][$k];
  		$file['type'] = $files['type'][$k];
  		$file['tmp_name'] = $files['tmp_name'][$k];
  		$file['error'] = $files['error'][$k];
  		$file['size'] = $files['size'][$k];
  		$new_names[$k] = uploadLiuxing($file);
  		/* if(!($new_names[$k] = $this->uploadImage($trends_id.'-'.md5(uniqid()),$file)))
  			{
  		return false;
  		} */
  			
  	}
  	return $new_names;
  
  }
  
  /**
   * 街拍设计评论
   * @param string $token
   * @param int $id  ----图片id
   * @param string $content
   */
  public function addComment($token,$id,$content)
  {
  	global $json;
  	global $incSet;
  	$cate = "series_comment";
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	
  	$photo_mod = m('userphoto');
  	$photo = $photo_mod->getById($id,500);
  	if (!$photo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'该图片不存在');
  		return $json->encode($arr);
  	}
  	if(!$photo['album_id'])
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'该照片还未加入相册');
  		return $json->encode($arr);
  	}
  	
  	$rs = setComment($user_id, $photo['uid'], $id, 'jiepai_comment', $content);
  	if (is_array($rs) && $rs['err'] == 1)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'已经评论过了');
  		return $json->encode($arr);
  	}
  	if ($rs)
  	{
  		$num = $incSet[$cate];
  		setPoint($user_id,$num,'add',$cate,$author = 'system',$msg = '',$way = 'pc');
  		$arr = array( 'statusCode'=>0,'msg'=>'评论成功');
  	}
  	else
  	{
  		$arr = array( 'statusCode'=>0,'msg'=>'未知错误');
  	}
  	
  	return $json->encode($arr);
  }
  
  /**
   * 获取街拍详情页评论列表
   * @param int $id
   */
  public function getCommentList($id,$pageSize,$pageIndex)
  {
  	global $json,$db,$ecs;
  	$comment_mod = m('comments');
  	if($pageIndex<1)
  	{
  		$pageIndex = 1;
  	}
  	
  	$conditions = "cate = 'jiepai_comment' AND comment_id ='{$id}'";
  	$comment_list = $comment_mod->find(array(
  			'conditions' => $conditions,
  			'order' => "add_time desc",		//根据权重 正序
  			'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
  	));
  	
  	/*会员详情*/
  	foreach ($comment_list as $k=>$v)
  	{
  		$uid = $v['uid'];
  		$user_info = getUinfoByUid($uid);
  		$comment_list[$k]['avatar'] = $user_info['avatar'];
  		$comment_list[$k]['user_name'] = $user_info['user_name'];
  		$comment_list[$k]['real_name'] = $user_info['real_name'];
  	}
  	/*判断有无下一页*/
  	$sql = "SELECT COUNT(*) FROM ". $ecs->table("comments") . "WHERE ".$conditions;
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
  	
  	$arr = array("hasNext"=>$hasNext,"comment_list"=>$comment_list);
  	return $json->encode($arr);
  }
  
  /**
   * 创建酷吧
   * @param string $token
   */
  public function createAlbum($token,$title,$desc)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	
  	if ($userInfo['serve_type'] != 4)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'只有设计师才能上传酷吧');
  		return $json->encode($arr);
  	}
  	$data = array(
  			'title'=>$title,
  			'description'=>$desc,
  			'add_time'=>time(),
  			'cate'=>1,
  			'uid'=>$user_id,
  	);
  	
  	
  	$m = m('album');
  	if ($m->add($data))
  	{
  		$arr = array( 'statusCode'=>0,'msg'=>'创建酷吧成功');
  	}
  	else 
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'创建失败');
  	}
  	
  	return $json->encode($arr);
  }
  
  /**
   * 设计师发设计图片
   */
  
  public function addSheji($token,$title,$base_info,$imgCode)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不到该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	
  	if ($userInfo['serve_type'] != 4)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'该会员不是设计师!不允许上传设计图片');
  		return $json->encode($arr);
  	}
  	if (!$imgCode['tmp_name'])
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'请上传图片');
  		return $json->encode($arr);
  	}
  	
  	$m = m('userphoto');
  	$img_name = $user_id  . "_" .md5( uniqid() . mt_rand(0,255) ) . ".jpg";
  	$imgpath = '/upload_user_photo/sheji/';
  	
  	/*上传原图*/
  	$fileName1 = ROOT_PATH.$imgpath.'original/'.$img_name;
  	file_put_contents($fileName1, file_get_contents($imgCode['tmp_name']));
  	/*上传缩络图*/
  	$fileName2 = ROOT_PATH.$imgpath.'235x315/'.$img_name;
  	pro_img_multi($imgCode,235,315,$fileName2);
  	$fileName3 = ROOT_PATH.$imgpath.'520x685/'.$img_name;
  	pro_img_multi($imgCode,520,685,$fileName3);
  	
  	/*图片名称添加数据库*/
  	
  	$data = array(
  			'add_time'=>time(),
  			'url'=>$img_name,
  			'base_info'=>$base_info,
  			'title'=>$title,
  			'status'=>1,
  			'uid'=>$user_id,
  			'cate'=>1,
  	);
  	$photo_id = $m->add($data);

  	
  	/*app端设计要直接加入酷吧 这里判断如果存在酷吧名字叫 ‘默认酷吧’ 就加入 如果不存在 先创建 再加入*/
  	$album = m('album');
  	$album_info = $album->get(array(
  			'conditions' => "uid=$user_id AND cate=1 AND title='默认酷吧' ",
  			));
  	if ($album_info)
  	{
  		$album_id = $album_info['id'];
  		$m->edit($photo_id,array('album_id'=>$album_id));
  		/*设置 当前图片为酷吧封面图*/
  		$album->edit($album_info['id'],array('top_url'=>$img_name));
  	}
  	else 
  	{
  		$data = array(
  				'uid' => $user_id,
  				'title' => '默认酷吧',
  				'description' => 'app端默认酷吧',
  				'add_time' => time(),
  				'cate' => 1,
  				);
  		$album_id = $album->add($data);
  		$m->edit($photo_id,array('album_id'=>$album_id));
  		/*设置 当前图片为酷吧封面图*/
  		$album->edit($album_info['id'],array('top_url'=>$img_name));
  	}
  	
  	/*会员的图片数和酷吧图片数加1*/
  	$m = m('member');
  	$rs = $m->setInc(" user_id = '{$user_id}' " , 'pic_num');
  	$album->setInc(" id = '{$album_id}' " , 'pic_num');
  	return $json->encode(array( 'statusCode'=>0,'msg'=>'添加街拍成功'));
  }
  
  /**
   * 获取相册列表  街拍
   * @param string $token
   */
  public function getAlbum($token,$pageSize,$pageIndex)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	

  	if($pageIndex<1)
  	{
  		$pageIndex = 1;
  	}
  	$m = m('album');
  	$m_list = $m->find(array(
  			'conditions' => "cate = 2 AND uid='{$user_id}'",
  			'fields' =>'title,top_url,pic_num',
  			'order' => "add_time desc",		//根据权重 正序
  			'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
  			'count' => true,
  	));
  	
  	/*格式化封面图地址*/
  	if ($m_list)
  	{
  		foreach ($m_list as $k=>$v)
  		{
  			if ($v['top_url'])
  			{
  				$m_list[$k]['top_url'] = SITE_URL.'/upload_user_photo/jiepai/520x685/'.$v['top_url'];
  			}
  		}
  	}
  	/*判断有无下一页*/
  	$pageNext = $pageIndex + 1;
  	$m_list_next = $m->find(array(
  			"conditions"	=>	"cate = 2 AND uid='{$user_id}'",
  			'limit' => ($pageSize * ($pageNext-1)) . ','. $pageSize,
  			'order' => "add_time desc",
  			'count'	=> true,
  	));
  	if ($m_list_next)
  	{
  		$hasNext = true;
  	}
  	else
  	{
  		$hasNext = false;
  	}
  	
  	$arr = array("hasNext"=>$hasNext,"list"=>$m_list);
  	return $json->encode($arr);
  }
  
  /**
   * 个人设计  图片 添加到酷吧
   */
  public function addtoAlbum($token,$album_id,$photo_id)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	
  	if(!$album_id)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'该酷吧不存在');
  		return $json->encode($arr);
  	}
  	if(!$photo_id)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'该设计图片不存在');
  		return $json->encode($arr);
  	}
  	
  	$album_mod = m('album');
  	$photo_mod = m('userphoto');
  	$album = $album_mod->getById($album_id);
  	$photo =  $photo_mod->getById($photo_id);
  	
  	$data = array(
  			'album_id'=>$album_id,
  	);
  	$photo_mod->edit($photo_id,$data);//修改图片所属相册
  	
  	/*判断该图片是否本来就属于该相册 如果属于则不执行图片数加1的操作*/
  	if ($photo['album_id'] != $album_id)
  	{
  		$rs = $album_mod->setInc(" id = $album_id " , 'pic_num');
  	}
  	//相册的图片数加1
  	
  	if(!$album['top_url']){
  		$data = array();
  		$data['top_url'] = $photo['url'];
  		$album_mod->edit($album_id,$data);
  	}
  	
  	return $json->encode(array('statusCode'=>0,'msg'=>'添加成功'));
  }
  
  /**
   * 获取相册详情  (街拍)
   */
  public function getAlbumDetail($token,$album_id)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	
  	$arr = array();
  	/*获取酷吧详情*/
  	$m = m('album');
  	$m_info = $m->get(array(
  			'conditions' => 'id='.$album_id,
  			));
  	
  	//获取相册下的所有图片
  	$conditions = 'AND status=1 ';
  	$userphoto_mod =& m('userphoto');
  	$photo_list = $userphoto_mod->find(array(
  			'conditions' => "album_id = $album_id ".$conditions,
  			'order' => "add_time desc",
  			'count' => true
  	));
  	
  	/*格式化图片地址*/
  	if ($photo_list)
  	{
  		foreach ($photo_list as $k=>$v)
  		{
  			$photo_list[$k]['url'] = SITE_URL.'/upload_user_photo/jiepai/235x315/'.$v['url'];
  			/*判断是否是封面图  如果是封面图加上top_url=>1*/
  			if($v['url'] == $m_info['top_url'])
  			{
  				$photo_list[$k]['top_url'] = 1;
  			}
  			
  		}
  	} 
  	$m_info['list'] = $photo_list;
  	return $json->encode($m_info);
  }
  
  /**
   * 酷客列表
   */
  public function getUserList($pageSize,$pageIndex)
  {
  	global $json,$db,$ecs;;
  	$m = m('member');
  	

  	if($pageIndex<1)
  	{
  		$pageIndex = 1;
  	}
  	
  	$conditions = " serve_type = 0 OR serve_type = 4 ";
  	$member = $m->find(array(
  			"conditions" => $conditions,
  			"order"      => "experience desc",
  			"limit"      =>($pageSize * ($pageIndex-1)) . ','. $pageSize,
  			));
  	
  	/*格式化数据*/
  	foreach($member as $k=>$v){
  		$member[$k]['avatar'] = getAvatarByFile($v['avatar']);
  	}
  	
  	/*判断有无下一页*/
  	$sql = "SELECT COUNT(*) FROM ". $ecs->table("member") . "WHERE ".$conditions;
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
  	
  	$arr = array("hasNext"=>$hasNext,"list"=>$member);
  	return $json->encode($arr);
  }
  
  /**
   * 晒酷列表
   */
  public function kukeIndex($type,$pageIndex,$pageSize)
  {
  	global $json,$db,$ecs;;

  	if($pageIndex<1)
  	{
  		$pageIndex = 1;
  	}
  	
  	if ($type == 1)
  	{
  		$conditions = " cate = 1 ";
  		$order = "add_time";
  	}
  	elseif ($type == 2)
  	{
  		$conditions = " cate = 1 ";
  		$order = "like_num";
  	}
  	elseif ($type == 3)
  	{
  		$conditions = " cate = 2 ";
  		$order = "add_time";
  	}
  	elseif ($type == 4)
  	{
  		$conditions = " cate = 2 ";
  		$order = "like_num";
  	}
  	else 
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'type is error');
  		return $json->encode($arr);
  	}
  	
  	$photo_mod = m('userphoto');
  	
  	$photo_list = $photo_mod->find(array(
  			'conditions' => $conditions,
  			'fields' => 'add_time,like_num,title,url,views,comment_num,uid,base_info,album_id',
  			'order' => "$order desc",		//根据权重 正序
  			'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
  			'count' => true
  	));
  	
  	$album_mod = m('album');
	
  	/*格式化数据*/
  	if ($photo_list)
  	{
  		
  		foreach ($photo_list as $k=>$v)
  		{
  			if ($type == 1 || $type == 2)
  			{
  				$photo_list[$k]['url'] = SITE_URL.'/upload_user_photo/sheji/520x685/'.$v['url'];
//   		echo $photo_list[$k]['base_info'];		
  				/*个人设计取base_info*/
  				$photo_list[$k]['des'] = $photo_list[$k]['base_info'];
  			}
  			elseif ($type == 3 || $type == 4)
  			{
  				$photo_list[$k]['url'] = SITE_URL.'/upload_user_photo/jiepai/520x685/'.$v['url'];
  				
  				/*街拍 找到对应的相册 取description*/
  				$album_id = $photo_list[$k]['album_id'];
  				$album_info = $album_mod->get($album_id);
  				$photo_list[$k]['des'] = $album_info['description'];
  			}
  			
  			/*获得突破的宽高比例*/
  			$img_info = getimagesize($photo_list[$k]['url']);
  			$photo_list[$k]['wh'] = round($img_info[0]/$img_info[1],1);
  			
  			$user_info = getUinfoByUid($v['uid']);
  			$photo_list[$k]['user_name'] = $user_info['user_name'];
  			$photo_list[$k]['nickname'] = $user_info['nickname'];
  			$av = $user_info['avatar'];
  			if (substr($av,0,4) != 'http')
  			{
  				$av = SITE_URL.$av;
  			}
  			$photo_list[$k]['avatar']    = $av;
  		}
  	}
  	
  	
  	/*判断有无下一页*/
  	$sql = "SELECT COUNT(*) FROM ". $ecs->table("userphoto") . "WHERE ".$conditions;
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
  	
  	$arr = array("hasNext"=>$hasNext,"list"=>$photo_list);
  	return $json->encode($arr);
  }
  
  /*酷客详情*/
  public function kukeInfo($picId)
  {
  	global $json;
  	if(!$picId)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不到晒库图片');
  		return $json->encode($arr);
  	}
  	
  	$photo_mod = m('userphoto');
  	$album_mod = m('album');
  	$photo_info = $photo_mod->getById($picId, 500);
  	/*相册信息*/
  	$album_info = $album_mod->get($photo_info['album_id']);
  	$top_url = $album_info['top_url'];
  	/*判断图片是否加入相册  如果没有加入 不允许查看详情*/
  	if (!$photo_info['album_id'])
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'该图片还没有加入相册');
  		return $json->encode($arr);
  	}
  	
  	$uid = $photo_info['uid'];
  	$user_info = getUinfoByUid($uid);
  	$photo_info['avatar'] = $user_info['avatar'];
  	$photo_info['user_name'] = $user_info['user_name'];
  	$photo_info['real_name'] = $user_info['real_name'];
  	$photo_info['nickname'] = $user_info['nickname'];
  	/*获取该图片对应相册的所有图片  并且格式化图片地址*/
  	$photo_list = $photo_mod->getByAlbumId($photo_info['album_id']);
  	
  	foreach ($photo_list as $k=>$v)
  	{
  		
  		
  		if ($v['cate'] == 2)
  		{
  			$photo_list[$k]['url'] = getCameraUrl($v['url'],2);
  		}
  		else 
  		{
  			$photo_list[$k]['url'] = getDesignUrl($v['url'],2);
  		}
  		/*获得突破的宽高比例*/
  		$img_info = getimagesize($photo_list[$k]['url']);
  		$photo_list[$k]['wh'] = round($img_info[0]/$img_info[1],1);
  		/*将封面图放在第一个*/
  		if ($top_url == $v['url'])
  		{
  			$tmp = $photo_list[0];
  			$photo_list[0] = $photo_list[$k];
  			$photo_list[$k] = $tmp;
  		}
  		
  		
//   print_exit($photo_list);exit;
  		
  	}
//   print_exit($photo_list);	
  	$photo_info['photo_list'] = $photo_list;
  	return $json->encode($photo_info);
  }
  
  /**
   * 喜欢街拍
   */
  public function loveJiePai($token,$id)
  {
  	global $json;
  	global $incSet;
  	$cate = "jiepai_like";
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
  
  
  /**
   * 喜欢设计
   */
  public function loveSheJi($token,$id)
  {
  	global $json;
  	global $incSet;
  	$cate = "sheji_like";
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
  
  /**
   *
   * @param string $token
   * @param string $desc
   * @param Array $imgCode    二进制流  (数组)
   * @param string $title
   */
  public function editAlbum($token,$desc,$imgCode,$title,$albumId)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	//测试开始------------------
  	$m_album = m('album');
  	$data = array(
  			'title'=>$title,
  			'description'=>$desc,
  	);
  
  	$m = m('userphoto');
  	$album_info = $m_album->get($albumId);
  	
  	/*判断相册是否有封面图*/
  	if($album_info['top_url'])
  	{
  		$is_top = 1;
  	}
  	else 
  	{
  		$is_top = 0;
  	}
  	
  	$m_album->edit($albumId,$data);
  	$j=0;
  	//   print_exit($imgCode);
  	foreach($imgCode['error'] as $k=>$v)
  	{
  		if ($v) {
  			continue;
  		}
  		$img_name = $user_id  . "_" .md5( uniqid() . mt_rand(0,255) ) . ".jpg";
  		$imgpath = '/upload_user_photo/jiepai/';
  		$file['name'] = $imgCode['name'][$k];
  		$file['type'] = $imgCode['type'][$k];
  		$file['tmp_name'] = $imgCode['tmp_name'][$k];
  		$file['error'] = $imgCode['error'][$k];
  		$file['size'] = $imgCode['size'][$k];
  		/*上传原图*/
  		$fileName1 = ROOT_PATH.$imgpath.'original/'.$img_name;
  		//   echo $imgCode['tmp_name'][$k];exit;
  		$src_info = getimagesize($file['tmp_name']);
  		// 	print_exit($src_info);
  		pro_img_multi($file,$src_info[0],$src_info[1],$fileName1);
  		/* if (!move_uploaded_file($file['tmp_name'],$fileName1))
  		 {
  		echo '失败';
  		//return $json->encode(array( 'statusCode'=>1,'msg'=>'上传图片失败'));
  		} */
  		//   echo $imgCode['tmp_name'][$k];exit;
  		//   	print_exit($fileName1);
  		/*上传缩络图*/
  		$fileName2 = ROOT_PATH.$imgpath.'235x315/'.$img_name;
  		pro_img_multi($file,235,315,$fileName2);
  		$fileName3 = ROOT_PATH.$imgpath.'520x685/'.$img_name;
  		pro_img_multi($file,520,685,$fileName3);
  		/*图片名称添加数据库*/
  		if($img_name){
  			$data = array(
  					'add_time'=>time(),
  					'url'=>$img_name,
  					'uid'=>$user_id,
  					'cate'=>2,
  					'album_id'=>$albumId,
  			);
  			$m->add($data);
  
  			if(!$is_top){
  				$new_data = array('top_url'=>$img_name);
  				$m_album->edit($albumId,$new_data);
  				$is_top = 1;
  			}
  			$j++;
  		}
  	}
  	 
  	$m = m('member');
  	$rs = $m->setInc(" user_id = '{$user_id}' " , 'pic_num',$j);
  	$m = m('album');
  	$rs = $m->setInc(" id = $albumId " , 'pic_num',$j);
  	return $json->encode(array( 'statusCode'=>0,'msg'=>'添加街拍成功'));
  	//测试结束------------------------
  }
  
  /**
   * 删除相册
   */
  public function delAlbum($id)
  {
  	global $json;
  	$album = m('album');
  	$rs = $album->delById($id);
  	
  	if ($rs == -2) 
  	{
  		$arr = array("stateCode"=>1,'msg'=>'要删除的相册不存在');
  	}
  	else 
  	{
  		$arr = array("stateCode"=>0,'msg'=>'删除成功');
  	}
  	return $json->encode($arr);
  }
  
  /**
   * 删除图片
   */
  public function delPhoto($id)
  {
  	global $json;
  	$m = m('userphoto');
  	$a = m('album');
  	/*判断如果该图片是封面图 要把对应的相册的封面图字段值删除*/
  	$photo = $m->getById($id);
  	$album_id = $photo['album_id'];
  	$uid = $photo['uid'];
  	$album = $a->get($album_id);
  	if ($photo['url'] == $album['top_url'])
  	{
  		$data = array("top_url"=>"");
  		$a->edit($album_id,$data);
  	}
  	
  	$rs = $m->delById($id);
  	if ($rs)
  	{
  		$arr = array("stateCode"=>0,'msg'=>'删除图片成功');
  	}
  	else
  	{
  		$arr = array("stateCode"=>1,'msg'=>"删除失败");
  	}
  	
  	$m = m('member');
  	$rs = $m->setDec(" user_id = '{$uid}' " , 'pic_num',1);
  	$m = m('album');
  	$rs = $m->setDec(" id = $album_id " , 'pic_num',1);
  	
  	return $json->encode($arr);
  }
  
  /**
   * 设为封面
   */
  public function setCover($id)
  {
  	global $json;
  	$m = m('userphoto');
  	
  	$photo = $m->getById($id);
  	$album_id = $photo['album_id'];
  	$url = $photo['url'];
  	$data = array("top_url"=>$url);
  	$album = m('album');
  	$rs = $album->edit($album_id,$data);
  	if ($rs !== false)
  	{
  		$arr = array("stateCode"=>0,'msg'=>'设为封面成功');
  	}
  	else
  	{
  		$arr = array("stateCode"=>1,'msg'=>"删除失败");
  	}
  	return $json->encode($arr);
  }
  
	/**
	 * 获得定制详情
	 */	
  	public function getBasis($id)
  	{
  		global $json;
  		
  		/*测试开始*/
  		/* 实例化基本款的公共接口 */
  		$cs =& cs();
  		$arr =  $cs->get_basis_info($id,'a');
  		
  		foreach ($arr['data'] as $k1=>$v1)
  		{
  			foreach ($arr['style'] as $k2=>$v2)
  			{
  				if ($k1 == $v2)
  				{
  					foreach ($v1 as $k3=>$v3)
  					{
  						foreach ($v3['part'] as $k4=>$v4)
  						{
  							$s_img = $v4['info']['s_img'];
  							$s_info = $this->check_remote_file_exists($s_img);
  							if (!$s_info)
  							{
  								$arr['data'][$k1][$k3]['part'][$k4]['info']['s_img'] = $arr['data'][$k1][$k3]['part'][$k4]['info']['part_small'];
  							}
  						}
  					}
  					break 1;
  				}
  			}
  			
  			/*纽扣*/
  			foreach ($arr['design'] as $k5=>$v5)
  			{
  				
  				if ($k1 == $v5)
  				{
  				
  					foreach ($v1 as $k6=>$v6)
  					{
  						foreach ($arr['buttons'] as $k7=>$v7)
  						{
  							if ($k6 == $v7)
  							{
  						
  								foreach ($v6['part'] as $k8=>$v8)
  								{
  									
  									$s_info = $this->check_remote_file_exists($v8['info']['s_img']);
  									//$s_info = file_exists($v8['info']['s_img']);
//   								var_dump($s_info);
//   								echo $v8['info']['s_img'];exit;
  									if (!$s_info)
  									{
  								//echo $k6;
  								//echo '<pre>';var_dump($arr['data'][$k1][$k6]);exit;
  										$arr['data'][$k1][$k6]['part'][$k8]['info']['s_img'] = $arr['data'][$k1][$k6]['part'][$k8]['info']['part_small'];
  									}
  								}
  							}
  							break 1;
  						}
  					}
  					
  					break 1;
  				}
  			}
  		}
  		
  		/*纽扣*/
  		
  		
  		return $json->encode($arr);
  	}
  
  	function check_remote_file_exists($url) {
  		$curl = curl_init($url);
  		// 不取回数据
  		curl_setopt($curl, CURLOPT_NOBODY, true);
  		// 发送请求
  		$result = curl_exec($curl);
  		$found = false;
  		// 如果请求没有发送失败
  		if ($result !== false) {
  			// 再检查http响应码是否为200
  			$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  			if ($statusCode == 200) {
  				$found = true;
  			}
  		}
  		curl_close($curl);
  	
  		return $found;
  	}
  	
  /**
   * 设置为默认收货地址
   */
  public function setDefAddr($id,$token)
  {
  	global $json;
  	$userInfo = getUserInfo($token);
  	if (!$userInfo)
  	{
  		$arr = array( 'statusCode'=>1,'msg'=>'找不该用户');
  		return $json->encode($arr);
  	}
  	$user_id = $userInfo['user_id'];
  	$address = m('address');
  	$address_info = $address->get($id);
  	if (!$address_info) {
  		$arr = array( 'statusCode'=>1,'msg'=>'该地址不存在');
  		return $json->encode($arr);
  	}
  	$m = m('member');
  	$m->edit('user_id='.$user_id,array('def_addr'=>$id));
  	$arr = array( 'statusCode'=>0,'msg'=>'设置默认地址成功');
  	return $json->encode($arr);
  }
  
  
  
  
  
  
  
  	
  	

}

?>