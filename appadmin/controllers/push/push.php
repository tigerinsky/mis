<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 推送
 */
class push extends MY_Controller{

	function __construct(){
		parent::__construct();
		$this->dbr=$this->load->database('dbr',TRUE);
		$this->load->library('redis');
		$this->key_img = 'mis_img_timestamp';
		$this->load->model('imgmgr/imgmgr_model','imgmgr_model');
	}

	//默认调用控制器
	function index(){
		$this->push_list();
	}

	//显示图片列表，同时有检索功能
	private function push_list(){
		$this->load->library('form');
		$page=$this->input->get('page');
		$page = max(intval($page),1);
		$dosearch=$this->input->get('dosearch');

		$search_arr['is_deleted']=1;
		$where_array[]="is_deleted=1";

		if($dosearch=='ok'){

			$search_filed=array(
				'img_type'=>array(
					'1'=>'img_type=1',
					'2'=>'img_type=2',
					'3'=>'img_type=3',
					'4'=>'img_type=4',
					'5'=>'img_type=5',
					'6'=>'img_type=6',
				)
			);

			if(intval($this->input->get('img_type_id'))!=''){
				$img_type_id=$this->input->get('img_type_id');
				if($search_filed['img_type'][$img_type_id]!=''){
					$where_array[]=$search_filed['img_type'][$img_type_id];
				}
			}

			$keywords=trim($this->input->get('keywords'));

			if($keywords!=''){
				$search_arr['keywords']=$keywords;
				$where_array[]="title like '%{$keywords}%'";
			}

		}

		if(is_array($where_array) and count($where_array)>0){
			$where=' WHERE '.join(' AND ',$where_array);
		}

		$pagesize=10;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";

		$user_num=$this->imgmgr_model->get_count_by_parm($where);
		$pages=pages($user_num,$page,$pagesize);
		$list_data=$this->imgmgr_model->get_data_by_parm($where,$limit);

		$this->load->library('form');
		$img_type_list=array('1'=>'素描','2'=>'色彩','3'=>'速写','4'=>'设计','5'=>'创作','6'=>'照片');
		$search_arr['img_type_sel']=$this->form->select($img_type_list,$img_type_id,'name="img_type_id"','选择图片类型');
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('img_type_list',$img_type_list);
		$this->smarty->assign('list_data',$list_data);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('push/push_list.html');
	}


}
