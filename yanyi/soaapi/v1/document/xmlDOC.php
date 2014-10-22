<?php
//file author:wangdongyan
//200:成功
//1001:ip不允许访问
//1002:POST值为空
//2001:XML请求格式错误
//2002:非法的请求:请求的APPID错误
//2003:非法的请求:KEY错误
//2004:非法的请求:md5值错误
//3001:此类不允许访问
//3002:类不存在
//3003:方法不存在
//3004:用户传输的参数个数与实际的个数不同
//3005:参数名有误
//3006:参数值有问题

$curr_time = time();
$appId = "1000001";
$keyword = "20000001";
$ctid = md5($appID.$keyword.$curr_time);
function getStaffListBycorpId($corpId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getStaffListBycorpId</method>
	    <parameter name="corpId">1</parameter>
  	</Head>
</msg>
EOD;

function getStaffListByDeptId($corpId,$deptId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getStaffListByDeptId</method>
	    <parameter name="corpId">1</parameter>
	    <parameter name="deptId">1</parameter>
  	</Head>
</msg>
EOD;

function getStaffeByStaffId($staffId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getStaffeByStaffId</method>
	    <parameter name="staffId">1</parameter>
  	</Head>
</msg>
EOD;


function getSomeStaffByStaffIds($staffIds){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getSomeStaffByStaffIds</method>
	    <parameter name="staffIds" type="array">
	    	<array>
		    	<element name="0">1</element>
		    	<element name="1">2</element>
		    	<element name="2">3</element>
		    	<element name="3">4</element>
	    	</array>
	    </parameter>
  	</Head>
</msg>
EOD;

function addStaff($staffInfo){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>addStaff</method>
	   	<parameter name="staffInfo" type="array">
	    	<array>
		    	<element name="name">66563</element>
		    	<element name="corpid">2</element>
		    	<element name="deptid">3</element>
		    	<element name="email">5678</element>
	    	</array>
	    	<array>
		    	<element name="name">66663</element>
		    	<element name="corpid">2</element>
		    	<element name="deptid">3</element>
		    	<element name="email">5678</element>
	    	</array>
	    </parameter>
  	</Head>
</msg>
EOD;

function upStaffByStaffId($staffId,$staffInfo){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>upStaffByStaffId</method>
	    <parameter name="staffId">1</parameter>
	   	<parameter name="staffInfo" type="array">
	    	<array>
		    	<element name="name">56784</element>
		    	<element name="corpid">2</element>
		    	<element name="deptid">3</element>
		    	<element name="email">5678</element>
	    	</array>
	    </parameter>
  	</Head>
</msg>
EOD;

function delDeptStaffByDeptId($corpId,$deptId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>delDeptStaffByDeptId</method>
	    <parameter name="corpId">2</parameter>
	    <parameter name="deptId">1</parameter>
  	</Head>
</msg>
EOD;

function finalDelDeptStaff($corpId,$deptId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>finalDelDeptStaff</method>
	    <parameter name="corpId">2</parameter>
	    <parameter name="deptId">1</parameter>
  	</Head>
</msg>
EOD;

function delStaffByStaffId($staffId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>delStaffByStaffId</method>
	    <parameter name="staffId">2</parameter>
  	</Head>
</msg>
EOD;

function finalDelStaff($staffId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>finalDelStaff</method>
	    <parameter name="staffId">2</parameter>
  	</Head>
</msg>
EOD;


function delSomeStaffByStaffIds($staffIds){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>delSomeStaffByStaffIds</method>
	    <parameter name="staffIds" type="array">
	    	<array>
		    	<element name="0">1</element>
		    	<element name="1">2</element>
		    	<element name="2">3</element>
		    	<element name="3">4</element>
	    	</array>
	    </parameter>
  	</Head>
</msg>
EOD;

function finalDelSomeStaff($staffIds){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>finalDelSomeStaff</method>
	    <parameter name="staffIds" type="array">
	    	<array>
		    	<element name="0">1</element>
		    	<element name="1">2</element>
		    	<element name="2">3</element>
		    	<element name="3">4</element>
	    	</array>
	    </parameter>
  	</Head>
</msg>
EOD;

function delcorpStaffByCorpId($corpId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>delcorpStaffByCorpId</method>
	    <parameter name="corpId">1</parameter>
  	</Head>
</msg>
EOD;

function finalDelCorpStaff($corpId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>finalDelCorpStaff</method>
	    <parameter name="corpId">1</parameter>
  	</Head>
</msg>
EOD;


function getDelStaffList(){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getDelStaffList</method>
  	</Head>
</msg>
EOD;

function getStaffName($staffId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getStaffName</method>
	    <parameter name="staffId">1</parameter>
  	</Head>
</msg>
EOD;

function getStaffNameByName($staffName){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getStaffNameByName</method>
	    <parameter name="staffName">1</parameter>
  	</Head>
</msg>
EOD;

function getStaffCorpId($staffId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getStaffCorpId</method>
	    <parameter name="staffId">1</parameter>
  	</Head>
</msg>
EOD;

function getStaffDeptId($staffId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getStaffDeptId</method>
	    <parameter name="staffId">1</parameter>
  	</Head>
</msg>
EOD;

function getStaffIsleader($staffId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getStaffIsleader</method>
	    <parameter name="staffId">1</parameter>
  	</Head>
</msg>
EOD;

function getStaffDuty($staffId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>getStaffDuty</method>
	    <parameter name="staffId">1</parameter>
  	</Head>
</msg>
EOD;

function moveStaffByCruuIdTargId($staffId,$firstDeptId,$targetDeptId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>moveStaffByCruuIdTargId</method>

	    <parameter name="firstDeptId">1</parameter>
	    <parameter name="targetDeptId">1</parameter>
  	</Head>
</msg>
EOD;

function moveSomeStaffByCruuIdTargId($staffIds,$firstDeptId,$targetDeptId){}
 $xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>moveSomeStaffByCruuIdTargId</method>
	    <parameter name="staffIds" type="array">
	    	<array>
	    		<element name="0">1</element>
	    		<element name="0">2</element>
	    	</array>
	    </parameter>
	    <parameter name="firstDeptId">1</parameter>
	    <parameter name="targetDeptId">1</parameter>
  	</Head>
</msg>
EOD;


function moveDeptStaffByCruuIdTargId($firstDeptId,$targetDeptId){}
$xml = <<<EOD
<?xml version="1.0" ?>
<msg>
	<Head>
		<appid>$appId</appid>
	    <keyword>$keyword</keyword>
	    <ctid>$ctid</ctid>
	    <submittime>$curr_time</submittime>
	    <class>Staff</class>
	    <method>moveDeptStaffByCruuIdTargId</method>
	    <parameter name="firstDeptId">1</parameter>
	    <parameter name="targetDeptId">1</parameter>
  	</Head>
</msg>
EOD;
