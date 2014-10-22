<?php
function litimgurls($imgid=0)
{
    global $lit_imglist,$dsql;
    //获取附加表
    $row = $dsql->GetOne("SELECT c.addtable FROM #@__archives AS a LEFT JOIN #@__channeltype AS c 
                                                            ON a.channel=c.id where a.id='$imgid'");
    $addtable = trim($row['addtable']);
    
    //获取图片附加表imgurls字段内容进行处理
    $row = $dsql->GetOne("Select imgurls From `$addtable` where aid='$imgid'");
    
    //调用inc_channel_unit.php中ChannelUnit类
    $ChannelUnit = new ChannelUnit(2,$imgid);
    
    //调用ChannelUnit类中GetlitImgLinks方法处理缩略图
    $lit_imglist = $ChannelUnit->GetlitImgLinks($row['imgurls']);
    
    //返回结果
    return $lit_imglist;
}

/* 
* 返回符合记录的文章数量 
* @description DEDE不允许执行子查询，解决栏目下文章统计的问题 
* @param $level 为真时查询所有子类目 
* */ 
function getTotalArcByTid($tid, $level=TRUE) { 
    global $dsql; 
    $level==TRUE && $tid = GetSonTypeID($tid); 
    $sql = "SELECT count(id) as total from `dede_archives` where typeid in($tid)"; 
    $result = $dsql->GetOne($sql); 
    return $result['total']; 
} 
/* 
* 递归获取符合条件的子栏目 
* @param $tid 栏目ID 
* @return string 
* */ 
function GetSonTypeID($tid) 
{ 
    global $dsql; 
    $dsql->SetQuery("Select id From `dede_arctype` where reid in($tid) And ishidden<>1 order by sortrank"); 
    $dsql->Execute($tid); 
    $typeid = ''; 
    while($row=$dsql->GetObject($tid)) 
    { 
        $typeid .= "{$row->id},"; 
        $typeid .= GetSonTypeID($row->id); 
    } 
    return trim($typeid,','); 
}