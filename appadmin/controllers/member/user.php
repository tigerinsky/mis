<?php

class user extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->load->library('form');
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->table_name_1 = "ci_user";
        $this->table_name_2 = "ci_user_detail";
    }

    public function index() {
        $this->user_list();
    }
    
    public function user_list() {
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $where_array[] = "id = uid";
        
        if($dosearch == 'ok'){
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "sname like '%{$keywords}%' or intro like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 10;
        $offset    = $pagesize*($page-1);                                                                                     
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY id DESC";
        $sql_ct    = "SELECT id FROM ci_user, ci_user_detail $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        //$sql       = "SELECT id, sname, avatar, umobile, province, city, intro, school, ukind, ukind_verify, ukind_info, locked, lock_time, create_time, login_time FROM ci_user, ci_user_detail $where $order $limit";
        $sql       = "SELECT id, sname, avatar, umobile, province, city, intro, school, ukind, ukind_verify, create_time FROM ci_user, ci_user_detail $where $order $limit";
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        //debug_show($list_data, 'list_data');
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('identify_arr', self::$common_config['identity']);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/user_list.html");
    }

    public function user_add() {
        exit("todo");
        $sex_radio = Form::radio(self::$common_config['sex'], 0,'name="info[sex]" id="sex"');
        $status_radio = Form::radio(self::$common_config['status'], 1,'name="info[status]" id="status"');
        $this->smarty->assign('sex_radio', $sex_radio);
        $this->smarty->assign('status_radio', $status_radio);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/user_add.html");
    }

    public function user_add_do() {
        $info = $this->input->post('info');
        //$this->load->library('mcrypt_3des');
        $info['create_time'] = time();
        //$info['umobile'] = $this->mcrypt_3des->en3des(trim($info['umobile']), array('create_time'=> $info['create_time']));
        //print_r($info);exit;
        if ($info['uname'] != '' && $info['umobile'] != '') {
            $insert_query = $this->db->insert_string($this->table_name, $info);

            $this->db->query($insert_query);
            show_tips('操作成功','','','add');
        } else {
            show_tips('数据不完整，请检测');
        }
    }

    public function user_view() {
        $this->load->library('form');
        $aid = intval($this->input->get('id'));
        $sql = "SELECT id, sname, avatar, umobile, province, city, intro, school, ukind, ukind_verify, create_time FROM ci_user, ci_user_detail WHERE id={$aid}";
        $result = $this->db->query($sql);
        $info = $result->row_array();
        //$this->load->library('mcrypt_3des');
        //$cfg['create_time'] = $info['create_time'];
        //$info['umobile'] = $this->mcrypt_3des->de3des($info['umobile'], $cfg);
        $sex_radio = Form::radio(self::$common_config['sex'], $info['sex'],'name="info[sex]" id="sex"');
        $status_radio = Form::radio(self::$common_config['status'], $info['status'],'name="info[status]" id="status"');
        $this->smarty->assign('sex_radio', $sex_radio);
        $this->smarty->assign('status_radio', $status_radio);
        $this->smarty->assign('info', $info);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/user_view.html");
    }
    public function user_edit() {
        $this->load->library('form');
        $aid = intval($this->input->get('id'));
        //$sql = "SELECT id, uname, sname, avatar, sex, age, gold, uemail, integral, ukind, status, locked, lock_num, lock_time, create_time FROM {$this->table_name} WHERE id={$aid}";
        $sql = "SELECT id, sname, avatar, umobile, province, city, intro, school, ukind, ukind_verify, create_time FROM ci_user, ci_user_detail WHERE id={$aid}";
        $result = $this->db->query($sql);
        $info = $result->row_array();
        
        $sex_radio = Form::radio(self::$common_config['sex'], $info['sex'],'name="info[sex]" id="sex"');
        $status_radio = Form::radio(self::$common_config['status'], $info['status'],'name="info[status]" id="status"');
        $this->smarty->assign('sex_radio', $sex_radio);
        $this->smarty->assign('status_radio', $status_radio);
        $this->smarty->assign('info', $info);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/user_edit.html");
    }

    public function user_edit_do() {
        $cfg = $this->input->post('cfg');
        if($cfg['id'] < 1) {show_tips('参数异常，请检测');} else {$where = "id={$cfg['id']}";}
        $info = $this->input->post('info');
		if ($_FILES['pic']['name'] != "") {
            $this->load->library('oss');                                                                                             
            $pic_ret = $this->oss->upload('pic', array('dir'=>'avatar'));
            if (isset($pic_ret['error_code']) && intval($pic_ret['error_code'])) {
                show_tips($pic_ret['error_code']. ":" . $pic_ret['error']);
            }
            $info['avatar'] = $pic_ret;
        }
        $query = $this->db->update_string($this->table_name, $info, $where);
        if($this->db->query($query)){
			$pre = self::get_user_key();
			$key = $pre.$cfg['id'];
			$this->hMset($key, $info);
            show_tips('操作成功','','','edit');
        }else{
            show_tips('操作异常，请检测');
        }
    }

	private static function get_user_key() {
		return 'user_';
	}

    public function user_locked() {
        $this->locked();
    }

    public function user_status() {
        $this->status();
    }

	public function user_del_one_ajax() {
		$aid = intval($this->input->get('uid'));
        if($aid>0) {
            $del_query = "DELETE FROM ci_user WHERE id={$aid}";
            $this->db->query($del_query);
            $del_query = "DELETE FROM ci_user_detail WHERE uid={$aid}";
            $this->db->query($del_query);
            echo 1;
        } else {
            echo 0;
        }
	
	}
    public function user_delete() {
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids) > 0) {
                $ids_str = join("','",$ids);
                $query   = "UPDATE {$this->table_name}  SET `status`= -1 WHERE id in('{$ids_str}') and status != 1";                         
                $rs = $this->db->query($query);
                //$this->log->write($this->user_info['user_name'], $this->ip, $ids_str, $this->table_name . ":status", $rs);
                show_tips('操作成功',HTTP_REFERER);
            } else {
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }
    public function sms_list() {
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
        if($dosearch=='ok'){
            $keywords=trim($this->input->get('keywords'));
            $search_arr['keywords']=$keywords;
            if($keywords!=''){
                $where_array[]="mobile='{$keywords}'";                                                                         
            }                                                                                                                        
            if(is_array($where_array) and count($where_array)>0){
                $where=' WHERE '.join(' AND ',$where_array);
            }
        } 
        $pagesize = 10;
        $offset = $pagesize*($page-1);                                                                                               
        $limit = " LIMIT $offset,$pagesize";
        $sql_ct = "SELECT sid FROM ci_user_sms $where";
        $query = $this->dbr->query($sql_ct);
        $log_num = $query->num_rows();
        $pages = pages($log_num, $page, $pagesize);
        $sql = "SELECT `sid`, `uid`, `mobile`, `verifycode`, `identifier`, `operate`, `status`, `valid`, `ip`, `ip_long`, `ctime_keep` FROM ci_user_sms $where  ORDER BY ctime DESC $limit";
        $result = $this->dbr->query($sql);
        $list_data = $result->result_array();                                                                                        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true');
        $this->smarty->display("member/user_sms_list.html");
    }
}
