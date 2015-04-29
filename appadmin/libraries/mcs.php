<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Mcs extends Memcache {
	// MEMCACHE加锁时间设置为5秒
	const PROJECT = 'project_admin';
	
	function __construct() {
	    $this->prefix = $_SERVER["LJSRV_MEMCACHE_KEY_PREFIX"].PROJECT.'20140109';
		$this->mem_cache_share();
	}

    private function mem_cache_share(){
        $servers = explode(" ",$_SERVER['LJSRV_MEMCACHE_SERVER'].':'.$_SERVER["LJSRV_MEMCACHE_PORT"]);
        foreach($servers as $val){         
            $v = explode(":",$val);
            $this->addServer($v[0],$v[1]);
        }
    }

	public function _set($prefix='', $data='', $time=60){
		return $this->set($this->prefix.md5($prefix), $data, MEMCACHE_COMPRESSED, $time);
	}

	public function _get($prefix=''){
		return $this->get($this->prefix.md5($prefix));
	}

	public function _getVersion(){
		return $this->getVersion();
	}

	public function _getExtendedStats(){
		return $this->getExtendedStats();
	}

	public function _delete($prefix='') {
		return $this->delete($this->prefix.md5($prefix));
	}

	/**
	  生成各控制器需要的缓存名称
	  需要传两个参数：控制器名，方法名，及自定义名称
	*/
	function getMemcacheName($controller='', $method='', $name_plus='') {
		$memcache_name = $this->prefix.$controller.$method.$name_plus;
		return $memcache_name;
	}
} 

/* End of file mcs.php */
/* Location: ./core/application/libraries/mcs.php */
