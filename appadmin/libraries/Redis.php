<?php
class CI_Redis {

	private $_instance = NULL;
	private $keyPre = NULL;
	private $redisConfig = NULL;

	public function __construct() {
		if ($this->_instance === NULL) {
			$this->_instance = new Redis();
			$this->getPoolConfig();
			if (empty($this->redisConfig['server']) || empty($this->redisConfig['port'])){
				return false;
			}
			$this->_instance->connect($this->redisConfig['server'], $this->redisConfig['port'], 
				$this->redisConfig['connect_timeout_redis']);
		}
	}
	
	/**
     * pipe模式批量设置set类型
     */
	public function multi_sAdd($key, $data) {
		$r = $this->_instance->multi(Redis::PIPELINE);
        foreach($data as $v) {
            $r->sAdd($key, $v);
        }
        $ret = $r->exec();
        return $ret;	
	}

	public function multi_sRem($key, $data) {
		$r = $this->_instance->multi(Redis::PIPELINE);
        foreach($data as $v) {
            $r->sRem($key, $v);
        }
        $ret = $r->exec();
        return $ret;    		
	}

	public function multi_zAdd($key, $data, $weights = 0) {
        $r = $this->_instance->multi(Redis::PIPELINE);
        foreach($data as $v) {
			$r->zAdd($key, $weights, $v);
        }
        $ret = $r->exec();
        return $ret;            
    }

	public function multi_zRem($key, $data) {
        $r = $this->_instance->multi(Redis::PIPELINE);
        foreach($data as $v) {
           	$r->zRem($key, $v);
        }
        $ret = $r->exec();
        return $ret;            
    }

	/**
     * pipe模式批量设置hash类型
     */
    public function multi_hMset(array $data) {
        $r = $this->_instance->multi(Redis::PIPELINE);
        foreach($data as $k => $v) {
            $r->hMset($k, $v);
        }
        $ret = $r->exec();
        return $ret;
    }

	public function __call($func, $args) {
		$ret = call_user_func_array(array($this->_instance, $func), $args);
		return $ret;
	}
	
	private function _multi($func, $args) {
        $r = $this->_instance->multi(Redis::PIPELINE);
        $keys = array_shift($args);
        if(null === $this->keyPre || $this->keyPre === '') {
            $keyPre = '';
        } else {
            $keyPre = $this->keyPre;
            $this->keyPre = '';
        }
        foreach($keys as $key) {
            call_user_func_array(array($r, substr($func, 6)), array_merge(array("{$keyPre}{$key}"), $args));
        }
        $values = $r->exec();
        return empty($values) ? array() : array_combine($keys, $values);
    }

	private function getPoolConfig() {
        if ( ! defined('ENVIRONMENT') OR ! file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/pool_config.php'))
        {
            if ( ! file_exists($file_path = APPPATH.'config/pool_config.php'))
            {
                show_error('The configuration file pool_config.php does not exist.');
            }                                                                                                                        
        }
        include($file_path);
        if ( ! isset($config['redis']) OR count($config['redis']) == 0)
        {
            show_error('No redis config settings were found in the pool config file.');                                  
 
        }
        $redis_name = $config['redis']['default'];
        $this->redisConfig = $config['redis'][$redis_name];
    }
}
