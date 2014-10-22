<li id="nav-li-{dede:field name='id'/}">
        		<a href="{dede:global.cfg_basehost/}{dede:field name='typeurl'/}" title="{dede:field name='typename'/}">{dede:field name='typename'/}</a>
        	</li>
        	 
			<div class="nav-down" id="nav-down-{dede:field name='id'/}" >
				<ul>
					{dede:channel type='son'  noself='yes'} 
					<li><a href='[field:typeurl/]' [field:rel/]><span>[field:typename/]</span></a></li>
					{/dede:channel}
				</ul>
			</div>
			
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