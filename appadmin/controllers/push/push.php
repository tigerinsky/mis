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

	//显示推送列表，同时有检索功能
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
		if(count($list_data)>0)
		{
			foreach($list_data as $key=>$value)
			{
				$list_data[$key]['user_type'] = ($value['user_type'] == 1)?"认证":"未认证";
				$list_data[$key]['time_push'] = date("Y-m-d H:i:s",$value['time_push']);
				$list_data[$key]['citys']	= $this->arrJson($value['citys'],'citys');
				$list_data[$key]['school']	= $this->arrJson($value['school'],'school');
			}
		}
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
		$city_type_list=$this->getCity();
		$city_type_sel=Form::select($city_type_list,$info['citys'],'id="citys" name="citys"','所在城市（多选）');
		//学校
		$school_type_list=$this->getSchool();
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

	//城市字典
	private function getCity($id = 0)
	{
		$city = array('1'=>'北京','2'=>'上海');
		if($id == 0) return $city;
		else
		{
			if(array_key_exists($id,$city)) {
				return $city[ $id ];
			} else return "";
		}
	}

	//城市字典
	private function getSchool($id = 0)
	{
		$city = array('1'=>'北京大学','2'=>'清华大学');
		if($id == 0) return $city;
		else
		{
			if(array_key_exists($id,$city)) {
				return $city[ $id ];
			} else return "";
		}
	}

	private function arrJson($json,$type='citys')
	{
		$arr = json_decode($json,true);
		if(empty($arr)) return "";
		$str="";
		foreach($arr as $key=>$value)
		{
			if($type == 'citys')
				$str .= $this->getCity($value) .",";
			else
				$str .= $this->getSchool($value) .",";
		}
		if(substr($str,-1,1) == ',')
			$str = substr($str,0,strlen($str)-1);
		return $str;
	}

	//对要闻进行单条删除属性变更
	function del_one_ajax(){
		if(intval($_GET['id'])>0) {
			// 更新时间戳
			$this->redis->set($this->key_img, time());
			$id=$this->input->get('id');
			if($this->push_model->del_info($id)){
				echo 1;
			}else{
				echo 0;
			}
		} else {
			echo 0;
		}
	}

	//修改推送
	function push_edit(){
		$this->load->library('form');
		//城市
		$city_type_list=$this->getCity();
		$city_type_sel=Form::select($city_type_list,$info['citys'],'id="citys" name="citys"','所在城市（多选）');
		//学校
		$school_type_list=$this->getSchool();
		$school_type_sel=Form::select($school_type_list,$info['school'],'id="school" name="school"','目标学校（多选）');
		$this->smarty->assign('city_type_sel', $city_type_sel);
		$this->smarty->assign('school_type_sel', $school_type_sel);

		$imgmgr_id = $this->input->get('id');
		$info = $this->push_model->get_info_by_id($imgmgr_id);

		$info['time_push'] = date("Y-m-d H:i:s",$info['time_push']);
		$info['citys_list']	= $this->pushForm(json_decode($info['citys'],true),'citys');
		$info['school_list']	= $this->pushForm(json_decode($info['school'],true),'school');

		$img_type_list=array('1'=>'认证','2'=>'未认证');
		$utype = ($info['user_type']==0)?"未认证":"认证";
		$img_type_sel=Form::select($img_type_list,$info['user_type'],'id="user_type" name="user_type"',$utype);

		$img_type_list = $this->mis_imgmgr['imgmgr_level_1'];

		$this->smarty->assign('info',$info);
		$this->smarty->assign('img_type_sel',$img_type_sel);
		$this->smarty->assign('random_version', rand(100,999));
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('push/push_edit.html');
	}

	private function pushForm($arr,$type = 'citys')
	{
		if(empty($arr)) return ""; $str = "";
		foreach($arr as $key=>$value)
		{
			if($type == 'citys') {
				$str .= '<li id="city_' . $value . '" style="padding-left: 5px;">' . $this->getCity ( $value ) . '<span style="padding-left: 8px; width:14px; height:14px; cursor:pointer; " onclick="_del(\'city_' . $value . '\')"><img src="/public/images/error.gif"></span><input type=\'hidden\' name=\'citys[]\' value="'.$value.'" /></li>';
			}else
			{
				$str .= '<li id="school_'.$value.'" style="padding-left: 5px;">'.$this->getSchool($value).'<span style="padding-left: 8px; width:14px; height:14px; cursor:pointer; " onclick="_del(\'school_'.$value.'\')"><img src="/public/images/error.gif"></span><input type=\'hidden\' name=\'citys[]\' value="'.$value.'" /></li>';
			}
		}
		return $str;
	}

	public function push_edit_do()
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
			$id = $this->input->post('id');

			if( $data['citys']!='' && count($this->input->post('citys')) > 0 && count($this->input->post('school')) > 0 && $data['title']!='' && $data['time_push'] != ''){

				if($this->push_model->edit_info($data,$id)){
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
