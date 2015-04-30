<?php
/**
 * 通讯录反馈信息表
 * @author Gaozhen'an <zhenan@lanjinger.com>
 * @version $Id: Feedback_model.php  $
 */

class Feedback_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database('dbr',TRUE,TRUE);
        //$this->dbw = $this->load->database('dbw',TRUE,TRUE);

        $this->table_name = "app_phone_feedback";
    }

    /**
     * 获取反馈信息表中指定id的手机号
     */
    public function get_contact_by_ids(Array $ids) {

        //$where = array('contact_status'=>1);
        
        $this->dbr->where_in('id', $ids);
        //$this->dbr->where('contact_status', 1);
        $q = $this->dbr->select('contact,phone_id')->get($this->table_name);
        $r = $q->result_array();
        return $r;
    }
    
}
