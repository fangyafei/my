<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>幻灯片管理</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
<link href="static/style/admin.css" rel="stylesheet" type="text/css" />
<link href="static/style/tb-box.css" rel="stylesheet" type="text/css" />
<script language='javascript' src="static/js/pic.js"></script>
</head>
<body leftmargin='8' topmargin='8'>
<div class="bodytitle">
 <div class="bodytitleleft"></div>
 <div class="bodytitletxt" style="padding-left:10px;"><b>问答模块</b> -- 幻灯片管理</div>
</div>
<form name="form1" action="?ct=slide&ac=save" method="post">
 <table width="98%" border="0" align="center" cellpadding="1" cellspacing="1" class="tbtitle" style="background:#cfcfcf;">
  <tr>
   <td height="25" bgcolor="#EDF9D5" background="static/images/tbg.gif" style="padding-left:10px;" colspan="4"><strong>编辑幻灯片</td>
  </tr>
  <tr>
   <td width="10%" align="right" bgcolor="#F9FCEF">&nbsp;</td>
   <td width="30%" align="center" bgcolor="#FFFFFF">幻灯片一</td>
   <td width="30%" align="center" bgcolor="#FFFFFF">幻灯片二</td>
   <td width="30%" align="center" bgcolor="#FFFFFF">幻灯片三</td>
  </tr>
  <tr>
   <td align="right" bgcolor="#F9FCEF">链接地址：</td>
   <td bgcolor="#FFFFFF"><input name="url1" type="text" id="url2" style="width:200px" value="<?php echo  $row[0]['url']=empty($row[0]['url'])? '' : $row[0]['url'];?>"/></td>
   <td bgcolor="#FFFFFF"><input name="url2" type="text" class="ip1" id="url3" style="width:200px" value="<?php echo  $row[1]['url']=empty($row[1]['url'])? '' : $row[1]['url'];?>"/></td>
   <td bgcolor="#FFFFFF"><input name="url3" type="text" class="ip1" id="url1" style="width:200px" value="<?php echo  $row[2]['url']=empty($row[2]['url'])? '' : $row[2]['url'];?>"/></td>
  </tr>
  <tr>
   <td align="right" bgcolor="#F9FCEF">描述：</td>
   <td bgcolor="#FFFFFF"><textarea name="description1" rows="2" id="description1" style="width:200px"><?php echo  $row[0]['description']=empty($row[0]['description'])? '' : $row[0]['description'];?></textarea></td>
   <td bgcolor="#FFFFFF"><textarea name="description2" rows="2" id="description2" style="width:200px"><?php echo  $row[1]['description']=empty($row[1]['description'])? '' : $row[1]['description'];?></textarea></td>
   <td bgcolor="#FFFFFF"><textarea name="description3" rows="2" id="description3" style="width:200px"><?php echo  $row[2]['description']=empty($row[2]['description'])? '' : $row[2]['description'];?></textarea></td>
  </tr>
  <tr>
   <td align="right" bgcolor="#F9FCEF">显示图片：</td>
   <td bgcolor="#FFFFFF"><input name="pic1" type="text" id="pic1" style="width:150px" value="<?php echo  $row[0]['pic']=empty($row[0]['pic'])? '' : $row[0]['pic'];?>"/>
    <input type="button" name="Submit" value="选择" style="margin-left:8px;" onclick="SelectImage('form1.pic1','small');" class='np coolbg'/></td>
   <td bgcolor="#FFFFFF"><input name="pic2" type="text" class="ip1" id="pic2" style="width:150px" value="<?php echo  $row[1]['pic']=empty($row[1]['pic'])? '' : $row[1]['pic'];?>"/>
    <input type="button" name="Submit3" value="选择" style="margin-left:8px;" onclick="SelectImage('form1.pic2','small');" class='np coolbg'/></td>
   <td bgcolor="#FFFFFF"><input name="pic3" type="text" id="pic3" style="width:150px" value="<?php echo  $row[2]['pic']=empty($row[2]['pic'])? '' : $row[2]['pic'];?>"/>
    <input type="button" name="Submit2" value="选择" style="margin-left:8px;" onclick="SelectImage('form1.pic3','small');" class='np coolbg'/></td>
  </tr>
  <tr>
   <td align="right" bgcolor="#F9FCEF">显示图片：</td>
   <td align="center" bgcolor="#FFFFFF"><img src="<?php echo  $row[0]['pic']=empty($row[0]['pic'])? '' : $row[0]['pic'];?>" width="140px" height="100px"/></td>
   <td align="center" bgcolor="#FFFFFF"><img src="<?php echo  $row[1]['pic']=empty($row[1]['pic'])? '' : $row[1]['pic'];?>" width="140px" height="100px"/></td>
   <td align="center" bgcolor="#FFFFFF"><img src="<?php echo  $row[2]['pic']=empty($row[2]['pic'])? '' : $row[2]['pic'];?>" width="140px" height="100px"/></td>
  </tr>
  <tr>
   <td colspan="4" bgcolor="#FFFFFF">&nbsp;</td>
  </tr>
  <tr>
   <td height="35" bgcolor="#F9FCEF" style="padding-left:10px;" colspan="4"><input name="submit" type="submit" value="确定"/></td>
  </tr>
 </table>
 <p>&nbsp;</p>
</form>
</body>
</html>