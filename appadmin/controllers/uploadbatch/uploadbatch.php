<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 二级分类图片管理
 */
class uploadbatch extends MY_Controller{

	function __construct(){
		parent::__construct();
		$this->dbr=$this->load->database('dbr',TRUE);
//		$this->load->config('mis_imgmgr',TRUE);
//		$this->mis_imgmgr = $this->config->item('mis_imgmgr');
		// $this->mis_imgmgr['imgmgr_level_1']
		$this->load->library('redis');
		$this->key_img = 'mis_img_timestamp';
		$this->load->model('uploadbatch/uploadbatch_model','uploadbatch_model');
	}

	//默认调用控制器
	function index(){
		$this->upload_list();
	}

	private function upload_list(){
		$this->load->library('form');
		$page=$this->input->get('page');
		$page = max(intval($page),1);
		$dosearch=$this->input->get('dosearch');

		$search_arr['is_del']=0;
		$where_array[]="is_del=0";

		if($dosearch=='ok'){

			$keywords=trim($this->input->get('keywords'));

			if($keywords!=''){
				$search_arr['keywords']=$keywords;
				$where_array[]="content like '%{$keywords}%'";
			}
		}

		if(is_array($where_array) and count($where_array)>0){
			$where=' WHERE '.join(' AND ',$where_array);
		}

		$pagesize=10;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";

		$user_num=$this->uploadbatch_model->get_count_by_parm($where);
		$pages=pages($user_num,$page,$pagesize);
		$list_data=$this->uploadbatch_model->get_data_by_parm($where,$limit);
		if(!empty($list_data))
		{
			foreach($list_data as $key => $value)
			{
				$list_data[$key]['img'] = json_decode($value['img'],true);
			}
		}
		$this->load->library('form');
		//$img_type_list=array('1'=>'素描','2'=>'色彩','3'=>'速写','4'=>'设计','5'=>'创作','6'=>'照片');
		$img_type_list = $this->mis_imgmgr['imgmgr_level_1'];

		$this->smarty->assign('img_type_list',$img_type_list);
		$this->smarty->assign('list_data',$list_data);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('uploadbatch/uploadbatch_list.html');
	}

}
