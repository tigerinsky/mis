<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class main extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
	}
	
	function index(){
        log_message('debug', "[******log******]--test log");
	    $this->rbac_config=$this->config->item('config_rbac');
		$userinfo=$this->session->userdata($this->rbac_config['rbac_admin_auth_key']);
		$user_role=intval($userinfo['role_id']);
		$user_guide_query="SELECT menu_guide,menu_left FROM ci_rbac_role WHERE id=?";
		$user_guide_result=$this->db->query($user_guide_query,array($user_role));
		$user_guide_info=$user_guide_result->row_array();
		$user_guide_info['def']=$user_guide_info['menu_left']>0?0:1;
		$this->smarty->assign('user_guide_info',$user_guide_info);
		$this->smarty->display('admin/main_index.html');
	}
	
	function right(){
		$this->rbac_config=$this->config->item('config_rbac');
		$userinfo=$this->session->userdata($this->rbac_config['rbac_admin_auth_key']);
		$this->smarty->display('admin/main_default.html');
	}
	
	function test(){
		$this->load->library('form');
		$kongjian=form::images('game_photo','game_photo','','games',0,50,2,'','','jpg|jpeg|gif',array('100','200'),0);
		$fliebox=form::upfiles('upload','upload','http://www.baidu.com/1.gif','content',0,5,'','',$alowexts = 'rar|zip');
		$editorbox=form::editor('content','full','yes','modelname','0','',1,1,'jpg|gif|png',200,0);
		
		$this->smarty->assign('kongjian',$kongjian);
		$this->smarty->assign('fliebox',$fliebox);
		$this->smarty->assign('editorbox',$editorbox);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/test.html');
	}
}
