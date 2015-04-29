<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sys_config{
		
	private $CI;
	function __construct(){
		$this->CI =& get_instance();
	}
	
	/**
	 * 写入缓存到数据库
	 * @param	string	$keyname	缓存名称
	 * @param	mixed	$data		缓存数据
	 * @return  bool 	$ret		执行成功状态
	 */

	public function set($keyname, $data) {
		
		$ret=false;
		$have_config="select id,keyname from ci_sys_config where `keyname`='{$keyname}' limit 1";
		$result_config=$this->CI->dbr->query($have_config);
		$row_config=$result_config->row_array();
		
		$data_str=serialize($data);
		//$data_str=var_export($data, true);
		//$data_str=addslashes($data_str);
		$now_time=time();
		if(is_array($row_config) and $row_config['keyname']==$keyname){
			$edit_config="update ci_sys_config set `data`='{$data_str}',`updatetime`='{$now_time}' where `id`={$row_config['id']}";
			$this->CI->db->query($edit_config);
			$ret=true;
		}else{
			$add_config="insert into ci_sys_config(keyname,data,updatetime) values('{$keyname}','{$data_str}','{$now_time}')";
			$this->CI->db->query($add_config);
			$ret=true;
		}
		
		return $ret;

	}
	
	/**
	 * 获取缓存
	 * @param	string	$keyname		缓存名称
	 * @return  arr		$data			缓存数组 	
	 */
	public function get($keyname) {
		$have_config="select id,data from ci_sys_config where `keyname`='{$keyname}' limit 1";
		$result_config=$this->CI->dbr->query($have_config);
		$row_config=$result_config->row_array();
		if($row_config['id']>0){
			$data=unserialize($row_config['data']);
			//@eval("\$data = $data_str;");
		}else{
			$data=array();
		}
		return $data;
	}
	
}