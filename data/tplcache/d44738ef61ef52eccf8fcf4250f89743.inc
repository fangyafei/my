<!-- list start -->
                <div class="list">
                    <div class="p-time" id="[field:id/]">
                        <p class="day">[field:pubdate function="strftime('%d','@me')"/] </p>
                        <p class="month">[field:pubdate function="strftime('%m','@me')"/]</p>
                        <p class="year" id="year-[field:id/]">[field:pubdate function="strftime('%Y','@me')"/]</p>
                        <script type="text/javascript">
                        $(document).ready(function(){
                            $("#[field:id/]").mouseover(function(){
                                $("#[field:id/] .day").css('display','none');
                                //$("#[field:id/] .month").css('display','none');
                                $("#[field:id/] .year").css('display','block');
                            });
                            $("#[field:id/]").mouseout(function(){
                                $("#[field:id/] .day").css('display','block');
                                $("#[field:id/] .month").css('display','block');
                                $("#[field:id/] .year").css('display','none');
                            });
                        });
                        </script>
                    </div>
                    <div class="detail">
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
                <!-- list start -->