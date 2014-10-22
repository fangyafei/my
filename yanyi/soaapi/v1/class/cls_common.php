<?php


/**
 * 这里写点啥呢？？？
 * ============================================================================
 * API auther: http://www.ec51.net/
 * ----------------------------------------------------------------------------
 */
include_once('../config.php');

function getSoapClients($class, $method, $para)
{
	$AppID = 101;
	$key   = 'gamebean';
	try{
		
		$wsdlUrl = WSDL_URL."soaapi/v1/soap/WSDL/SoapService.wsdl";
		$SoapUrl = WSDL_URL."soaapi/v1/soap/ServiceSoap.php";
	
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
		$client->__setSoapHeaders(array($headers));
// print_exit($client);
		$result = $client->Router($class,$method,$para);
// print_exit($result);
//		echo "<pre>";
//		
//		$temArr = json_decode($result,true);
//		find_array_key('imgUrl', &$temArr);
//		var_dump($temArr);
//		echo "</pre>";

		if(is_array($result))
		{
			global $json;
			$result=$json->encode($result);
		}
		
		return $result;
	}catch (Exception $e){
//		@var_dump($e->getMessage())."<br/>";
//		echo "<br/>";
//		echo $client->__getLastRequest()."<br/>";
//		echo "<br/>";
// 		$client = new SoapClient( $wsdlUrl ,array( 'trace' => 1 ) );
		echo $client->__getLastResponse()."<br/>";
	}
}


//登录成功后，要返回token？
/**
token的规则：
1、token是用在与会员有关的地方；
2、token的生成规则就用会员的md5（username+password）；
3、会员登录成功后，将生成好的token返回，往后需要的地方他们都带过来；
4、将获取过来的token进行验证，如果对不上，将statuscode返回1000；
5、会员密码更新时，token也要相应的更新。
*/

/**
* 1.获取会员令牌（token） API
*
* @access      public
* @param       string       $username       用户名
*/
function getSubscriberToken($username) 
{
	return check_token($username);
}

//如果会员token过期，重置
function renewSubscriberToken($username) 
{
	return check_token($username);
}

/**
* 检查当前用户的 token，若过期，则重新获取
* @param string $username
*  指定用户名
* @access public
* @return false|string
*/
function check_token($username) 
{
	global $_api_token; 
	if (empty($username)) return false;
	
	//这里进行一下SQL查询，将该会员的password查询出来，MD5(username+password)来生成token 然后return $token


	// 在同一次请求中，重复利用 token
	if (!empty($_api_token)) return $_api_token;

}


?>
