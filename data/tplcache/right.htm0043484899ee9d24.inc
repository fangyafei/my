<?php
$z[0]=Array("global","",58,83);
$z[0][4]['name']="cfg_cmsurl";
$z[1]=Array("arclist","\r\n                    <li>\r\n                        <a href=\"[field:arcurl/]\">[field:title/]</a> \r\n                        <span><small>人气:</small>[field:click/]</span>\r\n                    </li>\r\n                ",1035,1309);
$z[1][4]['flag']="c";
$z[1][4]['titlelen']="42";
$z[1][4]['row']="10";
$z[2]=Array("tag","\r\n                <dl><a href='[field:link/]'>[field:tag /]</a></dl>\r\n            ",1558,1760);
$z[2][4]['row']="30";
$z[2][4]['getall']="1";
$z[2][4]['sort']="month";
$z[2][4]['currentstyle']="<dl><a href='~link~' class='active'>~tag~</a></dl>";
$z[3]=Array("arclist","\r\n                    <li>\r\n                        <a href=\"[field:arcurl/]\">[field:title/]</a> \r\n                        <span><small>人气:</small>[field:click/]</span>\r\n                    </li>\r\n                ",2025,2292);
$z[3][4]['row']="10";
$z[3][4]['orderby']="click";
$z[4]=Array("channel","\r\n                    <li>\r\n                        <a href=\"[field:typelink/]\">[field:typename/]</a>\r\n                    </li>\r\n                ",2576,2771);
$z[4][4]['typeid']="1";
$z[4][4]['type']="son";
$z[5]=Array("type","\r\n                    [field:id function=\"GetTotalArc(@me)\" /]\r\n                ",3003,3115);
$z[5][4]['typeid']="1";
$z[6]=Array("sql","\r\n                    [field:b-typenum/]\r\n                ",3183,3333);
$z[6][4]['sql']="select count(id) as 'b-typenum' from fang_arctype where topid=1";
$z[7]=Array("type","\r\n                    [field:id function=\"GetTotalArc(@me)\" /]\r\n                ",3395,3507);
$z[7][4]['typeid']="4";
$z[8]=Array("global","",3791,3818);
$z[8][4]['name']="cfg_basehost";
$z[9]=Array("flinktype","\r\n                <p>{dede:flink/}</p> \r\n            ",3929,4030);
$z[9][4]['type']="dedecms";

?>