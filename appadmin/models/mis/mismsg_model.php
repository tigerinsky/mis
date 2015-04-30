<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
消息推送
*/
class Mismsg_model extends CI_Model {
    
    private $dbr = null;
    function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database('dbr',TRUE,FALSE);
    }
    
    /**
     * 查询出筛选条件下的数据
     * @param str $where 查询条件
     * @param str $limit 条数筛选 
     * @return int $data 分会符合条件二维数组
     */
    public function get_data_by_parm($where = "", $limit = ""){
        $query_data="SELECT `id`, `industry`, `content`, `wap_url`, `type`, `rel_id`, `title`, `status`, `pushed`, `time_push`, `time_create` FROM ci_app_mismsg {$where} {$limit}";
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
        $query_data="SELECT count(id) as nums FROM ci_app_mismsg {$where}";
        $result_data=$this->dbr->query($query_data);
        $row_data=$result_data->row_array();
        return $row_data['nums'];
    }
    
    /**
     * 查询出指定的编号数据
     * @param int $id 数据ID编号
     * @return int $data 返回数据内容
     */
    public function get_info_by_id($id){
        $query_data="SELECT `id`, `industry`, `rel_id`, `title`, `status`, `pushed`, `time_push`, `content`, `wap_url`, `type`,`time_create` FROM ci_app_mismsg WHERE id=?";
        $result_data=$this->dbr->query($query_data,array($id));
        $row_data=$result_data->row_array();
        if($row_data['id']>0){
            $result=$row_data;
        }else{
            $result='';
        }
        return $result;
    }
        
    /**
     * 批量变更推荐状态
     * @param str $ids_str 需要变更的id集合
     * @return bool 是否执行成功 
     */
    /*public function change_info_flag($ids_str){
        $update_change_query="UPDATE ci_app_mismsg SET `flag`=(`flag`+1)%2 WHERE id in('{$ids_str}')";
        if($this->db->query($update_change_query)){ 
            return true;
        }else{
            return false;
        }
    }*/
    
    /**
     * 批量变更审核状态
     * @param str $ids_str 需要变更的id集合
     * @return bool 是否执行成功 
     */
    public function change_info_status($ids_str){
        $update_change_query="UPDATE ci_app_mismsg SET `status`=(`status`+1)%2 WHERE id in('{$ids_str}')";
        if($this->db->query($update_change_query)){ 
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 批量修改排序编号
     * @param str $listorders 需要变更排序的数组id集合
     * @return bool 是否执行成功 
     */
    /*public function change_info_order($listorders){
        $result=true;
        $edit_query="UPDATE ci_app_mismsg SET listorder=? WHERE id=?";
        foreach($listorders as $id => $listorder) {
            if(!$this->db->query($edit_query,array($listorder,$id))){
                $result=false;
            }
        }
        return $result;
    }*/
        
    /**
     * 向数据表中写入一行数据
     * @param arr $info 需要插入的数据
     * @param bool 是否成功执行
     */
    public function create_info($info){
        $insert_query=$this->db->insert_string('ci_app_mismsg',$info);
        if($this->db->query($insert_query)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 对数据表中的单行数据进行修改
     * @param arr $info 需要修改的键值对
     * @param int $id 被修改的id编号
     * @return bool 是否执行成功 
     */
    public function update_info($info,$id){
        /*
        $info=array(
            'kind'=>$kind,
            'uid'=>$uid,
        );
        */
        $where="id={$id}";
        $update_rule=$this->db->update_string('ci_app_mismsg', $info, $where); 
        if($this->db->query($update_rule)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 对指定编号数据进行删除
     * @param arr $ids 需要删除的数据的id，可以是单个id，也可以是id的数组
     * @return bool 返回执行结果
     */
    public function del_info($ids){
        if(is_array($ids) && count($ids)){
            $id_str=join("','",$ids);
        }else{
            $id_str=$ids;
        }
        $del_rule="DELETE FROM ci_app_mismsg WHERE id IN('{$id_str}') AND `status` =0 AND `pushed` =0";
        if($this->db->query($del_rule)){
            return true;
        }else{
            return false;
        }
    }
}
/* End of file this file */
?>
