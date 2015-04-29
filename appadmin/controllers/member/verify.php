<?php
/**
 * 用户验证
 * @author Gaozhen'an <gaozhenan@lanjinger.com>
 * @version $Id: verify.php  $
 */

class verify extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->load->library('form');
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->table_name = "ci_user_verify";
        $this->table_name_short = "user_verify";
		$this->load->model('member/member_model', '_member');
		$this->load->model('member/member_verify_model', '_mverify');
    }

    public function index() {
        $this->verify_list();
    }
    
    public function verify_list() {
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        if($dosearch == 'ok'){
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "uid = {$uid} ";                                                                         
            }                                                                                                                        
            if(is_array($where_array) and count($where_array) > 0) {
                $where = ' WHERE '.join(' AND ',$where_array);
            }
        }
        $pagesize  = 10;
        $offset    = $pagesize*($page-1);                                                                                               
        $limit     = " LIMIT $offset,$pagesize";
        $sql_ct    = "SELECT id FROM {$this->table_name} $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT id, uid, verify, verify_sure, verify_data, verify_info, time_create FROM {$this->table_name} $where  ORDER BY time_create DESC $limit";
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        //debug_show($list_data, 'list_data');
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('identify', self::$common_config['identity']);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/verify_list.html");
    }

    public function verify_add() {
        exit("todo");
        $sex_radio = Form::radio(self::$common_config['sex'], 0,'name="info[sex]" id="sex"');
        $status_radio = Form::radio(self::$common_config['status'], 1,'name="info[status]" id="status"');
        $this->smarty->assign('sex_radio', $sex_radio);
        $this->smarty->assign('status_radio', $status_radio);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/verify_add.html");
    }

    public function verify_add_do() {
        exit("todo");
        $info = $this->input->post('info');
        $this->load->library('mcrypt_3des');
        $info['create_time'] = time();
        $info['umobile'] = $this->mcrypt_3des->en3des(trim($info['umobile']), array('create_time'=> $info['create_time']));
        //print_r($info);exit;
        if ($info['uname'] != '' && $info['umobile'] != '') {
            $insert_query = $this->db->insert_string($this->table_name, $info);

            $this->db->query($insert_query);
            show_tips('操作成功','','','add');
        } else {
            show_tips('数据不完整，请检测');
        }
    }

    public function verify_locked() {
        $this->locked();
    }

    public function verify_status() {
		$status = intval($this->input->get('status'));
		$status = $status == 1 ? 1 : 0;
        $ids = $this->input->post('ids');
		if ($ids == false) {
			show_tips('参数有误，请重新提交');
		}
		$info = array();
		$idsarr = array();
		foreach ($ids as $v) {
			list($id, $identify) = explode('-', $v);
			$idsarr[] = $id;
			$info[] = array('id' => $id, 'ukind_verify'=>$status);
		}
		$this->db->trans_start();
		$this->_member->multi_update($info);
		$this->_mverify->update_verify_status($idsarr, $status);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			show_tips('参数有误，请重新提交');
		} else {
			foreach($idsarr as $v) {
				$r = $this->_mverify->verify_notify($v, $status);
			}
			$status == 1 && $this->_member->multi_upload_phonebook($info);
            show_tips('操作成功',HTTP_REFERER);
		}
    }

	public function verify_status_one_ajax() {
		$uid    = intval($this->input->get('uid'));
		$id     = intval($this->input->get('id'));
		$status = intval($this->input->get('status'));
		$status = $status == 1 ? 1 : 0;
		if ($uid == false) {
			echo 0;
		}
		$info = array('id' => $uid, 'ukind_verify' => $status);
		$this->db->trans_start();
		$this->_member->update($info);
		$this->_mverify->update_verify_status(array($id), $status);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo 0;
		} else {
			$r = $this->_mverify->verify_notify($uid, $status);
			$status == 1 && $this->_member->upload_phonebook($info);
			echo 1;
		}
	}

	public function verify_data() {
		$id = intval($this->input->get('id'));
		if ($id > 0) {
			$this->db->where('id', $id);
			$q = $this->db->select('verify_data')->get($this->table_name_short);
			$r = $q->row()->verify_data;
			$r = unserialize($r);
			debug_show($r, 'verify_data');
			$this->smarty->assign('verify_data', $r);
			$this->smarty->display('member/verify_data.html');
		}
	}
    public function verify_delete() {
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids) > 0) {
                $ids_str = join("','",$ids);
				$query   = "UPDATE {$this->table_name}  SET `status`= -1 WHERE id in('{$ids_str}') and status != 1";
				$rs = $this->db->query($query);
                //$this->log->write($this->verify_info['verify_name'], $this->ip, $ids_str, $this->table_name . ":status", $rs);
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
        $sql_ct = "SELECT id FROM ci_verify_verify_sms $where";
        $query = $this->dbr->query($sql_ct);
        $log_num = $query->num_rows();
        $pages = pages($log_num, $page, $pagesize);
        $sql = "SELECT `id`, `uid`, `mobile`, `smscode`, `identifier`, `operate`, `valid`, `ip`, `time_keep`, `time_yday`, `time_brithdy` FROM ci_verify_verify_sms $where  ORDER BY time_brithdy DESC $limit";
        $result = $this->dbr->query($sql);
        $list_data = $result->result_array();                                                                                        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true');
        $this->smarty->display("member/verify_sms_list.html");
    }
}
