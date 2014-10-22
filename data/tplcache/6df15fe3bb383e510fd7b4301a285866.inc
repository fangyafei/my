
        	<li id="nav-li-[field:id/]">
        		<a href="[field:global.cfg_basehost/][field:typeurl/]" title="[field:typename/]">[field:typename/]</a>
        	</li>
			<script>
				$(function($){
					UE.menu.hover({
						btnEl : $('#nav-li-[field:id/]'),	//下拉菜单触发按钮jQuery对象
						menuEl : $('.nav-down'),	//下拉菜单外框jQuery对象
						dir : 'middle',	//控制下拉菜单对齐方式 默认为左侧对齐 （可选）
						hoverCss : 'active',  //鼠标滑过样式（可选）
					});
				})
			</script>
        