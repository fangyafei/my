<?php
	$APPID="wxe2c20f6793395b78";
	$APPSECRET="ba64256927f112ade8e25284367181b8";
	
	$TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$APPID."&secret=".$APPSECRET;
	$json=file_get_contents($TOKEN_URL);

	$result=json_decode($json,true);
	$ACC_TOKEN=$result['access_token'];	
	/*$data = '{
	 "button":[
	 {
	       "name":"清晏九洲",
	       "sub_button":[
	        {
	           "type":"click",
	           "name":"简介",
	           "key":"jianjie"
	        },
	        {
	           "type":"click",
	           "name":"联系我们",
	           "key":"lianxiwomen"
	        }]
	  },
	  
	 { 
	  "name":"我的账户",
	       "sub_button":[
	        {
	           "type":"click",
	           "name":"我要绑定",
	           "key":"woyaobangding"
	        },
	        {
	           "type":"click",
	           "name":"我的订单",
	           "key":"wodedingdan"
	        },
	         {
	           "type":"click",
	           "name":"会员优惠",
	           "key":"huiyuanyouhui"
	        },
	         {
	           "type":"click",
	           "name":"积分返点",
	           "key":"jifenfandian"
	        }]
	  },
	  
	  {
	   "name":"项目介绍",
	       "sub_button":[
	        {
	           "type":"click",
	           "name":"那香山",
	           "key":"naxiangshan"  
	        },
	         {
	           "type":"click",
	           "name":"雨林谷 ",
	           "key":"yulingu"
	        },
	        {
	           "type":"click",
	           "name":"阿那亚",
	           "key":"anaya"
	        }]
	  }]
	 
	}';
	
	function postm($url, $jsonData) {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$info = curl_exec($ch);
		
		if (curl_errno($ch)) {
			echo 'Errno'.curl_error($ch);
		}
		var_dump($info);
		curl_close($ch);
	}*/
	
	/* function selectm($url) {
		$cu = curl_init();
		curl_setopt($cu, CURLOPT_URL, $url);
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
		$menu_json = curl_exec($cu);
		$menu = json_decode($menu_json);
		curl_close($cu);
		
		echo $menu_json;
	}
 */	$MENU_URL="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$ACC_TOKEN;
	postm($MENU_URL, $data);
	//selectm($MENU_URL);
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	