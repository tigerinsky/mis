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
		$this->load->model('push/push_model','push_model');
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

		$where_array[]="removed=0";

		if($dosearch=='ok'){

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

		$user_num=$this->push_model->get_count_by_parm($where);
		$pages=pages($user_num,$page,$pagesize);
		$list_data=$this->push_model->get_data_by_parm($where,$limit);

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

	//添加推送
	function push_add(){
		$this->load->library('form');
		$img_type_list=array('1'=>'认证','2'=>'未认证');
		$img_type_sel=Form::select($img_type_list,$info['user_type'],'id="user_type" name="user_type"','');

		//城市
		$city_type_list=array('1'=>'北京','2'=>'上海');
		$city_type_sel=Form::select($city_type_list,$info['citys'],'id="citys" name="citys"','所在城市（多选）');
		//学校
		$school_type_list=array('1'=>'北京大学','2'=>'清华大学');
		$school_type_sel=Form::select($school_type_list,$info['school'],'id="school" name="school"','目标学校（多选）');
		$this->smarty->assign('img_type_sel',$img_type_sel);
		$this->smarty->assign('city_type_sel', $city_type_sel);
		$this->smarty->assign('school_type_sel', $school_type_sel);
		$this->smarty->assign('random_version', rand(100,999));
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('push/push_add.html');
	}


	//处理推送数据
	function push_add_do()
	{
		if($_POST) {
			$data = array (
				'user_type'		=> $this->input->post('user_type'),
				'citys'		=> json_encode($this->input->post('citys')),
				'school'	=> json_encode($this->input->post('school')),
				'wap_url'		=> $this->input->post('wap_url'),
				'title'		=> $this->input->post('title'),
				'time_push'	=> strtotime($this->input->post('push_time')),
				'time_create'	=> time(),
			);

			if( $data['citys']!='' && count($this->input->post('citys')) > 0 && count($this->input->post('school')) > 0 && $data['title']!='' && $data['time_push'] != ''){

				if($this->push_model->create_info($data)){
					show_tips('操作成功','','','add');
				}else{
					show_tips('操作异常');
				}
			}else{
				show_tips('数据不完整，请检测');
			}

		}
	}



}
