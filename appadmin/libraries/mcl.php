<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is local Memcache...
 *
 */
class Mcl extends Memcache {
	
	const PROJECT = 'travel_weibo_admin';
	
	function __construct() {
		$this->prefix = $_SERVER["SINASRV_MEMCACHED_KEY_PREFIX"].PROJECT.'20140109';
		$this->mem_cache_local();
	}

	public function _set($prefix='', $data='', $time=60){
		$this->set($this->prefix.md5($prefix), $data, MEMCACHE_COMPRESSED, $time);
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
	
    private function mem_cache_local(){                           
        $this->connect($_SERVER["SINASRV_MEMCACHED_HOST"], $_SERVER["SINASRV_MEMCACHED_PORT"]);
    }

} 

/* End of file mcl.php */
/* Location: ./core/application/libraries/mcl.php */