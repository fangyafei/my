<?php
$z[0]=Array("global","",297,325);
$z[0][4]['name']="cfg_soft_lang";
$z[1]=Array("field","",338,361);
$z[1][4]['name']="seotitle";
$z[2]=Array("global","",362,388);
$z[2][4]['name']="cfg_webname";
$z[3]=Array("field","",429,458);
$z[3][4]['name']="keywords";
$z[4]=Array("field","",498,556);
$z[4][4]['name']="description";
$z[4][4]['function']="html2text(@me)";
$z[5]=Array("global","",574,601);
$z[5][4]['name']="cfg_basehost";
$z[6]=Array("global","",601,633);
$z[6][4]['name']="cfg_templets_skin";
$z[7]=Array("global","",715,742);
$z[7][4]['name']="cfg_basehost";
$z[8]=Array("global","",742,774);
$z[8][4]['name']="cfg_templets_skin";
$z[9]=Array("global","",879,906);
$z[9][4]['name']="cfg_basehost";
$z[10]=Array("global","",906,938);
$z[10][4]['name']="cfg_templets_skin";
$z[11]=Array("include","",1058,1089);
$z[11][4]['file']="head.htm";
$z[12]=Array("include","",1130,1161);
$z[12][4]['file']="logo.htm";
$z[13]=Array("php","\r\n                \$GLOBALS['thisid'] = intval(\$refObj->Fields['id']);\r\n            ",1240,1344);
$z[14]=Array("channelartlist","\r\n                <span><a href=\"{dede:global.cfg_basehost/}{dede:field.typeurl/}\" {dede:php} if(\$GLOBALS['thisid'] == 1) echo \"class='active'\"; {/dede:php}>全部博客</a></span>\r\n                {dede:channel row='9' type='son' currentstyle=\"<span><a href='~typelink~' class='active' [field:rel/]>~typename~</a></span>\"} \r\n                    <span><a href='[field:global.cfg_basehost/][field:typelink/]' [field:rel/] [field:id runphp='yes'] if (@me == \$GLOBALS['thisid'] ) @me = \"class='active' \" ;[/field:id]>[field:typename/]</a></span> \r\n                {/dede:channel}\r\n            ",1358,2015);
$z[14][4]['type']="top";
$z[14][4]['typeid']="1,1";
$z[15]=Array("field","",2056,2078);
$z[15][4]['name']="position";
$z[16]=Array("list","\r\n                <!-- list start -->\r\n                <div class=\"list\">\r\n                    <div class=\"p-time\" id=\"[field:id/]\">\r\n                        <p class=\"day\">[field:pubdate function=\"strftime('%d','@me')\"/] </p>\r\n                        <p class=\"month\">[field:pubdate function=\"strftime('%m','@me')\"/]</p>\r\n                        <p class=\"year\" id=\"year-[field:id/]\">[field:pubdate function=\"strftime('%Y','@me')\"/]</p>\r\n                        <script type=\"text/javascript\">\r\n                        \$(document).ready(function(){\r\n                            \$(\"#[field:id/]\").mouseover(function(){\r\n                                \$(\"#[field:id/] .day\").css('display','none');\r\n                                //\$(\"#[field:id/] .month\").css('display','none');\r\n                                \$(\"#[field:id/] .year\").css('display','block');\r\n                            });\r\n                            \$(\"#[field:id/]\").mouseout(function(){\r\n                                \$(\"#[field:id/] .day\").css('display','block');\r\n                                \$(\"#[field:id/] .month\").css('display','block');\r\n                                \$(\"#[field:id/] .year\").css('display','none');\r\n                            });\r\n                        });\r\n                        </script>\r\n                    </div>\r\n                    <div class=\"detail width-900\">\r\n                        <div class=\"title\">\r\n                            <h2>\r\n                                <a href=\"[field:global.cfg_basehost/][field:arcurl/]\" title=\"[field:title/]\" target=\"_blank\">[field:title/]</a>\r\n                            </h2>\r\n                        </div>\r\n                        <div class=\"des\">\r\n                            [field:description/]<br/>\r\n                            <a href=\"[field:global.cfg_basehost/][field:arcurl/]\" class=\"go-on-ready\" title=\"[field:title/]\" target=\"_blank\"><我还想看></a>\r\n                        </div>\r\n                        <div class=\"title\">\r\n                            <p class=\"info clear-text-align margin-top10\">\r\n                                <span>作者：[field:writer/]</span>\r\n                                <span><a href=\"[field:global.cfg_basehost/][field:typeurl/]\">[field:typename/]</a></span>\r\n                                <span>Tags：[field:id function=GetTags_list('@me')/]</span>\r\n                                <span>浏览量：[field:click/]</span>\r\n                            </p>\r\n                        </div>\r\n                    </div>\r\n                </div><!-- list end -->	\r\n                ",2241,4891);
$z[16][4]['pagesize']="10";
$z[16][4]['titlelen']="80";
$z[17]=Array("pagelist","",4999,5077);
$z[17][4]['listitem']="info,index,end,pre,next,pageno,option";
$z[17][4]['listsize']="5";
$z[18]=Array("include","",5296,5330);
$z[18][4]['file']="footer.htm";

?>