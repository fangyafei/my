<li id="nav-li-{dede:field name='id'/}">
        		<a href="{dede:global.cfg_basehost/}{dede:field name='typeurl'/}" title="{dede:field name='typename'/}">{dede:field name='typename'/}</a>
        	</li>
        	{dede:channel type='top'  noself='yes'}  
			<div class="nav-down" id="nav-down-[field:id/]" >
				<ul>
					<li><a href='[field:typeurl/]' [field:rel/]><span>[field:typename/]</span></a></li>
				</ul>
			</div>
			{/dede:channel}
			<script>
				$(function($){
					UE.menu.hover({
						btnEl : $('#nav-li-{dede:field name='id'/}'),	//下拉菜单触发按钮jQuery对象
						menuEl : $('#nav-down-{dede:field name='id'/}'),	//下拉菜单外框jQuery对象
						dir : 'right',	//控制下拉菜单对齐方式 默认为左侧对齐 （可选）
						hoverCss : 'active',  //鼠标滑过样式（可选）
					});
				})
			</script>