<?php
function curl_http_header($url,$post_data){

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_URL,$url);

	curl_setopt($ch, CURLOPT_HEADER, 1);
	// 	curl_setopt($ch, CURLOPT_USERAGENT, "local.test.com");
	// 	curl_setopt($ch, CURLOPT_COOKIEJAR, './cookie.txt'); //保存
	// 	curl_setopt($ch, CURLOPT_COOKIEFILE, './cookie.txt'); //读取

	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	ob_start();
	try {
		curl_exec($ch);
	}catch (Exception $e){
		var_dump($e);
	}
	$result = ob_get_contents() ;
	ob_end_clean();

	return  $result;
}

function DZLogin($uname,$ps){
	if(!$uname || !$ps)return 0;
	$ul =
	'http://bbs.0708.com/member.php'.
	'?mod=logging&action=login&loginsubmit=yes&infloat=yes&lssubmit=yes&inajax=1';

	$arr = array(
			'fastloginfield' =>	'13522536459',
			'handlekey' =>	'ls',
			'password'=>	$ps,
			'quickforward'=>	'yes',
			'username'=>	$uname,
	);

	$rs = curl_http_header($ul,$arr);
// 	var_dump($rs);echo "<Br/>";exit;
	// list($header, $body) = explode("\r\n\r\n", $rs);
	// var_dump($header);exit;
	$matches = array();
	preg_match_all("/set\-cookie:([^\r\n]*)/i", $rs, $matches);
	foreach ($matches[0] as $k=>$v){
		$v = str_replace(' ', '', $v);
		$v = str_replace('Set-Cookie:', '', $v);

		$arr = explode(';', $v);
		// 	foreach ($arr as $k2=>$v2){
		$tmp = explode('=', $arr[0]);
		if($tmp[0] && $tmp[1]){
			$tmp[1] = urldecode($tmp[1]);
// 			echo $tmp[0] . " " . $tmp[1] ."<br/>";
			setcookie( $tmp[0], $tmp[1] , time() + 3600 , '/', '.0708.com');
		}
		// 	}
	}
}

function curl_http($url,$post_data){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_URL,$url);
	
	//传递一个作为HTTP “POST”操作的所有数据的字符串。
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	ob_start();
	try {
		curl_exec($ch);
	}catch (Exception $e){
		var_dump($e);
	}
	$result = ob_get_contents() ;
	ob_end_clean();
	
	return  $result;
}

function getSoapClient($class,$method,$para){
	$AppID = 101;
	$key = 'gamebean';
	$r = rand(1, 10000);
	try{
		$wsdlUrl = "http://dev.ecm.com/soaapi/soap/WSDL/SoapService.wsdl";
		$SoapUrl = "http://dev.ecm.com/soaapi/soap/ServiceSoap.php";
	
		$AppTime = date("U");
		$AppCtid = md5($key.$AppID.$AppTime);
		$client = new SoapClient( $wsdlUrl ,array( 'trace' => 1 ) );
		//头验证信息
		$headInfo= array(
				'AppID'=>$AppID,
				'AppCtid'=>$AppCtid,
				'AppTime'=>$AppTime,
		);
		$headers = new SoapHeader($SoapUrl,"Authorized",array($headInfo),false, SOAP_ACTOR_NEXT);
		// 	echo "Request :<br/>".htmlspecialchars($client->__getLastRequest())."<br/>";
		//  echo "Response :<br/>".htmlspecialchars($client->__getLastResponse())."<br/>";
		$client->__setSoapHeaders(array($headers));
		$result = $client->Router($class,$method,$para);
		return $result;
	}catch (Exception $e){
		var_dump($e->getMessage());
	}
}

function __autoload($class){
	include_once $class .".class.php";
}
function key_turn_arr($arr){
	$rs = array();
	foreach($arr as $k=>$v){
		$rs[] = $k;
	}
	return $rs;
}

// 取得对象实例 支持调用类的静态方法
function get_instance_of($name, $method='', $args=array()) {
	static $_instance = array();
	$identify = empty($args) ? $name . $method : $name . $method . to_guid_string($args);
	if (!isset($_instance[$identify])) {
		if (class_exists($name)) {
			$o = new $name();
			if (method_exists($o, $method)) {
				if (!empty($args)) {
					$_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
				} else {
					$_instance[$identify] = $o->$method();
				}
			}
			else
				$_instance[$identify] = $o;
		}
		else
			ExceptionFrame::halt("new class:". $name);
// 			halt(L('_CLASS_NOT_EXIST_') . ':' . $name);
	}
	return $_instance[$identify];
}
//切分
function split_arr($arr,$keyName = ''){
	$str = "";
	if(!$keyName){
	foreach($arr as $k=>$v){
			$str .= $v . ","; 
		}
	}else{
		foreach($arr as $k=>$v){
			$str .= $v[$keyName] . ","; 
		}
	}
	return substr($str, 0 , strlen($str) - 1);
}
function split_arr_sql($arr,$keyName = ''){
	$str = "";
	if(!$keyName){
		foreach($arr as $k=>$v){
			$str .= "'" . $v . "',";
		}
	}else{
		foreach($arr as $k=>$v){
			$str .= "'" .$v[$keyName] . "',";
		}
	}
	return substr($str, 0 , strlen($str) - 1);
}
//
function arr_in_arr($arr,$key,$node){
	$f = 0;
	foreach($arr as $k=>$v){
		if($node  == $v[$key]){
			$f = 1;
			break;
		}
	}

	return $f;
}
// 获取和设置语言定义(不区分大小写)
function L($name=null, $value=null) {
	static $_lang = array();
	// 空参数返回所有定义
	if (empty($name))
		return $_lang;
	// 判断语言获取(或设置)
	// 若不存在,直接返回全大写$name
	if (is_string($name)) {
		$name = strtoupper($name);
		if (is_null($value))
			return isset($_lang[$name]) ? $_lang[$name] : $name;
		$_lang[$name] = $value; // 语言定义
		return;
	}
	// 批量定义
	if (is_array($name))
		$_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
}

// 获取配置值
function C($name=null, $value=null) {
	static $_config = array();
	// 无参数时获取所有
	if (empty($name))   return $_config;
	// 优先执行设置获取或赋值
	if (is_string($name)) {
		if (!strpos($name, '.')) {
			$name = strtolower($name);
			if (is_null($value))
				return isset($_config[$name]) ? $_config[$name] : null;
			$_config[$name] = $value;
			return;
		}
		// 二维数组设置和获取支持
		$name = explode('.', $name);
		$name[0]   =  strtolower($name[0]);
		if (is_null($value))
			return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
		$_config[$name[0]][$name[1]] = $value;
		return;
	}
	// 批量设置
	if (is_array($name)){
		return $_config = array_merge($_config, array_change_key_case($name));
	}
	return null; // 避免非法参数
}
// 获取客户端IP地址
function get_client_ip() {
	static $ip = NULL;
	if ($ip !== NULL) return $ip;
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$pos =  array_search('unknown',$arr);
		if(false !== $pos) unset($arr[$pos]);
		$ip   =  trim($arr[0]);
	}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
	return $ip;
}
//判断长度
function slen($value,$max,$min = 1,$unicode = 'utf8'){
	$str = mb_strlen($value , $unicode);
	if( $str > $max || $str < $min){
		return 0;
	}
	
	return 1;
}
//GET POST REQUEST
//function _g()
function _g($name='', $type = '')
{
	 /**$contents = '{"passWord":"123456","phoneNum":"13146294015"}';
	// 	$contents = '{"pageIndex":1,"keyWord":"因为从有到无","pageSize":20}';
	//$contents = '{"consigneeId":"2","delivery":0,"payWay":0,"remark":"18610352845","goodsCountList":[{"quantity":1,"specId":"19","goodsId":"18"},{"quantity":1,"specId":"20","goodsId":"18"},{"quantity":2,"specId":"21","goodsId":"19"}],"token":"ec046e44f1cb60518050cf601a89f101"}';
	//$contents = '{"token":"ec046e44f1cb60518050cf601a89f101","orderId":5,"cardCode":222}';
	//$contents = @file_get_contents("php://input");
		if (empty($contents)) return false;
	$ret = json_decode($contents);
	**/
	
	/* 数据过滤 */
        if (!get_magic_quotes_gpc())
        {
        	/****$_GET、$_POST、$_COOKIE、$_REQUEST****/
        	$_GET  		= addslashes_deep($_GET,true);
        	$_POST 		= addslashes_deep($_POST,true);
        	$_COOKIE   	= addslashes_deep($_COOKIE,true);
        	$_REQUEST  	= addslashes_deep($_REQUEST,true);
        	
        }
	
	
	global $json;
	if(!$name){
		if($_POST)	return json_decode($json->encode($_POST));
		else if($_GET) return json_decode($json->encode($_GET));
		else if($_REQUEST) return json_decode($json->encode($_REQUEST));
	}
	
	if (isset($_POST[$name]))      $ret = $_POST[$name];
	elseif (isset($_GET[$name]))   $ret = $_GET[$name];
	elseif (isset($_REQUEST[$name]))   $ret = $_REQUEST[$name];
	else $ret = false;
	if ($ret !== false && $type != '') {
		if ($type == 'int') $ret = intval($ret);
		elseif ($type == 'str') $ret = strval($ret);
		else settype($ret, $type);
	}

	return $ret;
}


//发送HTTP状态
function send_http_status($code) {
	static $_status = array(
	// Success 2xx
			200 => 'OK',
			// Redirection 3xx
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily ',  // 1.1
			// Client Error 4xx
			400 => 'Bad Request',
			403 => 'Forbidden',
			404 => 'Not Found',
			// Server Error 5xx
			500 => 'Internal Server Error',
			503 => 'Service Unavailable',
	);
	if(isset($_status[$code])) {
		header('HTTP/1.1 '.$code.' '.$_status[$code]);
		// 确保FastCGI模式下正常
		header('Status:'.$code.' '.$_status[$code]);
	}
}
//取得SMARTY实例
function getSmartyObject($path = ''){
	include_once SMART_URL . "Smarty.class.php";
	$SmartyClass = Singleton('Smarty');
	
	
	if(!$path){
		$SmartyClass->setTemplateDir("./view");
		$SmartyClass->setCompileDir("./view_c");
	}else{
		$SmartyClass->setTemplateDir($path);
	}
	
	$SmartyClass->debugging = false;
	$SmartyClass->caching = false;
	
	return $SmartyClass;
	
}
//判断是否有权限
function file_mode_info($file_path) {
	/* 如果不存在，则不可读、不可写、不可改 */
	if (!file_exists($file_path)) return false;
	$mark = 0;
	if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'){
		/* 测试文件 */
		$test_file = $file_path . '/cf_test.txt';
		/* 如果是目录 */
		if (is_dir($file_path)){
			/* 检查目录是否可读 */
			$dir = @opendir($file_path);
			if ($dir === false){
				return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
			}
			if (@readdir($dir) !== false){
				$mark ^= 1; //目录可读 001，目录不可读 000
			}
			@closedir($dir);
			/* 检查目录是否可写 */
			$fp = @fopen($test_file, 'wb');
			if ($fp === false){
				return $mark; //如果目录中的文件创建失败，返回不可写。
			}
			if (@fwrite($fp, 'directory access testing.') !== false){
				$mark ^= 2; //目录可写可读011，目录可写不可读 010
			}
			@fclose($fp);
			@unlink($test_file);
			/* 检查目录是否可修改 */
			$fp = @fopen($test_file, 'ab+');
			if ($fp === false){
				return $mark;
			}
			if (@fwrite($fp, "modify test.\r\n") !== false){
				$mark ^= 4;
			}
			var_dump($mark);
// 			@fclose($fp);
// 			/* 检查目录下是否有执行rename()函数的权限 */
// 			if (@rename($test_file, $test_file) !== false){
// 				$mark ^= 8;
// 			}
			@unlink($test_file);
		}elseif (is_file($file_path)){/* 如果是文件 */
			/* 以读方式打开 */
			$fp = @fopen($file_path, 'rb');
			if ($fp)
			{
				$mark ^= 1; //可读 001
			}
			@fclose($fp);
			/* 试着修改文件 */
			$fp = @fopen($file_path, 'ab+');
			if ($fp && @fwrite($fp, '') !== false)
			{
				$mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
			}
			@fclose($fp);
			/* 检查目录下是否有执行rename()函数的权限 */
			if (@rename($test_file, $test_file) !== false){
				$mark ^= 8;
			}
		}
	}else{
		clearstatcache();
		if (@is_readable($file_path)){
			$mark ^= 1;
		}
		if (@is_writable($file_path)){
			$mark ^= 14;
		}
	}

	return $mark;
}
//取得数据库实例
function getDb($dbName){
	static $dbLink = array();
	if(!$dbLink[$dbName]){
		foreach($GLOBALS['db_config'] as $k=>$v){
			if($k == $dbName){
				$f = 1;
				$config = $v;
			}
		}
		if(!$f){
			ExceptionFrame::throwException('DB_config error','no');
		}
// 		include 'Model.class.php';
		$db = new Model($config);
		$dbLink[$dbName] =  $db;
	}
	return $dbLink[$dbName];
}
//取得数据库实例
function getDb2($dbName){
	static $dbLink = array();
	if(!$dbLink[$dbName]){
		foreach($GLOBALS['db_config'] as $k=>$v){
			if($k == $dbName){
				$f = 1;
				$config = $v;
			}
		}
		if(!$f){
			ExceptionFrame::throwException('DB_config error','no');
		}
		// 		include 'Model.class.php';
		$db = new DbTest($config);
		$dbLink[$dbName] =  $db;
	}
	return $dbLink[$dbName];
}
//单实例模式
function Singleton ($className){
	static $_instens = array();
	if(!isset($_instens[$className])){
		$_instens[$className] = new $className;
	}
	return $_instens[$className];
}
// 根据PHP各种类型变量生成唯一标识号
function to_guid_string($mix) {
	if (is_object($mix) && function_exists('spl_object_hash')) {
		return spl_object_hash($mix);
	} elseif (is_resource($mix)) {
		$mix = get_resource_type($mix) . strval($mix);
	} else {
		$mix = serialize($mix);
	}
	return md5($mix);
}
/**
 * 工厂模式实例化模块
 * @param id: 模块ID
 * @param instanceID: 对象ID,如uid-用户ID， tid-球队ID等
 * return class
 */
function getElementObject($id, $instanceID = NULL) {
	if (array_key_exists($id, self::$modules)) {
		$module = self::$modules[$id];
		if (class_exists($module[2])) {
			return new $module[2]($id, $instanceID, $module[0], $module[1], $module[4]);
		} else {
			error_log("Ftonline Element Class for ID $id does not exist");
		}
	} else {
		error_log("Ftonline Module ID $id does not exist!");
	}
	return false;
}

// 设置和获取统计数据
function N($key, $step=0) {
	static $_num = array();
	if (!isset($_num[$key])) {
		$_num[$key] = 0;
	}
	if (empty($step))
		return $_num[$key];
	else
		$_num[$key] = $_num[$key] + (int) $step;
}

// 记录和统计时间（微秒）
function G($start,$end='',$dec=4) {
	static $_info = array();
	if(is_float($end)) { // 记录时间
		$_info[$start]  =  $end;
	}elseif(!empty($end)){ // 统计时间
		if(!isset($_info[$end])) $_info[$end]   =  microtime(TRUE);
		return number_format(($_info[$end]-$_info[$start]),$dec);
	}else{ // 记录时间
		$_info[$start]  =  microtime(TRUE);
	}
}
// 显示运行时间
function showTime() {
	G('beginTime',$GLOBALS['beginTime']);
	$showTime   =   'Process: '.G('beginTime','viewEndTime').'s ';
	// 显示详细运行时间
// 	$showTime .= '( Load:'.G('beginTime','loadTime').'s Init:'.G('loadTime','initTime').'s Exec:'.G('initTime','viewStartTime').'s Template:'.G('viewStartTime','viewEndTime').'s )';
	// 显示数据库操作次数
	$showTime .= ' | DB :'.N('db_query').' queries '.N('db_write').' writes ';
	// 显示内存开销
	$showTime .= ' | UseMem:'. number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024).' kb';
	$showTime .= ' | LoadFile:'.count(get_included_files());
	$fun  =  get_defined_functions();
	$showTime .= ' | CallFun:'.count($fun['user']).','.count($fun['internal']);
	return $showTime;
}
/**
 * 验证输入的邮件地址是否合法
 *
 * @access  public
 * @param   string      $email      需要验证的邮件地址
 *
 * @return bool
 */
function is_email($user_email)
{
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
	if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false)
	{
		if (preg_match($chars, $user_email))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}
/**
 * 对象转数组
 * @param unknown_type $o
 * @return void|array
 */
function objToArr($o){
    $o=(array)$o;
    foreach($o as $k=>$v){
        if( gettype($v)=='resource' ) return;
        if( gettype($v)=='object' || gettype($v)=='array' )
            $o[$k]=(array)objToArr($v);
    }
    return $o;
}

function getUinfoByUid($uid){
    if(!$uid){
        $avatar = getAvatarByFile("");
        return array('like_num'=>0,'fans'=>0,'avatar'=>$avatar);
    }
    $db = &db();

    $sql = "select * from rc_member where user_id = ".$uid;
    $user = $db->getRow($sql);
    if(!$user)
        return array('like_num'=>0,'fans'=>0,'avatar'=>getAvatarByFile(""),'uid'=>0);

    if($user['province']){
        $province = getAreaByRid($user['province']);
        $user['province_name'] = $province['region_name'];
    }
    if($user['city']){
        $city = getAreaByRid($user['city']);
        $user['city_name'] = $city['region_name'];
    }
    $level = getLevel($user['member_lv_id']);
    if($level){
        $user['level'] = $level['name'];
        if($level['lv_logo']){
            $user['level_logo'] = "/".$level['lv_logo'];
        }else{
            $user['level_logo'] =  '/data/files/mall/lv_logo/1/1.png';
        }

    }else{
        $user['level_logo'] = '/data/files/mall/lv_logo/1/1.png';
    }
    $user['avatar'] = getAvatarByFile($user['avatar']);
    $user['link'] = "/index.php/club-album-{$uid}.html";
    $user['uid'] = $user['user_id'];
    return $user;
}


function getLevel($lid){
    $db = &db();

    $sql = "select * from rc_member_lv where member_lv_id = ".$lid;
    return $db->getRow($sql);
}

function getAvatarByFile($name,$size = ""){
    if(!$name)
        return AVATAR_DEF;

    if($size == '162'){
        $path = "162";
    }elseif($size == '20'){
        $path = "20";
    }elseif($size == '48'){
        $path = "48";
    }else{
        $path = "162";
    }

    $src= get_domain() . "/upload_user_photo/avatar/".$path."/".$name;
    return $src;
}

function getAreaByRid($rid){
    $db = &db();

    $sql = "select * from rc_region where region_id = ".$rid;
    return $db->getRow($sql);
}

function getPhotoDetailLink($id,$cate){
    if(!$id)
        return "";

    if($cate == 1)
        $url = "/index.php/club-personaldesign-{$id}.html";
    else
        $url = "/index.php/club-streetinfo-{$id}.html";

    return $url;
}
function getDesignUrl($file_name,$size = '500',$type = 'big'){
    if(!$file_name)
        return getDefAlbumPic($type);

    if($size == '500'){
        $path = "520x685";
    }elseif($size ==  '200'){
        $path = "235x315";
    }else{
        $path = "original";
    }


    $src= get_domain(). "/upload_user_photo/sheji/$path/".$file_name;

    return $src;
}

function getComment($uid,$comment_id,$cate,$limit = '' ,$desc = 'add_time'){
    $arr = array('personaldesign'=>'个人设计详情页','streetinfo'=>'街拍详情页','order_goods'=>'基本款订单评论', 'dis' => "主题系列",'serve'=>'服务点');

    $db = &db();

    if(!$uid || !$comment_id || !$cate)
        exit(" func <getComment>: uid or comment_id or cate  is null");

    $where = " uid = $uid and comment_id = $comment_id and cate = '$cate' ";
    
    if($limit)
        $limit = " limit ".$limit;

    $sql = "select * from rc_comments where $where  order by $desc desc $limit ";
    
    $rs = $db->getAll($sql);
    return $rs;
}
function getCommentByGid($gid,$comment_id = 0 ,$cate,$limit = ''){
    $db = &db();

    $where = " goods_id = $gid and cate = '$cate' ";
    if($comment_id)
        $where .=  "  and comment_id = $comment_id ";
    if($limit)
        $limit = " limit ".$limit;

    $sql = "select * from rc_comments where $where $limit ";
    $rs = $db->getAll($sql);
    return $rs;
}
function getCommentPage($comment_id,$cate,$page = 1,$desc = 'add_time'){
    $arr = array('personaldesign'=>'个人设计详情页','streetinfo'=>'街拍详情页');

    $db = &db();
    $where = " comment_id = $comment_id and cate = '$cate' ";

    $sql = "select count(*) as total from rc_comments where $where";
    $total = $db->getRow($sql);
    if($total['total']){
        include 'includes/libraries/pageSimple.lib.php';
        $page = new PageSimple($total['total'] , $page,5);
        $page->execPage();

        $sql = "select * from rc_comments where $where  order by $desc desc limit ".$page->mLimit[0]." , " .$page->mLimit[1];
        $rs = $db->getAll($sql);
        $rs = array('data'=>$rs,'page'=>$page);
    }

    return $rs;

}

function getCommentwidthoutuid($comment_id,$cate,$limit = '' ,$desc = 'add_time'){
    $arr = array('personaldesign'=>'个人设计详情页','streetinfo'=>'街拍详情页','order_goods'=>'基本款订单评论', 'dis' => "主题系列",'serve'=>'服务点');

    $db = &db();

    if( !$comment_id || !$cate)
        exit(" func <getComment>: uid or comment_id or cate  is null");

    $where = "  comment_id = $comment_id and cate = '$cate' ";
    
    if($limit)
        $limit = " limit ".$limit;

    $sql = "select * from rc_comments where $where  order by $desc desc $limit ";
    
    $rs = $db->getAll($sql);
    return $rs;
}
function getCameraUrl($file_name,$size = '500',$type = 'big'){
    if(!$file_name)
        return getDefAlbumPic($type);

    if($size == '500'){
        $path = "520x685";
    }elseif($size ==  '200'){
        $path = "235x315";
    }else{
        $path = "original";
    }
    $src= get_domain() . "/upload_user_photo/jiepai/$path/".$file_name;
    return $src;
}

function setPoint($uid,$num,$opt,$cate,$author = 'system',$msg = '',$way = 'pc',$auto_id = 0){
	$cate_arr = checkCate($cate);
	
	if(!$uid)
		return 0;
	
	$m = m('point_log');
	$data = array(
			'num'=>$num,
			'uid'=>$uid,
			'cate'=>$cate,
			'add_time'=>time(),
			'msg'=>$msg,
			'author'=>$author,
			'opt'=>$opt,
			'type'=>$cate_arr['type'],
	);
	
	if($auto_id)
		$data['auto_id'] = $auto_id;
	
	$m->add($data);
	$db = &db();
	
	if($opt != 'add'){
		$num = - $num;
	}
	
	$sql = "update rc_member set point = point + ".$num." where user_id = ".$uid;
	$_SESSION['user_info']['point'] = $_SESSION['user_info']['point'] + $num;
	
	$db->query($sql);
	
	return outinfo('ok',0 , $way);
}


function setCoin($uid,$num,$opt,$cate,$author = 'system',$msg,$way = 'pc'){
	$cate_arr = checkCate($cate);
	if(!$uid)
		return 0;
	
	// $cate = order 订单;
	$m = m('coin_log');
	$data = array(
			'num'=>$num,
			'uid'=>$uid,
			'cate'=>$cate,
			'add_time'=>time(),
			'msg'=>$msg,
			'author'=>$author,
			'opt'=>$opt,
			'type'=>$cate_arr['type'],
	);
	$rs = $m->add($data);
	$db = &db();
	if($opt != 'add'){
		$num = - $num;
	}
	
	$sql = "update rc_member set coin = coin + ".$num." where user_id = ".$uid;
	$db->query($sql);
	
	$_SESSION['user_info']['coin'] = $_SESSION['user_info']['coin'] + $num;
	
	return outinfo('ok',0 , $way);
}

function setExpe($uid,$num,$opt,$cate,$author = 'system',$msg = '' ,$way = 'pc'){
	$m = m('expe_log');
	$data = array(
			'num'=>$num,
			'uid'=>$uid,
			'cate'=>$cate,
			'add_time'=>time(),
			'msg'=>$msg,
			'author'=>$author,
			'opt'=>$opt,
	);
	$m->add($data);
	$db = &db();
	if($opt != 'add'){
		$num = - $num;
	}
	
	$sql = "update rc_member set coin = experience + ".$num." where user_id = ".$uid;
	$db->query($sql);
	
	$member_lv_mod =& m('memberlv');
	$member_lv_mod->auto_level($uid,'member',$num);
	
	$_SESSION['user_info']['experience'] = $_SESSION['user_info']['experience'] + $num;
	
	return outinfo('ok',0 , $way);
	
}

function checkCate($cate){
    if(!$cate)
        exit('cate null');

    $arr = getAPICate();
    $f = 0;
    $key_arr =null;
    foreach($arr as $k=>$v){
        if($k == $cate){
            $f = 1;
            $key_arr  = $v;
            break;
        }
    }

    if(!$f)
        exit('cate not in arr');

    return $key_arr;
}

function setLike($uid,$like_id,$cate,$away = 'ajax'){
    checkCate($cate);

    $m = m('like');
    $like = getLikeByUid($uid,$like_id,$cate);
    if($like){
        return outinfo('已喜欢',1,$away);
    }

    $data = array(
        'add_time'=>time(),
        'uid'=>$uid,
        'like_id'=>$like_id,
        'cate'=>$cate,
    );

    $m->add($data);
    $member = m('member');
    $member->setInc(" user_id = $uid " , 'like_num');

    /* 定制喜欢数量 */
    if($cate == 'dingzhi_like'){
        $_customs_mod      	=& m('customs');
        $_customs_mod->setInc(" cst_id = $like_id",'cst_likes');
    }


    $m = m('userphoto');
    $m->setInc(" id = $like_id",'like_num');

    $_SESSION['user_info']['like_num'] = $_SESSION['user_info']['like_num'] + 1;

    return outinfo('ok',0,$away);
}
function getAPICate(){
    $arr = array(
        //小白
        'order_reward'  =>array('desc'=>'订单消费获得奖励','type'=>'order','key'=>'order_reward'),
        'order_dk_point'  =>array('desc'=>'使用积分抵扣订单额','type'=>'order','key'=>'order_dk_point'),
        'order_dk_coin'  =>array('desc'=>'使用酷特币抵扣订单额','type'=>'order','key'=>'order_dk_coin'),

        'sheji_order'  =>array('desc'=>'设计作品产生订单赠送酷特币','type'=>'order','key'=>'sheji_order'),
        'jiepai_order'  =>array('desc'=>'街拍作品产生订单赠送酷特币','type'=>'order','key'=>'jiepai_order'),
        'series_comment'=>	array('desc'=>'发表主题系列的评论获得奖励','type'=>'series','key'=>'series_comment'),

        //帅
        'shoucang'	=>array('desc'=>'收藏基本款获得奖励','type'=>'shoucang','key'=>'shoucang'),

        //李亮
        'liuxing_like'=>array('desc'=>'喜欢流行趋势获得奖励','type'=>'like','key'=>'liuxing_like'),

        //广信
        'dingzhi_comment'	=>	array('desc'=>'订单消费后进行评论获得奖励','type'=>'comment','key'=>'dingzhi_comment'),
        'goods_comment'		=>	array('desc'=>'订单消费后进行评论获得奖励','type'=>'goods','key'=>'goods_comment'),
        'dingzhi_like'		=>	array('desc'=>'喜欢基本款获得奖励','type'=>'like','key'=>'dingzhi_like'),
        'zhuti_like'		=>	array('desc'=>'喜欢主题获得奖励','type'=>'like','key'=>'zhuti_like'),

        //王东岩&小5
        'jiepai_like'			=>array('desc'=>'喜欢街拍获得奖励','type'=>'like','key'=>'jiepai_like'),
        'sheji_like'			=>array('desc'=>'喜欢设计获得奖励','type'=>'like','key'=>'sheji_like'),

        'jiepai_comment'	=>array('desc'=>'评论街拍作品获得奖励','type'=>'comment','key'=>'jiepai_comment'),
        'sheji_comment'		=>array('desc'=>'评论设计作品获得奖励','type'=>'comment','key'=>'sheji_comment'),

        'login_reward'		=>array('desc'=>'会员登录获得奖励','type'=>'login','key'=>'login_reward'),
        'proecss_profile'	=>array('desc'=>'完善个人资料获得奖励','type'=>'profile','key'=>'proecss_profile'),

        'follow'			=>array('desc'=>'关注酷客获得奖励','type'=>'follow','key'=>'follow'),
        'unfollow'			=>array('desc'=>'取消关注','type'=>'unfollow','key'=>'unfollow'),

        'sheji_reward' =>array('desc'=>'设计上传获得奖励','type'=>'upload','key'=>'sheji_reward'),
        'jiepai_reward' =>array('desc'=>'街拍上传获得奖励','type'=>'upload','key'=>'jiepai_reward'),

        //下面是为了兼容老的<评论>KEY值
        'personaldesign'=>array('desc'=>'个人设计详情页'),
        'streetinfo'=>array('desc'=>'街拍详情页'),
        'order_goods'=>array('desc'=>'基本款订单评论'),
        'dis' => array('desc'=>"主题系列"),
        'serve'=>array('desc'=>'服务点')


    );
    return $arr;
}

function getLikeByUids($uids ,$like_id , $cate){
    $db = &db();
    $sql = " select * from rc_like where uid in ( $uids ) and cate = '$cate' and like_id =  $like_id";
    $like = $db->getAll($sql);
    $uids_arr = explode(",", $uids);
    if($like){
        $rs = array();
        foreach($uids_arr as $k=>$v){
            $f = 0;
            foreach($like as $k2=>$v2){
                if($v == $v['uid']){
                    $f = 1;
                    break;
                }
            }

            if($f)
                $rs[$v] = 1;
            else
                $rs[$v] = 0;
        }
    }else{
        foreach($uids_arr as $k=>$v){
            $rs[$v] = 0;
        }
    }

    return $rs;
}

function getLikeByUid($uid ,$like_id , $cate){
    if(!$uid || !$like_id || !$cate)
        return 0;

    $arr = array('design'=>'酷客首页-个人设计','userphoto'=>'酷客首页-街拍');

    $db = &db();
    $sql = " select * from rc_like where uid = $uid and cate = '$cate' and like_id =  '{$like_id}'";
    $like = $db->getRow($sql);
    if($like)
        return 1;
    return 0;
}
function outinfo($msg,$err = 1 ,$way = 'ajax'){
    $rs = array('err'=>$err,'msg'=>$msg);
    if($way == 'ajax'){
        echo json_encode($rs);
        exit;
    }elseif($way == 'pc'){
        return $rs;
    }else{
        if($err){
            echo 'false';
        }else
            echo 'true';
    }
}


function setComment($uid,$to_uid,$comment_id,$cate,$content,$goodid = 0){
    if( $uid ){
        $comment = getComment($uid,$comment_id,$cate);
        if($comment){
            if('serve' != $cate)
                return outinfo('已评论',1,'pc');
        }

        $m = m('comments');
        $data = array(
            'uid'=>$uid,
            'to_uid'=>$to_uid,
            'comment_id'=>$comment_id,
            'cate'=>$cate,
            'content'=>$content,
            'add_time'=>time(),
            'goods_id'=>$goodid
        );
        $new_id = $m->add($data);

        $db = &db();
        $sql = "update rc_member set comment_num = comment_num + 1 where user_id = ".$uid;
        $db->query($sql);

        if( $cate == 'sheji_comment' || $cate == 'jiepai_comment'){
            $sql = "update rc_userphoto set comment_num = comment_num + 1 where id =  ".$comment_id;
            $db->query($sql);
        }

        $_SESSION['user_info']['comment_num'] = $_SESSION['user_info']['comment_num'] + 1;
    }else{//匿名评论
        $m = m('comments');
        $data = array(
            'uid'=>$uid,
            'to_uid'=>$to_uid,
            'comment_id'=>$comment_id,
            'cate'=>$cate,
            'content'=>$content,
            'add_time'=>time(),
            'goods_id'=>$goodid
        );
        $new_id = $m->add($data);
    }

    return $new_id;
}

/*批处理上传图片  add liliang*/
function pro_img_multi($file_name,$RESIZEWIDTH=100,$RESIZEHEIGHT=100,$FILENAME="image.thumb",$waterMark = false){
    if($file_name['size']){
        if($file_name['type'] == "image/pjpeg" || $file_name['type'] == 'image/jpeg' ){
            $im = imagecreatefromjpeg($file_name['tmp_name']);
        }elseif($file_name['type'] == "image/png"){
            $im = imagecreatefrompng($file_name['tmp_name']);
        }elseif($file_name['type'] == "image/gif"){
            $im = imagecreatefromgif($file_name['tmp_name']);
        }else{
            exit('file error ');
        }
        if($im){
            if(file_exists("$FILENAME.jpg")){
                unlink("$FILENAME.jpg");
            }
            ResizeImage($im,$RESIZEWIDTH,$RESIZEHEIGHT,$FILENAME);
            ImageDestroy ($im);
        }
    }
}

function ResizeImage($im,$maxwidth,$maxheight,$name){
    $width = imagesx($im);
    $height = imagesy($im);
// 	echo $maxwidth . " " .$maxheight . " " .$width ." " . $height;
    if(($maxwidth && $width > $maxwidth) || ($maxheight && $height > $maxheight)){
        if($maxwidth && $width > $maxwidth){
            $widthratio = $maxwidth/$width;
            $RESIZEWIDTH=true;
        }
        if($maxheight && $height > $maxheight){
            $heightratio = $maxheight/$height;
            $RESIZEHEIGHT=true;
        }
        if($RESIZEWIDTH && $RESIZEHEIGHT){
            if($widthratio < $heightratio){
                $ratio = $widthratio;
            }else{
                $ratio = $heightratio;
            }
        }elseif($RESIZEWIDTH){
            $ratio = $widthratio;
        }elseif($RESIZEHEIGHT){
            $ratio = $heightratio;
        }
        $newwidth = $width * $ratio;
        $newheight = $height * $ratio;
        if(function_exists("imagecopyresampled")){
// 			echo $newwidth . " " . $newheight;
            $newim = imagecreatetruecolor($newwidth, $newheight);
            imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        }else{
            $newim = imagecreate($newwidth, $newheight);
            imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        }
        ImageJpeg ($newim,$name );
        ImageDestroy ($newim);
    }else{
        ImageJpeg ($im,$name );
    }
}
function delUserPhoto($pic_id){
    $m_photo = m('userphoto');
    $m_album =  m('album');
    $db = &db();
    $photo = $m_photo->getById($pic_id);
    if(!$photo)
        return 0;

    $sql = "update rc_member set pic_num = pic_num - 1 where user_id = ".$photo['uid'];
    $db->query($sql);

    $rs = $m_photo->delById($pic_id);
    if($photo['album_id']){
        $album = $m_album->getByID($photo['album_id']);
        if($album){
            if($album['top_url'] == $photo['url']){
                $m_album->edit($photo['album_id'],array('top_url'=>''));
            }
            $sql = "update rc_album set pic_num = pic_num - 1 where id = ".$photo['album_id'];
            $db->query($sql);
        }
    }
    $_SESSION['user_info']['pic_num'] = $_SESSION['user_info']['pic_num'] - 1;
    return 1;
}
?>
