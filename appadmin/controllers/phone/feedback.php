<?php

class feedback extends CI_Controller {

	private static $common_config;
	public function __construct() {
		parent::__construct();
		$this->load->config("common_config", TRUE);
		self::$common_config = $this->config->item('common_config');
		$this->table_name = "ci_app_phone_feedback";

	}

	public function index() {
		$this->feedback_list();
	}

	public function feedback_list() {
		$page = $this->input->get('page');
		$page = max(intval($page),1);
		$dosearch = $this->input->get('dosearch');
		$where_array[] = " status >= 0 ";
		if($dosearch == 'ok') {
			$keywords = trim($this->input->get('keywords'));
			$search_arr['keywords'] = $keywords;
			if($keywords != '') {
				$where_array[] = "name LIKE '%{$keywords}%' or mobile LIKE '%{$keywords}%' ";		
			}
			
			$search_arr['type'] = intval($this->input->get('type'));
			if($search_arr['type'] > 0) {
				$where_array[] = "type = {$search_arr['type']} ";		
			}
			$info = $this->input->get('info');
			$search_arr['industry'] = $info['industry'];
			if($search_arr['industry'] > 0) {
				$where_array[] = "industry = {$search_arr['industry']} ";		
			}
		}

		if(is_array($where_array) and count($where_array) > 0) {
			$where = ' WHERE '. implode(' AND ',$where_array);
		}
		
		$pagesize = 10;
		$offset = $pagesize * ($page-1);
		$limit = "LIMIT $offset,$pagesize";
		$order = "ORDER BY id DESC";
		
		$sql_num = "SELECT id, phone_id, uid, type, contact, comment, status, contact_status, time_create FROM {$this->table_name} $where";
		$result_num = $this->db->query($sql_num);
		$num = $result_num->num_rows();
		$pages = pages($num,$page,$pagesize);
		$query_sql = "SELECT id, phone_id, uid, type, contact, comment, status, contact_status, time_create FROM {$this->table_name} $where $order $limit";
		$result = $this->db->query($query_sql);
		$list = $result->result_array();
		//$this->get_industry_selectbox($search_arr['industry']);
		$this->smarty->assign('feedback_type', self::$common_config['feedback']);
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_data',$list);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display("phone/feedback_index.html");
	}

	public function feedback_add() {
		$this->get_feedback_selectbox();
		$this->get_status_radio(1, 1);
		$this->smarty->display("phone/feedback_add.html");
	}

	public function feedback_add_do() {
		$info = $this->input->post('info');
		$info['time_create'] = time();
		//$info['time_edit'] = $info['time_create'];
		if ($info['contact_status'] == 1) {
			//更新通讯录主表
			$this->load->model("phone/phonebook_model", "_book");
			//判断是手机号、还是固话、邮箱；
			//todo
			$this->_book->update_contact($info['phone_id'], $info['contact']);
		}
		if ($info['phone_id'] != '' && $info['comment'] != '' && intval($info['contact']) > 0) {
			$insert_query = $this->db->insert_string($this->table_name, $info);
			$this->db->query($insert_query);
			show_tips('操作成功','','','add');
		} else {
			show_tips('数据不完整，请检测');
		}
	}

	public function feedback_edit() {
		$this->load->library('form');
		$aid = intval($this->input->get('id'));
		$sql = "SELECT id, phone_id, uid, type, contact, comment, status, contact_status, time_create FROM {$this->table_name} WHERE id={$aid}";
		$result = $this->db->query($sql);
		$info = $result->row_array();
		$this->get_feedback_selectbox($info['type']);
		//$type_sel = Form::select($this->phone_type, $info['type'],'name="info[type]" id="type"');
		$status_radio = Form::radio(self::$common_config['status'], $info['status'],'name="info[status]" id="status"');
		$contact_status_radio = Form::radio(self::$common_config['status'], $info['contact_status'],'name="info[contact_status]" id="contact_status"');
		//$this->smarty->assign('type_sel',$type_sel);
		$this->smarty->assign('status_radio',$status_radio);
		$this->smarty->assign('contact_status_radio',$contact_status_radio);
		$this->smarty->assign('info',$info);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display("phone/feedback_edit.html");
	}

	public function feedback_edit_do() {
		$aid = $this->input->post('id');
		if($aid < 1) {show_tips('参数异常，请检测');}else{$where="id={$aid}";}
		$info = $this->input->post('info');
		$query = $this->db->update_string($this->table_name, $info, $where);
		if ($info['contact_status'] == 1) {
			//更新通讯录主表
			$this->load->model("phone/phonebook_model", "_book");
			//判断是手机号、还是固话、邮箱；
			//todo
			$this->_book->update_contact($info['phone_id'], $info['contact']);
		}
		if($this->db->query($query)) {
			show_tips('操作成功','','','edit');
		} else {
			show_tips('操作异常，请检测');
		}
	}

	public function feedback_view() {
		$this->feedback_edit();
	}

	//status为-1时，表示删除：
	public function feedback_del_one_ajax() {
		$aid = intval($this->input->get('id'));
		if($aid>0) {
			$del_query = "UPDATE {$this->table_name} SET status=-1 WHERE id={$aid}";
			$this->db->query($del_query);
			echo 1;
		} else {
			echo 0;
		}
	}

	public function feedback_del() {
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0) {
				$ids_str = join("','",$ids);
				$del_query = "UPDATE {$this->table_name} SET status=-1 WHERE id in('{$ids_str}') ";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			} else {
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}

	public function check_filed_have_ajax() {
		$this->load->library('check_filed');
		$true_table_arr=array(
			'A' => $this->table_name,
		);
		$this->check_filed->check_filed_have_ajax($true_table_arr);

	}


	public function feedback_contact_status() {
		if(intval($_POST['dosubmit'])==1) {
			$contact_status = intval($this->input->get('contact_status'));
			$ids = $this->input->post('ids');
			if(is_array($ids) and count($ids)>0) {
				$ids_str = join("','",$ids);
				if ($contact_status == 1) {
					//更新通讯录主表
					$this->load->model("phone/phonebook_model", "_book");
					//是否需要判断是手机号、还是固话、邮箱(todo)
					$affected_rows = $this->_book->update_contact_multi($ids);
				}
				if (isset($affected_rows) && $affected_rows > 0) {
					$sql = "UPDATE {$this->table_name} SET contact_status={$contact_status} WHERE id in('{$ids_str}') ";
					$this->db->query($sql);
					show_tips('操作成功',HTTP_REFERER);
				} else {
					show_tips('请核实该通讯录是否存在',HTTP_REFERER);
				}
			} else {
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}

	public function feedback_status() {
		if(intval($_POST['dosubmit'])==1) {
			$status = intval($this->input->get('status'));
			$ids = $this->input->post('ids');
			if(is_array($ids) and count($ids)>0) {
				$ids_str = join("','",$ids);
				$sql = "UPDATE {$this->table_name} SET status={$status} WHERE id in('{$ids_str}') ";
				$this->db->query($sql);
				show_tips('操作成功',HTTP_REFERER);
			} else {
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}


	private function get_feedback_selectbox($feedback_type = 0) {
		$this->load->library('form');
		$feedback_type_sel = Form::select(self::$common_config['feedback'], $info['type'],'name="info[type]" id="type"');
		$this->smarty->assign("feedback_type_sel",  $feedback_type_sel);
	}

	private function get_status_radio($status = 0, $contact_status = 0) {
		//$contact_status_radio
		$this->load->library('form');
		$status_radio = Form::radio(self::$common_config['status'], $status,'name="info[status]" id="status"');
		$contact_status_radio = Form::radio(self::$common_config['status'], $contact_status,'name="info[contact_status]" id="contact_status"');
		
		$this->smarty->assign('status_radio',$status_radio);
		$this->smarty->assign('contact_status_radio',$contact_status_radio);
	}
}
