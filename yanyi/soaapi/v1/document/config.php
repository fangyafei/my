<?php
define("PROJECT_PATH","d:\wamp\www\ecmall\");//存放项目的目录
define("KERNEL_PATH",PROJECT_PATH.'kernel/');//核心代码的目录
define('APP_NAME', 'webservice');
$rs = include_once KERNEL_PATH . 'config/shell_config.php';
if (!$rs){
	echo "include kernel index.php failed";
}

define ( "PARAMETER_ERRORS", '00001' );
define ( "RECORD_EMPTY_ERRORS", '00002' );
define ( "FAILURE_ERRORS", '00003' );
define ( "DELETE_ERRORS", '00004' );

define("WEBSER",dirname(__FILE__));
define("WEBSER_LIB",WEBSER."/library");
define("WEBSER_LIB_CTRL",WEBSER_LIB."/controllers");
define("CLASS_LIB", WEBSER ."/class/");
define("WEBSER_LIB_MODEL",WEBSER_LIB."/models");

define ( "SQL_LOG_SWITCH", 1 );

$rs = include_once CLASS_LIB . 'auth.class.php';
/*
 *  http状态:

1001:ip不允许访问
1002:POST值为空
2001:XML请求格式错误
2002:非法的请求:请求的APPID错误
2003:非法的请求:KEY错误
2004:非法的请求:md5值错误

3001:此类不允许访问
3002:类不存在
3003:方法不存在
3004:用户传输的参数个数与实际的个数不同
3005:参数名有误
3006:参数值有问题



http发送格式
<?xml version="1.0"
<msg>
<Head>
<appid>$appID</appid>
<keyword>$keywordcode</keyword>
<ctid>$CTID</ctid>
<submittime>$curr_time</submittime>
<class>Staff</class>
<method>moveDeptStaffByCruuIdTargId</method>
<parameter name="firstDeptId">1</parameter>
<parameter name="targetDeptId">1</parameter>
</Head>
</msg>

submittime:提交时间
appID:应用程序的ID
method:访问的方法
class:访问的类
parameter:参数
ctid= md5($key.$keywordcode.$appID.$curr_time);
keywordcode:此APP的密钥
*/
