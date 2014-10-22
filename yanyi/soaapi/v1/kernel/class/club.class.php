<?php

class club
{
	var $wdwl_url = '';
	var $error = '';
	var $token = '';

	/**
	 * 构造函数
	 * @param string $username
	 *  可设置当前用户
	 * @access protected
	 * @return void
	 */
	function __construct() {
		//
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

	/**
	 * 调试信息
	 */
	public function message($message) {
	}

	public function test($user_name,$password)
	{
		global $json;
		$mod=m('member');
		$res=$mod->get(array(
			'fields' => 'user_id' ,
            'conditions' => '1=1' ,
		));
		//var_dump($res['nickname']);exit;
		return $json->encode($res);
	}

	function noticeuser($token,$targetid,$state)
	{
		global $json;
		
		$res=$this->get_user($token);
		if(!$res)
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
			return $json->encode($arr_tmp);
		}
		$userid=$res['user_id'];
		$this->m =m('member');
		$user_follow_mod =m('userfollow');
		
		if($state==1){

			
	   		$is_follow = $user_follow_mod->get(array('uid'=>$userid, 'follow_uid'=>$targetid));
	  		
	   		if($is_follow)
	   		{
	   		 	return $json->encode(array('statusCode'=>10000,'msg'=>'follow_user_failed_1'));
	   		}
	   		
	   		//关注动作
		   	$return = 1;
		   	//他是否已经关注我
		   	$map = array('uid'=>$targetid, 'follow_uid'=>$userid);
		   	$isfollow_me = $user_follow_mod->get($map);
		   	$data = array('uid'=>$userid, 'follow_uid'=>$targetid, 'add_time'=>time());
		   	if ($isfollow_me) {
		   		$data['mutually'] = 1; //互相关注
		   		$user_follow_mod->edit($map,array('mutually'=>1)); //更新他关注我的记录为互相关注
		   		$return = 2;
		   	}
		   	//return $json->encode($data);
		   	if (!$user_follow_mod->add($data))
		   	{
		   		return $json->encode(array('statusCode'=>10000,'msg'=>'follow_user_failed_2'));
		   	}
		   	
		   	
		   	//增加我的关注人数
		   	$this->m->setInc(array('user_id'=>$userid),'follows');
		   	
		   	//增加Ta的粉丝人数
		   	$this->m->setInc(array('user_id'=>$targetid),'fans');
			return $json->encode(array('statusCode'=>0,'return'=>$return,'msg'=>'follow_user_success'));
		}elseif($state==2)
		{
			$user['user_id']=$userid;
			$uid=$targetid;
			
			if ($user_follow_mod->drop('uid='.$user['user_id'].' and follow_uid='.$uid)) {
		   		//他是否已经关注我
		   		$map = array('uid'=>$uid, 'follow_uid'=>$user['user_id']);
		   		$isfollow_me = $user_follow_mod->get($map);
		   		if ($isfollow_me) {
		   			$user_follow_mod->edit($map,array('mutually'=>0)); //更新他关注我的记录为互相关注
		   		}
		   		//减少我的关注人数
		   		$this->m->setDec(array('user_id'=>$user['user_id']),'follows');
		   		
		   		//减少Ta的粉丝人数
		   		$this->m->setDec(array('user_id'=>$uid),'fans');
		   		
		   		return $json->encode(array('statusCode'=>0,'msg'=>'unfollow_user_success'));
		   	} else {
		   		return $json->encode(array('statusCode'=>10000,'msg'=>'unfollow_user_failed'));
		   	}
		}
		
	}
	
	function fansList($userid,$pageSize,$pageIndex,$token)
	{
		global $json;
		if ($token)
		{
			$userInfo = getUserInfo($token);
			if (!$userInfo)
			  {
		  		$arr = array( 'statusCode'=>0,'msg'=>'找不到该用户');
		  		return $json->encode($arr);
			  }
			$user_id = $userInfo['user_id'];
		}
		
		$user_follow_mod=m('userfollow');
		$user_follow_mod->prikey='id';
		
		$res=$user_follow_mod->find(array('conditions' => 'follow_uid = ' . $userid,
				'count' => true,
				'order' => 'id DESC',
	            'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
		));
		//avatar
		//
		
		foreach ($res as $k=>$v)
		{
			
			$user_info=$this->getUserList($v['uid']);
			$res[$k]['portrait']=$user_info['portrait'];
			$res[$k]['nickname']=$user_info['nickname'];
			$res[$k]['user_name']=$user_info['user_name'];
			$res[$k]['fansnum']=$user_info['follows'];
			$res[$k]['fans']=$user_info['fans'];
			$res[$k]['signature']=$user_info['signature'];
			
			$tmp = 0;
			/*如果用户登录 判断是否已经关注了这些用户*/
			if ($token)
			{
				$res1=$user_follow_mod->find(array(
						'conditions' => 'follow_uid = ' . $v['uid'],
				));
				foreach ($res1 as $v1)
				{
					if ($v1['uid'] == $user_id)
					{
						$tmp = 1;
// 						echo $k;exit;
					}
					
				}
			}
			
			if ($tmp == 1)
			{
				$res[$k]['is_fans'] = 1;
				
			}
			else 
			{
				$res[$k]['is_fans'] = 0;
			}
		}
		
		$arr_tmp['statusCode']=0;
		$arr_tmp['list'] = $res;
		return $json->encode($arr_tmp);
	}
	
	function noticeList($userid,$pageSize,$pageIndex,$token)
	{
		global $json;
		if ($token)
		{
			$userInfo = getUserInfo($token);
			if (!$userInfo)
			{
				$arr = array( 'statusCode'=>0,'msg'=>'找不到该用户');
				return $json->encode($arr);
			}
			$user_id = $userInfo['user_id'];
		}
		
		$user_follow_mod=m('userfollow');
		$user_follow_mod->prikey='id';
		
		$res=$user_follow_mod->findall(array('conditions' => 'uid = ' . $userid,
				'fields'=>'id,uid,follow_uid,add_time,mutually',
				'order' => 'id DESC',
	            'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
		));
		
		//avatar
		//
		foreach ($res as $k=>$v)
		{
			
			$user_info=$this->getUserList($v['follow_uid']);
			$res[$k]['portrait']=$user_info['portrait'];
			$res[$k]['nickname']=$user_info['nickname'];
			$res[$k]['user_name']=$user_info['user_name'];
			$res[$k]['fansnum']=$user_info['follows'];
			$res[$k]['fans']=$user_info['fans'];
			$res[$k]['signature']=$user_info['signature'];
			
			$tmp = 0;
			/*如果用户登录 判断是否已经关注了这些用户*/
			if ($token)
			{
				$res1=$user_follow_mod->find(array(
						'conditions' => 'follow_uid = ' . $v['follow_uid'],
				));
				foreach ($res1 as $v1)
				{
					if ($v1['uid'] == $user_id)
					{
						$tmp = 1;
// 						echo $k;exit;
					}
					
				}
			}
			
			if ($tmp == 1)
			{
				$res[$k]['is_fans'] = 1;
				
			}
			else 
			{
				$res[$k]['is_fans'] = 0;
			}
			
		}
		
		$arr_tmp['statusCode']=0;
		$arr_tmp['list'] = $res;
		return $json->encode($arr_tmp);
	}
	
	function getUserInfo($data)
	{
		global $json;
		
		$userid=$data->userid;
		$res=$this->getUserList($userid);
		
		
		$mineuserid=isset($data->mineuserid)?$data->mineuserid:0;
		
		if($mineuserid)
		{
			//noticeList
			//return $json->encode('sss');
			
			$user_follow_mod=m('userfollow');
			$user_follow_mod->prikey='id';
			
			$user_follow_res=$user_follow_mod->findall(array('conditions' => ' uid = '.$mineuserid.' and follow_uid = ' . $userid,
					'fields'=>'id,uid,follow_uid,add_time,mutually',
					'order' => 'id DESC',
			));
			if($user_follow_res)
			{
				$res['isfollow']=true;
			}else 
			{
				$res['isfollow']=false;
			}
			
		}
		//
		
		$arr_tmp['statusCode']=0;
		$arr_tmp['list'] = $res;
		return $json->encode($arr_tmp);
	}
	
	function setUserInfo($data)
	{
		global $json;
		$res=$this->get_user($data->token);
		if(!$res)
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
			return $json->encode($arr_tmp);
		}else 
		{
			return $json->encode($res);
		}
		
	}
	
	function feedback($token,$content)
	{
		global $json;
		$res=$this->get_user($token);
		if(!$res)
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
			return $json->encode($arr_tmp);
		}
		
		$feedback_mod=m('feedback');
		$addres=$feedback_mod->add(array(
			'userid'=>$res['user_id'],
			'content'=>$content,
		));
		if($addres)
		{
			$arr_tmp['statusCode']=0;
			
			return $json->encode($arr_tmp);
		}else 
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
			return $json->encode($arr_tmp);
		}
		
		
		
	}
	
	function messageList($token,$pageSize,$pageIndex)
	{
		global $json;
		$res=$this->get_user($token);
		if(!$res)
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
			return $json->encode($arr_tmp);
		}
		$user_id=$res['user_id'];
		
		
		$condition = '((new = 1 AND status IN (1,3) AND to_id = ' . $user_id . ') OR (new =2 AND status IN (2,3) AND from_id = ' . $user_id . '))';
		$model_message = m('message');
        $messages = $model_message->find(array(
            'fields'        =>'this.*',
            'conditions'    =>  $condition .' AND parent_id=0 ',
            'count'         => true,
            'limit'         => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
            'order'         => 'last_update DESC',
        ));
		foreach ($messages as $k=>$v)
		{
			//var_dump($v['content']);exit;
			
			
			$content_str=$v['content'];
	        //var_dump($content_str);
	        
	        $patterns[0] = '/\[\/url\]/';
	        $patterns[1] = '/\[url.*\]/';
	        $replacements[0] = '';
	        $replacements[1] = '';
	        
	        $res_content_str=preg_replace($patterns, $replacements, $content_str);
	         //var_dump($res_content_str);
	        //var_dump($messages[201]['content']);exit;
	        //exit;
	        
	        $messages[$k]['content']=$res_content_str;
	        //var_dump($messages);exit;
		}
        
        
        
        
        
        $arr_tmp['statusCode']=0;
		$arr_tmp['list'] = $messages;
        
		return $json->encode($arr_tmp);
	}
	
	function checkVersion($devicetype,$version)
	{
		global $json;
		
		$adv = include_once ROOT_PATH.'/data/app_system.info.php';
		
		$arr_tmp['statusCode']=0;
		$arr_tmp['list'] = $adv[$devicetype];
		return $json->encode($arr_tmp);
	}
	
	function serverRate($token,$serverid,$star,$desc)
	{
		global $json;
		$res=$this->get_user($token);
		if(!$res)
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
			return $json->encode($arr_tmp);
		}
		$user_id=$res['user_id'];
		$idcomments=setComment($user_id,0 ,$serverid, 'serve', $desc);
		if($idcomments&&$idcomments['err'])
		{
			$arr_tmp['statusCode']=1;
			$arr_tmp['msg'] = '已评论';
			return $json->encode($arr_tmp);
			//var_dump($idcomments);exit;
		}
		
		$subscribe_mod=m('subscribe');
		$datas=$subscribe_mod->get(array(
			'fields'=>'subscribe.idsubscribe',
			'conditions' => 'serve_rate.idsubscribe is null and subscribe.userid ='.$user_id . ' and subscribe.idserve='.$serverid,
			'join'=>'has_serve_rate',
		));
		
		$idsubscribe=$datas['idsubscribe'];
		
		//var_dump($idsubscribe);exit;
		if(!$idsubscribe)
		{

			$arr_tmp['statusCode']=1;
			$arr_tmp['msg'] = '只有服务过的用户才能评论';
			return $json->encode($arr_tmp);
		}
		
		$arr_tmp['statusCode']=0;
		
		$serve_detail_mod=m('servedetail');
		
		$serverate_mod=m('serverate');
		$serverateadd['idserve']=$serverid;
		if($star==1)
		{
			$serverateadd['good']=1;
			$serverateadd['point']=5;
			$serve_detail_mod->edit('idserve='.$serverid,'goodnum=goodnum+1');
		}
		elseif($star==2)
		{
			$serverateadd['normal']=1;
			$serverateadd['point']=3;
			$serve_detail_mod->edit('idserve='.$serverid,'normalnum=normalnum+1');
			
		}
		elseif($star==3)
		{
			$serverateadd['bad']=1;
			$serverateadd['point']=1;
			$serve_detail_mod->edit('idserve='.$serverid,'bad=bad+1');
			
		}
		$serverateadd['idcomments']=$idcomments;
		$serverateadd['idsubscribe']=$idsubscribe;
		
		
		
		$serverate_mod->add($serverateadd);
		
		return $json->encode($arr_tmp);
	}
	function RatingInfo($serverid)
	{
		global $json;
		$serve_detail_mod=m('servedetail');
		$res=$serve_detail_mod->get(array(
		'conditions'=>'idserve='.$serverid
		));
		
		//SELECT sum(good),sum(normal),sum(bad) FROM rctailor1.rc_serve_rate where idserve=56
		
		
		
		$arr_tmp=$res;
		$arr_tmp['allrate']=$res['goodnum']+$res['normalnum']+$res['bad'];
		$arr_tmp['statusCode']=0;
		
		
		return $json->encode($arr_tmp);
	}
	
	function RatingList($serverid,$type,$pageSize,$pageIndex)
	{
		global $json;
		
		
		$conditions='';
		if($type==1)
		{
			$conditions= ' and serve_rate.good>0';
		}
		elseif($type==2)
		{
			$conditions= ' and serve_rate.normal>0';
		}elseif($type==3)
		{
			$conditions= ' and serve_rate.bad>0';
		}
		
		
		$serverate_mod=m('serverate');
		$res=$serverate_mod->find(array(
		
            'conditions'    => ' serve_rate.idserve= '.$serverid.'  '.$conditions,
            'count'         => true,
            'limit'         => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
            'order'         => 'idserverate DESC',
		));
		$comments_mod=m('comments');
		foreach ($res as $k=>$v)
		{
			//var_dump($v['idcomments']);exit;
			if($v['idcomments'])
			{
				$comments_data=$comments_mod->get($v['idcomments']);
				//var_dump($comments_data);exit;
				$res[$k]['desc']=$comments_data['content'];
				//var_dump($res[$k]);exit;
			}else 
			{
				$res[$k]['desc']='暂无评论';
			}
		}
		//exit;
		
		$arr_tmp['statusCode']=0;
		$arr_tmp['list']=$res;
		return $json->encode($arr_tmp);
	}
	
	
	function get_user($token)
	{
		global $json;
		if(!$token)
		{
			return false;
		}
		$mod=m('member');
		$res=$mod->get(array(
            'conditions' => "user_token='$token'", 
		));
		return $res;
	}
	
	//获取会员基本信息
	public function getUserList($uid){
		global $json,$db,$ecs;
		
// 		$sql  = 'SELECT user_id,avatar,user_name,nickname,follows,fans,signature,def_addr,gender,province,city,coin,point FROM ' .$ecs->table('member'). ' AS u ' .
// 				" WHERE u.user_id = '$uid'";
// 		$row = $db->getRow($sql);
		$user_info = getUinfoByUid($uid);
		
		return $user_info;
	}
	
	
	function getregionlist($parent_id)
	{
		global $json;
		$pid=$parent_id;
		
		$mod_region = m('region');
        $regions = $mod_region->get_list($pid);
		foreach ($regions as $key => $region)
        {
            $regions[$key]['region_name'] = htmlspecialchars($region['region_name']);
        }
        
        $arr_tmp['statusCode']=0;
		$arr_tmp['list']=$regions;
		return $json->encode($arr_tmp);
        
	}
	
}