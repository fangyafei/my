<?php 
ini_set("soap.wsdl_cache_enabled", "0");
class Card
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
	/**
	 * 绑定精彩卡
	 * @param unknown_type $card_no
	 * @param unknown_type $pwd
	 * @param unknown_type $user_id
	 * @return unknown
	 */
	public function bindJinCaiCard($card_no, $pwd, $user_id)
	{
		global $json;
		$arr = array(
			'card_no' => $card_no,
			'user_id' => $user_id,
			'status'  => 0,
			'effectivedate'=> time()+24*3600*10,
			'overage'  => 100,
			'bindtime' => time()
		);
		
		$sql = "SELECT COUNT(1) FROM " . $ecs->table("cards") . " WHERE user_id='{$user_id}'";
		if(!$db->getOne($sql)){
			return $json->encode(array("statusCode" => 1));
		}
		
		$res = $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table("cards"), $arr, "INSERT");
	  	
		$val = 0;
  		if(!$res){
  			$val = 1;
  		}
  		return $json->encode(array("statusCode" => $val));
	}
	
	/**
	 * 获取精彩卡
	 * @param unknown_type $pageSize
	 * @param unknown_type $pageIndex
	 * @param unknown_type $userId
	 * @return string
	 */
	public function getJinCaiCard($userId)
	{
		global $json;
// 		if($pageIndex < 1) $pageIndex = 1;
		
// 		$sql = "SELECT COUNT(1) FROM " . $GLOBALS['ecs']->table("cards") . " WHERE user_id = '{$userId}'";
// 		$count = $GLOBALS['db']->getOne($sql);
		
		
// 		$maxPage = ceil($count / $pageSize);
		
// 		$hasNext = 'true';
// 		// 超出范围
// 		if ($pageIndex > $maxPage)
// 		{
// 			return $json->encode( array ('hasNext' => 'false','cardList' => array() ));
// 		}
		
// 		if($maxPage <= $pageIndex+1){
// 			$hasNext = 'false';
// 		}
		
		
		$sql = "SELECT * FROM " . $GLOBALS['ecs']->table("cards") . " WHERE user_id = '{$userId}' ";


		$row = $GLOBALS['db']->getRow($sql);
	
			$card = array();
			$card['cardCode'] = $row ['card_no'];
			$card['status'] = $row["status"];
			$card['effectiveDate'] = $row['effectivedate'];
			$card['overage'] = $row["overage"];
			$card['dealRecordList'] = array();
		
		return $json->encode($card);
		
	}
	/**
	 * 解绑精彩卡
	 * 
	 */
	public function unbindJinCaiCard($cardNo, $userId)
	{
		global $db, $ecs, $json;
		$arr = array(
			'status' => 1		
		);
		
		$sql = "SELECT COUNT(1) FROM " . $ecs->table("cards") . " WHERE user_id='{$userId}' && card_no = '{$cardNo}'";
		if(!$db->getOne($sql)){
			return $json->encode(array("statusCode" => 1));
		}
		
		$res = $db->autoExecute($ecs->table("cards"), $arr, "UPDATE", "user_id='{$userId}' && card_no = '{$cardNo}'");
	  	
		$val = 0;
  		if(!$res){
  			$val = 1;
  		}
  		return $json->encode(array("statusCode" => $val));
	}
	
	/**
	 * 
	 */
	public function payByJinCaiCard($card_no, $user_id, $order_id){
		global $db, $ecs, $json;
		$sql = "SELECT * FROM " . $ecs->table("cards") . " WHERE card_no = '{$card_no}' && user_id = '{$user_id}'";
		$card = $db->getRow($sql);
		$time = time();
		if(empty($card) || $card["status"] != 0 || $card["effectivedate"] < $time){
			return $json->encode(array("statusCode" => 2));
		}
		
		$sql = "SELECT order_amount FROM " . $ecs->table("order") . " WHERE order_id = '{$order_id}' && buyer_id = '{$user_id}'";
		$order_amount = $db->getOne($sql);
		if($order_amount > $card["overage"]){
			return $json->encode(array("statusCode" => 1));
		}
		
		$db->autoExecute($ecs->table("order"), array("status" => 20), "UPDATE" , "order_id='{$order_id}' && buyer_id='{$user_id}'");
		
		$db->autoExecute($ecs->table("cards"), array("overage" => $card["overage"] - $order_amount), "UPDATE" , "card_no='{$card_no}' && user_id='{$user_id}'");
		
		return $json->encode(array("statusCode" => 0));
	}
}
?>