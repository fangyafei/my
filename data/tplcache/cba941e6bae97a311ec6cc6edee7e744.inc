
                    <li><a href='[field:link/]'>[field:tag /]</a></li>
                {/dede:tag
            </ul>
        </div>
        <p class="hr"></p>
    </div><!-- 热门标签 结束 -->
    <!-- 热门文章 -->
    <div class="side">
        <div class="title">热门文章</div>
        <div class="content">
            <ul>                
                {dede:arclist row=10 orderby=click}
                    <li>
                        <a href="[field:arcurl/]">[field:title/]</a> 
                        <span><small>人气:</small>[field:click/]</span>
                    </li>
                {/dede:arclist}
            </ul>
        </div>
        <p class="hr"></p>
    </div><!-- 热门文章 结束 -->
    <!-- 热门文章 -->
    <div class="side">
        <div class="title">博客分类</div>
        <div class="content">
            <ul>                
                {dede:channel typeid=1 type='son'}
                    <li>
                        <a href="[field:typelink/]">[field:typename/]</a>
                    </li>
                {/dede:channel}
            </ul>
        </div>
        <p class="hr"></p>
    </div><!-- 热门文章 结束 -->
    <!-- count start -->
    <div class="count">
        <div class="count-n">
            <p>博客数：
                {dede:type typeid=1}
                    [field:id function="GetTotalArc(@me)" /]
                {/dede:type}
            </p>
            <p>博客分类：
                {dede:sql sql="select count(id) as 'b-typenum' from fang_arctype where topid=1 "}
                    [field:b-typenum/]
                {/dede:sql}
            </p>
            <p>工具：
                {dede:type typeid=4}
                    [field:id function="GetTotalArc(@me)" /]
                {/dede:type}</p>
            <p>访问：46384</p>
        </div>
    </div><!-- count start -->
    <!-- 友好网站 start -->
    <div class="side">
        <div class="count-n">
            <div class="title clear-padding">
                友好网站
                <!-- <a href="{dede:global.cfg_basehost