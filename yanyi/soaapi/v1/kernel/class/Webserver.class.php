<?php

class Webserver
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

  public function test($user_name,$password)
  {
  		global $json;
  		$mod=m('member');
		$res=$mod->get(array(
            'conditions' => '1=1' ,
        ));
        //var_dump($res['nickname']);exit;
        return $json->encode($res);
  }
  
  
  /**
   * 会员登录
   * @param string $name
   * @param string $pwd
   * @access protected
   * @return void
   */
   public function login($user_name,$password){		
		global $json,$db,$ecs;

		$mod=m('member');
		/* $res=$mod->get(array(
            'conditions' => '1=1' ,
        )); */
        //return $json->encode($res);
        //var_dump($res);exit;
        
		
		//要连表进行验证
		$sql = "SELECT *  FROM " .$ecs->table('member').
			   " WHERE user_name='" . $user_name . "' OR phone_tel='" . $user_name . "' OR phone_mob='" . $user_name . "'";
		$uInfo = $db->getRow($sql);
		$return = array();
		if (empty($uInfo)){
			/* 用户名不存在 */
			$return['statusCode'] = 1;
			$return['msg'] = 'usera is not exist';
			
		}else{
		    if ($uInfo['password'] == md5($password)){
		    	$return['statusCode'] = 0;
		    	
		    	if($uInfo['user_token']){
		    		
		    		$return['token'] 	  = $uInfo['user_token'];
		    	
		    	}else{
		    		
		    		$token 	  			  = md5($user_name.$uInfo['password']);
		    		$sql = "UPDATE " .$ecs->table('member')." SET user_token='".$token."'WHERE user_id='" . $uInfo['user_id'] . "' LIMIT 1";
		    		$db->query($sql);
		    		
		    		$return['token']	  = $token;
		    	}
		    	
		    	$sql = "UPDATE " .$ecs->table('member')." SET last_login='".time()."' WHERE user_id='" . $uInfo['user_id'] . "' LIMIT 1";
		    	$db->query($sql);
		    	
		    	$arr_tmp=array();
		    	$arr_tmp['id']			=	$uInfo['user_id']?$uInfo['user_id']:'';
		    	$arr_tmp['headImgUrl']	=	$uInfo['portrait']? IMG_PREFIX.$uInfo['portrait'].'?ver='.time():'';	//头像
		    	$arr_tmp['nickName']	=	$uInfo['nick_name'] ? $uInfo['nick_name']:'';
		    	$arr_tmp['phoneNum']	=	$uInfo['phone_mob']?$uInfo['phone_mob']:'';
		    	$arr_tmp['passWord']	=	$uInfo['password']?$uInfo['password']:'';
		    	$arr_tmp['score']		=	$uInfo['score']?$uInfo['score']:'';
		    	$arr_tmp['constellation']=	$uInfo['constellation']?$uInfo['constellation']:'';
		    	$arr_tmp['blook']		=	$uInfo['blook']?$uInfo['blook']:'';
		    	$arr_tmp['gender']		=	$uInfo['gender']?$uInfo['gender']:'';
		    	$arr_tmp['address']		=	$uInfo['address']?$uInfo['address']:'';
		    	$arr_tmp['serve_type']		=	$uInfo['serve_type']?$uInfo['serve_type']:'';
		    	if($arr_tmp['serve_type'])
		    	{
		    		$arr_tmp['serve_type']=='2';
		    		$serve_mod=m('serve');
		    		$serve_data=$serve_mod->get(array(
		    		'conditions' => 'userid='.$arr_tmp['id'],
		    		));
		    		$arr_tmp['idserve']=$serve_data['idserve'];
		    	}
		    	
		    	
				if($uInfo['last_login']){
					$arr_tmp['lastUpdateTime']		=	$uInfo['last_login'];
				}else{
					$arr_tmp['lastUpdateTime']		=	time();
				}
		    	$return['User']			=	$arr_tmp;
		    	
		    }else{
		        /* 密码不正确 */
		    	$return['statusCode'] = 1;
				$return['msg'] = '密码不正确！';
		    }
		}
		return $json->encode($return);
	}
	
	/**
	 *  编译密码函数
	 *
	 * @access  public
	 * @param   array   $cfg 包含参数为 $password, $md5password, $salt, $type
	 *
	 * @return void
	 */
	public function compile_password ($cfg)
	{
		if (isset($cfg['password']))
		{
			$cfg['md5password'] = md5($cfg['password']);
		}
		if (empty($cfg['type']))
		{
			$cfg['type'] = PWD_MD5;
		}
	
		switch ($cfg['type'])
		{
		    case PWD_MD5 :
		    	return $cfg['md5password'];
	
		    case PWD_PRE_SALT :
		    	if (empty($cfg['salt']))
		    	{
		    		$cfg['salt'] = '';
		    	}
	
		    	return md5($cfg['salt'] . $cfg['md5password']);
	
		    case PWD_SUF_SALT :
		    	if (empty($cfg['salt']))
		    	{
		    		$cfg['salt'] = '';
		    	}
	
		    	return md5($cfg['md5password'] . $cfg['salt']);
	
		    default:
		    	return '';
		}
	}
	

  /**
   * 会员注册
   * @param string $name
   * @param string $pwd
   * @param string $email
   * @access protected
   * @return void
   */
   public function register($mobile,$password,$nickname){
   	    global $json,$db,$ecs;
   	    
   	    $return = array();
   	    
   	    /* 用户名是否重复 */   	    
   	    $sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_name='" . $mobile . "' or phone_mob='".$mobile."' ";
   	    //return $sql;
   	    if ($db->getOne($sql, true) > 0)
   	    {
   	    	$return['statusCode'] = 1;
   	    	$return['msg'] = '用户已存在！';
   	    	return $json->encode($return);
   	    }
   	       	    
   	    $post_password = md5($password);
   	    $token=md5($mobile.$post_password);
   	    $fields = array('user_name','password','phone_mob','phone_tel','reg_time','user_token','nickname');
   	    $values = array($mobile, $post_password,$mobile,$mobile,time(),$token,$nickname);
   	    
   	    $sql = "INSERT INTO " .$ecs->table('member').
   	    " (" . implode(',', $fields) . ")".
   	    " VALUES ('" . implode("', '", $values) . "')";
   	    
   	    //return $sql;
   	    if ($db->query($sql)){
   	    	$return['statusCode'] = 0;
   	    	$sql = "SELECT * FROM " .$ecs->table('member')." WHERE user_name='" . $mobile . "'";
   	    	$uInfo = $db->getRow($sql);  	
   	    	    	
   	    	$return['token'] 	  	= 	$token;	    	
	    	$arr_tmp=array();
	    	$arr_tmp['id']			=	$uInfo['user_id'];
			$arr_tmp['real_name']	=	$uInfo['real_name'];
	    	$arr_tmp['nickName']	=	$uInfo['nickname'];
	    	$arr_tmp['phoneNum']	=	$uInfo['phone_mob'];
	    	$arr_tmp['passWord']	=	$uInfo['password'];
	    	$arr_tmp['user_name']	=	$uInfo['user_name'];
			$arr_tmp['lastUpdateTime']		=	0;
	    	$return['User']			=	$arr_tmp;
   	    }
   	    
		return $json->encode($return);
	}
	/**
	 * 用户手机找回密码
	 * @param string $mobile,$string $module
	 * @access public
	 * @return void
	 */
	public function resetPassword($mobile,$module){
		global $json,$db,$ecs;
		
		$return = array();
		
		/* 用户名是否重复 */
		$sql = "SELECT user_id,org_pwd  FROM " .$ecs->table('member')." WHERE user_name='" . $mobile . "'";
		$res=$db->getRow($sql);
		
		if (!$res)
		{
			$return['statusCode'] = 1;
			$return['msg'] = 'user not is exsit';
			return $json->encode($return);
		}
		
		$times=date('Y-m-d 00:00:00');
		$sql = "SELECT count(*) FROM " .$ecs->table('publish_log')." WHERE uid='" . $res['user_id'] . "' and module='find_ps_phone' and fail_time>'$times'";
		$res_list = $db->getOne($sql);
		if($res_list > 5){
			$return['statusCode'] = 3;
			
			return $json->encode($return);
		}
		
		$org_pwd = "亲爱的用户, 您的密码: '".$res['org_pwd']."', 请注意保管好~";
		
		$result = $this->SendSms($mobile,$org_pwd,1,$module);
		
		//$result=223;
		if ($result){		
			$end_time = 60;
			$end_time = date("U") + $end_time;
				
			$fields = array('uname','uid','keyword','title','type','code','fail_time','status','ctime','module');
			$values = array($mobile, $res['user_id'],'','发送手机找回密码','phone',$result,date('Y-m-d H:i:s',$end_time),0,date("Y-m-d H:i:s"),$module);
				
			$sql = "INSERT INTO " .$ecs->table('publish_log').
			" (" . implode(',', $fields) . ")".
			" VALUES ('" . implode("', '", $values) . "')";
				
			$db->query($sql);
		
			$return['statusCode'] 		= 0;
			//$return['verificationCode'] = $res;  //验证码
		}else{
				
			$return['statusCode'] 			= 1;
			$return['msg'] 					= 'send failed';  //验证码
		}
		
		return $json->encode($return);
		
	}
	/**
	 * 判断用户是否存在
	 * 发送短信验证
	 * @param string $name	
	 * @access public
	 * @return void
	 */
	public function sendCode($mobile){
		global $json,$db,$ecs;
	
		$return = array();
	
		/* 用户名是否重复 */
		$sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_name='" . $mobile . "'";
		/* if ($db->getOne($sql, true) > 0)
		{
			$return['statusCode'] = 1;
			$return['msg'] = '用户已存在';
			return $json->encode($return);
		} */
		
		$code = rand(100000, 999999 );
		$res = $this->sendTemplateSMS($mobile, array($code), 1);
		
		if ($res){		

			$sms_mod = m('smsRegTmp');
			/*清空改手机号的验证码*/
			$conditins = " phone=$mobile ";
		
			$sms_mod->drop(array('condtions=>'.$conditins));
			
			/*添加到sms_reg_tmp*/
			$data = array();
			$data['type'] = 'mobile';
			$data['category'] = 'phone';
			$data['add_time'] = time();
			$data['phone'] = $mobile;
			$data['fail_time'] = time() + 120;  //过期时间
			$data['code']  = $code;
			if ($sms_mod->add($data))
			{
				$return['statusCode'] 		= 0;
				$return['msg'] = '发送成功';  
			}
			else
			{
				$return['statusCode'] 		= 1;
				$return['msg'] = '数据库错误';  
			}
			
		}else{
			$return['statusCode'] 			= 1;
			$return['msg'] 					= '发送失败';  //验证码
		}
	
		return $json->encode($return);
	}
	
	/**
	 * 发送模板短信
	 * @param to 手机号码集合,用英文逗号分开
	 * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
	 * @param $tempId 模板Id
	 */
	function sendTemplateSMS($to,$datas,$tempId)
	{
		include_once(ROOT_PATH."/includes/CCPRestSDK.php");
		// 初始化REST SDK
		//主帐号
		$accountSid= 'aaf98f894830369d014834e57ce7020c';
	
		//主帐号Token
		$accountToken= 'c9ccd295b045415787f56d1147335b9d';
	
		//应用Id
		$appId='8a48b55148303878014834e5b32a01e0';
	
		//请求地址，格式如下，不需要写https://
		$serverIP='sandboxapp.cloopen.com';
	
		//请求端口
		$serverPort='8883';
	
		//REST版本号
		$softVersion='2013-12-26';
	
		// global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;
		$rest = new REST($serverIP,$serverPort,$softVersion);
		$rest->setAccount($accountSid,$accountToken);
		$rest->setAppId($appId);
	
		// 发送模板短信
		
		$result = $rest->sendTemplateSMS($to,$datas,$tempId);
		if($result == NULL ) 
		{
			return false;
			break;
     	}
	     if($result->statusCode !=0) 
	     {
	    	return false;
	     }
	     else
	     {
	     	return true;
	     }
	  }
	
	
	public function design($token,$pageSize,$pageIndex)
	{
		global $json,$db,$ecs;
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		
		$sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		
		if($row){
			
			$model_album = m('album');
			$album_data = $model_album->find(array(
				'conditions' => 'uid = ' . $row['user_id'],
				'count' => true,
				'order' => 'id DESC',
	            'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
			));
			$arr_tmp['statusCode']=0;
			$arr_tmp['datalist']=$album_data;
		}else{
			$arr_tmp['statusCode']=10000;
		}
		
		return $json->encode($arr_tmp);
	}
	
	public function coin($token,$pageSize,$pageIndex,$type)
	{
		global $json,$db,$ecs;
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		
		$sql = "SELECT user_id,point,coin  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		$typestr='';
		if($type=='add')
		{
			$typestr=' and opt=\'add\' ';
		}else if($type=='del')
		{
			$typestr=' and opt=\'del\' ';
		}
		else if($type=='order')
		{
			$typestr=' and type=\'order\' ';
		}
		else if($type=='sheji')
		{
			$typestr=' and type=\'sheji\' ';
		}
		else if($type=='jiepai')
		{
			$typestr=' and type=\'jiepai\' ';
		}
		
		//all，order，sheji，jiepai
		//return $typestr;
		if($row){
			
			$model_album = m('coin_log');
			$album_data = $model_album->find(array(
				'conditions' => 'uid = ' . $row['user_id'].$typestr,
				'count' => true,
				'order' => 'id DESC',
	            'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
			));
			$arr_tmp['coin']=$row['coin'];
			$arr_tmp['statusCode']=0;
			$arr_tmp['scoreItemList']=$album_data;
		}else{
			$arr_tmp['statusCode']=10000;
		}
		
		return $json->encode($arr_tmp);
	}
	public function point($token,$pageSize,$pageIndex,$type)
	{
		global $json,$db,$ecs;
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		
		$sql = "SELECT user_id,point,coin  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		
		$typestr='';
		if($type=='add')
		{
			$typestr=' and opt=\'add\' ';
		}else if($type=='del')
		{
			$typestr=' and opt=\'del\' ';
		}
		else if($type=='order')
		{
			$typestr=' and type=\'order\' ';
		}
		else if($type=='comment')
		{
			$typestr=' and type=\'comment\' ';
		}
		else if($type=='hudong')
		{
			$typestr=' and type=\'hudong\' ';
		}
		
		
		
		//order,comment,hudong
		
		
		if($row){
			
			$model_album = m('point_log');
			$album_data = $model_album->find(array(
				'conditions' => 'uid = ' . $row['user_id'].$typestr,
				'count' => true,
				'order' => 'id DESC',
	            'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
			));
			$arr_tmp['point']=$row['point'];
			$arr_tmp['statusCode']=0;
			$arr_tmp['scoreItemList']=$album_data;
		}else{
			$arr_tmp['statusCode']=10000;
		}
		
		return $json->encode($arr_tmp);
	}
	public function coupon($token,$status,$pageSize,$pageIndex)
	{
		global $json,$db,$ecs;
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		
		$sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		
		if($row){
			
			$model_album = m('couponsn');
			$album_data = $model_album->find(array(
				'conditions' => 'uid = ' . $row['user_id'].' AND status='.$status,
				'count' => true,
				'order' => 'coupon_sn DESC',
	            'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
			));
			$arr_tmp['statusCode']=0;
			$arr_tmp['datalist']=$album_data;
		}else{
			$arr_tmp['statusCode']=10000;
		}
		
		return $json->encode($arr_tmp);
	}
	
	public function follow($token,$pageSize,$pageIndex)
	{
		global $json,$db,$ecs;
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		
		$sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		
		if($row){
			
			$model_album = m('userfollow');
			$album_data = $model_album->find(array(
				'conditions' => 'uid = ' . $row['user_id'],
				'count' => true,
				'order' => 'add_time DESC',
	            'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
			));
			$arr_tmp['statusCode']=0;
			$arr_tmp['datalist']=$album_data;
		}else{
			$arr_tmp['statusCode']=10000;
		}
		
		return $json->encode($arr_tmp);
	}
	
	//获取用户收藏列表
	public function getCollect($token,$pageSize,$pageIndex)
	{
		global $json,$db,$ecs;
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		$sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		
		if($row){
			 $model_store =& m('store');
			$collect_store = $model_store->find(array(
	            'join'  => 'be_collect,belongs_to_user',
	            'fields'=> 'this.*,member.user_name,collect.add_time',
	            'conditions' => 'collect.user_id = ' . $row['user_id'],
	            'count' => true,
	            'order' => 'collect.add_time DESC',
	            'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
	        ));
	        $arr_tmp['statusCode']=0;
	        $arr_tmp['Count']=$model_store->getCount();
	        $arr_tmp['goodsList']=$collect_store;
		}else{
			$arr_tmp['statusCode']=10000;
		}
		return $json->encode($arr_tmp);
		/*
		$arr_tmp=array();
		$sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		
		if($row){
			$sql = "SELECT item_id FROM " .$ecs->table('collect')." WHERE user_id='" . $row['user_id'] . "' AND type='goods' ORDER BY add_time DESC limit " . ($pageSize * ($pageIndex-1)) . ','. $pageSize;
			//return $sql;
			$row= $db->getAll($sql);
			if($row){
				$goods_tmp=array();
				foreach($row as $k=>$v){
					$goodsList=$this->getGoodsDetail($v['item_id']);
					//return $goodsList;
					$goods_tmp[$k]['id'] 	= $goodsList['goods_id'];
					$goods_tmp[$k]['name']	= $goodsList['goods_name'];
					$goods_tmp[$k]['smallImgUrl'] = $goodsList['default_image'];
					$goods_tmp[$k]['price'] = $goodsList['price'];
					$goods_tmp[$k]['imgHeight'] = '300';
					$goods_tmp[$k]['cpsUrl'] = $goodsList['cps_url'];
					$goods_tmp[$k]['status'] = 0;
					$goods_tmp[$k]['shopType'] = 0;
					//判断是否已下架
					$sql = "SELECT `goods_id` FROM ".$ecs->table('goods')." WHERE goods_id=".$goodsList['goods_id']." AND if_show=1";
					$row = $db->getRow($sql);
					if(!$row){
						$goods_tmp[$k]['status'] = 2;
					}
					$sql = "SELECT `stock` FROM ".$ecs->table('goods_spec')." WHERE goods_id=".$goodsList['goods_id'];
					$stock = $db->getRow($sql);
					//判断是否有库存
					if(!$stock['stock']){
						$goods_tmp[$k]['status'] = 1;
					}
				}
				$arr_tmp['goodsList']=$goods_tmp;
				$arr_tmp['count']=count($row);
			}else{
				$arr_tmp['goodsList']=array();
				$arr_tmp['count']=0;
			}
		}else{
			$arr_tmp['statusCode']=10000;
		}
		
		return $json->encode($arr_tmp);
		*/
	}
	//获取商品详细
	public function getGoodsDetail($id){
		global $json,$db,$ecs;
		$sql = "SELECT goods_id,goods_name,cate_id,cate_name,default_image,price FROM ".$ecs->table('goods')." WHERE goods_id = ".$id ;
		//return $sql;
		
		$goods= $db->getRow($sql);
		if($goods['default_image']){

			$goods['default_image']=IMG_PREFIX.$goods['default_image'];
		}else{
			$goods['default_image']='';
		}
		$goods['goods_id']	=	empty($goods['goods_id'])?'':$goods['goods_id'];
		$goods['goods_name']=	empty($goods['goods_name'])?'':$goods['goods_name'];
		$goods['price']		=	empty($goods['price'])?0:$goods['price'];
		//$goods['goods_id']	=	empty($goods['goods_id'])?'':$goods['goods_id'];
		return $goods;
	}
	//待评论
	public function getMyCommentList($token)
	{
	
		global $json,$db,$ecs;		
	
		$arr_tmp=array();
		$sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		if($row){
			$sql = "SELECT og.goods_id,o.add_time FROM " .$ecs->table('order')." AS o LEFT JOIN ".$ecs->table('order_goods')." AS og on o.order_id=og.order_id WHERE o.status IN (10,20,30,40) AND o.evaluation_status=0 AND o.buyer_id=".$row['user_id'];
				
			$res=$db->getAll($sql);
			
			if($res){
				$brr=array();
				foreach($res as $k=>$v){
					$goodsInfo=$this->getGoodsDetail($v['goods_id']);
					$brr[$k]['goodsId']			=	$goodsInfo['goods_id'];
					$brr[$k]['googsImgUrl']		=	$goodsInfo['default_image'];
					$brr[$k]['googsName']		=	$goodsInfo['goods_name'];
					$brr[$k]['status']			= 	1;
					$brr[$k]['buyDate']			=  	$v['add_time'];	
					
				}
				$newarr=array('commentItemList'=>$brr);
				return $json->encode($newarr);
			}else{
				$arr_tmp['commentItemList']=array();
			}
				
			return $json->encode($arr_tmp);
				
		}else{
			$arr_tmp['commentItemList']=array();
// 			$arr_tmp['statusCode']=10000;
		}
	
		return $json->encode($arr_tmp);
	
	}
	//意见反馈
	public function feedback($idea){
		global $json,$db,$ecs;

			$data=array();
			$data['idea']		=	$idea;
			$db->autoExecute ( $ecs->table ( 'feedback' ), $data, 'INSERT' );
			
			$arr_tmp['statusCode']=0;
		
		
		return $json->encode($arr_tmp);
	}
	//待自提
	public function getMyGoodsList($token)
	{
		global $json,$db,$ecs;
		
		$arr_tmp=array();
		$sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		if($row){
			$sql = "SELECT o.order_id,o.order_sn,o.order_amount,o.add_time,o.payment_id, o.status  FROM " .$ecs->table('order')." AS o 
			 LEFT JOIN " . $ecs->table("order_extm") . " AS e ON o.order_id = e.order_id WHERE  o.buyer_id=$row[user_id] AND e.shipping_name like '%自提%'";
			
			$row= $db->getAll($sql);
			if($row){
				
				
				$new_temp=array();
				$order_newtemp=array();
				foreach($row as $k=>$v){
					//return $sql;
					$shipping_id = $db->getOne($sql);
					$new_temp['id']			=	$v['order_id'];
					$new_temp['Consignee']	=	$this->getConsigeeInfo($v['order_id']);
					
					 			
					$sql = "SELECT goods_id, spec_id FROM " . $ecs->table("order_goods") . " WHERE order_id = '{$v["order_id"]}'";
					$goods_list = $db->getAll($sql);
					
					$new_temp['cartItemList'] = array();
					
					foreach($goods_list as $key => $val){
						$gwhere = $val["spec_id"] > 0 ? " AND s.spec_id = '{$val["spec_id"]}'" : '';
							
						$sql = "SELECT g.if_show, s.stock, g.price, g.goods_name, g.default_image FROM " . $ecs->table("goods") . " AS g ".
								" LEFT JOIN " . $ecs->table("goods_spec") . " AS s ON s.goods_id = g.goods_id WHERE s.goods_id = '{$val["goods_id"]}'" . $gwhere;
						//return $sql;
						$ginfo = $db->getRow($sql);
						 
						$status = 0;
						 
						if($ginfo['if_show'] != 1){
							$status = 2;
						}
						 
						if($ginfo['stock'] <= 0){
							$status = 1;
						}
							
							
						$goods = array(
								'goodsId' => $val["goods_id"],
								'specId'  => $val["spec_id"],
								'quantity' => $val["quantity"],
								'stauts'   => $status,
								'goods' => array(
										'id' => $val["goods_id"],
										'name' => $ginfo["goods_name"],
										'price' => $ginfo['price'],
										'smallImgUrl' => IMG_PREFIX.$ginfo["default_img"]
								)
						);
						$new_temp['cartItemList'][]	=	$goods;
					}

					$payWay = $this->format_payment($v['payment_id']);
					$new_temp['orderCode']	=	$v['order_sn'];
					$new_temp['orderMoney']	=	$v['order_amount'];
					
				  		if($v["status"] == 20){
				  			$s = 0;
				  		}
				  		elseif($v['status'] == 11){
				  			$s = 1;
				  		}
				  		elseif($v['status'] == 0){
				  			$s = 2;
				  		}elseif($v['status'] == 40){
				  			$s = 3;
				  		}elseif($v['status'] == 30){
				  			$s = 4;
				  		}
					
					$new_temp['orderStatus']=	$status;
					$new_temp['orderTime']	=	$v['add_time'];
					$new_temp["delivery"] = 1;
					$new_temp["payUrl"] = '';
					$new_temp["payWay"] = $payWay;
					$order_newtemp['takeList'][]['Order']=$new_temp;
					$order_newtemp['takeList'][$k]['takeAddress']='';
				}
				
				
				
			}else{
				$order_newtemp=array('takeList'=>array());
			}		
				
				//$order_newtemp['takeList'][]['Order']=$new_temp;
			return $json->encode($order_newtemp);
		}else{
			
		$arr_tmp['statusCode']=10000;
		}
		
		return $json->encode($arr_tmp);
		
	}
	
	public function format_payment($pay_id){
		global $db, $ecs;
		$sql = "SELECT payment_code FROM " . $ecs->table("payment") . " WHERE payment_id = '{$pay_id}'";
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
	//获取收获人信息
	public function getConsigeeInfo($id)
	{	global $json,$db,$ecs;
		$sql = "SELECT *  FROM " .$ecs->table('order_extm')." WHERE order_id='" . $id . "'";
		
		
		$row= $db->getRow($sql);
		$consingee = array();
			
			$consingee['name']		=	$row['consignee'];
			$consingee['phone']		=	$row['phone_mob'];
			$consingee['cityID']	=	$row['region_id'];
			$consingee['address']	=	$row['address'];
			$consingee['zipCode']	=	$row['zipcode'];
		return $consingee;
	}
	//获取订单商品
	public function getOrGoodsList($id)
	{		
		global $json,$db,$ecs;
		$sql = "SELECT goods_id  FROM " .$ecs->table('order_goods')." WHERE order_id='" . $id . "'";
		$row= $db->getAll($sql);
		$goods_tmp=array();
		foreach($row as $k=>$v){
			$goodsList=$this->getGoodsDetail($v['goods_id']);
					//return $goodsList;
			$goods_tmp[$k]['id'] 	= $goodsList['goods_id'];
			$goods_tmp[$k]['name']	= $goodsList['goods_name'];
			$goods_tmp[$k]['smallImgUrl'] = $goodsList['default_image'];
			$goods_tmp[$k]['price'] = $goodsList['price'];
			$goods_tmp[$k]['imgHeight'] = '300';
			$goods_tmp[$k]['cpsUrl'] = $goodsList['cps_url'];
		}
		return $goods_tmp;
	}
	//待付款数量/待评价/待自提
	public function getUserAction($token)
	{
		global $json,$db,$ecs;
		
		$sql = "SELECT user_id,nick_name,score FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";		
		$row= $db->getRow($sql);
		if($row){			
			$sql = "SELECT count(rec_id) FROM " .$ecs->table('order_goods')." AS o INNER JOIN ".$ecs->table('order').
				   " AS oa on o.order_id=oa.order_id WHERE oa.status IN (10,20,30,40) AND oa.buyer_id='".$row['user_id']."' AND oa.evaluation_status=0" ;
			$waitcomment= $db->getOne($sql); //待评价

			$sql = "SELECT count(order_id) FROM " .$ecs->table('order')." WHERE buyer_id='".$row['user_id']."' AND status != 40" ;

			$waitpay= $db->getOne($sql); //待付款数量


			$sql = "SELECT count(order_id) FROM " .$ecs->table('order')." AS o WHERE  o.buyer_id=$row[user_id] " ;
			$waitzj= $db->getOne($sql); //待自提

			$arr_tmp=array();
			$arr_tmp['noPayCount']		=	$waitpay;
			$arr_tmp['reviewCount']		=	$waitcomment;
			$arr_tmp['selfHelpCount']	=	$waitzj;
			$arr_tmp['nickName']	=	$row['nick_name']?$row['nick_name']:'';
			$arr_tmp['score']	=	$row['score']?$row['score']:0;

			return $json->encode($arr_tmp);
			
			
		}else{
			
			$arr_tmp['statusCode']=10000;
		}
		
		return $json->encode($arr_tmp);
		
	}
	//获取评论列表
	public function getCommentList($token,$goodsId,$pageSize,$pageIndex,$type)
	{
		
		global $json,$db,$ecs;
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		
		$arr_tmp=array();
		$sql = "SELECT user_id  FROM " .$ecs->table('member')." WHERE user_token='" . $token . "'";
		
		$row= $db->getRow($sql);
		if($row){
			
			
				/*
				$sql = "SELECT comment,oa.buyer_id,oa.buyer_name,evaluation_time FROM " .$ecs->table('order_goods')." AS o INNER JOIN ".$ecs->table('order')." AS oa on o.order_id=oa.order_id WHERE oa.evaluation_status=1 AND goods_id=$goodsId  limit " . ($pageSize * ($pageIndex-1)) . ','. $pageSize;
				
				//$res = $db->SelectLimit($sql, $pageSize, ($pageIndex - 1) * $pageSize);
				$res=$db->getAll($sql);
				
				
				*/

			
			
			if($type==1)
			{
				$cate='goods_comment';
				$res=getCommentByGid($goodsId, 0 ,$cate,($pageSize * ($pageIndex-1)) . ','. $pageSize);
				//基本款订单评论
			}elseif($type==2)
			{
				$cate='personaldesign';
				$res=getCommentwidthoutuid($goodsId,$cate,($pageSize * ($pageIndex-1)) . ','. $pageSize);
				//个人设计详情页
			}elseif($type==3)
			{
				$cate='streetinfo';
				$res=getCommentwidthoutuid($goodsId,$cate,($pageSize * ($pageIndex-1)) . ','. $pageSize);
				//街拍详情页
			}
			$brr['statusCode']=0;
			foreach($res as $k=>$v){
					//$u=$res[$k]['uid'];
					//$userInfo=getUinfoByUid($res[$k]['uid']);
					//$userInfo=$this->getUserInfo($res[$k]['uid']);
					$userInfo=$this->getUserList($res[$k]['uid']);
					$arr_r['headImgUrl']	=	$userInfo['portrait'];
					$arr_r['Content']		=	$res[$k]['content'];
					$arr_r['Time']			=	$res[$k]['add_time'];
					$arr_r['username']			=	$userInfo['user_name'];
					
					$brr['commentList'][]=$arr_r;
				}

			return $json->encode($brr);
			
		}else{
			
			$arr_tmp['statusCode']=10000;
		}
		
		return $json->encode($arr_tmp);
		
	}
	
	function qrcode($user_id)
	{
		global $json;
		//$url=SITE_URL.$view->build_url(array('app'=>'service','act'=>'info','arg'=>$res['idserve']));
		//var_dump($url);exit;
		
		$url=M_SITE_URL.'index.php/club-cooler-'.$user_id.'.html';
		
		
		
		QRcode('appuser',$user_id,$url);
		
		$mqrcode=getQrcodeImage('appuser',$user_id,4);
		
		$arr_tmp['statusCode']=0;
		$arr_tmp['mqrcode']=$mqrcode;
		return $json->encode($arr_tmp);
	}
	
	//获取会员基本信息
	public function getUserList($uid){
		global $json,$db,$ecs;
		
		$sql  = 'SELECT portrait,user_name FROM ' .$ecs->table('member'). ' AS u ' .
				" WHERE u.user_id = '$uid'";
		$row = $db->getRow($sql);
		
		return $row;
	}
  /**
   * 获取会员信息
   * @param string $name
   * @access protected
   * @return void
   */
   public function getUserInfo($uid)
   {
		global $json,$db,$ecs;
		$user_info = getUinfoByUid($uid);
		
		return $json->encode($user_info);
	}
	
	/**
	 * 查询会员的红包金额
	 *
	 * @access  public
	 * @param   integer     $user_id
	 * @return  void
	 */
	private function get_user_bonus($user_id = 0)
	{
		global $db,$ecs;
	
		$sql = "SELECT SUM(bt.type_money) AS bonus_value, COUNT(*) AS bonus_count ".
				"FROM " .$ecs->table('user_bonus'). " AS ub, ".
				$ecs->table('bonus_type') . " AS bt ".
				"WHERE ub.user_id = '$user_id' AND ub.bonus_type_id = bt.type_id AND ub.order_id = 0";
		$row = $db->getRow($sql);
	
		return $row;
	}
	
	
	/**
	 * 记录帐户变动
	 * @param   int     $user_id        用户id
	 * @param   float   $user_money     可用余额变动
	 * @param   float   $frozen_money   冻结余额变动
	 * @param   int     $rank_points    等级积分变动
	 * @param   int     $pay_points     消费积分变动
	 * @param   string  $change_desc    变动说明
	 * @param   int     $change_type    变动类型：186 wap借口修改（字段类型 上限186）
	 * @return  void
	 */
	public function execAccountChange($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '')
	{
		global $json,$db,$ecs;
		
		$change_type = 186;
		$return = array();
		/* 插入帐户变动记录 */
		$account_log = array(
				'user_id'       => $user_id,
				'user_money'    => $user_money,
				'frozen_money'  => $frozen_money,
				'rank_points'   => $rank_points,
				'pay_points'    => $pay_points,
				'change_time'   =>  time() - date('Z'),
				'change_desc'   => $change_desc,
				'change_type'   => $change_type
		);
		if ($db->autoExecute($ecs->table('account_log'), $account_log, 'INSERT') > 0){
			/* 更新用户信息 */
			$sql = "UPDATE " . $ecs->table('users') .
			" SET user_money = user_money + ('$user_money')," .
			" frozen_money = frozen_money + ('$frozen_money')," .
			" rank_points = rank_points + ('$rank_points')," .
			" pay_points = pay_points + ('$pay_points')" .
			" WHERE user_id = '$user_id' LIMIT 1";
			if ($db->query($sql)){
				$return['success'] = 'true';
				$return['state'] = 1;
			}else{
				$return['success'] = 'false';
				$return['state'] = 3;
			}
			
		}else{
			$return['success'] = 'false';
			$return['state'] = 2;
		}
	
		return $json->encode($return);
	}
	
	//修改用户中心密码
	function modifyPassWord($token,$oldPassWord,$newPassWord){
		global $json,$db,$ecs;
		$return = array();
		
		$sql = "SELECT user_id ,user_name, user_token, password ".
				"FROM " .$ecs->table('member'). 
				" WHERE user_token = '$token'";
		
		$row = $db->getRow($sql);
		
		if($row){
			if($row['password'] == md5($oldPassWord)){
				$newpsd = md5($newPassWord);
				$newtoken = md5($row['user_name'].$newpsd);
				$sql = "UPDATE " .$ecs->table('member').
						"SET password='$newpsd', user_token='$newtoken' WHERE user_id = ".$row['user_id'];
				$row = $db->query($sql);
				
				$return['statusCode'] 	= 0;
				$return['msg'] 		= 'modify ok';
				
			}else{
				$return['statusCode'] 	= 1;
				$return['msg'] 		= 'oldPassWord error';
				
			}
		}else{
			
			$return['statusCode'] 	= 1;
			$return['msg'] 		= 'token error';
		}
		return $json->encode($return);
	}
	//修改用户昵称
	function modifyNickName($token,$newNickName){
		global $json,$db,$ecs;
		$return = array();
	
		$sql = "SELECT user_id ".
				"FROM " .$ecs->table('member').
				" WHERE user_token = '$token'";
	
		$row = $db->getRow($sql);
	
		if($row){
			$sql = "UPDATE " .$ecs->table('member').
			"SET nickname='$newNickName'WHERE user_id = ".$row['user_id'];
			//return $sql;
			$row = $db->query($sql);			
			$return['statusCode'] 	= 0;
			
		}else{
				
			$return['statusCode'] 	= 10000;
			$return['msg'] 		= 'token error';
		}
				return $json->encode($return);
	}
	function editUserInfo($uid,$birthday='',$sex='',$email='',$msn='',$qq='',$office_phone='',$home_phone='',$mobile_phone=''){
		global $json,$db,$ecs;
		$return = array();
		/* 更新会员的其它信息 */
		$other =  array();
		if ($birthday != "''") $other['birthday'] = "$birthday";
		
		if ($sex != "''" ) $other['sex']  = "$sex";
		if ($email != "''") $other['email']        = "$email";

		if ($msn != "''") $other['msn'] = isset($msn) ? htmlspecialchars(trim($msn)) : '';
		if ($qq != "''") $other['qq'] = isset($qq) ? htmlspecialchars(trim($qq)) : '';
		if ($office_phone != "''") $other['office_phone'] = isset($office_phone) ? htmlspecialchars(trim($office_phone)) : '';
		if ($home_phone != "''") $other['home_phone'] = isset($home_phone) ? htmlspecialchars(trim($home_phone)) : '';
		if ($mobile_phone != "''") $other['mobile_phone'] = isset($mobile_phone) ? htmlspecialchars(trim($mobile_phone)) : '';
		
		;
		
		if ($db->autoExecute($ecs->table('users'), $other, 'UPDATE', "user_id = '$uid'") > 0){
				$return['success'] = 'true';
				$return['state'] = 1;
		}else{
			$return['success'] = 'false';
			$return['state'] = 2;
		}
		
		return $json->encode($return);
	}
	

	
//--------------------------------------------------------商品信息----------------------------------------------------------------------//

  /**
   * 首页轮播
   * @param int    num
   * @return array
   */
   public function getIndexImg($limit){	
   	     global $json,$db,$ecs;
   	     $return = array(); 
		 $sql = 'SELECT img_phone,link_url FROM ' . $ecs->table('index_img') . " WHERE is_position=0 ORDER BY sort_order LIMIT $limit";
		 $all = $db->getAll($sql);
		 if ($all){
		 	$return['success'] = 'true';
		 	$return['data'] = $all;
		 	
		 }else{
		 	$return['success'] = 'true';
		 	$return['data'] = array();
		 }
	
		 return $json->encode($return);
	}
	
	/**
	 * 首页 特价商品
	 */
	public function getSpecialGoods($xf_select, $limit){
		global $json;
	    $return = array();
		$goods = array();
	    
		$goods = $this->getRecommendGoods('is_tjcx', $xf_select, $limit);
		if ($goods){
			$return['success'] = 'true';
			$return['data'] = $goods;
		
		}else{
			$return['success'] = 'true';
			$return['data'] = array();
		}
		
		return $json->encode($return);
	}
	
	/**
	 * 首页 推荐
	 */
	public function getBestGoods($xf_select, $limit){
		global $json;
		$return = array();
		$goods = array();
		 
		$goods = $this->getRecommendGoods('is_best', $xf_select, $limit);
		if ($goods){
			$return['success'] = 'true';
			$return['data'] = $goods;
	
		}else{
			$return['success'] = 'true';
			$return['data'] = array();
		}
		
		return $json->encode($return);
	}
	
	function camera($userID,$data)
	{
		global $json,$db,$ecs;
		
		//$img = $data['newheadImg']?base64_decode($data['newheadImg']):'';
		$img = $data['newheadImg']?$data['newheadImg']:'';
		
		$dir = substr(getcwd(),0,-12);
		
		$imgpath='/upload_user_photo/'.$userID.'_0_'.md5( uniqid() . mt_rand(0,255) ).'.jpg';
		file_put_contents($dir.$imgpath,$img);
		
		$imgurl=SITE_URL.$imgpath;
		
		$m = m('userphoto');
		$imgdata = array(
							'add_time'=>time(),
							'url'=>$imgurl,
							'uid'=>$userID,
							'cate'=>2,
					);
		$res=$m->add($imgdata);
		if($res){
			$statusCode = array("statusCode"=>0,"msg"=>"修改成功");
  				return $json->encode($statusCode);
		}else 
		{
			$statusCode = array("statusCode"=>1,"msg"=>"修改失败");
  			return $json->encode($statusCode);
		}

	}
	
	//修改用户信息
	function modifyUserData($userID,$data){
		global $json,$db,$ecs;

			//插入的数据值
			
			
			
			$nikename = array_key_exists('newNickName', $data)?$data['newNickName']:'';
			$constella= array_key_exists('newConstellation', $data)?$data['newConstellation']:'';
			$sex      = array_key_exists('newSex', $data)?$data['newSex']:'';
			$tell     = array_key_exists('newPhone', $data)?$data['newPhone']:'';
			//$addr     = array_key_exists('newAddress', $data)?$data['newAddress']:'';
			$newBlood = array_key_exists('newBloodtype', $data)?$data['newBloodtype']:'';
			
			$signature = array_key_exists('newsignature', $data)?$data['newsignature']:'';
			//$def_addr = $data['newdef_addr']?$data['newdef_addr']:'';
			$province = array_key_exists('newprovince', $data)?$data['newprovince']:'';
			$city = array_key_exists('newcity', $data)?$data['newcity']:'';
			//$gender = $data['newgender']?$data['newgender']:'';
			
			$data['newNickName']?$newNickName = "nickname = '".$nikename."',":'';
			$data['newSex']?$newSex = "gender = ".$sex.",":'';
			//$data['newPhone']?$newPhone = "phone_mob = '".$tell."',":'';
			
			
		
			//处理上传头像
			//$img              = $data['newheadImg']?base64_decode($data['newheadImg']):'';
			$img              = $data['newheadImg']?file_get_contents($data['newheadImg']['tmp_name']):'';
			//$img='';
			if ($img)
			{
				$img_name = $userID."_".time().'.jpg';
				$imgpath = '/upload_user_photo/avatar/';
					
				/*上传原图*/
				$fileName1 = ROOT_PATH.$imgpath.'original/'.$img_name;
				file_put_contents($fileName1,$img);
				
				/*生成3分缩络图*/
				$fileName2 = ROOT_PATH.$imgpath.'20/'.$img_name;
				pro_img_multi($data['newheadImg'],20,20,$fileName2);
				$fileName3 = ROOT_PATH.$imgpath.'48/'.$img_name;
				pro_img_multi($data['newheadImg'],48,48,$fileName3);
				$fileName4 = ROOT_PATH.$imgpath.'162/'.$img_name;
				pro_img_multi($data['newheadImg'],162,162,$fileName4);
				
				
			}
			$data['newheadImg']?$newheadImg = "avatar = '$img_name' ":'';
			
			$data['newsignature']?$newsignature = "signature = '".$signature."',":'';
			//$data['newdef_addr']?$newdef_addr = "def_addr = '".$def_addr."',":'';
			$data['newprovince']?$newprovince = "province = '".$province."',":'';
			$data['newcity']?$newcity = "city = '".$city."',":'';
			//$data['newgender']?$newgender = "gender = '".$gender."',":'';
			
			
			//return $json->encode($data['newheadImg']);
			
			$str = $newNickName.$newSex.$newBlood.$newheadImg.$newsignature.$newprovince.$newcity;
			$sql_str = substr($str,0,-1);
			$sql  = "UPDATE ".$ecs->table('member')." SET ".$sql_str." where user_id = $userID";
			//$sql = "select nick_name from ecm_member";
			$row = $db->query($sql);
			//$row = $db->getALL($sql);
			
	  		if ($row)
  			{
  				$statusCode = array("statusCode"=>0,"msg"=>"修改成功");
  				return $json->encode($statusCode);
  				//return $json->encode($sql_str);
  			}else{
  				$statusCode = array("statusCode"=>1,"msg"=>"修改失败");
  				return $json->encode($statusCode);
  			}
			
	}
	
	/**
	 * 首页 新品
	 */
	public function getNewGoods($xf_select, $limit){
		global $json;
		$return = array();
		$goods = array();
		 
		$goods = $this->getRecommendGoods('is_new', $xf_select, $limit);
		if ($goods){
			$return['success'] = 'true';
			$return['data'] = $goods;
	
		}else{
			$return['success'] = 'true';
			$return['data'] = array();
		}
		return $json->encode($return);
	}
	
	/**
	 * 获得推荐商品 外卖
	 *
	 * @access  public
	 * @param   string      $type       推荐类型，is_tjcx[特价] is_best[推荐], is_new[新品]
	 * @return  array
	 */
	private function getRecommendGoods($type = '', $xf_select=null, $num=4 )
	{
		global $json,$db,$ecs;
		if(!empty($num)){
			$condi='LIMIT '.$num;
		}else{
			$condi=='';
		}
		//取出所有符合条件的商品数据，并将结果存入对应的推荐类型数组中
		$sql = 'SELECT  b.brand_name,g.goods_id, g.goods_name, g.xf_select, g.goods_name_style, g.market_price, g.shop_price , g.promote_price,g.taste, ' .
				"g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img,g.imglist_phone,g.goods_sn " .
				'FROM ' . $ecs->table('goods') . ' AS g ' .
				"LEFT JOIN " . $ecs->table('brand') . " AS b ON g.brand_id = b.brand_id ".
				$sql .= " WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.goods_type = 0 AND g.$type= 1 ";
		if ($xf_select != null)
			//$sql .= "  AND g.xf_select = '$xf_select' ";
			
		$sql .= ' ORDER BY g.sort_order, g.last_update '.$condi;

		$result = $db->getAll($sql);
		foreach ($result AS $idx => $row)
		{
			$goods[$idx]['index'] = $idx+1;
			$goods[$idx]['Code']  = $row['goods_sn'];
			$goods[$idx]['Name']  = $row['goods_name'];
			$goods[$idx]['URL']   = $row['imglist_phone'];
			$goods[$idx]['id']           = $row['goods_id'];
 			$goods[$idx]['name']         = $row['goods_name'];
			//$goods[$idx]['brief']        = $row['goods_brief'];
			//$goods[$idx]['taste']        = $row['taste'];
			$goods[$idx]['xf_select']    = $row['xf_select'];
			//$goods[$idx]['brand_name']   = $row['brand_name'];
			//$goods[$idx]['goods_style_name']   = $row['goods_name'];
			//$goods[$idx]['short_name']   = $row['goods_name'];
			//$goods[$idx]['short_style_name']   = $goods[$idx]['short_name'];
			//$goods[$idx]['market_price'] = $row['market_price'];
			//$goods[$idx]['shop_price']   = $row['shop_price'];
			//$goods[$idx]['market_price1'] = intval(ceil($row['market_price']));
			//$goods[$idx]['shop_price2']   = intval(ceil($row['shop_price']));
			//$goods[$idx]['thumb']        = 'http://118.144.86.100/51miu/'.$row['goods_thumb'];
			//$goods[$idx]['goods_img']    = 'http://118.144.86.100/51miu/'.$row['goods_img'];
			//$goods[$idx]['imglist_phone']    = $row['imglist_phone'];
			//$goods[$idx]['url']          = 'goods.php?id='.$row['goods_id'];
	
			//TODO 查询本菜品是否在当前的店中
			$goods[$idx]['cando'] = 1;
			if (isset($_SESSION['shop_id']) && $_SESSION['shop_id'] > 0)
			{
				$sql = "SELECT shop_ids FROM " .$ecs->table('goods'). " WHERE goods_id = '$row[goods_id]'";
				$rst = $db->getRow($sql);
				$shop_arr = @explode(',', $rst['shop_ids']);
				$goods[$idx]['cando'] = (@in_array($_SESSION['shop_id'], $shop_arr) !== false) ? 1 : 0;
			}
		}
	
		return $goods;
	}
	
	/**
	 *  获取会员余额- 查看帐户明细
	 *
	 * @access  public
	 * @param   int         $user_id        用户ID号
	 * @param   int         $num            列表最大数量
	 * @param   int         $start          列表起始位置
	 * @return  array       $order_list     订单列表
	 */
	public function getAccountDetail($user_id, $num = 10, $start = 0)
	{
		global $json,$db,$ecs;
		
		//获取余额记录
        $account_log = array();
        $account_type = 'user_money';
        $sql = "SELECT * FROM " . $ecs->table('account_log') .
               " WHERE user_id = '$user_id'" .
               " AND $account_type <> 0 " .
               " ORDER BY log_id DESC";
        $res = $db->SelectLimit($sql, $num, $start);
        while ($row = $db->fetchRow($res))
        {
            $row['change_time'] = $this->_local_date('Y-m-d H:i:s', $row['change_time']);
            $row['type'] = $row[$account_type] > 0 ? '增加' : '减少';
            $row['user_money'] = abs($row['user_money']);
            $row['frozen_money'] = abs($row['frozen_money']);
            $row['rank_points'] = abs($row['rank_points']);
            $row['pay_points'] = abs($row['pay_points']);
            $row['short_change_desc'] = $this->_sub_str($row['change_desc'], 60);
            $row['amount'] = $row[$account_type];
            $account_log[] = $row;
        }
    	
		if ($account_log){
			$return['success'] = 'true';
			$return['data'] = $account_log;
		
		}else{
			$return['success'] = 'true';
			$return['data'] = array();
		}
		return $json->encode($return);
	}
	
	
	
	/**
	 *  获取用户指定范围的订单列表
	 *
	 * @access  public
	 * @param   int         $user_id        用户ID号
	 * @param   int         $num            列表最大数量
	 * @param   int         $start          列表起始位置
	 * @return  array       $order_list     订单列表
	 */
	public function getUserOrders($user_id, $num = 10, $start = 0)
	{
		global $json,$db,$ecs;
	
		/* 取得订单列表 */
		$arr    = array();
		$return = array();
		$sql = "SELECT order_id,wort,shop_id, order_sn, order_status, shipping_status, pay_status, add_time, " .
				"(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee ".
				" FROM " .$ecs->table('order_info') .
				" WHERE user_id = '$user_id' ORDER BY add_time DESC";
		$res = $db->SelectLimit($sql, $num, $start);
	
		while ($row = $db->fetchRow($res))
		{
			$_LANG['os'][0] = '未确认';
			$_LANG['os'][1] = '已确认';
			$_LANG['os'][2] = '已确认';
			$_LANG['os'][3] = '已确认';
			$_LANG['os'][4] = '已取消';
			$_LANG['os'][5] = '无效';
			$_LANG['os'][6] = '退货';
				
			$_LANG['ss'][0] = '未发货';
			$_LANG['ss'][1] = '配货中';
			$_LANG['ss'][2] = '已发货';
			$_LANG['ss'][3] = '收货确认';
			$_LANG['ss'][4] = '已发货(部分商品)';
			$_LANG['ss'][5] = '配货中'; // 已分单
				
			$_LANG['ps'][0] = '未付款';
			$_LANG['ps'][1] = '付款中';
			$_LANG['ps'][2] = '已付款';
			
			/*订单类型 1；订座 2外卖 3：订餐 */
			$orderType = (!empty($row['shop_id'])) ? 1 : (($row['wort'] == 1) ? 3 : 2);
				
				
			//发货中(处理分单) // 备货中
			$row['shipping_status'] = ($row['shipping_status'] == 5) ? 3 : $row['shipping_status'];
			$row['order_status'] = $_LANG['os'][$row['order_status']] . ',' . $_LANG['ps'][$row['pay_status']] . ',' . $_LANG['ss'][$row['shipping_status']];
	
			$arr[] = array('order_id'       => $row['order_id'],
					'order_sn'       => $row['order_sn'],
					'order_time'     => $this->_local_date('Y-m-d H:i:s', $row['add_time']),
					'order_status'   => $row['order_status'],
					'order_type'   => $orderType,
					'shop_id'   => $row['shop_id'],
					'total_fee'      => $row['total_fee']);
		}
	
		if ($arr){
			$return['success'] = 'true';
			$return['data'] = $arr;
	
		}else{
			$return['success'] = 'true';
			$return['data'] = array();
		}
		return $json->encode($return);
	}
	
	/**
	 * 取得收货人地址列表
	 * 
	 * @param int $token 用户加密唯一串
	 * @return array id 收货信息ID name 收货人姓名 phone 收货人电话 cityID 县的ID address 收货地址 zipCode 邮编
	 * @author v5
	 */
	public function getConsigneeList($token) {
		global $json, $db, $ecs;
				
		$list = array ();
		$return = array ();
		
		$sql = "SELECT user_id " . "FROM " . $ecs->table ( 'member' ) . " WHERE user_token = '$token'";
		$row = $db->getRow ( $sql );
		if ($row) {
			$sql = "SELECT * FROM " . $ecs->table ( 'address' ) . " WHERE user_id = '{$row['user_id']}'";
			$list = $db->getAll ( $sql );
			if (! $list) {
				return $json->encode ( array('consigneeList'=>$return) );
			}
			
			foreach ( $list as $region_id => $consignee ) {				
				$return [$region_id] ['id'] = $consignee ['addr_id'];
				$return [$region_id] ['name'] = isset ( $consignee ['consignee'] ) ?  $consignee ['consignee']  : '';
				$return [$region_id] ['phone'] = isset ( $consignee ['phone_mob'] ) ?   $consignee ['phone_mob']  : 0;
				$return [$region_id] ['cityID'] = isset ( $consignee ['region_id'] ) ? intval ( $consignee ['region_id'] ) : 0;
				$return [$region_id] ['address'] = isset ( $consignee ['address'] ) ?  $consignee ['address'] : '';
				$return [$region_id] ['zipCode'] = isset ( $consignee ['zipcode'] ) ? intval ( $consignee ['zipcode'] ) : 0;
			}
			return $json->encode ( array('consigneeList'=>$return) );
		} else {
			
			$return ['statusCode'] = 10000;
			$return ['msg'] = 'token error';
		}
		return $json->encode ( $return );
	}
	
	/**
	 * 添加或更新指定用户收货地址
	 * @param str $token
	 * @param array $Consignee
	 * @param int $flag
	 * @return array
	 * @author v5
	 */
	public function operatConsignee($token,$Consignee,$flag){	
		global $json, $db, $ecs;
		
		$list = array ();
		$return = array ();
		
		if (in_array($flag, array(1,3))){
		if (!isset($Consignee['id'])||empty($Consignee['id']))
			return $json->encode ( array('statusCode'=>1,'msg'=>'无效数据') );//无效数据
		}
		if (!isset($Consignee['phone'])||empty($Consignee['phone'])||!is_numeric($Consignee['phone']))
			return $json->encode ( array('statusCode'=>2,'msg'=>'无效号码') );//无效号码
	
		/*检测手机合法性*/
		if(!preg_match('/^1[3458][0-9]{9}$/',$Consignee['phone']))
			return $json->encode ( array('statusCode'=>2,'msg'=>'无效号码') );//无效号码
		
		if (!isset($Consignee['cityID'])||empty($Consignee['cityID'])||!is_numeric($Consignee['cityID']))
			return $json->encode ( array('statusCode'=>3,'msg'=>'暂不支持本地区') );//无效地址
	
		$sql = "SELECT region_id " . "FROM " . $ecs->table ( 'region' ) . " WHERE region_id = {$Consignee['cityID']}";
		
		$row = $db->getRow ( $sql );
	
		if (empty($row['region_id']))
			return $json->encode ( array('statusCode'=>3,'msg'=>'暂不支持本地区') );	

// 		if (empty($flag)&&isset($row['region_id']))
// 			return $json->encode ( array('statusCode'=>3,'msg'=>'invalid number1') );
		
		$sql = "SELECT user_id " . "FROM " . $ecs->table ( 'member' ) . " WHERE user_token = '$token'";
		$row = $db->getRow ( $sql );
		if ($row) {
		$temData = array(
		'addr_id'=>$Consignee['id'],
		'user_id'=>$row['user_id'],
		'consignee'=>$Consignee['name'],
		'phone_mob'=>$Consignee['phone'],
		'region_id'=>$Consignee['cityID'],
		'address'=>$Consignee['address'],
		'zipcode'=>$Consignee['zipCode']
		);
			switch ($flag) {
				case 1:
					/* 更新指定记录 */
					if (! $db->autoExecute ( $ecs->table ( 'address' ), $temData, 'UPDATE', 'addr_id = ' . $temData ['addr_id'] . ' AND user_id = ' . $temData ['user_id'] ))
							return $json->encode ( array('statusCode'=>4,'msg'=>'数据库错误') );
					break;
				case 3 :
					/* 删除记录 */
					if (! $db->query ("DELETE FROM ".$ecs->table ( 'address' )." WHERE `addr_id` = {$temData ['addr_id']}"))
						return $json->encode ( array('statusCode'=>4,'msg'=>'数据库错误') );
					break;
				default:
  					/* 插入一条新记录 */
					if (! $db->autoExecute ( $ecs->table ( 'address' ), $temData, 'INSERT' ))
						return $json->encode ( array('statusCode'=>4,'msg'=>'数据库错误') );
			}
			
			return $json->encode ( array('statusCode'=>0,'msg'=>'成功') );
		} else {
				
			$return ['statusCode'] = 10000;
			$return ['msg'] = 'token错误';
		}
		return $json->encode ( $return );
		
	}
	
	
	/**
	 * 获得指定国家的所有省份
	 *
	 * @access      public
	 * @param       int     country    国家的编号
	 * @return      array
	 */
	protected function _get_regions($region_id = 0)
	{
		global $db,$ecs;
		$sql = 'SELECT region_name FROM ' . $ecs->table('region') .
		" WHERE region_id = '$region_id'";
		return $db->getOne($sql);
	}
	
	/**
	 *  获取指订单的详情
	 *
	 * @access  public
	 * @param   int         $order_id       订单ID
	 * @param   int         $user_id        用户ID
	 *
	 * @return   arr        $order          订单所有信息的数组
	 */
	public function getOrderDetail($order_id, $user_id = 0)
	{
		global $json,$db,$ecs;
		
		$return = array();
		$order_id = intval($order_id);
		if ($order_id <= 0)
		{
			$return['success'] = 'false';
			$return['error'] = '-1';
			return $json->encode($return);
		}
		$order = $this->_order_info($order_id);
	
		//检查订单是否属于该用户
		if ($user_id > 0 && $user_id != $order['user_id'])
		{
			$return['success'] = 'false';
			$return['error'] = '-2';
			return $json->encode($return);
		}
	
		/* 对发货号处理 */
// 		if (!empty($order['invoice_no']))
// 		{
// 			$shipping_code = $db->GetOne("SELECT shipping_code FROM ".$ecs->table('shipping') ." WHERE shipping_id = '$order[shipping_id]'");
// 			$plugin = ROOT_PATH.'includes/modules/shipping/'. $shipping_code. '.php';
// 			if (file_exists($plugin))
// 			{
// 				include_once($plugin);
// 				$shipping = new $shipping_code;
// 				$order['invoice_no'] = $shipping->query($order['invoice_no']);
// 			}
// 		}
	
		/* 只有未确认才允许用户修改订单地址 */
		if ($order['order_status'] == 0) // 未确认
		{
			$order['allow_update_address'] = 1; //允许修改收货地址
		}
		else
		{
			$order['allow_update_address'] = 0;
		}
	
		/* 获取订单中实体商品数量 */
		$order['exist_real_goods'] = $this->_exist_real_goods($order_id);
	
		/* 如果是未付款状态，生成支付按钮 */
		if ($order['pay_status'] == 0 &&
				($order['order_status'] == 0 ||
						$order['order_status'] == 1))
		{
			/*
			 * 在线支付按钮
			*/
			//支付方式信息
			$payment_info = array();
			$payment_info = $this->_payment_info($order['pay_id']);
	
			//无效支付方式
			if ($payment_info === false)
			{
				$order['pay_online'] = '';
			}
			else
			{
				//取得支付信息，生成支付代码
				$payment = $this->_unserialize_config($payment_info['pay_config']);
	
				//获取需要支付的log_id
				$order['log_id']    = $this->_get_paylog_id($order['order_id'], $pay_type = PAY_ORDER);
				$order['user_name'] = $_SESSION['user_name'];
				$order['pay_desc']  = $payment_info['pay_desc'];
	
// 				/* 调用相应的支付方式文件 */
// 				include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');
	
// 				/* 取得在线支付方式的支付按钮 */
// 				$pay_obj    = new $payment_info['pay_code'];
// 				$order['pay_online'] = $pay_obj->get_code($order, $payment);
			}
		}
		else
		{
			$order['pay_online'] = '';
		}
	
		/* 无配送时的处理 */
		$order['shipping_id'] == -1 and $order['shipping_name'] = $GLOBALS['_LANG']['shipping_not_need'];
	
		/* 其他信息初始化 */
		$order['how_oos_name']     = $order['how_oos'];
		$order['how_surplus_name'] = $order['how_surplus'];
	
		/* 虚拟商品付款后处理 */
		if ($order['pay_status'] != 0)
		{
			/* 取得已发货的虚拟商品信息 */
			$virtual_goods = $this->_get_virtual_goods($order_id, true);
			$virtual_card = array();
			foreach ($virtual_goods AS $code => $goods_list)
			{
				/* 只处理虚拟卡 */
				if ($code == 'virtual_card')
				{
					foreach ($goods_list as $goods)
					{
						if ($info = $this->_virtual_card_result($order['order_sn'], $goods))
						{
							$virtual_card[] = array('goods_id'=>$goods['goods_id'], 'goods_name'=>$goods['goods_name'], 'info'=>$info);
						}
					}
				}
				/* 处理超值礼包里面的虚拟卡 */
				if ($code == 'package_buy')
				{
					foreach ($goods_list as $goods)
					{
						$sql = 'SELECT g.goods_id FROM ' . $ecs->table('package_goods') . ' AS pg, ' . $ecs->table('goods') . ' AS g ' .
								"WHERE pg.goods_id = g.goods_id AND pg.package_id = '" . $goods['goods_id'] . "' AND extension_code = 'virtual_card'";
						$vcard_arr = $db->getAll($sql);
	
						foreach ($vcard_arr AS $val)
						{
							if ($info = $this->_virtual_card_result($order['order_sn'], $val))
							{
								$virtual_card[] = array('goods_id'=>$goods['goods_id'], 'goods_name'=>$goods['goods_name'], 'info'=>$info);
							}
						}
					}
				}
			}
			$var_card = $this->_deleteRepeat($virtual_card);
			$GLOBALS['smarty']->assign('virtual_card', $var_card);
		}
	
		/* 确认时间 支付时间 发货时间 */
		if ($order['confirm_time'] > 0 && ($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED || $order['order_status'] == OS_SPLITING_PART))
		{
			$order['confirm_time'] = $this->_local_date('Y-m-d H:i:s', $order['confirm_time']);
		}
		else
		{
			$order['confirm_time'] = '';
		}
		if ($order['pay_time'] > 0 && $order['pay_status'] != PS_UNPAYED)
		{
			$order['pay_time'] = $this->_local_date('Y-m-d H:i:s', $order['pay_time']);
		}
		else
		{
			$order['pay_time'] = '';
		}
		if ($order['shipping_time'] > 0 && in_array($order['shipping_status'], array(1, 2)))
		{
			$order['shipping_time'] = $this->_local_date('Y-m-d H:i:s', $order['shipping_time']);
		}
		else
		{
			$order['shipping_time'] = '';
		}
	
		/* 订座信息 */
		if ($order['shop_id']){
			$sql5 = "SELECT sit_group_id1,sit_group_id6,sit_group_id2,sit_group_id3,sit_group_id4,sit_group_id5,sit_group_id7,sit_group_id8,daotian_time,time_2,telphone,mobile,sex,ren_num,lianxiren FROM " . $ecs->table('shop_sit_log').
			" WHERE log_id = '$order[shop_id]' LIMIT 1";
			$orderluck= $db->getAll($sql5);
			
			$sql3 = "SELECT * FROM " . $ecs->table('room_name').
			" WHERE order_id = '$order[order_id]' LIMIT 1";
			$orderlist= $db->getRow($sql3);
			if($orderlist['order_id']){
				$orderluck[0]['fenpei']="<font color='green'>已分配";
			}else{
				$orderluck[0]['fenpei']="<font color='red'>未分配";
			}
	
			foreach($orderluck as $k=>$v){
				$orderluck[$k]['daotian_time']=$this->_local_date('Y年m月d日 H:i',$v['daotian_time']);
			}
			$sonof='';
			$sonof2='';
			if(!empty($orderluck[0]['sit_group_id1'])){
				$sonof.='4人桌('.$orderluck[0]['sit_group_id1'].')['.$orderlist['sit1'].']<br>';
			}
			if(!empty($orderluck[0]['sit_group_id2'])){
				$sonof.='6人桌('.$orderluck[0]['sit_group_id2'].')['.$orderlist['sit2'].']<br>';
			}
			if(!empty($orderluck[0]['sit_group_id3'])){
				$sonof.='8人桌('.$orderluck[0]['sit_group_id3'].')['.$orderlist['sit3'].']<br>';
			}
			if(!empty($orderluck[0]['sit_group_id4'])){
				$sonof.='10人桌('.$orderluck[0]['sit_group_id4'].')['.$orderlist['sit4'].']<br>';
			}
			if(!empty($orderluck[0]['sit_group_id5'])){
				$sonof2.='6人桌('.$orderluck[0]['sit_group_id5'].')['.$orderlist['sit5'].']<br>';
			}
			if(!empty($orderluck[0]['sit_group_id6'])){
				$sonof2.='8人桌('.$orderluck[0]['sit_group_id6'].')['.$orderlist['sit6'].']<br>';
			}
			if(!empty($orderluck[0]['sit_group_id7'])){
				$sonof2.='10人桌('.$orderluck[0]['sit_group_id7'].')['.$orderlist['sit7'].']<br>';
			}
			if(!empty($orderluck[0]['sit_group_id8'])){
				$sonof2.='12人桌('.$orderluck[0]['sit_group_id8'].')['.$orderlist['sit8'].']';
			}
			if(!empty($sonof)){
				$orderluck[0]['sonof']='大厅:'.($sonof);
			}
			if(!empty($sonof2)){
				$orderluck[0]['sonof2']='包间:'.($sonof2);
			}
			if ($order['mendian_id']){
				$laoshu=$this->get_canting_info($order['mendian_id']);
				$orderluck[0]['mendian_info'] = $laoshu['shop_name'].' 地址： '.$laoshu['address'].' 电话 '.$laoshu['tel'];
			}
		}
		
		/*蛋糕信息*/
		if($order['is_cake']==1){
			$appstore=array();
			if($order['lzneed']==2){
				$appstore['lznumber']=intval($order['lznumber'])*3;
			}
			if($order['lzcanju']==2){
				$appstore['fjcanju']=intval($order['fjcanju'])*1;
			}
			if($order['is_five']==1){
				$appstore['fivefee']=10;
			}
			$order['formated_total_fee']=intval($order['total_fee'])+$appstore['lznumber']+$appstore['fjcanju']+$appstore['fivefee'];
			$cakeinfo = array();
			$cakeinfo['lz'] = '蜡烛:'.$order['lznumber'].'盒'; 
			$cakeinfo['canju'] = '附加餐具数量:'.$order['fjcanju'].'套'; 
			$cakeinfo['ziti'] = '巧克力生日牌字体样式:'.($order['chocolate'] == 2) ? ($order['bjziti'] == 2) ? '黑底白字' : '白底黑字' : '';
			$cakeinfo['birthcontent'] = '巧克力生日牌内容:'.($order['chocolate'] == 2) ? ($order['birthcontent']) ? $order['birthcontent'] : '' : '';
			
		}
		
		if ($order){
			$return['success'] = 'true';
			$return['data'] = $order;
			
			/*订座信息*/
			$return['data']['orderluck'] = array();
			if ($orderluck[0]) $return['data']['orderluck'] = $orderluck[0];
			
			/*蛋糕信息*/
			$return['data']['cakeinfo'] = array();
			if ($cakeinfo) $return['data']['cakeinfo'] = $cakeinfo;
			
		
		}else{
			$return['success'] = 'true';
			$return['data'] = array();
		}
// 		return $return;
		return $json->encode($return);
	
	}
	
	public function get_canting_info($shop_id){
		global $db,$ecs;
		
		if(!empty($shop_id))
		{
			$sql = "SELECT shop_name, address, tel FROM " . $ecs->table('shop') . "WHERE shop_id = '$shop_id'";
			$res = $db->getROW($sql);
		}else
		{
			$res=array();
		}
		
		return $res;
	
	}
	/**
	 * 去除虚拟卡中重复数据
	 *
	 *
	 */
	private function _deleteRepeat($array){
		$_card_sn_record = array();
		foreach ($array as $_k => $_v){
			foreach ($_v['info'] as $__k => $__v){
				if (in_array($__v['card_sn'],$_card_sn_record)){
					unset($array[$_k]['info'][$__k]);
				} else {
					array_push($_card_sn_record,$__v['card_sn']);
				}
			}
		}
		return $array;
	}
	
	/**
	 *  返回虚拟卡信息
	 *
	 * @access  public
	 * @param
	 *
	 * @return void
	 */
	private function _virtual_card_result($order_sn, $goods)
	{
		global $db,$ecs;
		
		/* 获取已经发送的卡片数据 */
		$sql = "SELECT card_sn, card_password, end_date, crc32 FROM ".$ecs->table('virtual_card')." WHERE goods_id= '$goods[goods_id]' AND order_sn = '$order_sn' ";
		$res= $db->query($sql);
	
		$cards = array();
	
		while ($row = $db->FetchRow($res))
		{
			/* 卡号和密码解密 */
			if ($row['crc32'] == 0 || $row['crc32'] == crc32('this is a key'))
			{
				$row['card_sn'] = $this->_decrypt($row['card_sn']);
				$row['card_password'] = $this->_decrypt($row['card_password']);
			}
			elseif ($row['crc32'] == crc32(''))
			{
				$row['card_sn'] = $this->_decrypt($row['card_sn'], '');
				$row['card_password'] = $this->_decrypt($row['card_password'], '');
			}
			else
			{
				$row['card_sn'] = '***';
				$row['card_password'] = '***';
			}
	
			$cards[] = array('card_sn'=>$row['card_sn'], 'card_password'=>$row['card_password'], 'end_date'=>date('Y-m-d H:i:s', $row['end_date']));
		}
	
		return $cards;
	}
	
	/**
	 * 解密函数
	 * @param   string  $str    加密后的字符串
	 * @param   string  $key    密钥
	 * @return  string  加密前的字符串
	 */
	private function _decrypt($str, $key = AUTH_KEY)
	{
		$coded = '';
		$keylength = strlen($key);
		$str = base64_decode($str);
	
		for ($i = 0, $count = strlen($str); $i < $count; $i += $keylength)
		{
			$coded .= substr($str, $i, $keylength) ^ $key;
		}
	
		return $coded;
	}
	
	
	/**
	 * 返回订单中的虚拟商品
	 *
	 * @access  public
	 * @param   int   $order_id   订单id值
	 * @param   bool  $shipping   是否已经发货
	 *
	 * @return array()
	 */
	private function _get_virtual_goods($order_id, $shipping = false)
	{
		global $db,$ecs;
		if ($shipping)
		{
			$sql = 'SELECT goods_id, goods_name, send_number AS num, extension_code FROM '.
					$ecs->table('order_goods') .
					" WHERE order_id = '$order_id' AND extension_code > ''";
		}
		else
		{
			$sql = 'SELECT goods_id, goods_name, (goods_number - send_number) AS num, extension_code FROM '.
					$ecs->table('order_goods') .
					" WHERE order_id = '$order_id' AND is_real = 0 AND (goods_number - send_number) > 0 AND extension_code > '' ";
		}
		$res = $db->getAll($sql);
	
		$virtual_goods = array();
		foreach ($res AS $row)
		{
			$virtual_goods[$row['extension_code']][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
		}
	
		return $virtual_goods;
	}
	
	/**
	 * 取得上次未支付的pay_lig_id
	 *
	 * @access  public
	 * @param   array     $surplus_id  余额记录的ID
	 * @param   array     $pay_type    支付的类型：预付款/订单支付
	 *
	 * @return  int
	 */
	private function _get_paylog_id($surplus_id, $pay_type = PAY_SURPLUS)
	{
		global $db,$ecs;
		$sql = 'SELECT log_id FROM' .$ecs->table('pay_log').
		" WHERE order_id = '$surplus_id' AND order_type = '$pay_type' AND is_paid = 0";
	
		return $db->getOne($sql);
	}
	
	
	/**
	 * 处理序列化的支付、配送的配置参数
	 * 返回一个以name为索引的数组
	 *
	 * @access  public
	 * @param   string       $cfg
	 * @return  void
	 */
	private function _unserialize_config($cfg)
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
	/**
	 * 取得支付方式信息
	 * @param   int     $pay_id     支付方式id
	 * @return  array   支付方式信息
	 */
	public function _payment_info($pay_id)
	{
		global $db,$ecs;
		$sql = 'SELECT * FROM ' . $ecs->table('payment') .
		" WHERE pay_id = '$pay_id' AND enabled = 1";
	
		return $db->getRow($sql);
	}
	
	
	/**
	 * 查询购物车（订单id为0）或订单中是否有实体商品
	 * @param   int     $order_id   订单id
	 * @param   int     $flow_type  购物流程类型
	 * @return  bool
	 */
	public function _exist_real_goods($order_id = 0, $flow_type = CART_GENERAL_GOODS)
	{
		global $db,$ecs;
		if ($order_id <= 0)
		{
			$sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
			" WHERE session_id = '" . SESS_ID . "' AND is_real = 1 " .
			"AND rec_type = '$flow_type'";
		}
		else
		{
			$sql = "SELECT COUNT(*) FROM " . $ecs->table('order_goods') .
			" WHERE order_id = '$order_id' AND is_real = 1";
		}
	
		return $db->getOne($sql) > 0;
	}
	
	/**
	 * 取得订单信息
	 * @param   int     $order_id   订单id（如果order_id > 0 就按id查，否则按sn查）
	 * @param   string  $order_sn   订单号
	 * @return  array   订单信息（金额都有相应格式化的字段，前缀是formated_）
	 */
	public function _order_info($order_id, $order_sn = '')
	{
		global $db,$ecs;
		/* 计算订单各种费用之和的语句 */
		$total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_fee ";
		$order_id = intval($order_id);
		if ($order_id > 0)
		{
			$sql = "SELECT *, " . $total_fee . " FROM " . $ecs->table('order_info') .
			" WHERE order_id = '$order_id'";
		}
		else
		{
			$sql = "SELECT *, " . $total_fee . "  FROM " . $ecs->table('order_info') .
			" WHERE order_sn = '$order_sn'";
		}
		$order = $db->getRow($sql);
	
		/* 格式化金额字段 */
		if ($order)
		{
			$order['formated_goods_amount']   = $order['goods_amount'];
			$order['formated_discount']       = $order['discount'];
			$order['formated_tax']            = $order['tax'];
			$order['formated_shipping_fee']   = $order['shipping_fee'];
			$order['formated_insure_fee']     = $order['insure_fee'];
			$order['formated_pay_fee']        = $order['pay_fee'];
			$order['formated_pack_fee']       = $order['pack_fee'];
			$order['formated_card_fee']       = $order['card_fee'];
			$order['formated_total_fee']      = $order['total_fee'];
			$order['formated_money_paid']     = $order['money_paid'];
			$order['formated_bonus']          = $order['bonus'];
			$order['formated_integral_money'] = $order['integral_money'];
			$order['formated_surplus']        = $order['surplus'];
			$order['formated_order_amount']   = abs($order['order_amount']);
			$order['formated_add_time']       = $this->_local_date('Y-m-d H:i:s', $order['add_time']);
		}
	
		return $order;
	}
	
	//--------------------------------------------- 门店的收银与网站接口部分 ----------------------------------------------//
	/**
	 *  获取用户指定范围的订单列表
	 *
	 * @access  public
	 * @param   int         $mendian_id     门店ID号
	 * @param   int         $stime        	时间戳开始
	 * @param   int         $etime        	时间戳结束
	 * @param   int         $num            列表最大数量
	 * @param   int         $start          列表起始位置
	 * @return  array       $order_list     订单列表
	 */
	public function getOrdersList($mendian_id,$stime=0,$etime=0,$num = 10, $start = 0)
	{
		global $json,$db,$ecs;
	
		$where = " wort in (0,1)  and  `grab` = 0 ";
	
	
		if ($mendian_id) {
			$where .= " and mendian_id = '$mendian_id' ";
		}
		if ($stime){
			$where .= " and add_time>='".strtotime($stime)."'  ";
		}
	
		if ($etime){
			$where .= " and add_time<='".strtotime($etime)."' ";
		}
	
	
		/* 取得订单列表 */
		$arr    = array();
		$return = array();
		$info = array();
		$goods = array();
		$sql = "SELECT * " .
				" FROM " .$ecs->table('order_info') .
				" WHERE $where ORDER BY add_time ASC";
		$res = $db->SelectLimit($sql, $num, $start);
	
		while ($row = $db->fetchRow($res))
		{
	
			$info['order_id'] = $row['order_id'];
			$info['shop_id'] = $row['shop_id'];
			$info['order_sn']= $row['order_sn'];
			$info['user_id']= $row['user_id'];
			$info['order_status']= $row['order_status'];
			$info['shipping_status']= $row['shipping_status'];
			$info['pay_status']= $row['pay_status'];
			$info['consignee']= $row['consignee'];
			$info['country']= $row['country'];
			$info['province']= $row['province'];
			$info['city']= $row['city'];
			$info['district']= $row['district'];
			$info['address']= $row['address'];
			$info['zipcode']= $row['zipcode'];
			$info['tel']= $row['tel'];
			$info['mobile']= $row['mobile'];
			$info['email']= $row['email'];
			$info['postscript']= $row['postscript'];
			$info['goods_amount']= $row['goods_amount'];
			$info['order_amount']= $row['order_amount'];
			$info['wort']= $row['wort'];
			$info['agency_id']= $row['agency_id'];
			
			/* 订座信息 */
			$info['orderluck'] = array();
			if ($row['shop_id']){
				$sql5 = "SELECT sit_group_id1,sit_group_id6,sit_group_id2,sit_group_id3,sit_group_id4,sit_group_id5,sit_group_id7,sit_group_id8,daotian_time,time_2,telphone,mobile,sex,ren_num,lianxiren FROM " . $ecs->table('shop_sit_log').
				" WHERE log_id = '$row[shop_id]' LIMIT 1";
				$orderluck= $db->getAll($sql5);
			
				$sql3 = "SELECT * FROM " . $ecs->table('room_name').
				" WHERE order_id = '$row[order_id]' LIMIT 1";
				$orderlist= $db->getRow($sql3);
				if($orderlist['order_id']){
					$orderluck[0]['fenpei']="<font color='green'>已分配";
				}else{
					$orderluck[0]['fenpei']="<font color='red'>未分配";
				}
			
				foreach($orderluck as $key=>$value){
					$orderluck[$key]['daotian_time']=$this->_local_date('Y年m月d日 H:i',$value['daotian_time']);
				}
				$sonof='';
				$sonof2='';
				$zhuohao = '';
				if(!empty($orderluck[0]['sit_group_id1'])){
					$sonof.='4人桌('.$orderluck[0]['sit_group_id1'].')['.$orderlist['sit1'].']<br>';
					$zhuohao .= $orderlist['sit1'];
				}
				if(!empty($orderluck[0]['sit_group_id2'])){
					$sonof.='6人桌('.$orderluck[0]['sit_group_id2'].')['.$orderlist['sit2'].']<br>';
					$zhuohao .= $orderlist['sit2'];
				}
				if(!empty($orderluck[0]['sit_group_id3'])){
					$sonof.='8人桌('.$orderluck[0]['sit_group_id3'].')['.$orderlist['sit3'].']<br>';
					$zhuohao .= $orderlist['sit3'];
				}
				if(!empty($orderluck[0]['sit_group_id4'])){
					$sonof.='10人桌('.$orderluck[0]['sit_group_id4'].')['.$orderlist['sit4'].']<br>';
					$zhuohao .= $orderlist['sit4'];
				}
				if(!empty($orderluck[0]['sit_group_id5'])){
					$sonof2.='6人桌('.$orderluck[0]['sit_group_id5'].')['.$orderlist['sit5'].']<br>';
					$zhuohao .= $orderlist['sit5'];
				}
				if(!empty($orderluck[0]['sit_group_id6'])){
					$sonof2.='8人桌('.$orderluck[0]['sit_group_id6'].')['.$orderlist['sit6'].']<br>';
					$zhuohao .= $orderlist['sit6'];
				}
				if(!empty($orderluck[0]['sit_group_id7'])){
					$sonof2.='10人桌('.$orderluck[0]['sit_group_id7'].')['.$orderlist['sit7'].']<br>';
					$zhuohao .= $orderlist['sit7'];
				}
				if(!empty($orderluck[0]['sit_group_id8'])){
					$sonof2.='12人桌('.$orderluck[0]['sit_group_id8'].')['.$orderlist['sit8'].']';
					$zhuohao .= $orderlist['sit8'];
				}
				if(!empty($sonof)){
					$orderluck[0]['sonof']='大厅:'.($sonof);
				}
				if(!empty($sonof2)){
					$orderluck[0]['sonof2']='包间:'.($sonof2);
				}
				$orderluck[0]['zuohao']=$zhuohao;
				for ($i=1;$i<=8;$i++){
					unset($orderluck[0]['sit_group_id'.$i]);
				}
				
				/*订座信息*/
				
			}
			
			
			
			$sql = "SELECT * " .
					" FROM " .$ecs->table('order_goods') .
					" WHERE order_id = '{$row['order_id']}'";
			// 					" WHERE order_id = '6'";
			$requert = $db->getAll($sql);
				
			foreach ($requert as $k=>$v){
				$goods[$k]['rec_id']= $v['rec_id'];
				$goods[$k]['order_id']= $v['order_id'];
				$goods[$k]['goods_id']= $v['goods_id'];
				$goods[$k]['goods_name']= $v['goods_name'];
				$goods[$k]['goods_sn']= $v['goods_sn'];
				$goods[$k]['goods_number']= $v['goods_number'];
				$goods[$k]['market_price']= $v['market_price'];
				$goods[$k]['goods_price']= $v['goods_price'];
				$goods[$k]['goods_attr']= $v['goods_attr'];
				$goods[$k]['send_number']= $v['send_number'];
				
			}
				
				
			$arr[] = array('info'       =>$info,
					'goods'       => $goods);
		}
	
		if ($arr){
			$return['success'] = 'true';
			$return['data'] = $arr;
	
		}else{
			$return['success'] = 'true';
			$return['data'] = array();
		}
// 		return $return;
		return $json->encode($return);
	}
	/**
	 * 更新订单商（菜)品
	 * @param   int     $order_id        订单id
	 * @param   int   	$goods_id     	 菜品id
	 * @return  json
	 */
	public function getOrderStatus($order_id)
	{
		global $json,$db,$ecs;
	
		$return = array();
	
		if (!$order_id ){
			$return['success'] = 'false';
			$return['state'] = 2;
		}else{
			$sql = "SELECT *  FROM " .$ecs->table('order_info')." WHERE order_id='" . $order_id . "' AND grab=0";
			$oInfo = $db->getRow($sql);
			
			if ($oInfo['order_id']){
				$date['grad'] = 1;
				if ($db->query("UPDATE " .$ecs->table('order_info')." SET `grab` = '1'  WHERE order_id='" . $oInfo['order_id'] . "'") > 0){
					$return['success'] = 'true';
					$return['state'] = 1;
				}else{
					$return['success'] = 'false';
					$return['state'] = 3;
				}
			}else {
				$return['success'] = 'true';
				$return['state'] = 1;
			}
		}
			
		return $json->encode($return);
	}
	
	/**
	 * 更新订单商（菜)品
	 * @param   int     $order_id        订单id
	 * @param   int   	$goods_id     	 菜品id
	 * @return  json 
	 */
	public function updateOrderGoods($order_id, $goods_id)
	{
		global $json,$db,$ecs;
	
		$return = array();
		
		if (!$order_id || !$goods_id){
			$return['success'] = 'false';
			$return['state'] = 2;
		}else{
			$sql = "SELECT *  FROM " .$ecs->table('order_goods')." WHERE order_id='" . $order_id . "' AND goods_id='" . $goods_id . "'";
			$oInfo = $db->getRow($sql);
			if ($oInfo['rec_id']){
				if ($db->query("DELETE FROM " .$ecs->table('order_goods')." WHERE rec_id='" . $oInfo['rec_id'] . "'") > 0){
					$return['success'] = 'true';
					$return['state'] = 1;
				}else{
					$return['success'] = 'false';
					$return['state'] = 3;
				}
			}else {
			$return['success'] = 'true';
			$return['state'] = 1;
			}
		}
			
		return $json->encode($return);
	}
	
	/**
	 * 更新订单商（菜)品
	 * @param   int     $order_id        			订单id
	 * @param   int   	$mendian_id    	 			门店id
	 * @param   int   	$actual_consumption     	实际消费金额
	 * @param   int   	$pay_time     	 			消费时间
	 * @return  json 
	 */
	public function OrdersUpdateStatus($order_id, $mendian_id=null,$actual_consumption,$pay_time)
	{
		global $json,$db,$ecs;
	
		$return = array();
	
		if (!$order_id ){
			$return['success'] = 'false';
			$return['state'] = 2;
		}elseif (!$actual_consumption ){
			$return['success'] = 'false';
			$return['state'] = 3;
		}else{
			$sql = "SELECT *  FROM " .$ecs->table('order_info')." WHERE order_id='" . $order_id . "' ";
			if ($mendian_id != null){
				$sql .= " AND mendian_id =  '" . $mendian_id . "'";
			}
			$oInfo = $db->getRow($sql);
			if ($oInfo['order_id']){
				$date = array(
						'actual_consumption'       => $actual_consumption,
						'pay_time'    => strtotime($pay_time)
				);
				if ($db->autoExecute($ecs->table('order_info'), $date, 'UPDATE' , "order_id = '" . $order_id . "' ") > 0){
						$return['success'] = 'true';
						$return['state'] = 1;
				}else{
					$return['success'] = 'false';
					$return['state'] = 4;
				}
			}else{
				$return['success'] = 'false';
				$return['state'] = 5;
			}
		}
			
		return $json->encode($return);
	}
	
	//版本空置API
	public function getVersionInfo($appCode){
		global $json,$db,$ecs;
		$return = array();
		$sql = "select	appName,downloadUrl,appVersionCode,appVersionName,forcedUpdate,updateInfo,addrVersionCode from ecm_app_ersion where id = ".$appCode;
		$versionInfo = $db->getRow($sql);
		if($appCode == 13){
			$versionInfo['addrVersionCode']?$app['addrVersionCode']=$versionInfo['addrVersionCode']:$app['addrVersionCode']='';
		}else{
			$versionInfo['appName']?$app['appName']=$versionInfo['appName']:$app['appName']='';
			$versionInfo['downloadUrl']?$app['downloadUrl']=$versionInfo['downloadUrl']:$app['downloadUrl']='';
			$versionInfo['appVersionCode']?$app['appVersionCode']=$versionInfo['appVersionCode']:$app['appVersionCode']='';
			$versionInfo['appVersionName']?$app['appVersionName']=$versionInfo['appVersionName']:$app['appVersionName']='';
			$versionInfo['updateInfo']?$app['updateInfo']=$versionInfo['updateInfo']:$app['updateInfo']='';
			$versionInfo['addrVersionCode']?$app['addrVersionCode']=$versionInfo['addrVersionCode']:$app['addrVersionCode']='';
			if($versionInfo['forcedUpdate'] == 0){
				$app['forcedUpdate']='false';	
			}else if($versionInfo['forcedUpdate'] == 1){
				$app['forcedUpdate']='true';
			}
		}
		$return = $app;
		return $json->encode($return);
	}
	
	//地区更新
	public function getAddrAndPostalCode($appCode){
		global $json,$db,$ecs;
		$sql = "select region_id,parent_id,region_name,region_type,agency_id,shipping_offline_fee,py,pinyin from ecm_region";
		$rel = $db->getALL($sql);
		$regions = $rel;
		$return['regions'] = $regions;
		return $json->encode($return);
	}
	
	/**
	 * 将GMT时间戳格式化为用户自定义时区日期
	 *
	 * @param  string       $format
	 * @param  integer      $time       该参数必须是一个GMT的时间戳
	 *
	 * @return  string
	 */
	
	private function _local_date($format, $time = NULL)
	{
		$timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
	
		if ($time === NULL)
		{
			$time = $this->_gmtime();
		}
		elseif ($time <= 0)
		{
			return '';
		}
	
		$time += ($timezone * 3600);
	
		return date($format, $time);
	}
	
	/**
	 * 获得当前格林威治时间的时间戳
	 *
	 * @return  integer
	 */
	private function _gmtime()
	{
		return (time() - date('Z'));
	}
	
	/**
	 * 截取UTF-8编码下字符串的函数
	 *
	 * @param   string      $str        被截取的字符串
	 * @param   int         $length     截取的长度
	 * @param   bool        $append     是否附加省略号
	 *
	 * @return  string
	 */
	private function _sub_str($str, $length = 0, $append = true)
	{
		$str = trim($str);
		$strlength = strlen($str);
	
		if ($length == 0 || $length >= $strlength)
		{
			return $str;
		}
		elseif ($length < 0)
		{
			$length = $strlength + $length;
			if ($length < 0)
			{
				$length = $strlength;
			}
		}
	
		if (function_exists('mb_substr'))
		{
			$newstr = mb_substr($str, 0, $length, EC_CHARSET);
		}
		elseif (function_exists('iconv_substr'))
		{
			$newstr = iconv_substr($str, 0, $length, EC_CHARSET);
		}
		else
		{
			//$newstr = trim_right(substr($str, 0, $length));
			$newstr = substr($str, 0, $length);
		}
	
		if ($append && $str != $newstr)
		{
			$newstr .= '...';
		}
	
		return $newstr;
	}
	
	

}

