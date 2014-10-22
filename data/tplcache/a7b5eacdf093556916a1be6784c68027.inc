<!-- list start -->
                <div class="list">
                    <div class="p-time">
                        <p class="day">[field:pubdate function="GetDateTimeMK('d','@me')"/] </p>
                        <p class="month">May</p>
                    </div>
                    <div class="detail width-900">
                        <div class="title">
                            <h2>
                                <a href="[field:global.cfg_basehost/][field:arcurl/]" title="[field:title/]" target="_blank">[field:title/]</a>
                            </h2>
                        </div>
                        <div class="des">
                            [field:description/]<br/>
                            <a href="[field:global.cfg_basehost/][field:arcurl/]" class="go-on-ready" title="[field:title/]" target="_blank"><我还想看></a>
                        </div>
                        <div class="title">
                            <p class="info clear-text-align margin-top10">
                                <span>作者：[field:writer/]</span>
                                <span><a href="[field:typeurl/]">[field:typename/]</a></span>
                                <span>Tags：[field:id function=GetTags_list('@me')/]</span>
                            </p>
                        </div>
                    </div>
                </div><!-- list end -->