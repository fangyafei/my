<?php

class experience
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
	
	public function topad($type)
	{
		global $json;
		$this->_folly_mod =m('fimg');
		
		if($type=='5')
		{
			$res=$this->_folly_mod->find(array(
			'fields'=>'id,uploadfiles,link,tpl_child_title,img_title',
			'conditions' => 'tpl_child_id='.$type ,
			'order' => "l_order desc,id desc",
			));

			$arr_tmp['statusCode']=0;
			$arr_tmp['datalist']	=$res;
		}
		
		return $json->encode($arr_tmp);
	}
	
	
}