<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?>﻿
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
<title>商务留言板管理</title>
<link href="css/base.css" rel="stylesheet" type="text/css">
<script language="javascript" src="../include/dedeajax2.js"></script>
<script language="javascript" src="js/ieemu.js"></script>
<script language="javascript" src="js/main.js"></script>
<script language="javascript" src="js/list.js"></script>
</head>
<body background='images/allbg.gif' leftmargin='8' topmargin='8'>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#D6D6D6">
<form name="form2">
  <tr>
   <td height="28" background="images/tbg.gif" colspan="9" style="padding-left:10px;">
     <b>商务留言管理</b>
	</td>
</tr>
  <tr align="center" bgcolor="#FBFCE2" height="24">
      <td width="6%">ID</td>
      <td width="4%">选择</td>
      <td width="25%">留言标题</td>
      <td width="10%">留言人</td>
      <td width="15%">单位</td>
      <td width="10%">留言时间</td>
      <td width="10%">回复时间</td>
      <td width="5%">审核</td>
      <td width="15%">管理</td>
   </tr>
        <?php
$atts = array();
$atts['tagname'] = 'datalist';
$blockValue = $this->refObj->GetArcList($atts,$this->refObj,$fields); 
if(is_array($blockValue)){
foreach( $blockValue as $key=>$fields )
{
?>
         <tr align="center" bgcolor="#FFFFFF" height="24" 
         	onMouseMove="javascript:this.bgColor='#f4fbf4';" onMouseOut="javascript:this.bgColor='#FFFFFF';"> 
          <td><?php echo $fields['id']; ?></td>
          <td><input name="arcID" type="checkbox" id="arcID" value="<?php echo $fields['id']; ?>" class="np" /></td>
          <td align="left"><a href='javascript:;' onClick="showGB(<?php echo $fields['id']; ?>,event,this)"><u><?php echo $fields['title']; ?></u></a></td>
          <td align="left"><?php echo $fields['uname']; ?></td>
          <td align="left"><?php echo $fields['org']; ?></td>
          <td><?php echo GetDateMk($fields['dtime']); ?></td>
          <td><?php echo GetDateMk($fields['posttime']); ?></td>
          <td><a href="javascript:checkGB(<?php echo $fields['id']; ?>)" id="ic_<?php echo $fields['id']; ?>" title="点击改变审核状态"><?php echo $fields['ischeck']; ?></a></td>
          <td>
          	<a href="comguestbook_main.php?action=show&id=<?php echo $fields['id']; ?>">[回复/修改]</a>
            <a href='javascript:delGB(<?php echo $fields['id']; ?>)'>[删除]</a>
		      </td>
        </tr>
		<?php
}
}?>
     <tr bgcolor="#FAFAF1">
    <td height="36" colspan="10">
        &nbsp;
        <a href="javascript:selAll()" class="coolbg">全选</a>
        <a href="javascript:noSelAll()" class="coolbg">取消</a>
        <a href="javascript:obSelAll()" class="coolbg">反选</a>
        <a href="javascript:checkGB(0)" class="coolbg">&nbsp;审核&nbsp;</a>
        <a href="javascript:delGB(0)" class="coolbg">&nbsp;删除&nbsp;</a>
    </td>
    </tr>
    </form>
     <tr align="center" bgcolor="#F9FCEF" height="24"> 
      <td colspan="9"><?php
$atts = array();
$atts['tagname'] = 'pagelist';
$atts['listsize'] = '10';
 echo $this->refObj->GetPageList($atts,$this->refObj,$fields); 
?>
</td>
     </tr>
   </table>
   </td>
</tr>
</table>

<!--  搜索表单  -->
<form name='form3' action='comguestbook_main.php' method='get'>
<input type='hidden' name='dopost' value='search' />
<table width='98%'  border='0' cellpadding='1' cellspacing='1' bgcolor='#CBD8AC' align="center" style="margin-top:8px">
  <tr bgcolor='#EEF4EA'>
    <td background='images/wbg.gif' align='center'>
      <table border='0' cellpadding='0' cellspacing='0' height="32">
        <tr>
        <td nowrap>
          关键字：
        </td>
        <td width='130'>
          	<input type='text' name='keyword' value='<?php echo $GLOBALS['keyword']; ?>' style='width:120px' />
        </td>
        <td width='90'>
    		<select name='orderby' style='width:80px'>
            <option value='id'>排序...</option>
            <option value='title'>标题</option>
            <option value='uname'>留言人</option>
            <option value='org'>单位</option>
            <option value='dtime'>留言时间</option>
            <option value='posttime'>回复时间</option>
            <option value='ischeck'>审核</option>
      	</select>
      </td>
       <td>
          <input name="imageField" type="image" src="images/button_search.gif" width="60" height="22" border="0" class="np" />
       </td>
      </tr>
     </table>
   </td>
  </tr>
</table>
</form>
</body>
</html>
<script language="javascript">
function checkGB(id)
{
	if(id>0){
		var obj = document.getElementById('ic_'+id);
		var ischeck = obj.innerHTML;
		if(confirm('是否要审核'+(ischeck==1?'不':'')+'通过留言？')){
			myajax = new DedeAjax(obj);
			myajax.SendGet2('comguestbook_do.php?action=check&id='+id+'&ischeck='+(ischeck==1?0:1)+'&rnd='+Math.random());
			return;
		}
	}else{
		var qstr=getCheckboxItem();
		if(qstr=='')
		{
			alert('必须选择一个或多个留言！');
			return;
		}
		if(confirm('是否要改变选中留言的审核状态？')){
			location="comguestbook_do.php?action=check&dopost=more&idstr="+qstr;
		}		
	}
}
function delGB(id)
{
	if(id>0){
		location="comguestbook_do.php?action=del&id="+id;
	}else{
		var qstr=getCheckboxItem();
		if(qstr=='')
		{
			alert('必须选择一个或多个留言！');
			return;
		}
		if(confirm('确认要删除选中的留言吗？')){
			location="comguestbook_do.php?action=del&dopost=more&idstr="+qstr;
		}		
	}
}
function showGB(id, e, obj){
	LoadQuickDiv(e, 'comguestbook_do.php?action=showGB&id='+id+'&rnd='+Math.random(), 'ShowGB', '600px', '150px');
	ChangeFullDiv('show');
}
function obSelAll()
{
	for(i=0;i<document.form2.arcID.length;i++)
	{
		if(!document.form2.arcID[i].checked)
		{
			document.form2.arcID[i].checked=true;
		}else{
			document.form2.arcID[i].checked=false;
		}
	}
}
</script>