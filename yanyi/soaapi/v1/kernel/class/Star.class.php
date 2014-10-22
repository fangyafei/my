<?php
class Star{
	var $wdwl_url = '';
	var $error = '';
	var $token = '';

  function __construct() {

  }

  /**
   * 设置参数
   */
  public function set($key, $value) {
    $this->$key = $value;
  }
  
  /**
   * 获取参数
   */
  public function get($key) {
    return isset($this->$key) ? $this->$key : NULL;
  }
  
  
  // 获取广告轮播图

  function advertInfo($starID){
  	global $json,$db,$ecs;
  	$time = date('Y-m-d');
  	$sql  = 'SELECT *'.
  			' FROM ' .$ecs->table('fabstar_photo'). ' AS p ' .
  			" WHERE p.star_id = '$starID' and p.if_cover = 1  order by p.add_time desc";
  	$row = $db->getAll($sql);
  	$photo =array();
  	if ($row)
  	{
  		foreach ($row as $k =>$v){
  			$photo[$k]['advertID'] = $v['id'];
  			$photo[$k]['advertUrl'] = IMG_PREFIX.$v['pic_path_b'];
  		}
  	}
  	return $json->encode($photo);  	
  }
  
  //明星公告 单条 
  function noticeList($starID,$pageSize,$page){
  	global $json,$db,$ecs;
   	if($page){
  		$page = $page-1;
  	}
  	$sql  = 'SELECT *'.
  			' FROM ' .$ecs->table('fabstar_notice'). ' AS p ' .
  			" WHERE p.star_id = '$starID' order by p.add_time desc limit ".$page*$pageSize.",".$pageSize;
  	$row = $db->getAll($sql);
  	$list = array();
  	if ($row)
  	{
  	  	$notice =array();
  		foreach ($row as $k =>$v){
  			$notice[date('Y-m-d',$v['add_time'])][] = $v;
  		}
  		foreach ($notice as $key=>$value){
  			$data['publishtime'] = $key;

  			foreach ($value as $k=>$v){
  				$sql_p  = 'SELECT cover FROM ' .$ecs->table('fabstar_notice_photo').'where notice_id = '.$v['id'];
  				$row_p = $db->getAll($sql_p);
  				
  				foreach ($row_p as $key=>$p){
  					if($p){
  						$value[$k]["cover"][$key]   = IMG_PREFIX.current($p);
  					}else{
  						$value[$k]["cover"]   = array();
  					}
  				}

  				$value[$k]["content"] = $v['content'];
  				//mb_substr($v['content'],0,20,'utf-8'); 
  			}
  			$data['noticeList'] = $value;
			$list[] = $data;
  		} 
  	}
  	return $json->encode($list);
  	
  }
  
  //明星公告详情
  function noticeInfo($starID,$noticeID){
  	global $json,$db,$ecs;
  	$sql  = 'SELECT *'.
  			' FROM ' .$ecs->table('fabstar_notice'). ' AS p ' .
  			" WHERE p.star_id = ".$starID." and p.id = ".$noticeID." order by p.add_time desc";
  	$row = $db->getAll($sql);
  	$row = current($row);
  	//获取多图片
  	$sql_photo  = 'SELECT id,cover FROM ' .$ecs->table('fabstar_notice_photo').'where notice_id = '.$row['id'];
  	$row_photo  = $db->getAll($sql_photo);
  	
  	$row['content'] = mb_substr($row['content'],0,20,'utf-8');
  	$row['cover']   = IMG_PREFIX.$row_photo['cover'];

  	return $json->encode($row);
  	 
  }
  //获取明星照片列表
  function photoList($starID,$pageSize,$page){
  	global $json,$db,$ecs;
   	if($page){
  		$page = $page-1;
  	}
  	$sql  = 'SELECT *'.
  			' FROM ' .$ecs->table('fabstar_photo'). ' AS p ' .
  			" WHERE p.star_id = '$starID' order by p.add_time desc limit ".$page*$pageSize.",".$pageSize;
  	$row = $db->getAll($sql);
  	$list = array();
  	if ($row)
  	{
  		$photo =array();
  		foreach ($row as $k =>$v){
  			$v['pic_path_s'] = IMG_PREFIX.$v['pic_path_s'];
  			$v['pic_path_b'] = IMG_PREFIX.$v['pic_path_b'];
  			$photo[date('Y-m-d',$v['add_time'])][] = $v;
  		}
  		foreach ($photo as $key=>$value){
  			$data['publishtime'] = $key;
  			$data['photoList'] = $value;
			$list[] = $data;
  		} 		
  	}
  	
  	return $json->encode($list); 
  	
  }
  

  //获取专辑
  function musicAlbum($starID,$pageSize,$page){
  	global $json,$db,$ecs;
  	if($page){
  		$page = $page-1;
  	}
  	$sql  = "SELECT * FROM " . $ecs->table('fabstar_musicalbum') .
  	" WHERE star_id = '$starID' order by add_time desc limit ".$page*$pageSize.",".$pageSize;
  	$row = $db->getAll($sql);
  	$musicAlbum =array();
  	if ($row)
  	{
  		foreach ($row as $k =>$v){
  			$musicAlbum[$k]['albumID']    = $v['id'];
  			$musicAlbum[$k]['albumName']  = $v['album_name'];
  			$musicAlbum[$k]['albumCover'] = IMG_PREFIX.$v['album_cover'];
  		}
  	}
  	return $json->encode($musicAlbum);
  }
  
  
  //获取单曲
  function musicInfo($starID,$albumID,$pageSize,$page){
  	global $json,$db,$ecs;
   	if($page){
  		$page = $page-1;
  	}
  	if($albumID == -1){
  		$sql  = "SELECT * FROM " . $ecs->table('fabstar_music') .
  		" WHERE star_id = ".$starID." order by add_time desc limit ".$page*$pageSize.",".$pageSize;
  	}else{
  		$sql  = "SELECT * FROM " . $ecs->table('fabstar_music') .
  		" WHERE star_id = ".$starID." and album_id = ".$albumID." order by add_time desc limit ".$page*$pageSize.",".$pageSize;
  	}
  	$row = $db->getAll($sql);
  	$musicAlbum =array();
  	if ($row)
  	{
  		foreach ($row as $k =>$v){
  			$musicAlbum[$k]['musicid']    = $v['id'];
  			$musicAlbum[$k]['musictitle']  = $v['music_name'];
  			$musicAlbum[$k]['musicurl'] = $v['music_path'];
  			$musicAlbum[$k]['musiccover'] = IMG_PREFIX.$v['music_cover'];
  		}
  	}
  	return $json->encode($musicAlbum);
  }

  //获取留言
  function messageInfo($starID,$userID,$pageSize,$page){
  	global $json,$db,$ecs;
   	if($page){
  		$page = $page-1;
  	}
  	if($userID){
  		$sql  = 'SELECT *'.
  				' FROM ' .$ecs->table('fabstar_message'). ' AS p ' .
  				" WHERE p.star_id = '$starID' and user_id ='$userID' and if_auditing = 1 order by p.add_time desc limit ".$page*$pageSize.",".$pageSize;  		
  	}else{
  		$sql  = 'SELECT *'.
  				' FROM ' .$ecs->table('fabstar_message'). ' AS p ' .
  				" WHERE p.star_id = '$starID' and if_auditing = 1 order by p.add_time desc limit ".$page*$pageSize.",".$pageSize;
  	}
  	$row = $db->getAll($sql);
  	$list = array();
  	if ($row)
  	{
  		$message =array();
  		foreach ($row as $k =>$v){
  			$message[date('Y-m-d',$v['add_time'])][] = $v;
  		}
  		foreach ($message as $key=>$value){
  			$data['publishtime'] = $key;
  		  	foreach ($value as $k=>$v){
  				$sql  = 'SELECT user_name,nick_name,portrait FROM ' .$ecs->table('member').'where user_id = '.$v['user_id'];
  				$row = $db->getRow($sql);
  				if($row){
  					if( $row['nick_name']){
  						$value[$k]["username"] = $row['nick_name'];
  					}else{
  						$value[$k]["username"] = $row['user_name'];
  					}
  					if(!$row['portrait']){
  						$value[$k]["userUrl"]  = '';
  					}else{
  						$value[$k]["userUrl"]  = $row['portrait'];
  					}
  				}else{
  					$value[$k]["username"] = '';
  				}	
  			}
  			$data['messageList'] = $value;
  			$list[] = $data;
  		}
  	}
  	 
  	return $json->encode($list);
  }
  //增加留言
  function  insertMessage($starID,$userID,$mContent){
  	global $json,$db,$ecs;
	$time = time();
  	$sql  = "INSERT INTO" . $ecs->table('fabstar_message') .
  	"(star_id ,	user_id,message,post_time,add_time) VALUES($starID,$userID,'$mContent',$time,$time)";
  	
  	//
  	$row = $db->query($sql);
  	if ($row)
  	{
  		$num = 1;
  		return $json->encode($num);
  	}else{
  		$num = 0;
  		return $json->encode($num);
  	}
  	
  	
  }
}
?>