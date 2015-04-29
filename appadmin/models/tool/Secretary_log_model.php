<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 用户沟通记录
 * 用以在管理平台中确定哪些用户有未处理的消息
 */
class Secretary_log_model extends CI_Model {

    public function __construct(){
        parent::__construct();
        $this->dbr = $this->load->database('dbr',TRUE);
    }
    
    /**
     * 查询出筛选条件下的数据
     * @param str $where 查询条件
     * @param str $limit 条数筛选 
     * @return int $data 分会符合条件二维数组
     */
    public function get_data_by_parm($where,$order,$limit){
        $query_data="SELECT `uid`, `uid_sec`, `time_user`, `time_sec`, `is_new` FROM ci_secretary_log {$where} {$order} {$limit}";
        $result_data=$this->dbr->query($query_data);
        $list_data=$result_data->result_array();
        return $list_data;
    }
    
    /**
     * 计算出筛选条件下的数据的条数
     * @param int $where 查询条件
     * @return int $data 返回数据的条数
     */
    public function get_count_by_parm($where){
        $query_data="SELECT count(uid) as nums FROM ci_secretary_log {$where}";
        $result_data=$this->dbr->query($query_data);
        $row_data=$result_data->row_array();
        return $row_data['nums'];
    }

    /**
     * 维护用户沟通日志
     * @param arr $seclog 用户日志信息
     */
    public function set_seclog($seclog){
        /*
        $seclog['uid']=$uid;
        $seclog['time_user']=$now_time;
        */
        //$seclog['is_new']=1;

        $update_seclog_query=$this->db->insert_string('secretary_log',$seclog);
        $update_seclog_query=$update_seclog_query.' ON DUPLICATE KEY UPDATE uid=LAST_INSERT_ID(uid),time_sec=?,is_new=0';
        if($this->db->query($update_seclog_query,array($seclog['time_sec']))){
            $errno=0;
        }else{
            $errno=20702;//私信日志写入异常
        }

        $result['errno']=$errno;
        return $result;
    }
}