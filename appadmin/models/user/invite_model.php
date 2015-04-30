<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 用户注册邀请码管理，分发
 *
 */
class Invite_model extends CI_Model {

    //初始化
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
    public function get_data_by_parm($where = "", $limit = ""){
        $query_data="SELECT `id`, `uid`, `new_uid`, `hash_key`, `valid`, `time_year`, `time_yday`, `time_keep`, `time_create` FROM ci_user_invite_code {$where} ORDER BY id DESC {$limit} ";
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
        $query_data="SELECT count(id) as nums FROM ci_user_invite_code {$where}";
        $result_data=$this->dbr->query($query_data);
        $row_data=$result_data->row_array();
        return $row_data['nums'];
    }
    
    /**
     * 
     */
        
    /**
     * 向数据表中写一行数据
     * @param arr $info 需要插入的数据,一维数组
     * @return bool 是否成功执行
     */
    public function create_info($info){
        $insert_query=$this->db->insert_string('ci_user_invite_code',$info);
        if($this->db->query($insert_query)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 向数据表中写入若干条数据
     * @param int $num 产生的记录条数
     * @return bool 是否成功执行
     */
    public function create_info_batch($uid,$nums){
        $info_batch=$this->set_invite($uid,$nums);
        if($this->db->insert_batch('user_invite_code',$info_batch)){
            return true;
        }else{
            return false;
        }
    }


    
    /**
     * 
     * @param int $uid 用户UID
     * @param int $num 产生的记录条数
     * @return arr $data 产生的数据集
     * errno
     */
    public function set_invite($uid,$nums){
        $now_time=getdate(time());
        $invite_data=array();
        for ($i=0; $i < $nums; $i++) { 
            $invite_data[]=array(
                'uid'=>$uid,
                'hash_key'=>random(8),//born_token($uid),
                'valid'=>1,
                'time_year'=>$now_time['year'],
                'time_yday'=>$now_time['year'].$now_time['yday'],
                'time_keep'=>$now_time[0]+86400*10+10,
                'time_create'=>$now_time[0],
            );
        }
        return $invite_data;
    }
    
}