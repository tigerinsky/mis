<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 设置该频道中的配置参数
 * @author Faxhaidong
 * @version 20141020
 */
class configure extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
		$this->dbr=$this->load->database('dbr',TRUE);
		$this->load->library('sys_config');
	}
	
	//默认引导方法
	function index(){
		$this->siteinfo();
	}
	
	//网站SEO基本信息
	public  function siteinfo(){
		if($_POST['dosubmit']!='') {
			$data=$this->input->post('setting');
			$this->sys_config->set('site_set_siteinfo',$data);
			show_tips('操作成功',site_url('/common/configure/index/'));
		}else{			
			$siteinfo=$this->sys_config->get('site_set_siteinfo');
			$this->smarty->assign('siteinfo',$siteinfo);
			$this->smarty->assign('show_dialog','true');
			$this->smarty->assign('show_validator','true');
			$this->smarty->display('common/configure_siteinfo.html');
		}
	}
	
	//网站基本信息，如站电话地址等
	public  function contact(){
		if($_POST['dosubmit']!='') {
			$data=$this->input->post('setting');
			$this->sys_config->set('site_set_contact',$data);
			show_tips('操作成功',site_url('/common/configure/contact/'));
		}else{			
			$siteinfo=$this->sys_config->get('site_set_contact');
			$this->smarty->assign('siteinfo',$siteinfo);
			$this->smarty->assign('show_dialog','true');
			$this->smarty->assign('show_validator','true');
			$this->smarty->display('common/configure_contact.html');
		}
	}
	
	/*
	//优惠信息
	public function sales_info(){
		if($_POST['dosubmit']!='') {
			$data=$this->input->post('setting');
			$this->sys_config->set('weibo_set_sales_info',$data);
			show_tips('操作成功',site_url('/common/configure/sales_info/'));
		}else{
			$sales_info=$this->sys_config->get('weibo_set_sales_info');
			$this->smarty->assign('sales_info',$sales_info);
			$this->smarty->assign('show_dialog','true');
			$this->smarty->assign('show_validator','true');
			$this->smarty->display('common/setting_sales_info.html');
		}
	}
	
	//名人信息推荐
	public function celebrity_flag(){
		if($_POST['dosubmit']!='') {
			$data=$this->input->post('setting');
			$this->sys_config->set('weibo_set_celebrity_flag',$data);
			show_tips('操作成功',site_url('/common/configure/celebrity_flag/'));
		}else{
			$query_tips="SELECT `id`,`listorder`,`kind`,`tips_name`,`bind_val`,`status`,`top`,`flag` FROM ci_trp_tips_info where kind=3 AND status=1";
			$resule_tips=$this->dbr->query($query_tips);
			$list_tips=$resule_tips->result_array();
			$celebrity_flag=$this->sys_config->get('weibo_set_celebrity_flag');
			$this->smarty->assign('list_tips',$list_tips);
			$this->smarty->assign('celebrity_flag',$celebrity_flag);
			$this->smarty->assign('show_dialog','true');
			$this->smarty->assign('show_validator','true');
			$this->smarty->display('common/setting_celebrity_flag.html');
		}
	}
	*/
}