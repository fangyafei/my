<?php

@set_time_limit(0);
@ini_set('display_errors',1);
ini_set("soap.wsdl_cache_enabled", "0"); 

$GLOBALS['beginTime'] = microtime(TRUE);

if(!defined('APP_NAME')) exit("APP_NAME not defined!");//项目路径
if(!defined('PROJECT_PATH')) exit("APP_NAME not defined!");//虚拟路径
if(!defined('KERNEL_PATH')) exit("APP_NAME not defined!");//核心文件路径
// 项目日志目录
defined('LOG_PATH') or define('LOG_PATH',  './logs/');
//记录内存初始使用
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();
//调试模式
defined('APP_DEBUG') or define('APP_DEBUG',1);

//数据库字符集
define('DB_CHARSET', "utf8");
//加载语言包
// define('LANG','CN');
define('IS_SHELL',1);

if(APP_DEBUG)ini_set('display_errors', 1);
//公共类与公共配置
set_include_path(get_include_path() . PATH_SEPARATOR . KERNEL_PATH . 'class');
set_include_path(get_include_path() . PATH_SEPARATOR . KERNEL_PATH . 'config');

include_once KERNEL_PATH.'functions.php';//公共函数
include_once KERNEL_PATH.'class/Log.class.php';//日志
include_once KERNEL_PATH.'class/cls_json.php'; //JSON配置
include_once KERNEL_PATH.'class/cls_ecshop.php'; //JSON配置
$json = new JSON;

include_once KERNEL_PATH.'config/db_config.php'; //DB配置
$ecs  = new ECS($db_name, $prefix);

include_once KERNEL_PATH.'class/cls_mysql.php';
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;

include_once KERNEL_PATH.'class/model.base.php';
include_once KERNEL_PATH.'class/app.base.php';

define('IN_ECM', true);
define('ROOT_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))));   //该常量是ECCore要求的
/* 定义PHP_SELF常量 */
define('PHP_SELF',  htmlentities($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']));
define('SITE_URL','http://yanyi.fangyafei.com/');
define('DB_CONFIG','mysql://fangyafei:f631079@localhost:3306/fangyafei');
define('DB_PREFIX','ecm_');
define('LANG','sc-utf-8');
define('COOKIE_DOMAIN','');
define('COOKIE_PATH','/');
define('ECM_KEY','ff42fd23d8ce4dc71cc27bafc4d7484f');
define('MALL_SITE_ID','EMNMOQTnd6kuvx9K');
define('ENABLED_GZIP',0);
define('DEBUG_MODE',0);
define('CACHE_SERVER','default');
define('MEMBER_TYPE','default');
define('ENABLED_SUBDOMAIN',0);
define('SUBDOMAIN_SUFFIX','');
define('SESSION_TYPE','mysql');
define('SESSION_MEMCACHED','localhost:11211');
define('CACHE_MEMCACHED','localhost:11211');
define('CHANGE_TYPE1','1');
define('CHANGE_TYPE2','2');
define('CHANGE_TYPE3','3');
define('APPLY','1');
define('DEPOSIT','2');
define('APPLY_UNPROCESS',0);
define('APPLY_FINISHED',1);
define('APPLY_CANCELED',2);
define('APPLY_INVALID',3);

define('CHARSET', 'utf-8');

/* 订单状态 */
define('ORDER_SUBMITTED', 10);                 // 针对货到付款而言，他的下一个状态是卖家已发货
define('ORDER_PENDING', 11);                   // 等待买家付款
define('ORDER_ACCEPTED', 20);                  // 买家已付款，等待卖家发货
define('ORDER_SHIPPED', 30);                   // 卖家已发货
define('ORDER_FINISHED', 40);                  // 交易成功
define('ORDER_CANCELED', 0);                   // 交易已取消

//include_once KERNEL_PATH.'class/app.base.php';
//include_once KERNEL_PATH.'class/ecapp.base.php';
//include_once KERNEL_PATH.'class/frontend.base.php';

/**
 * 创建MySQL数据库对象实例
 *
 * @author  wj
 * @return  object
 */
function &db()
{
    include_once(KERNEL_PATH . 'class/mysql.php');
    static $db = null;
    if ($db === null)
    {
        $cfg = parse_url(DB_CONFIG);

        if ($cfg['scheme'] == 'mysql')
        {
            if (empty($cfg['pass']))
            {
                $cfg['pass'] = '';
            }
            else
            {
                $cfg['pass'] = urldecode($cfg['pass']);
            }
            $cfg ['user'] = urldecode($cfg['user']);

            if (empty($cfg['path']))
            {
                trigger_error('Invalid database name.', E_USER_ERROR);
            }
            else
            {
                $cfg['path'] = str_replace('/', '', $cfg['path']);
            }

            $charset = (CHARSET == 'utf-8') ? 'utf8' : CHARSET;
            $db = new ec_cls_mysql();
            $db->cache_dir = ROOT_PATH. '/temp/query_caches/';
            $db->connect($cfg['host']. ':' .$cfg['port'], $cfg['user'],
                $cfg['pass'], $cfg['path'], $charset);
        }
        else
        {
            trigger_error('Unkown database type.', E_USER_ERROR);
        }
    }

    return $db;
}




/**
 *  获取一个模型
 *
 *  @author Garbin
 *  @param  string $model_name
 *  @param  array  $params
 *  @param  book   $is_new
 *  @return object
 */
function &m($model_name, $params = array(), $is_new = false)
{
	
    static $models = array();
    $model_hash = md5($model_name . var_export($params, true));
   
    if ($is_new || !isset($models[$model_hash]))
    { 
    	
        $model_file = ROOT_PATH . '/includes/models/' . $model_name . '.model.php';
   
        if (!is_file($model_file))
        {
            /* 不存在该文件，则无法获取模型 */
            return false;
        }
        include_once($model_file);
        $model_name = ucfirst($model_name) . 'Model';
        if ($is_new)
        {
            return new $model_name($params, db());
        }
        $models[$model_hash] = new $model_name($params, db());
    }

    return $models[$model_hash];
}

/**
 * 实例化一个接口
 * @param  string $filename
 * @return
 * @author Ruesin
 */
function &f ($filename){

	$file = ROOT_PATH . '/includes/libraries/' . $filename . '.lib.php';
	if (!is_file($file)) return false;

	include_once($file);
	$name = ucfirst($filename);
	return new $name();
}

/**
 *    获取订单类型对象
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
function &ot_mob($type, $params = array())
{
	static $order_type = null;
	if ($order_type === null)
	{
		/* 加载订单类型基础类 */
		include_once(KERNEL_PATH . 'class/order.base.php');
		include(KERNEL_PATH . 'class/' . $type . '.otype.php');
		$class_name = ucfirst($type) . 'Order';
		$order_type = new $class_name($params);
	}

	return $order_type;
}

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access   public
 * @param    mix      $item_list      列表数组或字符串,如果为字符串时,字符串只接受数字串
 * @param    string   $field_name     字段名称
 * @author   wj
 *
 * @return   void
 */
function db_create_in($item_list, $field_name = '')
{
	if (empty($item_list))
	{
		return $field_name . " IN ('') ";
	}
	else
	{
		if (!is_array($item_list))
		{
			$item_list = explode(',', $item_list);
			foreach ($item_list as $k=>$v)
			{
				$item_list[$k] = intval($v);
			}
		}

		$item_list = array_unique($item_list);
		$item_list_tmp = '';
		foreach ($item_list AS $item)
		{
			if ($item !== '')
			{
				$item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
			}
		}
		if (empty($item_list_tmp))
		{
			return $field_name . " IN ('') ";
		}
		else
		{
			return $field_name . ' IN (' . $item_list_tmp . ') ';
		}
	}
}

/**
 * 创建目录（如果该目录的上级目录不存在，会先创建上级目录）
 * 依赖于 ROOT_PATH 常量，且只能创建 ROOT_PATH 目录下的目录
 * 目录分隔符必须是 / 不能是 \
 *
 * @param   string  $absolute_path  绝对路径
 * @param   int     $mode           目录权限
 * @return  bool
 */
function ecm_mkdir($absolute_path, $mode = 0777)
{
	if (is_dir($absolute_path))
	{
		return true;
	}

	$root_path      = ROOT_PATH;
	$relative_path  = str_replace($root_path, '', $absolute_path);
	$each_path      = explode('/', $relative_path);
	$cur_path       = $root_path; // 当前循环处理的路径
	foreach ($each_path as $path)
	{
		if ($path)
		{
			$cur_path = $cur_path . '/' . $path;
			if (!is_dir($cur_path))
			{
				if (@mkdir($cur_path, $mode))
				{
					fclose(fopen($cur_path . '/index.htm', 'w'));
				}
				else
				{
					return false;
				}
			}
		}
	}

	return true;
}
/**
 * 重写
 * 递归方式的对变量中的特殊字符进行转义
 * @access  public
 * @param   mix     $value
 * @return  mix
 */
function addslashes_deep($value,$htmlspecialchars=false)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        if(is_array($value))
        {
         foreach($value as $key => $v)
         {
          unset($value[$key]);
          
          if($htmlspecialchars==true)
          {
           $key=get_magic_quotes_gpc()? addslashes(stripslashes(htmlspecialchars($key,ENT_NOQUOTES))) : addslashes(htmlspecialchars($key,ENT_NOQUOTES));
          }
          else{
           $key=get_magic_quotes_gpc()? addslashes(stripslashes($key)) : addslashes($key);
          }
          
          if(is_array($v))
          {
           $value[$key]=addslashes_deep($v);
          }
          else{
           if($htmlspecialchars==true)
           {
            $value[$key]=get_magic_quotes_gpc()? addslashes(stripslashes(htmlspecialchars($v,ENT_NOQUOTES))) : addslashes(htmlspecialchars($v,ENT_NOQUOTES));
           }
           else{
            $value[$key]=get_magic_quotes_gpc()? addslashes(stripslashes($v)) : addslashes($v);
           }
          }
         }
        }
        else{
         if($htmlspecialchars==true)
         {
          $value=get_magic_quotes_gpc()? addslashes(stripslashes(htmlspecialchars($value,ENT_NOQUOTES))) : addslashes(htmlspecialchars($value,ENT_NOQUOTES));
         }
         else{
          $value=get_magic_quotes_gpc()? addslashes(stripslashes($value)) : addslashes($value);
         }
        }
        
        return $value;
    }
}
/**
 * 获得当前格林威治时间的时间戳
 *
 * @return  integer
 */
function gmtime()
{
	return (time() - date('Z'));
}

/**
 * 获得用户的真实IP地址
 *
 * @return  string
 */
function real_ip()
{
	static $realip = NULL;

	if ($realip !== NULL)
	{
		return $realip;
	}

	if (isset($_SERVER))
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

			/* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
			foreach ($arr AS $ip)
			{
				$ip = trim($ip);

				if ($ip != 'unknown')
				{
					$realip = $ip;

					break;
				}
			}
		}
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$realip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else
		{
			if (isset($_SERVER['REMOTE_ADDR']))
			{
				$realip = $_SERVER['REMOTE_ADDR'];
			}
			else
			{
				$realip = '0.0.0.0';
			}
		}
	}
	else
	{
		if (getenv('HTTP_X_FORWARDED_FOR'))
		{
			$realip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_CLIENT_IP'))
		{
			$realip = getenv('HTTP_CLIENT_IP');
		}
		else
		{
			$realip = getenv('REMOTE_ADDR');
		}
	}

	preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
	$realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

	return $realip;
}
/**
 *    导入一个类
 *
 *    @author    Garbin
 *    @return    void
 */
function import()
{
	$c = func_get_args();
	if (empty($c))
	{
		return;
	}
	array_walk($c, create_function('$item, $key', 'include_once(ROOT_PATH . \'/includes/libraries/\' . $item . \'.php\');'));
}

/**
 * 根据token 获得user_info
 */
function getUserInfo($token)
{
	$user_mod = m("member");
	$user_info = $user_mod->get(array(
			'conditions'	=>	"user_token= '$token'",
			));
	return $user_info;
}
/**
 * 获得网站的URL地址
 *
 * @return  string
 */
function site_url()
{
	return get_domain() . substr(PHP_SELF, 0, strrpos(PHP_SELF, '/'));
}
/**
 * 获得当前的域名
 *
 * @return  string
 */
function get_domain()
{
	/* 协议 */
	$protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';

	/* 域名或IP地址 */
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
	{
		$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
	elseif (isset($_SERVER['HTTP_HOST']))
	{
		$host = $_SERVER['HTTP_HOST'];
	}
	else
	{
		/* 端口 */
		if (isset($_SERVER['SERVER_PORT']))
		{
			$port = ':' . $_SERVER['SERVER_PORT'];

			if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol))
			{
				$port = '';
			}
		}
		else
		{
			$port = '';
		}

		if (isset($_SERVER['SERVER_NAME']))
		{
			$host = $_SERVER['SERVER_NAME'] . $port;
		}
		elseif (isset($_SERVER['SERVER_ADDR']))
		{
			$host = $_SERVER['SERVER_ADDR'] . $port;
		}
	}

	return $protocol . $host;
}


include_once ROOT_PATH.'/includes/global.lib.php';



