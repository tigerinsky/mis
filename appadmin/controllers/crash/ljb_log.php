<?php

class ljb_log extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->dbw = $this->load->database("dbw", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->table_name = "ci_user_gold_log";
    }

    public function index() {
        $this->ljb_log_list();
    }
    
    public function ljb_log_list() {
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
        $sql       = "SELECT id, uid, keyid, action, affect, intro, time_create FROM {$this->table_name} $where $limit";
        $result    = $this->dbr->query($sql);
        $list_data = $result->result_array();
        //debug_show($list_data, 'list_data');
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/ljb_log_list.html");
    }

}