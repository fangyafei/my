<?php 
ini_set("soap.wsdl_cache_enabled", "0");
class Point
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
	 * 获取积分
	 * @param unknown_type $pageSize
	 * @param unknown_type $pageIndex
	 * @param unknown_type $userId
	 * @return string
	 */
	public function getMyScores($userId)
	{
		global $json;
// 		if($pageIndex < 1) $pageIndex = 1;
		
// 		$sql = "SELECT COUNT(1) FROM " . $GLOBALS['ecs']->table("point") . " WHERE user_id = '{$userId}'";
// 		$count = $GLOBALS['db']->getOne($sql);
		
		
// 		$maxPage = ceil($count / $pageSize);
		
// 		$hasNext = 'true';
// 		// 超出范围
// 		if ($pageIndex > $maxPage)
// 		{
// 			return $json->encode( array ('hasNext' => 'false','pointList' => array() ));
// 		}
		
// 		if($maxPage <= $pageIndex+1){
// 			$hasNext = 'false';
// 		}
		
		$pointList = array();
		$point = array();
		$sql = "SELECT * FROM " . $GLOBALS['ecs']->table("point") . " WHERE user_id = '{$userId}' ";
		$points = $GLOBALS['db']->getAll($sql);
		
		$total = $GLOBALS['db']->getOne("SELECT score FROM " . $GLOBALS['ecs']->table("member") . " WHERE user_id='{$userId}'");
		//$res = $GLOBALS['db']->SelectLimit ( $sql, $pageSize, ($pageIndex-1)*$pageSize);
		$pointList['scoreItemList'] = array();
		foreach($points as $key => $row)
		{
			
			$point["quantity"] = 0;
			$point["operaton"] = $row['point_type'];
			$point["score"] = $row['point'];
			$point["dealTime"] = $row["create_time"];
			$point["explain"] = $row["remark"];
			$point["orderId"] = $row["order_id"];
 			$pointList['scoreItemList'][] = $point;
		}
		//$pointList['totalScores'] = $total;
		$pointList['totalScores'] = $total;
		return $json->encode($pointList);
		
	}
}
?>