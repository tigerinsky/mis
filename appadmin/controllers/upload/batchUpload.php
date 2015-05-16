<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 二级分类图片管理
 */
class batchUpload extends MY_Controller{

	function __construct(){
		parent::__construct();
		$this->dbr=$this->load->database('dbr',TRUE);
		$this->load->config('mis_imgmgr',TRUE);
		$this->mis_imgmgr = $this->config->item('mis_imgmgr');
		// $this->mis_imgmgr['imgmgr_level_1']
		$this->load->library('redis');
		$this->key_img = 'mis_img_timestamp';
		$this->load->model('imgmgr/imgmgr_model','imgmgr_model');
	}

	//默认调用控制器
	function index(){
		$this->upload_add();
	}

	//显示图片列表，同时有检索功能
	private function upload_add(){
		$this->load->library('form');
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('upload/upload_add.html');
	}

	public function doUpload()
	{
		if($_POST)
		{

		}
	}


}
