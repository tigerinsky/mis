<?php

class ljb_rule extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->dbw = $this->load->database("dbw", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->table_name = "ci_user_gold_rule";
    }

    public function index() {
        $this->ljb_rule_list();
    }
    
    public function ljb_rule_list() {
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        if($dosearch == 'ok'){
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                //$where_array[] = "sname like '%{$keywords}%' or uname like '%{$keywords}%' ";                                                                         
            }                                                                                                                        
            if(is_array($where_array) and count($where_array) > 0) {
                //$where = ' WHERE '.join(' AND ',$where_array);
            }
        }
        $pagesize  = 10;
        $offset    = $pagesize*($page-1);                                                                                               
        $limit     = " LIMIT $offset,$pagesize";
        $sql_ct    = "SELECT id FROM {$this->table_name} $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT id, keyid, rule_name, gold, rule_intro FROM {$this->table_name} $where $limit";
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        //debug_show($list_data, 'list_data');
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/ljb_rule_list.html");
    }

    public function ljb_rule_add() {
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/ljb_rule_add.html");
    }

    public function ljb_rule_add_do() {
        $info = $this->input->post('info');
        if ($info['keyid'] != '' && $info['gold'] != '') {
            $insert_query = $this->db->insert_string($this->table_name, $info);
            $this->db->query($insert_query);
            show_tips('操作成功','','','add');
        } else {
            show_tips('数据不完整，请检测');
        }
    }

    public function ljb_rule_view() {
        $this->ljb_rule_edit();
    }
    public function ljb_rule_edit() {
        $this->load->library('form');
        $aid = intval($this->input->get('id'));
        $sql = "SELECT id, keyid, rule_name, gold, rule_intro FROM {$this->table_name} WHERE id={$aid}";
        $result = $this->db->query($sql);
        $info = $result->row_array();
        $this->smarty->assign('info', $info);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/ljb_rule_edit.html");
    }

    public function ljb_rule_edit_do() {
        $aid = $this->input->post('id');
        if($aid < 1) {show_tips('参数异常，请检测');} else {$where = "id={$aid}";}
        $info = $this->input->post('info');
        $query = $this->db->update_string($this->table_name, $info, $where);
        if($this->db->query($query)){
            show_tips('操作成功','','','edit');
        }else{
            show_tips('操作异常，请检测');
        }
    }

    public function ljb_rule_locked() {
        $this->locked();
    }

    public function ljb_rule_status() {
        $this->status();
    }

    public function ljb_rule_delete() {
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids) > 0) {
                $ids_str = join("','",$ids);
                $query   = "UPDATE {$this->table_name}  SET `status`= -1 WHERE id in('{$ids_str}') and status != 1";                         
                $rs = $this->db->query($query);
                //$this->log->write($this->ljb_rule_info['ljb_rule_name'], $this->ip, $ids_str, $this->table_name . ":status", $rs);
                show_tips('操作成功',HTTP_REFERER);
            } else {
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }
}