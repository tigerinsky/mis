<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class rbac_admin extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
		$this->dbr=$this->load->database('dbr', TRUE);
	}
	
	//显示会员信息
	function index(){
		$this->show_user_info();
	}
	
	//显示基本信息
	private function show_user_info(){
		echo '显示会员基本信息';
	}
	
	//修改用户密码
	function user_edit(){
		echo '修改用户密码';
	}
	
	//执行修改用户密码
	function user_edit_do(){
		echo '执行修改用户密码';
	}
}