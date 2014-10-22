<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><div class="footer">
    <div class="foot">
        <span> <?php echo $GLOBALS['cfg_beian']; ?> </span>
        <script type="text/javascript">
			var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
			document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F526916c95057c21a41cd83e422400c2b' type='text/javascript'%3E%3C/script%3E"));
		</script>

    </div>
</div><!-- footer end -->
<!-- 返回顶部和扫描二维码 -->
<div id="elevator_item"> <a id="elevator" onclick="return false;" title="回到顶部"></a> <a class="qr"></a>
  <div class="qr-popup"> <a class="code-link"> <img class="code" src="http://www.fangyafei.com/templets/fangyafei/images/qq.jpg"/> </a> <span>加他为QQ好友</span>
    <div class="arr"></div>
  </div>
</div>
<script type="text/javascript">
$(function() {
	$(window).scroll(function(){
		var scrolltop=$(this).scrollTop();		
		if(scrolltop>=200){		
			$("#elevator_item").show();
		}else{
			$("#elevator_item").hide();
		}
	});		
	$("#elevator").click(function(){
		$("html,body").animate({scrollTop: 0}, 500);	
	});		
	$(".qr").hover(function(){
		$(".qr-popup").show();
	},function(){
		$(".qr-popup").hide();
	});	
});
</script>