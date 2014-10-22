<li id="nav-li-{dede:field name='id'/}">
        		<a href="{dede:global.cfg_basehost/}{dede:field name='typeurl'/}" title="{dede:field name='typename'/}">{dede:field name='typename'/}</a>
        	</li>
        	{dede:channel type='son'  noself='yes'}  
			<div class="nav-down" id="nav-li-{dede:field name='id'/}" style="display:;">
				<ul>
					<li><a href='[field:typeurl/]' [field:rel/]><span>[field:typename/]</span></a></li>
				</ul>
			</div>
			{/dede:channel}