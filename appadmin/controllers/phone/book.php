<?php

class book extends CI_Controller {

	private $identity;//通讯录身份类型
	private static $common_config;
	public function __construct() {
		parent::__construct();

		$this->load->config("common_config", TRUE);
		self::$common_config = $this->config->item('common_config');
		$this->identity = self::$common_config['identity'];
		$this->table_name = "ci_app_phone_book";

	}

	public function index() {
		$this->book_list();
	}

	public function book_list() {
		$page = $this->input->get('page');
		$page = max(intval($page),1);
		$dosearch = $this->input->get('dosearch');
		$where_array[] = " status >= 0 ";
		if($dosearch == 'ok') {

			$keywords = trim($this->input->get('keywords'));
			$search_arr['keywords'] = $keywords;
			if($keywords != '') {
				$keywords = mysql_real_escape_string($keywords);
				$where_array[] = "name LIKE '%{$keywords}%' or mobile LIKE '%{$keywords}%' or company LIKE '%{$keywords}%' ";		
			}
			
			$search_arr['identity'] = intval($this->input->get('identity'));
			if($search_arr['identity'] > 0) {
				$where_array[] = "identity = {$search_arr['identity']} ";		
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
		$order = "ORDER BY time_create DESC";
		
		$sql_num = "SELECT id, name, identity, mobile, industry, work, job, company, total_view, total_comment, status, uid, time_create FROM {$this->table_name} $where";
		$result_num = $this->db->query($sql_num);
		$num = $result_num->num_rows();
		$pages = pages($num,$page,$pagesize);
		$query_sql = "SELECT id, name, identity, mobile, industry, work, job, company, total_view, total_comment, status, uid, time_create FROM {$this->table_name} $where  $order $limit";
		$result = $this->db->query($query_sql);
		$list = $result->result_array();
		$this->get_industry_selectbox($search_arr['industry']);
		$this->smarty->assign('identity_list', $this->identity);
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_data',$list);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display("phone/book_index.html");
	}

	public function book_add() {
		$this->get_industry_selectbox();
		$this->smarty->assign('identity_list', $this->identity);
		$this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
		$this->smarty->display("phone/book_add.html");
	}

	public function book_add_do() {
		$info = $this->input->post('info');
		$info['time_create'] = time();
		$info['time_edit'] = $info['time_create'];
		$this->load->library('mcrypt_3des');
		$info['mobile']  = trim($info['mobile']);
		$info['uniq'] = phone_md5($info['mobile']);
		$info['mobile'] = $this->mcrypt_3des->en3des(trim($info['mobile']), array('create_time'=> $info['time_edit']));
		if ($info['name'] != '' && $info['mobile'] != '' && intval($info['identity']) > 0) {
			$this->load->model("phone/phonebook_model", '_phonebook');
			if($this->_phonebook->insert_phone($info)) {
				show_tips('操作成功','','','add');
			} else {
				show_tips('该手机号已添加过');
			}
		} else {
			show_tips('数据不完整，请检测');
		}
	}

	public function book_edit() {
		$this->load->library('form');
		$aid = intval($this->input->get('id'));
		$sql = "SELECT id, name, identity, mobile, phone, email, industry, work, job, company, total_view, total_comment, status, uid, remark, card_pic, time_create FROM {$this->table_name} WHERE id={$aid}";
		$result = $this->db->query($sql);
		$info = $result->row_array();
		$info['mobile'] = phone_decode($info['mobile'], array($info['time_create']));
		$this->get_industry_selectbox($info['industry']);
		$type_sel = Form::select($this->identity, $info['identity'],'name="info[identity]" id="identity"');
		$status_radio = Form::radio(self::$common_config['status'], $info['status'],'name="info[status]" id="status"');
		$this->smarty->assign('type_sel',$type_sel);
		$this->smarty->assign('status_radio',$status_radio);
		$this->smarty->assign('info',$info);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display("phone/book_edit.html");
	}

	public function book_edit_do() {
		$aid = $this->input->post('id');
		if($aid < 1) {show_tips('参数异常，请检测');}else{$where="id={$aid}";}
		$info = $this->input->post('info');
		debug_show($info,'info_book');
		$info['uniq'] = phone_md5($info['mobile']);
		$info['mobile'] = phone_encode($info['mobile'], array($info['time_create']));
		$query = $this->db->update_string($this->table_name, $info, $where);
		if($this->db->query($query)){
			show_tips('操作成功','','','edit');
		}else{
			show_tips('操作异常，请检测');
		}
	}

	public function book_view() {

		$this->book_edit();
	}

	//status为-1时，表示删除：
	public function book_del_one_ajax() {
		$aid = intval($this->input->get('id'));
		if($aid>0) {
			$del_query = "UPDATE {$this->table_name} SET status=-1 WHERE id={$aid}";
			$this->db->query($del_query);
			echo 1;
		} else {
			echo 0;
		}
	}

	public function book_del() {
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0) {
				$ids_str = join("','",$ids);
				$del_query = "UPDATE {$this->table_name} SET status=-1 WHERE id in('{$ids_str}') ";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('请选择后重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}

	public function book_status() {
        if(intval($_POST['dosubmit'])==1) {
            $ids = $this->input->post('ids');
            $status = $this->input->get('status');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                $query="UPDATE {$this->table_name}  SET `status`={$status} WHERE id in('{$ids_str}')";                         
                $rs = $this->db->query($query);
                //$this->log->write($this->user_info['user_name'], $this->ip, $ids_str, $this->table_name . ":status", $rs);
                show_tips('操作成功',HTTP_REFERER);
            }else{
                show_tips('请选择后重新提交');
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

	private function get_industry_selectbox($industry_id = 0) {
		$this->load->library('form');
		$this->load->model('mis/industry_model','_industry');
		$r = $this->_industry->get_data_by_parm();
		$industry_arr = array();
		foreach ($r as $k => $v) {
			$industry_arr[$v['id']] = $v['title'];
		}
		$industry_sel = Form::select($industry_arr, $industry_id,'name="info[industry]" id="industry"', '全行业');
		$this->smarty->assign('industry_arr', $industry_arr);
		$this->smarty->assign('industry_sel', $industry_sel);
	}
}
