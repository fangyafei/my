<span><a href="{dede:global.cfg_basehost/}{dede:field.typeurl/}" [field:rel/] [field:id runphp='yes'] if (@me == $GLOBALS['thisid'] ) @me = "class='active' " ;[/field:id]>全部博客</a></span>
                {dede:channel row='9' type='son' currentstyle="<span><a href='~typelink~' class='active' [field:rel/]>~typename~</a></span>"} 
                    <span><a href='[field:global.cfg_basehost/][field:typelink/]' [field:rel/] [field:id runphp='yes'] if (@me == $GLOBALS['thisid'] ) @me = "class='active' " ;[/field:id]>[field:typename/]</a></span> 
                {/dede:channel}