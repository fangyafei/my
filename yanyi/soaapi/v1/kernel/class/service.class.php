<?php

class service
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

	function info($idserve)
	{
		global $json;
		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => 'serve.idserve='.$idserve ,
			'join' => 'has_serve_detai'
			));
		/*根据idsevier 获得服务点等级*/
		
			if(!$res)
			{
				$res['statusCode'] 	= 1;
				$res['msg'] 		= 'data empty';
			}else
			{
				$res['statusCode']=0;
			}
			return $json->encode($res);
	}

	function index($pageSize,$pageIndex,$lat,$lng){
		global $json;
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}
		
		
		//$mapjson= file_get_contents('http://api.map.baidu.com/geocoder/v2/?ak=D86faa317d5dd0367df3ea346f27ec86&location=39.983424,116.322987&output=json&pois=0');
		$mapjson= file_get_contents('http://api.map.baidu.com/geocoder/v2/?ak=D86faa317d5dd0367df3ea346f27ec86&location='.$lat.','.$lng.'&output=json&pois=0');
		$mapjson=json_decode($mapjson);
		//var_dump($mapjson);exit;
		//var_dump($mapjson->status==0);exit;
		$conditions_str='';
		if($mapjson->status==0&&$mapjson->result->addressComponent->city)
		{
			$mcity=$mapjson->result->addressComponent->city;
			if($mcity)
			{
				$mcity=str_replace('市', '', $mcity);
				
				
				
				$region_mod=m('region');
				$region_data=$region_mod->get(array(
				'conditions'=>"region_name like '%".$mcity."%' ",
				'order'=>'region_id desc',
				));
				if($region_data)
				{
					$region_list=$region_mod->get_descendant($region_data['region_id']);
					if($region_list)
					{
						$conditions_str=implode(',', $region_list);
						$conditions_str=" and region_id in(".$conditions_str.") ";
						
					}
				}				
			}
			
			//var_dump($conditions_str);exit;
			
			
		}
		
		
		//exit;
		$mod=m('serve');
		
		if($conditions_str){
			$res=$mod->find(array(
	            'conditions' => 'serve.serve_type=2 and serve.state=1 '.$conditions_str ,
				'join' => 'has_serve_detai',
				'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
		        //'order' => "serve.idserve desc",
				'order'=>'ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((ifnull(latitude,\'0\') * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((ifnull(latitude,\'0\') * 3.1415) / 180 ) *COS(('.$lng.'* 3.1415) / 180 - (ifnull(longitude,\'0\') * 3.1415) / 180 ) ) * 6380 asc',
		        'count' => true,
			));
		}else 
		{
			$res=$mod->find(array(
	            'conditions' => 'serve.serve_type=2 and serve.state=1 '.$conditions_str ,
				'join' => 'has_serve_detai',
				'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
		        'order' => "serve.idserve desc",
				//'order'=>'ACOS(SIN((39.983424 * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) +COS((39.983424 * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS((116.322987* 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380 asc',
		        'count' => true,
			));
		}
		
		
		
		
		//var_dump($res);exit;
		
		$arr_tmp['statusCode']=0;
		//
			if($conditions_str)
			{
				$arr_tmp['gps']='1';
			}else 
			{
				$arr_tmp['gps']='0';
			}
			
			if($mcity)
			{
				$arr_tmp['gps_city']=$mcity;
			}else 
			{
				$arr_tmp['gps_city']='';
			}
			
		$arr_tmp['datalist']	=$res;
		
		return $json->encode($arr_tmp);
	}

	function employee($token,$pageSize,$pageIndex)
	{
		global $json;
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}

		 
		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => "member.user_token='$token'", 
  			'join'=>'has_member',
		));

		 
		if($res){
			$data_mod=m('employee');
			$data = $data_mod->find(array(
	        	'conditions' => 'idserve='.$res['idserve'],    
	        	'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
	            'order' => "idemployee desc",
	            'count' => true,
			));

			$arr_tmp['statusCode']=0;
			$arr_tmp['datalist']	=$data;

			//  			$arr_tmp['idserve']=$res['idserve'];


		}else{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}

		return $json->encode($arr_tmp);

	}
	function employee_add($token,$employee_name,$sex,$job_number,$mobile,$id_number,$mark,$head_img){
		global $json;
		

		 
		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => "member.user_token='$token'", 
  			'join'=>'has_member',
		));
		if($res){
			$employee_mod=m('employee');
			
			$cres=$employee_mod->check_job_number($job_number, $res['idserve']);
       		if (!$cres)
			{
				$arr_tmp['statusCode']=10000;
				$arr_tmp['msg'] = '员工编号已存在！';
				return $json->encode($arr_tmp);
			}
            
			
        	$cres=$employee_mod->check_employee_name($employee_name, $res['idserve']);
       		if (!$cres)
			{
				$arr_tmp['statusCode']=10000;
				$arr_tmp['msg'] = '员工名称已存在！';
				return $json->encode($arr_tmp);
			}
			
			/*添加员工图像*/
			$img = $head_img?file_get_contents($head_img):'';
			$dir = substr(getcwd(),0,-12);
			
			if ($img)
			{
				$img_dir = "/data/files/mall/portrait/1/serve_".$res['idserve']."_".$job_number."_".time().".jpg";
				file_put_contents($dir.$img_dir,$img);
			}
			else 
			{
				$img_dir = '';
			}
			
			
			
			$data_mod=m('employee');
			$arr=array(
				'employee_name'=>$employee_name,
				'sex'=>$sex,
				'job_number'=>$job_number,
				'mobile'=>$mobile,
				'id_number'=>$id_number,
				'mark'=>$mark,
				'head_img'=>$img_dir,
			 	'idserve'    => $res['idserve'],
				);
			$addres=$data_mod->add($arr);
			$arr_tmp['statusCode']=0;
		}else{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}
		return $json->encode($arr_tmp);
	}
	function employee_edit($data)
	{
		global $json;
		
		$token=isset($data->token)?$data->token:'';
		$idemployee=isset($data->idemployee)?$data->idemployee:'';
		
		if(!$token)
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
			return $json->encode($arr_tmp);
		}
		
		
			
		
		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => "member.user_token='$token'", 
			'order'=>'serve.idserve desc',
  			'join'=>'has_member',
		));
		
		
		if($res){
// 	print_exit($res);	
			/*修改员工图像*/
			$img = $data->head_img?file_get_contents($data->head_img):'';
			$dir = substr(getcwd(),0,-12);
				
			if ($img)
			{
				$employee_mod=m('employee');
				$employee_info = $employee_mod->get($data->idemployee);
				$img_dir = "/data/files/mall/portrait/1/serve_".$res['idserve']."_".$res['job_number']."_".time().".jpg";
				file_put_contents($dir.$img_dir,$img);
			}
			else
			{
				$img_dir = '';
			}
			
			
			$employee_mod=m('employee');
			
			$ids = explode(',', $idemployee);
	
	        $ids=' idserve = '.$res['idserve'].' and idemployee = '.$idemployee;
			
	        $uparr=array();
	        
	        if(isset($data->sex))$uparr['sex']=$data->sex;
	        if(isset($data->mobile))$uparr['mobile']=$data->mobile;
	        if(isset($data->id_number))$uparr['id_number']=$data->id_number;
	        if(isset($data->mark))$uparr['mark']=$data->mark;
	        if($img_dir)$uparr['head_img']=$img_dir;
	        
	        if(count($uparr)==0)
	        {
	        	$arr_tmp['statusCode']=10000;
				$arr_tmp['msg'] = '修改失败!!';
				return $json->encode($arr_tmp);
	        }
	        
	        
			if (!$employee_mod->edit($ids,$uparr))
	        {
	            $arr_tmp['statusCode']=10000;
				$arr_tmp['msg'] = '修改失败!';
				return $json->encode($arr_tmp);
	        }
			$arr_tmp['statusCode']=0;
		}
		else
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}
		return $json->encode($arr_tmp);
	}
	
	
	function employee_drop($token,$idemployee)
	{
		global $json;
		

		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => "member.user_token='$token'", 
  			'join'=>'has_member',
		));
		
		
		if($res){
			
			$employee_mod=m('employee');
			
			$ids = explode(',', $idemployee);
	
	        $ids=' idserve = '.$res['idserve'].' and idemployee='.$idemployee;
	        
	        if (!$employee_mod->drop($ids))
	        {
	            $arr_tmp['statusCode']=10000;
				$arr_tmp['msg'] = '删除失败!';
				return $json->encode($arr_tmp);
	        }
	        
			$arr_tmp['statusCode']=0;
		}else {
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}
		return $json->encode($arr_tmp);
	}
	
	function figureorder($token,$pageSize,$pageIndex)
	{
		global $json;
		
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}

		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => "member.user_token='$token'", 
			'order'=>'serve.idserve desc',
  			'join'=>'has_member',
		));
		//return $json->encode($res);
		
		if($res){
			
		$model_order =m('order');
			
		$pagelimit=($pageSize * ($pageIndex-1)) . ','. $pageSize;
		
			$orders = $model_order->findAll(array(
				'conditions'    => " order_figure.serviceid=".$res['idserve'] ,
	            //'conditions'    => " 1=1 " ,
	            'fields'        => 'this.*,order_figure.*',
	            'count'         => true,
	            'limit'         => $pagelimit,
	            'order'         => 'order.order_id desc,add_time DESC',
				'join'=>'has_orderfigure',
				'include'       =>  array(
	                'has_ordergoods',       //取出商品
	            ),
	        ));
	        
	        
	        foreach ($orders as $key1 => $order)
	        {
	        	
	            foreach ($order['order_goods'] as $key2 => $goods)
	            {
	                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = '/themes/mall/default/styles/default/images/dizi_tu.gif';
	            }//figure
	            /*
	            $figure_mod=m('figure');
	            $res=$figure_mod->uniquebyid($orders[$key1]['buyer_id']);
	            $orders[$key1]['isfigure']=$res;
	            //var_dump($orders[$key1]['isfigure']);
            	//var_dump($res);false不需要补,true需要补录量体数据
	            */
		        if($orders[$key1]['figure']=='-1'||$orders[$key1]['figure']=='-2')
	            {
	            	 $orders[$key1]['isfigure']=true;
	            }else 
	            {
	            	$orders[$key1]['isfigure']=false;
	            }
	            
	        }
			
			
			$arr_tmp['statusCode']=0;
			$arr_tmp['datalist']=$orders;
		}else 
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}
		return $json->encode($arr_tmp);
	}
	function applyserver($data)
	{
		global $json;
		
		$mod=m('member');
		$res=$mod->get(array(
            'conditions' => "user_token='$data->token'", 
		));
		
		
		
		
		
		
		if($res){
			$this->_serve_mod = m('serve');
			
			
			
			
			$this->serve_type=$res['serve_type'];
	        if($this->serve_type)
	        {
	        	$arrservetype=array('1'=>'供应商','2'=>'服务点','3'=>'加盟商','4'=>'设计师');
	        	$arr_tmp['statusCode']=10000;
				$arr_tmp['msg'] = "已经申请".$arrservetype[$this->serve_type];
				return $json->encode($arr_tmp);
	        	
	        }
			
			
			$this->_applylog_mod=m('applylog');
	        $applyres=$this->_applylog_mod->get(array(
	    			'conditions' => 'status!=2 and user_id = '.$res['user_id'],));
	        if($applyres['user_id'])
	        {
	        	$serve=$this->_serve_mod->get(array(
	    			'conditions' => 'userid = '.$res['user_id'],));
	        	if($applyres['apply_type']=='2'||$applyres['apply_type']==3)
	        	{
	        		$arr_tmp['statusCode']=10000;
					$arr_tmp['msg'] = '重复申请_1';
	        		
	        	}elseif($applyres['apply_type']=='1')
	        	{
	        		//$this->show_warning("已经申请供应商，不能申请加盟商！");
	        		$arr_tmp['statusCode']=10000;
					$arr_tmp['msg'] = '重复申请_2';
	        	}
	        	return $json->encode($arr_tmp);
	        }
			
			
			
			
			
			
			
			
			
			if (!$this->_serve_mod->unique($data->company_name))
			{
				$arr_tmp['statusCode']=10000;
				$arr_tmp['msg'] = '公司名称已存在！';
				return $json->encode($arr_tmp);
			}
			
			$servedata=array('company_name'=>$data->company_name,
			'email'=>$data->email,
			'establish_date'=>$data->establish_date,
			'enterprise_url'=>$data->enterprise_url,
			'linkman'=>$data->linkman,
			'mobile'=>$data->mobile,
			//'business_license'=>$data->business_license,
			//'tax_card'=>$data->tax_card,
			//'organization_card'=>$data->organization_card,
			'company_synopsis'=>$data->company_synopsis,
			'post'=>$data->post,
			'rc_code'=>getRandOnlyId(),
			'userid'=>$res['user_id'],
			'region_id'=>$data->region_id,
			'serve_name'=>$data->serve_name,
			'serve_address'=>$data->serve_address,
			'region_name'=>$data->region_name,
			'serve_type'=>2,
			);
			
			$memberlv_mod=m('memberlv');
			
			$level_res=$memberlv_mod->get_default_level(array('lv_type'=>'service'));
			$servedata['brokerage_level']=$level_res['member_lv_id'];
			
			$idserve=$this->_serve_mod->add($servedata);
			if(!$idserve){
				$arr_tmp['statusCode']=10000;
				$arr_tmp['msg'] = '添加失败！';
			}else {
				
				$this->_service=m('servedetail');
	            $this->_service->add(array('idserve'=>$idserve));
				
	            $this->_applylog_mod=m('applylog');
	            $logdate['user_id']=$res['user_id'];
	            $logdate['apply_type']=2;
	            $logdate['status']=0;
	            $logdate['mark']='';
	            $this->_applylog_mod->add($logdate);
				
				$arr_tmp['statusCode']=0;
			}
		}else 
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}
		
		
		
		
		return $json->encode($arr_tmp);
	}
	
	function qrcode($idemployee)
	{
		global $json;
		//$url=SITE_URL.$view->build_url(array('app'=>'service','act'=>'info','arg'=>$res['idserve']));
		//var_dump($url);exit;
		
		$url=M_SITE_URL.'index.php/service-info-'.$idemployee.'.html';
		
		QRcode('appservice',$idemployee,$url);
		
		$mqrcode=getQrcodeImage('appservice',$idemployee,4);
		
		$arr_tmp['statusCode']=0;
		$arr_tmp['mqrcode']=$mqrcode;
		return $json->encode($arr_tmp);
	}
	
	function servedetail($data)
	{
		global $json;
		$token=isset($data->token)?$data->token:'';
		
		if(!$token)
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
			return $json->encode($arr_tmp);
		}
		$user_info = getUserInfo($token);
		$userID = $user_info['user_id'];
		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => "member.user_token='$token'", 
  			'join'=>'has_member',
		));
		
		
		if($res){
		
			//$portrait= isset($data->portrait)?$data->portrait:'';
			$synopsis=isset($data->synopsis)?$data->synopsis:'';
			$longitude=isset($data->longitude)?$data->longitude:'';
			$latitude=isset($data->latitude)?$data->latitude:'';
			$qq=isset($data->qq)?$data->qq:'';
			$weixin=isset($data->weixin)?$data->weixin:'';
			$serve_name=isset($data->serve_name)?$data->serve_name:'';
// 	print_exit($data->newheadImg);	
			$img              = isset($data->newheadImg)?file_get_contents($data->newheadImg):'';
			
			if ($img)
			{
				$dir = substr(getcwd(),0,-12);
				$img_dir="/data/files/mall/portrait/1/serve_".$userID."_".time().".jpg";
				file_put_contents($dir.$img_dir,$img);
				$portrait=$img_dir;
			}
			
			
			$arr=array();
			if($portrait)
			{
				$arr['portrait']=$portrait;
			}
			if($synopsis)
			{
				$arr['synopsis']=$synopsis;
			}
			if($longitude)
			{
				$arr['longitude']=$longitude;
			}
			if($latitude)
			{
				$arr['latitude']=$latitude;
			}
			if($qq)
			{
				$arr['qq']=$qq;
			}
			if($weixin)
			{
				$arr['weixin']=$weixin;
			}
			
			if(count($arr))
			{
				$this->_service=m('servedetail');
				$this->_service->edit('idserve='.$res['idserve'], $arr);
			}
			
			$serve_address=isset($data->serve_address)?$data->serve_address:'';
			if($serve_address)
			{
				$this->_service=m('serve');
				$this->_service->edit('idserve='.$res['idserve'], 
				array('serve_address'=>$serve_address));
			}
			if($serve_name)
			{
				$this->_service=m('serve');
				$this->_service->edit('idserve='.$res['idserve'], 
				array('serve_name'=>$serve_name));
			}
			
			$arr_tmp['statusCode']=0;
		}
		else
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}
		return $json->encode($arr_tmp);
	}
	
	function PassengerList($token,$pageSize,$pageIndex)
	{
		global $json;
		
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}

		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => "member.user_token='$token'", 
  			'join'=>'has_member',
		));
		
		
		if($res){
			$member_mod=m('member');
			/*
			$member_res=$member_mod->find(array(
				'conditions'    => " 1=1 " ,
	            'fields'        => 'user_id,portrait,user_name,nickname,follows,fans,signature,def_addr,gender,province,city,coin,point,phone_mob,email',
	            'count'         => true,
	            'limit'         => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
	            'order'         => 'reg_time DESC',
				
			));
			*/
			
			//var_dump($res['idserve']);exit;
			$this->idserve=$res['idserve'];
			
			$this->_subscribe=m('subscribe');
			
			$subscribes = $this->_subscribe->find(array(
	        //'fields'=>'this.*,member.user_name,serve.serve_name',
	        'fields'=>'member.user_name,member.email,member.phone_mob,member.nickname,member.last_login,member.reg_time',
	        	'conditions' => ' subscribe.idserve ='.$this->idserve . $conditions.' group by subscribe.userid ',    
	        'join' => 'has_member,has_serve', 
	        	'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
	            'order' => "idsubscribe desc",
	            'count' => true,
	        ));
			
			$arr_tmp['statusCode']=0;
			//$arr_tmp['list'] = $member_res;
			$arr_tmp['list'] = $subscribes;
			
		}else 
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}
		return $json->encode($arr_tmp);
	}
	function figureorderlog($token,$pageSize,$pageIndex)
	{
		global $json;
		
		if($pageIndex<1)
		{
			$pageIndex = 1;
		}

		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => "member.user_token='$token'", 
			'order'=>'serve.idserve desc',
  			'join'=>'has_member',
		));
		if($res){
			//return $json->encode(1);
			
			$this->data_mod=m('figureorderlog');
			
			$figures = $this->data_mod->find(array(
	        	'conditions' => 'serviceid ='.$res['idserve'] ,    
	        	'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
	            'order' => "idfigureorderlog desc",
	            'count' => true,
	        ));
			
			$arr_tmp['statusCode']=0;
			$arr_tmp['list'] = $figures;
		}else 
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}
		return $json->encode($arr_tmp);
	}
	function figureadd($data)
	{
		global $json;
		$token=$data->token;
		$m_data=$data;
		
		if(!$token)
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
			return $json->encode($arr_tmp);
		}else if(!isset($m_data->user_name)||!$m_data->user_name)
		{
			$arr_tmp['statusCode']=1;
			$arr_tmp['msg'] = '用户名必填';
			return $json->encode($arr_tmp);
		}else if(!isset($m_data->figure_name)||!$m_data->figure_name)
		{
			$arr_tmp['statusCode']=1;
			$arr_tmp['msg'] = '量体名称不能为空';
			return $json->encode($arr_tmp);
		}else if(!isset($m_data->lw)||!$m_data->lw)
		{
			$arr_tmp['statusCode']=1;
			$arr_tmp['msg'] = '领围不能为空';
			return $json->encode($arr_tmp);
		}else if(!isset($m_data->xw)||!$m_data->xw)
		{
			$arr_tmp['statusCode']=1;
			$arr_tmp['msg'] = '胸围不能为空';
			return $json->encode($arr_tmp);
		}else if(!isset($m_data->zyw)||!$m_data->zyw)
		{
			$arr_tmp['statusCode']=1;
			$arr_tmp['msg'] = '中腰围不能为空';
			return $json->encode($arr_tmp);
		}
		
		
		
		
		
		$mod=m('serve');
		$res=$mod->get(array(
            'conditions' => "member.user_token='$token'", 
			'order'=>'serve.idserve desc',
  			'join'=>'has_member',
		));
		if($res){
			
			
			
			
			
		$data = array(
                'lw' => $data->lw,
                'xw'    => $data->xw,
                'zyw'     => $data->zyw,
                'tw'    => $data->tw,
				'stw'    => $data->stw,
            	'zjk'    => $data->zjk,
            	'yxc'    => $data->yxc,
            	'zxc'    => $data->zxc,
            	'qjk'    => $data->qjk,
            
            	'hyc'    => $data->hyc,
	            'yw'    => $data->yw,
	            'hd'    => $data->hd,
	            'td'    => $data->td,
	            'hyg'    => $data->hyg,
	            'qyg'    => $data->qyg,
	            'kk'    => $data->kk,
	            'figure_type'    => 1,
	            'height'    => $data->height,
	            'weight'    => $data->weight,
				//'userid'    => $data->userid,
            	'serve_userid'    => $res['user_id'],
            	
            	'figure_name'    => $data->figure_name,
            	'employee_name'    => $data->employee_name,
            	'figure_mode'    => $data->figure_mode,
            
            	
            	'hyjc'    => $data->hyjc,
	            'tgw'    => $data->tgw,
	            'qyj'    => $data->qyj,
	            'ykc'    => $data->ykc,
	            'zkc'    => $data->zkc,
	            'xiw'    => $data->xiw,
            	
            	'user_name'    => $data->user_name,
            	
            	
            		
            	
            );
            
            $check_res=$this->check_user_name($data['user_name']);
            
            if(!$check_res)
            {
            	
            	$arr_tmp['statusCode']=1;
				$arr_tmp['msg'] = '用户已经有量体数据，或不存在此用户';
				return $json->encode($arr_tmp);
            }
            
            
            
            $member_mod=m('member');
            $res_member=$member_mod->get(array(
            'fields'=>'user_id',
            'conditions'=>"user_name='".$data['user_name']."'",
            ));
            
            
            
            $data['userid']=$res_member['user_id'];
            
            
            $serve_mod=m('serve');
            $res_serve=$serve_mod->get(array(
            'fields'=>'idserve',
            'conditions'=>"userid='".$data['serve_userid']."'",
            'order'=>'idserve desc',
            ));
            
            $data['idserve']=$res_serve['idserve'];
            
            
            
            $data['body_type_19']=$m_data->body_type_19;
            $data['body_type_20']=$m_data->body_type_20;
            $data['body_type_24']=$m_data->body_type_24;
            $data['body_type_25']=$m_data->body_type_25;
            $data['body_type_26']=$m_data->body_type_26;
            $data['body_type_3']=$m_data->body_type_3;
            $data['body_type_2000']=$m_data->body_type_2000;
            $data['styleLength']=$m_data->styleLength;
            
            
            $data['part_label_10130']=$m_data->part_label_10130;
            $data['part_label_10131']=$m_data->part_label_10131;
            $data['part_label_10725']=$m_data->part_label_10725;
            $data['part_label_10726']=$m_data->part_label_10726;
            
            
            //return $json->encode($data);
            
            //-----------------------
            
			$res_user_name=$this->_figure->unique($data['user_name']);
            if($res_user_name)
            {
            	$idfigure=$this->_figure->add($data);
            }
            //订单补录量体数据（不需要存量体表），
            
            
			$orderid=isset($m_data->order_id)?intval($m_data->order_id):0;
            if($orderid)
            {
            	if(!$idfigure)
            	{
            		$idfigure=0;
            	}
            }
            
            
            if (!$idfigure&&!$orderid)
            {
                //$this->show_warning($this->_figure->get_error());
                
                $arr_tmp['statusCode']=1;
				$arr_tmp['msg'] = '添加量体数据失败';
				return $json->encode($arr_tmp);
                return;
            }else 
            {
            	
            	
            	
            	$orderid=isset($m_data->order_id)?intval($m_data->order_id):0;
            	$serviceid=$data['idserve'];
            	
            	
            	//return $json->encode($orderid.'|'.$serviceid);
            	
            	$this->data_mod=m('figureorderlog');
            	$this->data_mod->addlog($orderid,$serviceid);
            	//分成记录
            	
            	
	    		//修改orderfigure量体数据
		    	if($orderid&&$serviceid)
		    	{
			    	$orderfigure_mod=m('orderfigure');
			    	$orderfigure_data= $orderfigure_mod->get(array(
			    		'conditions' => 'serviceid='.$serviceid.' and order_id ='.$orderid,
			    	));
			    	if($orderfigure_data&&!$orderfigure_data['lw']&&!$orderfigure_data['xw']&&!$orderfigure_data['zyw'])
			    	{
			    		$orderfigure_data_update=array('lw' => $data['lw'],
		                'xw'    =>$data['xw'],
		                'zyw'     => $data['zyw'],
		                'tw'    => $data['tw'],
						'stw'    => $data['stw'],
		            	'zjk'    => $data['zjk'],
		            	'yxc'    => $data['yxc'],
		            	'zxc'    => $data['zxc'],
		            	'qjk'    => $data['qjk'],
		            
		            	'hyc'    => $data['hyc'],
			            'yw'    => $data['yw'],
			            'hd'    => $data['hd'],
			            'td'    => $data['td'],
			            'hyg'    => $data['hyg'],
			            'qyg'    => $data['qyg'],
			            'kk'    => $data['kk'],
				    		'height'    => $data['height'],
			            'weight'    => $data['weight'],
				    		'hyjc'    => $data['hyjc'],
			            'tgw'    => $data['tgw'],
			            'qyj'    => $data['qyj'],
			            'ykc'    => $data['ykc'],
			            'zkc'    => $data['zkc'],
			            'xiw'    => $data['xiw'],
			    		
			    		'body_type_19' =>  $data['body_type_19'],
						'body_type_20' =>  $data['body_type_20'],
						'body_type_24' =>  $data['body_type_24'],
						'body_type_25' =>  $data['body_type_25'],
						'body_type_26' =>  $data['body_type_26'],
						'body_type_3' =>  $data['body_type_3'],
						'body_type_2000' =>  $data['body_type_2000'],
						'styleLength' =>  $data['styleLength'],
						'figure_name' =>  $data['figure_name'],
						'figure'  => $idfigure,
			    		
			    		'part_label_10130' =>  $data['part_label_10130'],
			    		'part_label_10131' =>  $data['part_label_10131'],
			    		'part_label_10725' =>  $data['part_label_10725'],
			    		'part_label_10726' =>  $data['part_label_10726'],
			    		
			    		
			    		
			    		);
			    		$orderfigure_edit_res=$orderfigure_mod->edit('serviceid='.$serviceid.' and order_id ='.$orderid,$orderfigure_data_update);

			    	}
		    	}
            }

			
			$arr_tmp['statusCode']=0;
			
			
		}else 
		{
			$arr_tmp['statusCode']=10000;
			$arr_tmp['msg'] = 'token error';
		}
		return $json->encode($arr_tmp);
		
	}
	function check_user_name($user_name){
    	
		$this->_figure=m('figure');
		
		if (!$user_name)
		{
			return false;
		}
		//用户表里面存在
		$member_mod=m('member');
		$res=$member_mod->unique($user_name);
		if($res)
		{
			return false;
		}else 
		{//量体数据--补录修改
			return true;
		}
		//FIGURE表里面不存在
		/////////return $this->_figure->unique($user_name);//---------------
		
    }
    
    /**
     * 获取服务点的预约顾客
     * @author liang.li
     */
    function getCus($token,$pageSize,$pageIndex,$status)
    {
    	global $json;
    	$userInfo = getUserInfo($token);
    	if (!$userInfo)
    	{
    		$arr = array( 'statusCode'=>1,'msg'=>'找不到用户');
    		return $json->encode($arr);
    	}
    	$user_id = $userInfo['user_id'];
    	if ($userInfo['serve_type'] != 2)
    	{
    		$arr = array( 'statusCode'=>1,'msg'=>'你不是服务点');
    		return $json->encode($arr);
    	}
    	$server = m('serve');
    	$server_info = $server->get('userid='.$user_id);
    	$idserve = $server_info['idserve'];
    	
    	/*完成状态*/
    	if ($status == 5)
    	{
    		$conditions = '';
    	}
    	else 
    	{
    		$conditions = ' AND subscribe.state = '.$status;
    	}
    	$_subscribe=m('subscribe');
    	$subscribes = $_subscribe->find(array(
    			'fields'=>'this.*,member.user_name,serve.serve_name',
    			'conditions' => ' subscribe.idserve ='.$idserve . $conditions,
    			'join' => 'has_member,has_serve',
    			'limit' => ($pageSize * ($pageIndex-1)) . ','. $pageSize,
    			'order' => "idsubscribe desc",
    			'count' => true,
    	));
    	
    	/*判断有无下一页*/
    	$pageNext = $pageIndex + 1;
    	$subscribes_next =  $_subscribe->find(array(
    			'fields'=>'this.*,member.user_name,serve.serve_name',
    			'conditions' => ' subscribe.idserve ='.$idserve . $conditions,
    			'join' => 'has_member,has_serve',
    			'limit' => ($pageSize * ($pageNext-1)) . ','. $pageSize,
    			'order' => "idsubscribe desc",
    			'count' => true,
    	));
    	if ($subscribes_next)
    	{
    		$hasNext = true;
    	}
    	else
    	{
    		$hasNext = false;
    	}
    	
    	$arr = array("hasNext"=>$hasNext,"subscribes"=>$subscribes);
    	return $json->encode($arr);
    	
    }
    
    
    
    
    
	
}