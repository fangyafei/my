<?php
class Base{
	var $wdwl_url = '';
	var $error = '';
	var $token = '';

  function __construct() {

  }

  /**
   * 设置参数
   */
  public function set($key, $value) 
  {
    $this->$key = $value;
  }
  
  /**
   * 获取参数
   */
  public function get($key) 
  {
    return isset($this->$key) ? $this->$key : NULL;
  }
 
 /**
  * 获取用户的各项统计数据
  */
  public function getCount($token)
  {
  	global $json,$db,$ecs;
  	$user_info = getUserInfo($token);
  	if (!$user_info)
  	{
  		$arr = array( 'statusCode'=>0,'msg'=>'找不到该用户');
  		return $json->encode($arr);
  	}
  	$uid = $user_info['user_id'];
  	$data = array();
  	/*街拍*/
  	$sql = "SELECT COUNT(*) FROM ".$ecs->table('album')." WHERE uid=$uid AND cate=2";
  	$jiepaixiangce  = $db->getOne($sql);
  	$data['jiepaixiangce'] = $jiepaixiangce;
  	
  	/*如果是设计师  显示我的设计图片个数*/
  	if ($user_info['serve_type'] == 4)
  	{
  		/*街拍*/
  		$sql = "SELECT COUNT(*) FROM ".$ecs->table('userphoto')." WHERE uid=$uid AND cate=1";
  		$sheji  = $db->getOne($sql);
  		$data['sheji'] = $sheji;
  	}
  	
  	/*积分*/
  	$sql = "SELECT point FROM ".$ecs->table('member')." WHERE user_id=$uid";
  	$jifen  = $db->getOne($sql);
  	$data['jifen'] = $jifen;
  	
  	/*库特币*/
  	$sql = "SELECT coin FROM ".$ecs->table('member')." WHERE user_id=$uid";
  	$kutebi  = $db->getOne($sql);
  	$data['kutebi'] = $kutebi;
  	
  	/*收藏*/
  	$sql = "SELECT count(*) FROM ".$ecs->table('collect')." WHERE user_id=$uid AND type='customs'";
  	$shoucang  = $db->getOne($sql);
  	$data['shoucang'] = $shoucang;
  	
  	/*订单*/
  	$sql = "SELECT count(*) FROM ".$ecs->table('order')." WHERE buyer_id=$uid";
  	$dingdan  = $db->getOne($sql);
  	$data['dingdan'] = $dingdan;
  	
  	/*优惠劵*/
  	$sql = "SELECT count(*) FROM ".$ecs->table('coupon_sn')." WHERE uid=$uid AND status=0 AND claim=1";
  	$youhuiquan  = $db->getOne($sql);
  	$data['youhuiquan'] = $youhuiquan;
  	
  	/*购物车*/
  	$sql = "SELECT count(*) FROM ".$ecs->table('mob_cart')." WHERE user_id=$uid";
  	$gouwuche  = $db->getOne($sql);
  	$data['gouwuche'] = $gouwuche;
  	
  	/*我的量体个数*/
  	$sql = "SELECT count(*) FROM ".$ecs->table('figure')." WHERE userid=$uid";
  	$liangti  = $db->getOne($sql);
  	$data['liangti'] = $liangti;
  	
  	return $json->encode($data);
  	
  }
  
  
  
  
  
  
  
  
  
  
  

}

?>