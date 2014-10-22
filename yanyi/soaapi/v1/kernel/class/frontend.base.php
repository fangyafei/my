<?php

/**
 *    前台控制器基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class FrontendApp extends ECBaseApp
{
    function __construct()
    {
       $this->FrontendApp();
    }
    
    function FrontendApp()
    {
        Lang::load(lang_file('common'));
        Lang::load(lang_file(APP));
        parent::__construct();

        // 判断商城是否关闭
        if (!Conf::get('site_status'))
        {
            $this->show_warning(Conf::get('closed_reason'));
            exit;
        }
        # 在运行action之前，无法访问到visitor对象
    }
    function _config_view()
    {
        parent::_config_view();
        $this->_view->template_dir  = ROOT_PATH . '/themes';
        $this->_view->compile_dir   = ROOT_PATH . '/temp/compiled/mall';
        $this->_view->res_base      = SITE_URL . '/themes';
        $this->_config_seo(array(
            'title' => Conf::get('site_title'),
            'description' => Conf::get('site_description'),
            'keywords' => Conf::get('site_keywords')
        ));
    }
	
	
	 /**
     *    获取可用功能列表
     *
     *    @author    andcpp
     *    @return    array
     */
    function _get_functions()
    {
        $arr = array();        
        $arr[] = 'buy'; //来自买家下单通知   
        $arr[] = 'send'; //卖家发货通知买家   
		$arr[] = 'check';//来自买家确认通知   
        return $arr;
    }
	
	//中国网建接口 by andcpp 
	/*function Sms_Get($url)
	{
		if(function_exists('file_get_contents'))
		{
			$file_contents = file_get_contents($url);
		}
		else
		{
			$ch = curl_init();
			$timeout = 5;
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$file_contents = curl_exec($ch);
			curl_close($ch);
		}
		return $file_contents;
	}*/
    /*短信发送*/
	function SendSms($mobile,$content,$return=FALSE,$user_id=0,$user_name='admin',$code=0)
    
	{
    	
	$user_id = SMS_UID; // sms9平台用户id
    	
	$pass = SMS_KEY; // 用户密码
    	
	$channelid = SMS_CHANNELID; // 发送频道id
    
    	
	//if(!$mobile || !$content || !$user_id || !$pass || !$channelid) return false;
    
    	
		if(is_array($mobile)) $mobile = implode(",",$mobile);
    
    
    	
		$db_content = $content;
    
    	//utf8需要转码
    	
	$content = iconv("utf-8","gbk//ignore",$content);
    	
		$content = urlencode($content);

	sms($mobile,$content);	
		
		
		#模拟发送短信-add by v5 
//		$api = "http://admin.sms9.net/houtai/sms.php?cpid={$user_id}&password={$pass}&channelid={$channelid}&tele={$mobile}&msg={$content}";
//     	$res = file_get_contents($api);
//     	$rs = strpos($res,'success') === false ? explode(":",$res) : array('succeed',1);
    	
		$rs[1] = 1;
    	
		$add_msglog = array(
					'user_id' => $user_id,
					 'user_name' => $user_name,
					 'to_mobile' => $mobile,
					 'content' => $db_content,
					 'state' => $rs[1],
					 'time' => time(),
					 'code' => $code);
    	
		$this->mod_msglog->add($add_msglog);
 		return true;
 //调用是否需要返回值，返回特定的提示和跳转 .默认是后台发送短信
    	
	if ($return) return $rs[1];
    
    
		if('error' == $rs[0])
    	
		{
    		
		$this->show_message('cuowu_duanxinfasongshibai', 'go_back', 'index.php?module=msg');
    		return;
    	}else{
    		 
    		$this->show_message('send_msg_successed', 'go_back', 'index.php?module=msg');
    		return;
    	}
    }

    function getCode ($length = 32, $mode = 0)
    {
    	switch ($mode) {
    		case '1':
    			$str = '1234567890';
    			break;
    		case '2':
    			$str = 'abcdefghijklmnopqrstuvwxyz';
    			break;
    		case '3':
    			$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    			break;
    		case '4':
    			$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';break;
    		case '5':
    			$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    			break;
    		case '6':
    			$str = 'abcdefghijklmnopqrstuvwxyz1234567890';
    			break;
    		default:
    			$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    			break;
    	}
    	$randString = '';
    	$len = strlen($str)-1;
    	for($i = 0;$i < $length;$i ++){
    		$num = mt_rand(0, $len);
    		$randString .= $str[$num];
    	}
    	return $randString ;
    }
    
    function display($tpl)
    {

        $cart =& m('cart');
        //$this->assign('cart_goods_kinds', $cart->get_kinds(SESS_ID, $this->visitor->get('user_id')));
        $this->assign('cart_goods_kinds', is_object($cart) && is_object($this->visitor) ? $cart->get_kinds(SESS_ID, $this->visitor->get('user_id')) : 0);
        /* 新消息 */
        $this->assign('new_message', isset($this->visitor) ? $this->_get_new_message() : '');
        
        $this->assign('currentApp', APP);
        //$this->assign('navs', $this->_get_navs());  // 自定义导航
		$this->assign('helps', $this->_get_helps());  // 帮助中心
		$this->assign('cate', $this->_get_goodscats1());  // 顶部导航1级
		$this->assign('arr0', $this->_get_goodscats2());  // 顶部导航2级
        $this->assign('acc_help', ACC_HELP);        // 帮助中心分类code
		$gcategorys = $this->_list_gcategorys();
		$this->assign('gcategorys', $this->_list_gcategorys());  // 所有商品分类

        $this->assign('site_title', Conf::get('site_title'));
        $this->assign('site_logo', Conf::get('site_logo'));
        $this->assign('statistics_code', Conf::get('statistics_code')); // 统计代码
        $current_url = explode('/', $_SERVER['REQUEST_URI']);
        $count = count($current_url);
        $this->assign('current_url',  $count > 1 ? $current_url[$count-1] : $_SERVER['REQUEST_URI']);// 用于设置导航状态(以后可能会有问题)
		$this->assign('hot_keywords', $this->_get_hot_keywords()); //热门搜索 by ancpp
        parent::display($tpl);
    }
	
	/* 热门搜索提到全局 by andcpp */
	function _get_hot_keywords()
    {
        $keywords = explode(',', conf::get('hot_search'));
        return $keywords;
    }
	/*end*/
	
    function login()
    {
    	
    	$this->assign('back', $_SERVER['HTTP_REFERER']);
    	
//    echo "<pre>"; var_dump($this->visitor);exit;
        if ($this->visitor->has_login)
        {
            $this->show_warning('has_login');

            return;
        }
        if (!IS_POST)
        {
            if (!empty($_GET['ret_url']))
            {
                $ret_url = trim($_GET['ret_url']);
            }
            else
            {
                if (isset($_SERVER['HTTP_REFERER']))
                {
                    $ret_url = $_SERVER['HTTP_REFERER'];
                }
                else
                {
                    $ret_url = SITE_URL . '/index.php';
                }
            }
            /* 防止登陆成功后跳转到登陆、退出的页面 */
            $ret_url = strtolower($ret_url);            
            if (str_replace(array('act=login', 'act=logout',), '', $ret_url) != $ret_url)
            {
                $ret_url = SITE_URL . '/index.php';
            }

            if (Conf::get('captcha_status.login'))
            {
                $this->assign('captcha', 1);
            }
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
            
            $this->assign('ret_url', rawurlencode($ret_url));
            $this->_curlocal(LANG::get('user_login'));
            $this->_config_seo('title', Lang::get('user_login') . ' - ' . Conf::get('site_title'));
            $this->display('login.html');
            /* 同步退出外部系统 */
            if (!empty($_GET['synlogout']))
            {
                $ms =& ms();
                echo $synlogout = $ms->user->synlogout();
            }
        }
        else
        {
        	//var_dump(Conf::get('captcha_status.login'));
        	//var_dump(base64_decode($_SESSION['captcha']) == strtolower($_POST['captcha']));
            if (Conf::get('captcha_status.login') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
            {
            	//$this->show_message("验证码错误",0);
                //$this->show_warning('captcha_failed');
				echo 3;	
                return;
            }
		
            $user_name = trim($_POST['username']);
            $password  = $_POST['password'];
            $ms =& ms();
            $user_id = $ms->user->auth($user_name, $password);
            if (!$user_id)
            {
                /* 未通过验证，提示错误信息 */
                //$this->show_warning($ms->user->get_error());
				echo 1;
                return;
            }
            else
            {
            	echo 2;
                /* 通过验证，执行登陆操作 */
                $this->_do_login($user_id);

                /* 同步登陆外部系统 */
                $synlogin = $ms->user->synlogin($user_id);
                return;
            }

            $this->show_message(Lang::get('login_successed') . $synlogin,
                'back_before_login', rawurldecode($_POST['ret_url']),
                'enter_member_center', 'index.php/member-login.html'
            );
        }
    }

    function pop_warning ($msg, $dialog_id = '',$url = '')
    {
        if($msg == 'ok')
        {
            if(empty($dialog_id))
            {
                $dialog_id = APP . '_' . ACT;
            }
            if (!empty($url))
            {
                echo "<script type='text/javascript'>window.parent.location.href='".$url."';</script>";
            }
            echo "<script type='text/javascript'>window.parent.js_success('" . $dialog_id ."');</script>";
        }
        else
        {
            header("Content-Type:text/html;charset=".CHARSET);
            $msg = is_array($msg) ? $msg : array(array('msg' => $msg));
            $errors = '';
            foreach ($msg as $k => $v)
            {
                $error = $v[obj] ? Lang::get($v[msg]) . " [" . Lang::get($v[obj]) . "]" : Lang::get($v[msg]);
                $errors .= $errors ? "<br />" . $error : $error;
            }
            echo "<script type='text/javascript'>window.parent.js_fail('" . $errors . "');</script>";
        }
    }

    function logout()
    {
        $this->visitor->logout();

        /* 跳转到登录页，执行同步退出操作 */
//         header("Location: index.php?app=member&act=login&synlogout=1");
        header("Location:/");
        return;
    }

    /* 执行登录动作 */
    function _do_login($user_id)
    {
        $mod_user =& m('member');

        $user_info = $mod_user->get(array(
            'conditions'    => "user_id = '{$user_id}'",
            'join'          => 'has_store',                 //关联查找看看是否有店铺
            'fields'        => '*',
        ));

        
        $db = &db();
        $sql = "select * from rc_member_lv where member_lv_id = '{$user_info['member_lv_id']}'";
        $level = $db->getRow($sql);
        $user_info['level'] = $level;
        /* 店铺ID */
        $my_store = empty($user_info['store_id']) ? 0 : $user_info['store_id'];

        /* 保证基础数据整洁 */
        //unset($user_info['store_id']);

        /* 分派身份 */
        $this->visitor->assign($user_info);

        /* 更新用户登录信息 */
        $mod_user->edit("user_id = '{$user_id}'", "last_login = '" . gmtime()  . "', last_ip = '" . real_ip() . "', logins = logins + 1");

        /* 更新购物车中的数据 */
        $mod_cart =& m('cart');
        $mod_cart->edit("(user_id = '{$user_id}' OR session_id = '" . SESS_ID . "') AND store_id <> '{$my_store}'", array(
            'user_id'    => $user_id,
            'session_id' => SESS_ID,
        ));

        /* 去掉重复的项 */
        $cart_items = $mod_cart->find(array(
            'conditions'    => "user_id='{$user_id}' GROUP BY spec_id",
            'fields'        => 'COUNT(spec_id) as spec_count, spec_id, rec_id',
        ));
        if (!empty($cart_items))
        {
            foreach ($cart_items as $rec_id => $cart_item)
            {
                if ($cart_item['spec_count'] > 1)
                {
                    $mod_cart->drop("user_id='{$user_id}' AND spec_id='{$cart_item['spec_id']}' AND rec_id <> {$cart_item['rec_id']}");
                }
            }
        }
    }

    /* 取得导航 */
    function _get_navs()
    {
        $cache_server =& cache_server();
        $key = 'common.navigation';
        $data = $cache_server->get($key);
        if($data === false)
        {
            $data = array(
                'header' => array(),
                'middle' => array(),
                'footer' => array(),
            );
            $nav_mod =& m('navigation');
            $rows = $nav_mod->find(array(
                'order' => 'type, sort_order',
            ));
            foreach ($rows as $row)
            {
                $data[$row['type']][] = $row;
            }
            $cache_server->set($key, $data, 86400);
        }

        return $data;
    }

	/* 取的帮助中心 */
    function _get_helps()
    {
		$db = &db();
		$sql = 'SELECT c.*, a.article_id,a.title ' .
				'FROM rc_acategory AS c LEFT JOIN rc_article AS a ON c.cate_id = a.cate_id '.
				" WHERE c.cate_id in(1,5,6,16) AND a.if_show = 1 " .
				'ORDER BY a.sort_order ASC, a.article_id';
		$res = $db->getall($sql);

		$arr = array();
		foreach ($res AS $key => $row)
		{
			$arr[$row['cate_id']]['cate_name']                    = $row['cate_name'];
			$arr[$row['cate_id']]['article'][$key]['article_id']  = $row['article_id'];
			$arr[$row['cate_id']]['article'][$key]['title']       = $row['title'];
		}

		return $arr;
    }

	/* 取的顶部商品导航 */
    function _get_goodscats1()
    {
		$db = &db();
		$cat = $db->getall("select * from rc_gcategory where parent_id = '0' and store_id=0 order by cate_id desc");

		return $cat;
	}
    function _get_goodscats2()
    {
		$db = &db();
		$cat = $db->getall("select * from rc_gcategory where parent_id = '0' and store_id=0 order by cate_id desc");
		foreach($cat as $k=>$val){
			$arr1_id[]=$val['cate_id'];	
		}
		$_cateid1 = empty($arr1_id) ? 0 : implode(",",$arr1_id);
		$arr0=$db->getall("select * from rc_gcategory where parent_id in ($_cateid1)");

		return $arr0;
	}
	
	/* 取所有商品分类 */
    function _list_gcategorys()
    {
        $cache_server =& cache_server();
        $key = 'page_goods_category';
        $data = $cache_server->get($key);
        if ($data === false)
        {
            $gcategory_mod =& bm('gcategory', array('_store_id' => 0));
            $gcategories = $gcategory_mod->get_list(-1,true);
    
            import('tree.lib');
            $tree = new Tree();
            $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
            $data = $tree->getArrayList(0);		
            $cache_server->set($key, $data, 3600);
        }


        return $data;
    }

    /**
     *    获取JS语言项
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function jslang($lang="")
    {
        $lang = Lang::fetch(lang_file('jslang'));
        parent::jslang($lang);
    }

    /**
     *    视图回调函数[显示小挂件]
     *
     *    @author    Garbin
     *    @param     array $options
     *    @return    void
     */
    function display_widgets($options)
    {
        $area = isset($options['area']) ? $options['area'] : '';
        $page = isset($options['page']) ? $options['page'] : '';
        if (!$area || !$page)
        {
            return;
        }
        include_once(ROOT_PATH . '/includes/widget.base.php');

        /* 获取该页面的挂件配置信息 */
        $widgets = get_widget_config($this->_get_template_name(), $page);

        /* 如果没有该区域 */
        if (!isset($widgets['config'][$area]))
        {
            return;
        }

        /* 将该区域内的挂件依次显示出来 */
        foreach ($widgets['config'][$area] as $widget_id)
        {
            $widget_info = $widgets['widgets'][$widget_id];
            $wn     =   $widget_info['name'];
            $options=   $widget_info['options'];

            $widget =& widget($widget_id, $wn, $options);
            $widget->display();
        }
    }

    /**
     *    获取当前使用的模板名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        return 'default';
    }

    /**
     *    获取当前使用的风格名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        return 'default';
    }

    /**
     *    当前位置
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _curlocal($arr)
    {
        $curlocal = array(array(
            'text'  => Lang::get('index'),
            'url'   => SITE_URL . '/index.php',
        ));
        if (is_array($arr))
        {
            $curlocal = array_merge($curlocal, $arr);
        }
        else
        {
            $args = func_get_args();
            if (!empty($args))
            {
                $len = count($args);
                for ($i = 0; $i < $len; $i += 2)
                {
                    $curlocal[] = array(
                        'text'  =>  $args[$i],
                        'url'   =>  $args[$i+1],
                    );
                }
            }
        }

        $this->assign('_curlocal', $curlocal);
    }
    
    function _init_visitor()
    {
        $this->visitor =& env('visitor', new UserVisitor());
    }
    
    
    /**
     *    获取分页信息
     *
     *    @author    yhao.bai
     *    @return    array
     */
    function _get_page($page_per = 10)
    {
    	$args = $this->get_params();
    	$page = empty($args[0]) || !is_numeric($args[0]) ? 1 : intval($args[0]);
    	
    	$start = ($page -1) * $page_per;
    
    	return array('limit' => "{$start},{$page_per}", 'curr_page' => $page, 'pageper' => $page_per);
    }
    
    
    /**
     * 格式化分页信息 - link 方式
     * @author yhao.bai
     * @param   array   $page
     * @param   int     $num    显示几页的链接
     */
    function _format_page(&$page, $num = 7)
    {
    
    	//var_dump($this->_view);
    
    	$page['page_count'] = ceil($page['item_count'] / $page['pageper']);
    	$mid = ceil($num / 2) - 1;
    	if ($page['page_count'] <= $num)
    	{
    		$from = 1;
    		$to   = $page['page_count'];
    	}
    	else
    	{
    		$from = $page['curr_page'] <= $mid ? 1 : $page['curr_page'] - $mid + 1;
    		$to   = $from + $num - 1;
    		$to > $page['page_count'] && $to = $page['page_count'];
    	}
    
    	 
    	$args = $this->get_params();
    
    	$page['page_links'] = array();
    	$page['first_link'] = ''; // 首页链接
    	$page['first_suspen'] = ''; // 首页省略号
    	$page['last_link'] = ''; // 尾页链接
    	$page['last_suspen'] = ''; // 尾页省略号
    	 
    	$link = array('app' => APP, 'act' => ACT);
    	 
    	
    	if($args){
    		unset($args[0]);
    		foreach($args as $key => $val){
    			$link['arg'.$key] = $val;
    		}
    	}


    	for ($i = $from; $i <= $to; $i++)
    	{
    			$link['arg0'] = $i;
    			$page['page_links'][$i] = $this->_view->build_url($link);
    	}
    	 
    	 
    	if (($page['curr_page'] - $from) < ($page['curr_page'] -1) && $page['page_count'] > $num)
    	{		$link['arg0'] = 1;
		    	$page['first_link'] = $this->_view->build_url($link);
		    
		    	if (($page['curr_page'] -1) - ($page['curr_page'] - $from) != 1)
			    			{
		    		$page['first_suspen'] = '..';
		    	 }
    	}
    
    	if (($to - $page['curr_page']) < ($page['page_count'] - $page['curr_page']) && $page['page_count'] > $num)
    	{
    		$link['arg0'] = $page['page_count'];
    		$page['last_link'] = $this->_view->build_url($link);
	    	if (($page['page_count'] - $page['curr_page']) - ($to - $page['curr_page']) != 1)
	    	{
	    		$page['last_suspen'] = '..';
	    	}
    	}
    
    	if($page['curr_page'] > $from)
    	{
	    	$link['arg0'] = $page['curr_page'] - 1;
	    	$page['prev_link'] = $this->_view->build_url($link);
   	    }
	    else
	    {
		    $page['prev_link'] = '';
	    }
    
	    if($page['curr_page'] < $to)
	    {
	    	$link['arg0'] = $page['curr_page'] + 1;
	    	$page['next_link'] = $this->_view->build_url($link);
	    }
	    else
	    {
    			$page['next_link'] = '';
    	}
   }
    
}
/**
 *    前台访问者
 *
 *    @author    Garbin
 *    @usage    none
 */
class UserVisitor extends BaseVisitor
{
    var $_info_key = 'user_info';

    /**
     *    退出登录
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function logout()
    {
        /* 将购物车中的相关项的session_id置为空 */
        $mod_cart =& m('cart');
        $mod_cart->edit("user_id = '" . $this->get('user_id') . "'", array(
            'session_id' => '',
        ));

        /* 退出登录 */
        parent::logout();
    }
}
/**
 *    商城控制器基类
 *
 *    @author    Garbin
 *    @usage    none
 */
class MallbaseApp extends FrontendApp
{

    function _run_action()
    {

        /* 只有登录的用户才可访问 */
        // if (!$this->visitor->has_login && in_array(APP, array('apply')))
        //{
            //ns add 
        	//$link = array("app" => "member","act" => "login" );
            //$_view = &v();
            //$url = $_view->build_url($link);
            //header('Location: '.$url.'?ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));
            //header('Location: index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

            //return;
        //}

        parent::_run_action(); 
    }

    function _config_view()
    {
        parent::_config_view();

        $template_name = $this->_get_template_name();
        $style_name    = $this->_get_style_name();

        $this->_view->template_dir = ROOT_PATH . "/themes/mall/{$template_name}";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/mall/{$template_name}";
        $this->_view->res_base     = SITE_URL . "/themes/mall/{$template_name}/styles/{$style_name}";
    }

    /* 取得支付方式实例 */
    function _get_payment($code, $payment_info)
    {
        include_once(ROOT_PATH . '/includes/payment.base.php');
        include(ROOT_PATH . '/includes/payments/' . $code . '/' . $code . '.payment.php');
        $class_name = ucfirst($code) . 'Payment';

        return new $class_name($payment_info);
    }

    /**
     *   获取当前所使用的模板名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        $template_name = Conf::get('template_name');
        if (!$template_name)
        {
            $template_name = 'default';
        }

        return $template_name;
    }

    /**
     *    获取当前模板中所使用的风格名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        $style_name = Conf::get('style_name');
        if (!$style_name)
        {
            $style_name = 'default';
        }

        return $style_name;
    }
    /* 获取当前店铺所使用的主题 */
    function _get_theme()
    {
    	$model_store =& m('store');
    	$store_info  = $model_store->get($this->visitor->get('manage_store'));
    	$theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
    	list($curr_template_name, $curr_style_name) = explode('|', $theme);
    	return array(
    			'template_name' => $curr_template_name,
    			'style_name'    => $curr_style_name,
    	);
    }
}

/**
 *    购物流程子系统基础类
 *
 *    @author    yhao.bai
 */
class ShoppingbaseApp extends FrontendApp
{
	var $_mod_figure;
	var $_mod_pay;
	/**
	 * 初始比相关模型
	 * @author yaho.bai
	 * @return void
	 */
	function __construct()
	{
		
		$this->_mod_figure = &m("figure");
		$this->_mod_pay = &m("payment");
		parent::__construct();
		
	}
	
    /**
     * 购物流程权限设置 
     * @author yaho.bai
     * @return void
     */
    function _run_action()
    {
        /* 只有登录的用户才可访问 */
    	$guestActs = array("index", "drop", "update", 'add', 'clear', 'login', 'register', 'check_user');
    	$guest = 0;
    	$args = $this->get_params();
    	if($args[0] == "guest" && isset($args[0])){
    		ecm_setcookie("RcIdentity", $args[0]);
    		$guest = 1;
    	}
    	
    	if(ecm_getcookie("RcIdentity") == "guest"){
    		$guest = 1;
    	}
    	
        if (!$this->visitor->has_login && !$guest && !in_array(ACT, $guestActs))
        {
        	
        	$view = &v();
        	$url = $view->build_url(array("app" => "member","act" => "login" ));
        	
            if (!IS_AJAX)
            {
                header('Location:'.$url.'?ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));

                return;
            }
            else
            {
                $this->json_error('login_please','login');
                return;
            }
        }

        parent::_run_action();
    }
    
    function login(){
    	if($this->visitor->has_login){
    		$this->json_result();
    	}else{
    		$this->json_error();
    	}
    }
    
    function _config_view()
    {
    	parent::_config_view();
    
        $template_name = $this->_get_template_name();
        $style_name    = $this->_get_style_name();

        $this->_view->template_dir = ROOT_PATH . "/themes/mall/{$template_name}/flow";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/mall/{$template_name}/flow";
        $this->_view->res_base     = SITE_URL . "/themes/mall/{$template_name}/styles/{$style_name}";
    }
    
    /**
     *    以购物车为单位获取购物车列表及商品项
     *
     *    @author    yhao.bai
     *    @return    void
     */
    function _cart_main()
    {
    	$carts = array();
    
    	$where_user_id = $this->visitor->get('user_id') ? " AND cart.user_id=" . $this->visitor->get('user_id') : '';
    
    	$cart_model =& m('cart');
    
    	$cart_items = $cart_model->find(array(
    			'conditions'    => 'session_id = \'' . SESS_ID . "'" . $where_user_id
    	));
    	if (empty($cart_items))
    	{
    		return array('goods_list' => array(), 'amount' => 0);
    	}
    
    	$amount    = 0;
    	$goods_num = 0;
    	foreach ($cart_items as $item)
    	{
    		/* 小计 */
    		$item['subtotal']   = $item['price'] * $item['quantity'];
    
    		/* 总计 */
    		$amount += $item['subtotal'];
    		$goods_num += $item['quantity'];
    		$carts[] = $item;
    	}
    
    	return array("goods_list" => $carts, "amount" => $amount, 'goods_num' => $goods_num);
    }
    
    /**
     * @author yhao.bai
     * @return arr
     */
    function orderFee(){
    	return array();
    }
    /**
     * 获取用户量体数据 
     * @author yaho.bai
     * @return array
     */
    function _memFigureData()
    {
    	if(isset($_SESSION["_cart_figure"])){
    		return $_SESSION["_cart_figure"];
    	}else{
    		if($this->visitor->has_login){
    			$data = $this->_mod_figure->get($this->visitor->get('user_id'));
    			if($data){
    				$_SESSION["_cart_figure"] = $data;
    				return $data;
    			}
    		}
    	}
    	return array();
    }
    
    /**
     * 量体数据列表
     * @author yaho.bai
     * @return array
     */
    function _memFigureList()
    {
    	
    	$_where = $this->visitor->has_login ? " || userid = '".$this->visitor->get('user_id')."'" : '';
    	
    	$list = $this->_mod_figure->find(array(
    				'conditions' => "figure_type = 0" . $_where,
    				'order'      => "userid DESC, idfigure ASC"
    			));
    	
    	return $list;
    }
	    
    /**
     * 获取量体数据
     * @param int $id 量体数据id
     * @return array
     */
    function _figureInfo($id)
    {
    	$id = intval($id);
    	if($id < 1) return false;
    	
    	$data = $this->_mod_figure->get($id);
    	
    	if($data["userid"] && $data["userid"] != $this->visitor->get('user_id')){
    		return false;
    	}
    	return $data;
    }

    /**
     * 默认支付方式
     * @return array
     */
    function _defPayment(){
    	if($_SESSION['_cart_payment']){
    		return $_SESSION['_cart_payment'];
    	}else{
    		if(!$this->visitor->has_login){
    			return array();
    		}
    		
    		$pay_id = $this->visitor->get('def_pay');
    		
    		$payment = $this->_mod_pay->get($pay_id);
    		
    		if($payment){
    			$_SESSION['_cart_payment'] = $payment;
    			return $payment;
    		}
    		
    		return array();
    	}
    }
    
    /**
     * 默认配送方式
     * @return array
     */
    function _defShipping(){
    	if($_SESSION['_cart_shipping']){
    		return $_SESSION['_cart_shipping'];
    	}else{
    		if(!$this->visitor->has_login){
    			return array();
    		}
    	
    		$ship_id = $this->visitor->get('def_ship');
    	
    		$shipping = $this->_mod_pay->get($pay_id);
    		
    		if($shipping){
    			$_SESSION['_cart_shipping'] = $shipping;
    			return $shipping;
    		}
    		return array();
    	}
    }
    
    /**
     * 支付列表
     */
    function payments(){
    	return $this->_mod_pay->find(array(
    				'conditions' => "enabled=1",
    				'order'      => "sort_order DESC"
    			));
    }
    
    /**
     * 支付详细信息
     */
    
    function payInfo($id){
    	$pay = $this->_mod_pay->get(array('payment_id' => $id, "enabled" => "1"));
    	if(!$pay){
    		return array();
    	}
    	return $pay;
    }
    
    
    /* 取得支付方式实例 */
    function _get_payment($code, $payment_info)
    {
    	include_once(ROOT_PATH . '/includes/payment.base.php');
    	include(ROOT_PATH . '/includes/payments/' . $code . '/' . $code . '.payment.php');
    	$class_name = ucfirst($code) . 'Payment';
    
    	return new $class_name($payment_info);
    }
}

/**
 *    用户中心子系统基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class MemberbaseApp extends MallbaseApp
{
    function _run_action()
    {

        /* 只有登录的用户才可访问 */
         if (!$this->visitor->has_login && !in_array(ACT, 
         		array('login', 'register', 'check_user', 'mbregister',
         				'verifycode','check_account','check_verifycode',
         				'findpsSMSCode','findpsEmail','findpsRestSMSCode','findpsResetEmail','upload',
         				'find_password','set_password','register_confirm')))
        
         
         {
            if (!IS_AJAX)
            {
                //ns add 
                $link = array("app" => "member","act" => "login" );
                $_view = &v();
                $url = $_view->build_url($link);

                header('Location: '.$url.'?ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));
                // header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));
                return;
            }
            else
            {
                $this->json_error('login_please');
                return;
            }
        }
        parent::_run_action();
    }
    /**
     *    当前选中的菜单项
     *
     *    @author    Garbin
     *    @param     string $item
     *    @return    void
     */
    function _curitem($item)
    {
        $this->assign('has_store', $this->visitor->get('has_store'));
        $this->assign('_member_menu', $this->_get_member_menu());
        $this->assign('_curitem', $item);
        $user = $this->visitor->get();
        $this->assign('user', $user);
    }
    /**
     *    当前选中的子菜单
     *
     *    @author    Garbin
     *    @param     string $item
     *    @return    void
     */
    function _curmenu($item)
    {
        $_member_submenu = $this->_get_member_submenu();
        foreach ($_member_submenu as $key => $value)
        {
            $_member_submenu[$key]['text'] = $value['text'] ? $value['text'] : Lang::get($value['name']);
        }

        $this->assign('_member_submenu', $_member_submenu);
        $this->assign('_curmenu', $item);
    }
    /**
     *    获取子菜单列表
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_member_submenu()
    {
        return array();
    }
    /**
     *    获取用户中心全局菜单列表
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _get_member_menu()
    {
        $menu = array();
        $_view = &v();
        //酷客中心-交易中心
        $menu['deal'] = array(
            'name'  => '我的账户',
            'text'  => '我的账户',
            'submenu' => array(
	                '1'  => array(
	                    'text'  =>'账户概览',
	                    'url'   => 'member.html',
	                    'name'  => '账户概览',
	                ),
            		'2'  => array(
            				'text'  => '我的订单',
            				'url'   => 'buyer_order.html',
            				'name'  => '我的订单',
            		),
            		'3'  => array(
            				'text'  => '我的设计',
            				'url'   => 'my_favorite_2.html',
            				'name'  => '我的设计',
            		),
//             		'4'  => array(
//             				'text'  => '我的收藏',
//             				'url'   => 'my_favorite.html',
//             				'name'  => '我的收藏',
//             		),
            		'5'  => array(
            				'text'  => '我的推荐',
            				'url'   => 'my_recommend.html',
            				'name'  => '我的推荐',
            		),
            		'6'  => array(
            				'text'  => '我的酷特币',
            				'url'   => 'kuke-coin.html',
            				'name'  => 'my_rc',
            		),
            		'7'  => array(
            				'text'  => '我的积分',
            				'url'   => 'kuke-point.html',
            				'name'  => 'my_rc',
            		),
            		'8'  => array(
            				'text'  => '我的优惠券',
            				'url'   => 'kuke-coupon.html',
            				'name'  => 'my_rc',
            		),
            		'9'  => array(
            				'text'  => '充值和提现',
            				'url'   => 'account.html',
            				'name'  => 'my_rc',
            		),

            		'10'  => array(
            				'text'  => '站内消息',
            				'url'   => 'message-newpm.html',
            				'name'  => 'my_rc',
            		),
            ),
        );
        //酷客中心-酷客信息
        $menu['info'] = array(
        		'name'  => '我的资料',
        		'text'  => '我的资料',
        		'submenu' => array(
        				'1'  => array(
        						'text'  =>'个人信息',
        						'url'   => 'kuke-userinfo.html',
        						'name'  => 'my_rc',
        				),
        				'2'  => array(
        						'text'  => '地址管理',
        						'url'   => 'kuke-address.html',
        						'name'  => 'my_rc',
        				),
        				'3'  => array(
        						'text'  => '安全设置',
        						'url'   => 'kuke-safeset.html',
        						'name'  => 'my_rc',
        				),
        				'4'  => array(
        						'text'  => '上传管理',
        						'url'   => 'kuke-manageimg.html',
        						'name'  => 'my_rc',
        				),
        				'5'  => array(
        						'text'  => '图片分享',
        						'url'   => 'kuke-camera.html',
        						'name'  => 'my_rc',
        				),

        		),
        );
        
        /* ns up 我的供应商 
        $menu['my_ecmall'] = array(
            // 'name'  => 'my_ecmall',
            // 'text'  => Lang::get('my_ecmall'),
            'name'  => '供应商',
            'text'  => '我是供应商',

             'submenu'   => array(
                'overview'  => array(
                    'text'  => Lang::get('overview'),
                    'url'   => $_view->build_url(array("app" => "member")),
                    'name'  => 'overview',
                    'icon'  => 'ico1',
                ),
                'my_profile'  => array(
                    'text'  => Lang::get('my_profile'),
                    'url'   => $_view->build_url(array("app" => "member","act" => "profile")),
                    'name'  => 'my_profile',
                    'icon'  => 'ico2',
                ),
                'message'  => array(
                    'text'  => Lang::get('message'),
                    'url'   => $_view->build_url(array("app" => "message","act" => "newpm")),
                    'name'  => 'message',
                    'icon'  => 'ico3',
                ),
                'friend'  => array(
                    'text'  => Lang::get('friend'),
                    'url'   => $_view->build_url(array("app" => "friend")),
                    'name'  => 'friend',
                    'icon'  => 'ico4',
                ),
            	'account'  => array(
            				'text'  => '资金管理',
            				'url'   => $_view->build_url(array("app" => "account")),
            				'name'  => 'account',
            				'icon'  => 'ico4',
            		),
            ),
        );*/

		//var_dump($this->visitor);			
		//echo $this->visitor->get("serve_type"); echo "<br />"; 
	    //echo $this->visitor->get("has_serve");
		//echo $this->visitor->get('manage_store');

		if($this->visitor->get("has_serve"))
		{
			//我是供应商
			if($this->visitor->get("serve_type") == '1')
			{
				/* 指定了要管理的店铺 */
				$menu['im_seller'] = array(
					'name'  => 'im_seller',
					'text'  => Lang::get('im_seller'),
					'submenu'   => array(),
				);
				//产品管理
				$menu['im_seller']['submenu']['my_goods'] = array(
						'text'  => Lang::get('my_goods'),
						'url'   => $_view->build_url(array("app" => "my_goods")),
						'name'  => 'my_goods',
						'icon'  => 'ico8',
				);
				//分类管理
				$menu['im_seller']['submenu']['my_category'] = array(
						'text'  => Lang::get('my_category'),
						'url'   => $_view->build_url(array("app" => "my_category")),
						'name'  => 'my_category',
						'icon'  => 'ico9',
				);
				//订单管理
				$menu['im_seller']['submenu']['order_manage'] = array(
						'text'  => Lang::get('order_manage'),
						'url'   => $_view->build_url(array("app" => "seller_order")),
						'name'  => 'order_manage',
						'icon'  => 'ico10',
				);
				//店铺设置
				$menu['im_seller']['submenu']['my_store']  = array(
						'text'  => Lang::get('my_store'),
						'url'   => $_view->build_url(array("app" => "my_store")),
						'name'  => 'my_store',
						'icon'  => 'ico11',
				);
				//主题设置
				$menu['im_seller']['submenu']['my_theme']  = array(
						'text'  => Lang::get('my_theme'),
						'url'   => $_view->build_url(array("app" => "my_theme")),
						'name'  => 'my_theme',
						'icon'  => 'ico12',
				);
			}
			/* 我是服务点 */
			if($this->visitor->get("serve_type") == '2')
			{
			$menu['im_service'] = array(
				'name'  => 'im_service',
				'text'  => Lang::get('im_service'),
				'submenu'   => array('im_service'  => array(
						'text'  => Lang::get('service'),
						'url'   => $this->_view->build_url(array('app' => 'servicemember', 'act' => 'profile')),
						'name'  => 'service',
						'icon'  => 'ico20',
					),'im_servedetail'  => array(
						'text'  => Lang::get('servedetail'),
						'url'   => $this->_view->build_url(array('app' => 'servicemember', 'act' => 'servedetail')),
						'name'  => 'servedetail',
						'icon'  => 'ico20',
					),'im_brokerage_log'  => array(
						'text'  => Lang::get('brokerage_log'),
						'url'   => $this->_view->build_url(array('app' => 'brokerage_log', 'act' => 'index')),
						'name'  => 'brokerage_log',
						'icon'  => 'ico20',
					),'im_figure'  => array(
						'text'  => Lang::get('figure'),
						'url'   => $this->_view->build_url(array('app' => 'figure', 'act' => 'index')),
						'name'  => 'figure',
						'icon'  => 'ico20',
					),'im_subscribeinfo'  => array(
						'text'  => Lang::get('subscribeinfo'),
						'url'   => $this->_view->build_url(array('app' => 'subscribeinfo', 'act' => 'index')),
						'name'  => 'subscribeinfo',
						'icon'  => 'ico20',
					),
					'im_employee'  => array(
						'text'  => Lang::get('employee'),
						'url'   => $this->_view->build_url(array('app' => 'employee', 'act' => 'index')),
						'name'  => 'employee',
						'icon'  => 'ico20',
					),
					
					'my_order_serve'  => array(
						'text'  => Lang::get('my_order_serve'),
						'url' => $_view->build_url(array("app" => "my_order_serve")),
						//'url' => '#',
						'name'  => 'my_order_serve',
						'icon'  => 'ico5',
					),
					
					'my_order_serve_2'  => array(
						'text'  => Lang::get('my_order_serve_2'),
						'url' => $_view->build_url(array("app" => "my_order_serve_2")),
						//'url' => '#',
						'name'  => 'my_order_serve_2',
						'icon'  => 'ico5',
					),					
					
					),				
			);
		}elseif($this->visitor->get("serve_type")=='3')
		{
			//我是加盟商
			$menu['im_partner'] = array(
        	'name'  => 'im_partner',
            'text'  => Lang::get('im_partner'),
        	'submenu'   => array('im_partner'  => array(
                    'text'  => Lang::get('partner'),
                    'url'   => $this->_view->build_url(array('app' => 'servicemember', 'act' => 'profile')),
                    'name'  => 'service',
                    'icon'  => 'ico20',
                ),

                'im_brokerage_user'  => array(
                    'text'  => '我的客户',
                    'url'   => $this->_view->build_url(array('app' => 'brokerage_log', 'act' => 'index')),
                    'name'  => 'brokerage_log',
                    'icon'  => 'ico20',
                ),
                'im_brokerage_order'  => array(
                    'text'  => '我的订单',
                    'url'   => $this->_view->build_url(array('app' => 'brokerage_log', 'act' => 'index')),
                    'name'  => 'brokerage_log',
                    'icon'  => 'ico20',
                ),

                'im_brokerage_log'  => array(
                    'text'  => Lang::get('brokerage_log'),
                    'url'   => $this->_view->build_url(array('app' => 'brokerage_log', 'act' => 'index')),
                    'name'  => 'brokerage_log',
                    'icon'  => 'ico20',
                ),
                
                ),
			);
		}
        
        
		}
        
        if (!$this->visitor->get('has_store') && Conf::get('store_allow'))
        {
            $menu['overview'] = array(
                'text' => Lang::get('apply_store'),
                'url'  => 'index.php?app=apply',
            );
        }
        if ($this->visitor->get('manage_store'))
        {
			//ns up 手机信息
            /* 指定了要管理的店铺 */
            $menu['im_seller'] = array(
                'name'  => 'im_seller',
                'text'  => Lang::get('im_seller'),
                'submenu'   => array(),
            );
			//产品管理
            $menu['im_seller']['submenu']['my_goods'] = array(
                    'text'  => Lang::get('my_goods'),
                    'url'   => $_view->build_url(array("app" => "my_goods")),
                    'name'  => 'my_goods',
                    'icon'  => 'ico8',
            );
            //分类管理
            $menu['im_seller']['submenu']['my_category'] = array(
                    'text'  => Lang::get('my_category'),
                    'url'   => $_view->build_url(array("app" => "my_category")),
                    'name'  => 'my_category',
                    'icon'  => 'ico9',
            );
            //订单管理
            $menu['im_seller']['submenu']['order_manage'] = array(
                    'text'  => Lang::get('order_manage'),
                    'url'   => $_view->build_url(array("app" => "seller_order")),
                    'name'  => 'order_manage',
                    'icon'  => 'ico10',
            );
            //店铺设置
            $menu['im_seller']['submenu']['my_store']  = array(
                    'text'  => Lang::get('my_store'),
                    'url'   => $_view->build_url(array("app" => "my_store")),
                    'name'  => 'my_store',
                    'icon'  => 'ico11',
            );
            //主题设置
            $menu['im_seller']['submenu']['my_theme']  = array(
                    'text'  => Lang::get('my_theme'),
                    'url'   => $_view->build_url(array("app" => "my_theme")),
                    'name'  => 'my_theme',
                    'icon'  => 'ico12',
            );
        }

        return $menu;
    }
}

/**
 *    店铺管理子系统基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class StoreadminbaseApp extends MemberbaseApp
{
    function _run_action()
    {
        /* 只有登录的用户才可访问 */
        if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register', 'check_user')))
        {
            if (!IS_AJAX)
            {
            //ns add 
            $link = array("app" => "member","act" => "login" );
            $_view = &v();
            $url = $_view->build_url($link);
            header('Location: '.$url.'?ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));
                // header('Location:index.php?app=member&act=login&ret_url=' . rawurlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']));
                return;
            }
            else
            {
                $this->json_error('login_please');
                return;
            }
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (strpos($referer, 'act=login') === false)
        {
            $ret_url = $_SERVER['HTTP_REFERER'];
            $ret_text = 'go_back';
        }
        else
        {
            $ret_url = SITE_URL . '/index.php';
            $ret_text = 'back_index';
        }

        /* 检查是否是店铺管理员 */
        if (!$this->visitor->get('manage_store'))
        {
            /* 您不是店铺管理员 */
            $this->show_warning(
                'not_storeadmin',
                'apply_now', 'index.php?app=apply',
                $ret_text, $ret_url
            );

            return;
        }

        /* 检查是否被授权 */
        $privileges = $this->_get_privileges();
        if (!$this->visitor->i_can('do_action', $privileges))
        {
            $this->show_warning('no_permission', $ret_text, $ret_url);

            return;
        }

        /* 检查店铺开启状态 */
        $state = $this->visitor->get('state');
        if ($state == 0)
        {
            $this->show_warning('apply_not_agree', $ret_text, $ret_url);

            return;
        }
        elseif ($state == 2)
        {
            $this->show_warning('store_is_closed', $ret_text, $ret_url);

            return;
        }

        /* 检查附加功能 */
        if (!$this->_check_add_functions())
        {
            $this->show_warning('not_support_function', $ret_text, $ret_url);
            return;
        }

        parent::_run_action();
    }
    function _get_privileges()
    {
        $store_id = $this->visitor->get('manage_store');
        $privs = $this->visitor->get('s');

        if (empty($privs))
        {
            return '';
        }

        foreach ($privs as $key => $admin_store)
        {
            if ($admin_store['store_id'] == $store_id)
            {
                return $admin_store['privs'];
            }
        }
    }
    
    /* 获取当前店铺所使用的主题 */
    function _get_theme()
    {
        $model_store =& m('store');
        $store_info  = $model_store->get($this->visitor->get('manage_store'));
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($curr_template_name, $curr_style_name) = explode('|', $theme);
        return array(
            'template_name' => $curr_template_name,
            'style_name'    => $curr_style_name,
        );
    }

    function _check_add_functions()
    {
        $apps_functions = array( // app与function对应关系
            'seller_groupbuy' => 'groupbuy',
            'coupon' => 'coupon',
        );
        if (isset($apps_functions[APP]))
        {
            $store_mod =& m('store');
            $settings = $store_mod->get_settings($this->_store_id);
            $add_functions = isset($settings['functions']) ? $settings['functions'] : ''; // 附加功能
            if (!in_array($apps_functions[APP], explode(',', $add_functions)))
            {
                return false;
            }
        }
        return true;
    }
}

/**
 *    店铺控制器基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class StorebaseApp extends FrontendApp
{
    var $_store_id;

    /**
     * 设置店铺id
     *
     * @param int $store_id
     */
    function set_store($store_id)
    {
        $this->_store_id = intval($store_id);

        /* 有了store id后对视图进行二次配置 */
        $this->_init_view();
        $this->_config_view();
    }

    function _config_view()
    {
        parent::_config_view();
        $template_name = $this->_get_template_name();
        $style_name    = $this->_get_style_name();

        $this->_view->template_dir = ROOT_PATH . "/themes/store/{$template_name}";
        $this->_view->compile_dir  = ROOT_PATH . "/temp/compiled/store/{$template_name}";
        $this->_view->res_base     = SITE_URL . "/themes/store/{$template_name}/styles/{$style_name}";
    }

    /**
     * 取得店铺信息
     */
    function get_store_data()
    {
        $cache_server =& cache_server();
        $key = 'function_get_store_data_' . $this->_store_id;
        $store = $cache_server->get($key);
        if ($store === false)
        {
            $store = $this->_get_store_info();
            if (empty($store))
            {
                $this->show_warning('the_store_not_exist');
                exit;
            }
            if ($store['state'] == 2)
            {
                $this->show_warning('the_store_is_closed');
                exit;
            }
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store_mod =& m('store');
            $store['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($store['credit_value'], $step);

            empty($store['store_logo']) && $store['store_logo'] = Conf::get('default_store_logo');
            $store['store_owner'] = $this->_get_store_owner();
            $store['store_navs']  = $this->_get_store_nav();
            $goods_mod =& m('goods');
            $store['goods_count'] = $goods_mod->get_count_of_store($this->_store_id);
            $store['store_gcates']= $this->_get_store_gcategory();
            $store['sgrade'] = $this->_get_store_grade('grade_name');
            $functions = $this->_get_store_grade('functions');
            $store['functions'] = array();
            if ($functions)
            {
                $functions = explode(',', $functions);
                foreach ($functions as $k => $v)
                {
                    $store['functions'][$v] = $v;
                }
            }
            $cache_server->set($key, $store, 1800);
        }

        return $store;
    }

    /* 取得店铺信息 */
    function _get_store_info()
    {
        if (!$this->_store_id)
        {
            /* 未设置前返回空 */
            return array();
        }
        static $store_info = null;
        if ($store_info === null)
        {
            $store_mod  =& m('store');
            $store_info = $store_mod->get_info($this->_store_id);
        }

        return $store_info;
    }

    /* 取得店主信息 */
    function _get_store_owner()
    {
        $user_mod =& m('member');
        $user = $user_mod->get($this->_store_id);

        return $user;
    }

    /* 取得店铺导航 */
    function _get_store_nav()
    {
        $article_mod =& m('article');
        return $article_mod->find(array(
            'conditions' => "store_id = '{$this->_store_id}' AND cate_id = '" . STORE_NAV . "' AND if_show = 1",
            'order' => 'sort_order',
            'fields' => 'title',
        ));
    }

    /*  取的店铺等级   */
    function _get_store_grade($field)
    {
        $store_info = $store_info = $this->_get_store_info();
        $sgrade_mod =& m('sgrade');
        $result = $sgrade_mod->get_info($store_info['sgrade']);
        return $result[$field];
    }
    /* 取得店铺分类 */
    function _get_store_gcategory()
    {
        $gcategory_mod =& bm('gcategory', array('_store_id' => $this->_store_id));
        $gcategories = $gcategory_mod->get_list(-1, true);
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getArrayList(0);
    }

    /**
     *    获取当前店铺所设定的模板名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_template_name()
    {
        $store_info = $this->_get_store_info();
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($template_name, $style_name) = explode('|', $theme);

        return $template_name;
    }

    /**
     *    获取当前店铺所设定的风格名称
     *
     *    @author    Garbin
     *    @return    string
     */
    function _get_style_name()
    {
        $store_info = $this->_get_store_info();
        $theme = !empty($store_info['theme']) ? $store_info['theme'] : 'default|default';
        list($template_name, $style_name) = explode('|', $theme);

        return $style_name;
    }
}

/* 实现消息基础类接口 */
class MessageBase extends MallbaseApp {};

/* 实现模块基础类接口 */
class BaseModule  extends FrontendApp {};

/* 消息处理器 */
require(ROOT_PATH . '/eccore/controller/message.base.php');

?>
