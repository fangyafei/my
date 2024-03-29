<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang;?>" />
<title><?php echo $navtitle;?>-<?php echo $cfg_ask_sitename;?></title>
<link href="static/style/service.css" rel="stylesheet" type="text/css" media="screen" />
<link href="static/style/tb-box.css" rel="stylesheet" type="text/css" />
<script src="/include/dedeajax2.js" type="text/javascript"></script>
<script src="static/js/j.js" type="text/javascript"></script>
<script src="static/js/gotop.js" type="text/javascript"></script>
<script src="static/js/box.js" type="text/javascript"></script>
<script src="static/js/question.js" type="text/javascript"></script>
</head>
<body>
<div id="wrap">
  <?php include(DEDEASK."/templates/default/header.htm"); ?>
  <div class="subnav"><span class="fl place"><?php echo $nav?></span></div>
  <div id="main">
    <div class="fl col1 mt10">
      <div class="bor1 viewbox">
        <h2 class="<?php echo $question['status'];?>"><?php echo $question['title']; ?></h2>
        <div class="ml10 send_info"><span>悬赏分：<?php echo $question['reward']; ?></span>|<span><?php if($question['dbstatus'] == 0){?>
		离问题结束还有<?php echo $question['toendday'];?> 天 <?php echo $question['toendhour'];?> 小时
<?php }else if($question['dbstatus'] == 2){?>
		<font color="red">已过期</font>
<?php }else{ ?>
		解决时间：<?php echo MyDate('Y-m-d',$question['solvetime']); }?></span><?php if($question['dbstatus'] == 0 && $cfg_ml->M_ID == $question['uid']){?>
        |<span><a href='?ct=question&ac=upreward&amp;askaid=<?php echo $askaid; ?>&height=300&amp;width=450' class="thickbox");>提高悬赏</a>：提高悬赏分,以提高问题的关注度</span>
        <?php }?></div>
        <div class="content_box">
          <div class="content_show">
            <?php echo $question['content'];?>
          </div>
          <?php if(($question['dbstatus'] == 0 && $cfg_ml->M_ID == $question['uid']) || $cfg_ml->isAdmin == 1){?>
          <div class="operate">          
          <a href='?ct=question&ac=edit&askaid=<?php echo $askaid;?>'>编辑</a>
          <?php if($cfg_ml->isAdmin == 1){?>
          <a href='#' onClick="javascript:del(<?php echo $askaid;?>,1)">删除</a>
          <?php }?>
          </div>
          <?php }?>
        </div>        
      </div> 
      <?php if(!empty($digestanswer)) {?>
      <div class="bor1 mt10 pd10 best_answer">
        <div class="replyinfo fr">回答者：
         <?php if($digestanswer['anonymous'] == 1){?>热心网友
        <?php }else{?>
        <a href="?ct=myask&ac=view&mid=<?php echo $digestanswer['uid'];?>"><?php echo $digestanswer['username'];?></a>
        <?php }?>
        <span>|</span><span class='grade'><?php echo $digestanswer['honor'];?></span><span>|</span><?php echo MyDate('Y-m-d H:i',$digestanswer['dateline']);?></div>
        <div class="best_answer_show">
        <?php echo $digestanswer['content']; if($cfg_ml->isAdmin == 1){?>
        <div class="operate"><a href="#" onClick="javascript:edit_reply(<?php echo $digestanswer['id'];?>);">修改</a></div>
         <?php }?>
        </div>
        <div class="mark" id="mark">
      <dl>
        <dt><strong>您觉得最佳答案好不好？ </strong></dt>
        <dd> <a href="#"  onclick="rate('mark',<?php echo $digestanswer['id']; ?>,'good')"><img src="static/images/mark_g.gif" width="14" height="16" />好</a> <span><?php echo $goodrateper; ?>% (<?php echo $goodrate; ?>)</span> </dd>
        <dd> <a href="#"  onclick="rate('mark',<?php echo $digestanswer['id']; ?>,'bad')"><img src="static/images/mark_b.gif" width="14" height="16" />不好</a> <span><?php echo $badrateper; ?>% (<?php echo $badrate; ?>)</span> </dd>
        <dt>(目前有 <?php echo $ratenum; ?> 个人评价)</dt>
      </dl>
    </div>
      </div>
      <?php }if($publisher == 0 && $myanswer == 0){?>
      <div class="bor1 mt10 pd10 reply">
      	<form method="post" name="answer" action="?ct=question&ac=answer&amp;askaid=<?php echo $question['id']; ?>">
         <h3><span class="fl">我来回答这个问题</span></h3>
         <div class="mt10 reply_edit"><?php GetFck("content","",400);?></div>
         <div class="reply_edit_btn"><span class="fl mt10">回答即可得<?php echo $cfg_ask_answerscore;?>分，回答被采纳则获得悬赏分以及奖励<?php echo $cfg_ask_bestanswer;?>分。</span><span class="fl ml10 mt10"><label>
         <?php 
         if($GLOBALS['cfg_ask_guestanswer'] == 'Y')
         {
         ?>
         <input name="anonymous" type="checkbox" value="1"/>匿名回答</label><?php
         }
         ?></span><button type="submit" class="btn fr">提交回答</button></div>
        </form>
      </div>
      <?php }if($answers){ foreach($answers as $answer){?>
      <div class="bor1 mt10  pd10 reply_box">
        <?php if($answer['floor'] == 1){
        if(!empty($digestanswer)){
        	echo '<h3>其 他 回 答<span>共'.$answernum.'条</span></h3>';
        }else{
        	echo '<h3>回 答<span>共'.$answernum.'条</span></h3>';
        }}
        ?> 
        <h4><?php echo $answer['floor'];?>楼
        <?php if($cfg_ml->isAdmin == 1){?>
        <span><a href='#' onClick="javascript:del(<?php echo $answer['id'];?>,3)">删除</a></span>
        <?php }?>
        </h4>
        <div class="pd10 reply_show"><?php echo $answer['content'];?></div>
        <?php if($answer['uid'] == $cfg_ml->M_ID && $question['dbstatus'] == 0){?>
		<div class="edit_btn"><a href="#" onClick="javascript:edit_reply(<?php echo $answer['id'];?>);">修改</a></div>
<?php }if($publisher == 1 && $question['dbstatus'] == 0){?>
<div class="edit_btn"><a href="#" onClick="location.href='?ct=question&ac=adopt&amp;id=<?php echo $answer['id'];?>'">采纳最佳答案</a></div>
<?php }?>
        <div class="replyinfo tright">回答者：
          <?php 
          if($answer['anonymous'] == 1){?>
          热心网友
<?php }else{?>
        <a href="?ct=myask&ac=view&mid=<?php echo $answer['uid'];?>"><?php echo $answer['username'];?></a>
        <?php }?>
        <span>|</span><span class='grade'><?php echo $answer['honor'];?></span><span>|</span><?php echo MyDate('Y-m-d H:i',$answer['dateline']);?></div>
      </div>
      <?php }}?>
    </div>
    <div class="fr col2 mt10">
    <?php if($publisher == 1){ ?>
      <div class="bor1 pd10 userinfo">
        <a href="#" class="fl"><img src="<?php echo $question['face'];?>" height="48" width="48" /></a>
        <p class="u_name"><a href="?ct=myask&ac=view&mid=<?php echo $question['uid'];?>"><?php echo $question['username'];?></a><span class='grade'><?php echo $question['honor'];?></span></p>
        <p class="links"><a href="?ct=myask">我的提问</a><a href="?ct=myask&ac=answer">我的回答</a><a href="/member">会员中心</a></p>
        <?php if(get_pms($question['uid']) > 0){?>
        <span><a href="/member/pm.php">（<?php echo get_pms($question['uid']);?>）条消息等待处理</a></span>
        <?php } ?>
      </div>
      <?php }else{ ?>
      <div class="bor1 pd10 userinfo bg2">
        <a href="#" class="fl"><img src="static/images/user.gif" height="48" width="48" /></a>
        <p class="u_name">
        <?php if($question['anonymous'] == 1){?>网友
        <?php }else{?>
        <a href="?ct=myask&ac=view&mid=<?php echo $question['uid'];?>"><?php echo $question['username'];?></a>
        <?php }?>
<span class='grade'><?php echo $question['honor'];?></span></p>
        <p>注册时间: <?php echo $cfg_ml->M_JoinTime;?></p>
      </div>
      <?php } ?>
    </div>
  </div><!--main end-->
  <?php include(DEDEASK."/templates/default/footer.htm"); ?>
  <div class="gotop"></div>
</div>
</body>
</html>