<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $GLOBALS['cfg_soft_lang']; ?>" />
<title>用户评论：<?php echo $GLOBALS['title']; ?></title>
<link href="<?php echo $GLOBALS['cfg_templets_skin']; ?>/style/dedecms.css" rel="stylesheet" media="screen" type="text/css" />
<script language="javascript" type="text/javascript" src="<?php echo $GLOBALS['cfg_cmsurl']; ?>/include/dedeajax2.js"></script>
<script language="javascript">
function postBadGood(ftype,fid)
{
	var taget_obj = document.getElementById(ftype+fid);
	var saveid = GetCookie('badgoodid');
	if(saveid != null)
	{
		 var saveids = saveid.split(',');
		 var hasid = false;
		 saveid = '';
		 j = 1;
		 for(i=saveids.length-1;i>=0;i--)
		 {
		 	  if(saveids[i]==fid && hasid) continue;
		 	  else {
		 	  	if(saveids[i]==fid && !hasid) hasid = true;
		 	  	saveid += (saveid=='' ? saveids[i] : ','+saveids[i]);
		 	  	j++;
		 	  	if(j==10 && hasid) break;
		 	  	if(j==9 && !hasid) break;
		 	  }
		 }
     if(hasid) { alert('您刚才已表决过了喔！'); return false; }
     else saveid += ','+fid;
		 SetCookie('badgoodid',saveid,1);
	}
	else
	{
		SetCookie('badgoodid',fid,1);
	}
	//document.write("feedback.php?action="+ftype+"&fid="+fid);
	//return;
  myajax = new DedeAjax(taget_obj,false,false,'','','');
  myajax.SendGet2("feedback.php?aid=<?php echo $GLOBALS['aid']; ?>&action="+ftype+"&fid="+fid);
  DedeXHTTP = null;
}

function ajaxFeedback(aid, fid, type)
{
	
	var taget_obj = $DE('ajaxfeedback_'+fid);
	if(taget_obj.innerHTML == '')
	{
		var myajax = new DedeAjax(taget_obj, true, true, '', 'x');
		myajax.SendGet2("<?php echo $GLOBALS['cfg_phpurl']; ?>/feedback.php?aid="+aid+"&fid="+fid+"&action=quote&type=ajax");
		eval('var result = typeof CKEDITOR.instances.msg_'+fid);
		if(result != 'undefined')
		{
			// 删除实例
			eval('var edit = CKEDITOR.instances.msg_'+fid);
			CKEDITOR.remove(edit);
		}
		CKEDITOR.replace(document.getElementById('msg_'+fid) , CKEDITOR.instances.msg.config);
		scroll(0, taget_obj.offsetTop - 120);
		var formname = 'f = document.ajaxfeedback_'+fid;
		eval(formname);
		if(f.validate)
		{
			if($DE('vdimg_'+fid)) $DE('vdimg_'+fid).src = "<?php echo $GLOBALS['cfg_cmsurl']; ?>/include/vdimgck.php?"+f.validate.value;
			f.validate.value = '';
		}
		
		DedeXHTTP = null;
	}
}

function ajaxQuotePost(fid)
{
	var formname = 'f = document.ajaxfeedback_'+fid;
	eval(formname);
	//var f = document.formname;
	//var f = f[0];
	var nvalidate = '';
	var nnotuser = '';
	var nusername = '';
	var npwd = '';
	var taget_obj = $DE('commetcontentNew');
	var waithtml = "<div style='line-height:30px'><img src='<?php echo $GLOBALS['cfg_cmsurl']; ?>/images/loadinglit.gif' />正在发送中...</div>";
	eval('var msg = CKEDITOR.instances.msg_'+fid+'.getData()');

	if(f.validate)
	{
		if(f.validate.value=='') {
			alert("请填写验证码！");
			return;
		}
		else {
			nvalidate = f.validate.value;
		}
	}
	var myajax = new DedeAjax(taget_obj, false, true, '', '', waithtml);
	
	if(f.notuser.checked) nnotuser = '1';
	if(f.username) nusername = f.username.value;
	if(f.pwd) npwd = f.pwd.value;
	
	myajax.sendlang = '<?php echo $GLOBALS['cfg_soft_lang']; ?>';
	myajax.AddKeyN('dopost', 'send');
	myajax.AddKeyN('aid', '<?php echo $GLOBALS['aid']; ?>');
	myajax.AddKeyN('fid', f.fid.value);
	myajax.AddKeyN('type', 'ajax');
	myajax.AddKeyN('comtype', f.comtype.value);
	myajax.AddKeyN('isconfirm','yes');
	
	myajax.AddKeyN('typeid', f.typeid.value);
	myajax.AddKeyN('quotemsg', f.quotemsg.value);
	myajax.AddKeyN('validate', nvalidate);
	myajax.AddKeyN('notuser', nnotuser);
	myajax.AddKeyN('username', nusername);
	myajax.AddKeyN('pwd', npwd);
	myajax.AddKeyN('msg', msg);
	myajax.SendPost2('<?php echo $GLOBALS['cfg_phpurl']; ?>/feedback_ajax.php');
	//alert(f.quotemsg.value);
	if($DE('ajaxfeedback_'+fid).innerHTML != null)
	{
		$DE('ajaxfeedback_'+fid).innerHTML = '';
	}
	scroll(0, taget_obj.offsetTop);
	taget_obj.removeAttribute('id');

}
</script>
<body class="commentpage">
<div class="header">
  <div class="top w960 center">
    <div class="title">
      <h1><a href="<?php echo $GLOBALS['cfg_basehost']; ?>" title="<?php echo $GLOBALS['cfg_webname']; ?>"><img src="<?php echo $GLOBALS['cfg_templets_skin']; ?>/images/logo.png"  alt="<?php echo $GLOBALS['cfg_webname']; ?>"/></a> </h1>
    </div>
    <!-- /title -->
    <div class="welcome">
      <?php if($cfg_ml->M_ID <= 0) { ?>
      您好,欢迎来到本网站,您还没有登陆,请先<a href="<?php echo $cfg_memberurl; ?>/index.php">登陆</a>或者<a href="<?php echo $cfg_memberurl; ?>/index_do.php?fmdo=user&dopost=regnew">注册</a>.
      <?php }else{  echo $cfg_ml->M_LoginID; ?>,欢迎您的登陆。 <a href="<?php echo $cfg_memberurl; ?>/index.php">会员中心</a> | <a href="<?php echo $cfg_memberurl; ?>/edit_fullinfo.php">我的资料</a> | <a href="<?php echo $cfg_memberurl; ?>/index.php?uid=<?php echo urlencode($cfg_ml->M_LoginID);?>">我的空间</a> | <a href="<?php echo $cfg_memberurl; ?>/index_do.php?fmdo=login&dopost=exit">退出登录</a>
      <?php
        }
        ?>
    </div>
    <!-- /banner -->
  </div>
  <!-- /top -->
</div>
<!-- /nav -->
</div>
<!-- /header -->
<div class="w960 clear center mt1 cmt-box">
  <div class="sp-title">
    <h2>评论文章:<a href='<?php echo $GLOBALS['arcurl']; ?>'><?php echo $GLOBALS['title']; ?></a></h2>
    （网友评论仅供网友表达个人看法，并不表明本站同意其观点或证实其描述）
  </div>
  <div>
    <dl class="tbox">
      <dt style="display:none;"> <strong>评论列表（网友评论仅供网友表达个人看法，并不表明本站同意其观点或证实其描述）</strong> <span class="label"> <a href='feedback.php?aid=<?php echo $GLOBALS['aid']; ?>&ftype=good' <?php if($ftype=='good') echo "class='thisclass'"; ?>>只看好评</a> <a href='feedback.php?aid=<?php echo $GLOBALS['aid']; ?>&ftype=bad' <?php if($ftype=='bad') echo "class='thisclass'"; ?>>只看差评</a> <a href='feedback.php?aid=<?php echo $GLOBALS['aid']; ?>&ftype=feedback' <?php if($ftype=='feedback') echo "class='thisclass'"; ?>>只看中立</a> <a href='feedback.php?aid=<?php echo $GLOBALS['aid']; ?>' <?php if($ftype=='') echo "class='thisclass'"; ?>>全部评论</a> </span> </dt>
      <dd>
        <div class="dede_comment">
          <div class='decmt-box1'>
            <ul>
            <li id="commetcontentNew"></li>
            
              <?php
$atts = array();
$atts['tagname'] = 'datalist';
$blockValue = $this->refObj->GetArcList($atts,$this->refObj,$fields); 
if(is_array($blockValue)){
foreach( $blockValue as $key=>$fields )
{

  if($fields['userid']!='') $spaceurl = $cfg_basehost.'/member/index.php?uid='.$fields['userid'];
  else $spaceurl = "#";
  if($fields['username']=='匿名') $spaceurl = "#";
  $fields['bgimg'] = 'cmt-neu.gif';
  $fields['ftypetitle'] = '该用户表示中立';
  if($fields['ftype']=='bad') {
  	$fields['bgimg'] = 'cmt-bad.gif';
  	$fields['ftypetitle'] = '该用户表示差评';
  }
  else if($fields['ftype']=='good') {
  	$fields['bgimg'] = 'cmt-good.gif';
  	$fields['ftypetitle'] = '该用户表示好评';
  }
  if(empty($fields['mface']))
  {
    if($fields['sex']=="女") $fields['mface']="/member/templets/images/dfgirl.png";
    else $fields['mface']="/member/templets/images/dfboy.png";
  }
?>
              <li> <a href='<?php echo $spaceurl; ?>' class='plpic'><img src='<?php echo $fields['mface'];?>'  height='40' width='40'/></a> <span class="title"> <?php echo $fields['username']; ?><img src="<?php echo $GLOBALS['cfg_templets_skin']; ?>/images/<?php echo $fields['bgimg']; ?>" /></span>
                <div class="comment_act"><span class="fr"> <span id='goodfb<?php echo $fields['id']; ?>'><a href="#" onclick="postBadGood('goodfb',<?php echo $fields['id']; ?>)">支持</a>[<?php echo $fields['good']; ?>]</span> <span id='badfb<?php echo $fields['id']; ?>'><a href="#" onclick="postBadGood('badfb',<?php echo $fields['id']; ?>)">反对</a>[<?php echo $fields['bad']; ?>]</span> <span class='quote'><a href="javascript:ajaxFeedback(<?php echo $fields['id']; ?>,<?php echo $fields['id']; ?>,'quote');">引用</a></span></span> <?php echo MyDate('m-d H:i',$fields['dtime']); ?>发表</div>
                <p>
                <?php
                echo RemoveXSS(Quote_replace(parseSmileys($fields['msg'],$cfg_cmspath.'/images/smiley')));
                ?></p>
              </li>
               <div id="ajaxfeedback_<?php echo $fields['id']; ?>"></div>
              <?php
}
}
$atts = array();
$atts['tagname'] = 'pagelist';
$atts['listitem'] = 'info,index,end,pre,next,pageno';
$atts['listsize'] = '5';
 echo $this->refObj->GetPageList($atts,$this->refObj,$fields); 
?>

            </ul>
          </div>
        </div>
        <!-- /dede_comment -->
      </dd>
    </dl>
  </div>
  <div class="mt1">
    <dl class="tbox">
      <dt> <strong>发表评论</strong> </dt>
      <dd>
        <div class="dede_comment_post">
          <form action="feedback.php" method="post" name="feedback">
            <input type="hidden" name="action" value="send" />
            <input type="hidden" name="comtype" value="comments">
            <input type="hidden" name="aid" value="<?php echo $aid; ?>" />
            <input type="hidden" name="isconfirm" value="yes" />
            <div class="dcmp-title"> <small>请自觉遵守互联网相关的政策法规，严禁发布色情、暴力、反动的言论。</small> </div>
            <!-- /dcmp-title -->
            <div class="dcmp-stand"> <strong>评价:</strong>
              <input type="radio" name="feedbacktype" checked="1" value="feedback" id="dcmp-stand-neu" />
              <label for="dcmp-stand-neu"><img src="<?php echo $GLOBALS['cfg_templets_skin']; ?>/images/cmt-neu.gif" />中立</label>
              <input type="radio" name="feedbacktype" value="good" id="dcmp-stand-good" />
              <label for="dcmp-stand-good"><img src="<?php echo $GLOBALS['cfg_templets_skin']; ?>/images/cmt-good.gif" />好评</label>
              <input type="radio" name="feedbacktype" value="bad" id="dcmp-stand-bad" />
              <label for="dcmp-stand-bad"><img src="<?php echo $GLOBALS['cfg_templets_skin']; ?>/images/cmt-bad.gif" />差评</label>
            </div>
            <!-- /dcmp-stand -->
            <div class="clr"></div>
            <!-- /dcmp-mood -->
            <div class="dcmp-content1">
            <script type="text/javascript">
					//<![CDATA[
					window.CKEDITOR_BASEPATH='<?php echo $GLOBALS['cfg_cmsurl']; ?>/include/ckeditor/';
					//]]>
                    </script>
            <script type="text/javascript" src="<?php echo $GLOBALS['cfg_cmsurl']; ?>/include/ckeditor/ckeditor.js?t=B8DJ5M3"></script>
            <?php
GetEditor('msg','',100,'Feedback','print','false',true);
?>
            </div>
            <!-- /dcmp-content -->
            <div class="dcmp-post">
              <!--未登陆-->
              <div class="dcmp-userinfo"> 用户名：
                <?php if($cfg_ml->M_ID <= 0) { ?>
                <input name="username" type="text" id="username" size="10" class="nb" />
                （<a href="../member/index_do.php?fmdo=user&dopost=regnew" target="_blank"><u>注册新用户</u></a>）
                <?php
                    }else{  echo $cfg_ml->M_LoginID."&nbsp;"; }
                   ?>
                <input name="notuser" type="checkbox" id="notuser" value="1" />
                匿名评论
                <?php if(preg_match("/4/",$safe_gdopen)){ ?>
                验证码：
                <input name="validate" type="text" id="validate"  style="width:80px;margin-right:6px;text-transform:uppercase;" class="nb" />
                <img src='../include/vdimgck.php' width='60' height='24' style="cursor:pointer" onclick="this.src=this.src+'?'" title="点击我更换图片" alt="点击我更换图片" />
                <?php } ?>
                <button type="submit" class="btn-2">发表评论</button>
              </div>
              <!-- /dcmp-submit -->
            </div>
            <!-- /dcmp-post -->
          </form>
        </div>
        <!-- /dede_comment_post -->
      </dd>
    </dl>
  </div>
</div>
<?php include $this->CompilerInclude("../fangyafei/footer.htm");
 ?>
<!-- /footer -->
</body>
</html>