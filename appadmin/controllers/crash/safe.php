<?php

class safe extends CI_Controller {
    
    private $dbr;
    public function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database("dbr", TRUE);
    }
    public function index() {
    
        echo "333";
    }

    public function logs() {
        $this->load->library('form');
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
                
        if($dosearch=='ok'){
                        
            $keywords=trim($this->input->get('keywords'));
            $search_arr['keywords']=$keywords;
            
            if($keywords!=''){
                $where_array[]="uname like '%{$keywords}%'";                                                                         
            }
            
            if(is_array($where_array) and count($where_array)>0){
                $where=' WHERE '.join(' AND ',$where_array);
            }
            
        }
        
        $pagesize = 10;
        $offset = $pagesize*($page-1);                                                                                               
        $limit = " LIMIT $offset,$pagesize";

        $sql_ct = "SELECT id FROM ci_user_login_log $where";
        $query = $this->dbr->query($sql_ct);
        $log_num = $query->num_rows();
        $pages = pages($log_num, $page, $pagesize);

        $sql = "SELECT `id`, `uid`, `uname`, `result`, `ip`, `login_yday`, `login_time`, `valid`  FROM ci_user_login_log $where  ORDER BY login_time DESC $limit"; 
        $result = $this->dbr->query($sql);
        $list_data = $result->result_array();
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true');
        $this->smarty->display("member/login_logs.html");
    }

    public function ipaddress() {
        $this->smarty->display("member/ipaddress.html");
    }
}
