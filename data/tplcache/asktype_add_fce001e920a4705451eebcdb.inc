<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang;?>" />
<title>增加分类</title>
<link href="static/style/admin.css" rel="stylesheet" type="text/css" />
</head>
<body>
<form name="form2" action="?ct=asktype&ac=add_save" method="post" style="padding-top: 15px;">
<?php if(!empty($id)){?>
<input name="reid" type="hidden" value="<?php echo $id;?>" />
<?php }?>
	<table width="98%" border="0" cellpadding="1" cellspacing="1" align="center" class="tbtitle" style="background:#E2F5BC;margin-bottom:6px;">
      <tr>
        <td colspan="2" bgcolor="#EDF9D5" background="img/wbg.gif"><strong>&nbsp;添加分类</strong>
      </td>
      </tr>
      <tr>
        <td width="150" height="20" align="right" bgcolor="#FFFFFF">分类名称：</td>
        <td height="20" bgcolor="#FFFFFF"><input type="text" name="name" value="" />(分类名称长度最好不要超过10个字符，也就是5个汉字)</td>
      </tr>
      <tr>
        <td height="20" align="right" bgcolor="#FFFFFF">
        	隶属大类：
        </td>
        <td height="20" bgcolor="#FFFFFF">
        <?php if(empty($id)){?>
        <select name="reid" style="width:150px;">
          <?php echo $sectorscache; ?>
        </select>
        <?php }else{ echo $name; }?>
        </td>
      </tr>
      <tr>
        <td height="35" bgcolor="#FFFFFF">&nbsp;</td>
        <td height="20" bgcolor="#FFFFFF"><input type="submit" name="submit2" value="提交" class="np coolbg"/></td>
      </tr>
    </table>
</form>
</body>
</html>