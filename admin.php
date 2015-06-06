<?php
if (defined('STDIN'))
{//执行cron时的配置
	set_time_limit(0);
	ini_set('display_error', 1);
	declare(ticks = 1);
	chdir(dirname(__FILE__));
	if (isset($_SERVER['HOSTNAME']) && in_array($_SERVER['HOSTNAME'], array('master.demopuppet.com', 'dev_136_100'), true)) {
		define('ENV_CONF', 'CLI_LJSRV_DEV_CONFIG');
	} else {
		define('ENV_CONF', 'CLI_LJSRV_CONFIG');
	}
	if(is_file(ENV_CONF)) {
    	$_SERVER = array_merge($_SERVER, parse_ini_file(ENV_CONF));//模拟获取环境变量
	} else {
    	exit('queue运行需要该文件：' . ENV_CONF . "，请依据线上LJSRV_CONFIG自行修改相关参数,放入根目录中, 开发机使用CLI_LJSRV_DEV_CONFIG, cron队列机使用CLI_LJSRV_CONFIG\n");
	}
	function caught_shut_down() {
		$error = error_get_last();
		if (empty($error) || !(E_ERROR & $error['type'])) {
			// This error code is not included in error_reporting
			echo date("[Y-m-d H:i:s]") . "end\n";
			return false;
		}
		defined('STDIN') && print_r($error);
		return true;
	}
	register_shutdown_function('caught_shut_down');
}

//chdir(dirname(__FILE__));
//if (isset($_SERVER['HOSTNAME']) && in_array($_SERVER['HOSTNAME'], array('master.demopuppet.com', 'dev_136_100'), true)) {
//    define('ENV_CONF', 'CLI_LJSRV_DEV_CONFIG');
//} else {
//    define('ENV_CONF', 'CLI_LJSRV_CONFIG');
//}
//if(is_file(ENV_CONF)) {
//    $_SERVER = array_merge($_SERVER, parse_ini_file(ENV_CONF));//模拟获取环境变量
//} else {
//    exit('queue运行需要该文件：' . ENV_CONF . "，请依据线上LJSRV_CONFIG自行修改相关参数,放入根目录中, 开发机使用CLI_LJSRV_DEV_CONFIG, cron队列机使用CLI_LJSRV_CONFIG\n");
//}

define('ENVIRONMENT', $_SERVER['LJSRV_ENV']);

if (defined('ENVIRONMENT'))
{
	switch (ENVIRONMENT)
	{
		case 'development':
		case 'cron':
			//error_reporting(E_ALL);
			error_reporting(E_ALL & ~E_NOTICE);
		break;
		case 'testing':
		case 'production':
			error_reporting(0);
		break;

		default:
			exit('The application environment is not set correctly.');
	}
}

//$system_path = './codeigniter/system';
$system_path = $_SERVER['LJSRV_CI'];

if (realpath($system_path) !== FALSE)
{
	$system_path = realpath($system_path).'/';
}

$system_path = rtrim($system_path, '/').'/';
$application_folder = 'appadmin';

if ( ! is_dir($system_path))
{
	exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: ".pathinfo(__FILE__, PATHINFO_BASENAME));
}

function debug_show($value, $type = 'DEBUG', $verbose = false) {
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP') !== false && ENVIRONMENT == 'development') {
        //检测到安装了FirePHP时，
        require_once "FirePHP.class.php";
        if ($type === 'db_sql_master' || substr($type, -4) === 'warn') {
            FirePHP::getInstance(true)->warn($value, $type);
        } elseif (in_array($type, array('db_sql_result', 'request_return', 'request_multi_return', 'all_info', 'dagger_error_trace'), true) || strpos($type, 'redis_call_') === 0) {
            FirePHP::getInstance(true)->table($type, $value);
        } elseif (substr($type, -5) === 'trace') {
            FirePHP::getInstance(true)->trace($value, $type);
        } elseif (substr($type, -5) === 'error') {
            FirePHP::getInstance(true)->error($value, $type);
        } elseif (substr($type, -4) === 'info') {
            FirePHP::getInstance(true)->info($value, $type);
        } else {
            FirePHP::getInstance(true)->log($value, $type);
        }
    }
}

// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// The PHP file extension
// this global constant is deprecated.
define('EXT', '.php');

// Path to the system folder
define('BASEPATH', str_replace("\\", "/", $system_path));

// Path to the front controller (this file)
define('FCPATH', str_replace(SELF, '', __FILE__));

// Name of the "system folder"
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

// The path to the "application" folder
if (is_dir($application_folder))
{
	define('APPPATH', $application_folder.'/');
}
else
{
	if ( ! is_dir(BASEPATH.$application_folder.'/'))
	{
		exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: ".SELF);
	}

	define('APPPATH', BASEPATH.$application_folder.'/');
}

define('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
	
require_once BASEPATH.'core/CodeIgniter.php';

/* End of file admin.php */
/* Location: ./admin.php */
