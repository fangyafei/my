<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang;?>" />
<title>提问-<?php echo $cfg_ask_sitename;?></title>
<link href="static/style/service.css" rel="stylesheet" type="text/css" media="screen" />
<script src="static/js/j.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function()
{	
	$('#ask').submit(function() { 
		if($('#title').val() == "")
		{
			$('#_title').html("<font color='red'>问题的长度不为空</font>");
			return false; 
		}
		else if($('#title').val().length < 4)
		{
			$('#_title').html("<font color='red'>问题的长度不能小于8个字符</font>");
			return false; 
		}
	});
});
</script>
</head>
<body>
<div id="wrap">
  <?php include(DEDEASK."/templates/default/header.htm"); ?>
  <div class="subnav"><span class="fl place"><?php echo $cfg_ask_position;?><a href="#">填写问题描述标题</a></span></div>
  <div id="main">
    <div class="post_box bor1 mt10">
      <h2 class="step1"></h2>
      <div class="mt10 post_content">
      <form action="?ct=search&ac=ask_search" method="post" name="ask" onsubmit="return checkform(this);" id="ask">
		<input type="hidden" name="step" value="2" />
        <table width="100%" class="step1_tab">
          <tr>
            <td width="90">填写您的问题：</td>
            <td><input name="title" type="text" class="post_txt1" id="title"/><span class="ml10 c1">问题需要清晰易懂(不少于8个字符)！</span><span id='_title'></span></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><input type="submit" value=""  class="post_btn1"/></td>
          </tr>
        </table>
        </form>
      </div>
    </div>
  </div><!--main end-->
  <?php include(DEDEASK."/templates/default/footer.htm"); ?>
</div>
</body>
</html>