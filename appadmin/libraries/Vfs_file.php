<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'VFS/VFS/dpool_storage.php';

class Vfs_file {
	private $CI;
	private $vfs;
	private $prefix = 'event';
	public function __construct(){
		$this->CI =& get_instance();
		$this->CI->load->helper('security');
		$this->vfs = new VFS_dpool_storage();
	}

	
	public function write($path='', $name='', $tmpFile='', $autocreate=true){
		
		if (trim($path) == ''){
			$path = '/'.$this->prefix.'/'.$this->_getPathHash().'/';
		}else{
			$path = '/'.$this->prefix.'/'.$path.'/';
		}
		$path = str_replace("//", "/", $path);

		if (trim($name) == ''){
			$name = time();
		}
		if ($autocreate !== true){
			$autocreate = false;
		}
		if (is_file($tmpFile)){
			$ret = $this->vfs->write($path, $name, $tmpFile, $autocreate);
			echo '<pre>';
			print_r($ret);
			echo '</pre>';
			//return $ret;
			/*
			if (!is_a($ret, 'PEAR_Error')){
				//return array('fileUrl'=>$this->CI->input->server('SINASRV_NDATA_CACHE_URL').trim($path, '/').'/'.$name, 'fileName'=>$name);
			}else{
				return false;
			}
			*/
		}
	}
	/*
	public function read($path='', $name='', $cache=0, $prefix=true){
		$cache = sprintf('%d', $cache);
		if ($prefix == true){
			$path = '/'.$this->prefix.'/'.$path;
		}
		$path = str_replace("//", "/", $path);
		if ($cache == -1){
			$ret = $this->vfs->read($path, $name);
		}else{
			$ret = $this->vfs->read($path, $name, array('cache'=>$cache));
		}
		return $ret;
	}

	private function _getPathHash(){
		$tmp = do_hash(time());
		return $tmp{0}.$tmp{3}."/".$tmp{1}.$tmp{2};
	}
	*/
}

/* End of file V_F_S.php */
/* Location: ./core/application/libraries/V_F_S.php */