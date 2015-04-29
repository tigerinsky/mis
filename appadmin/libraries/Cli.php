<?php
class Cli {

	private static  $config = array();
	private $CI;

	public function __construct() {
		if (!$this->is_cli_request() || isset($_SERVER['HTTP_HOST'])) {
			show_error("cron must be exec in the cli env.");
		}
		//declare(ticks = 1);
		$this->CI =& get_instance();
		
		$this->_init();
	}

	public function __call($method, $arguments) {
		//检测方法名是否合法$method

		//初始化ticks
		//declare(ticks = 1)

		//注册信号处理函数pctl_signal

		//初始化锁、DB记录连接、日志句柄

		//尝试加锁，写入记录、日志

		//是否校验返回值
		//call_user_method($method_name, $this);

		//处理结束状态、日志等，释放锁

		//OVER


	}

	private function _init() {
		if ($this->set_lock() === FALSE) {
			//echo date("[Y-m-d H:i:s]") . "set lock failed.\n";
			//exit;
		}
		//安装信号处理器回调函数
		if(function_exists('pcntl_signal')){
			pcntl_signal(SIGTERM, array($this, '_signal_handler'));
			pcntl_signal(SIGINT, array($this, '_signal_handler'));
			pcntl_signal(SIGQUIT, array($this, '_signal_handler'));
		}
	}

	private function set_lock() {
		//抢锁机制：加锁成功，执行cron；加锁失败，不执行；
		$this->CI->load->library("mcd");
		//return $this->CI->mcd->add($this->get_task_key(), 1, 59);
		$r =$this->CI->mcd->add($this->get_task_key(), $_SERVER['HOSTNAME'], 59);
		if ($r === FALSE && $this->CI->mcd->get($this->get_task_key()) != $_SERVER['HOSTNAME']) {
			return false;
		}
		return true;
	}

	/**
	 * 获取 cron的key
	 */
	private function get_task_key() {
		//通过scriptname等的md5作key
		return md5(implode("|", $_SERVER["argv"]));
	}

	/**
	 * 获取运行的cron配置
	 */
	private function get_task_config() {

	}

	/**
	 * 记录运行的cron的信息
	 */
	private function record_task_info() {

	}


	private function is_cli_request()
	{
		return (php_sapi_name() === 'cli' OR defined('STDIN'));
	}

	private function ending($code, $type) {
		echo date("[Y-m-d H:i:s]") . " {$type}: ";
	}
	public function _signal_handler($signo) {
		//调用队列控制类结束函数，平滑结束
		defined('STDIN') && $this->ending(1, 'signal catched');

		//根据所获信号类型，提供相应的输出信息
		switch($signo) {
			case SIGTERM:
				echo "Caught SIGTERM.";
				break;
			case SIGINT:
				echo "Caught SIGINT.";
				break;
			case SIGQUIT:
				echo "Caught SIGQUIT.";
				break;
			case SIGALRM:
				echo "Caught SIGALRM.";
				break;
			default:
				echo "Caught Other Signo.";
		}
		exit("\n");
	}
}
