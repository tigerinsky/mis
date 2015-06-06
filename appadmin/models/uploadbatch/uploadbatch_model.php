<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * 二级图片分类管理model
 */
class Uploadbatch_model extends CI_Model {

	private $dbr = null;
	function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database('dbr',TRUE,FALSE);
		$this->load->library('http2');
	}

	/**
	 * 查询出筛选条件下的数据
	 * @param str $where 查询条件
	 * @param str $limit 条数筛选
	 * @return int $data 分会符合条件二维数组
	 */
	public function get_data_by_parm($where,$limit=""){
		$query_data="SELECT `tid`, `uid`, `type`, `f_catalog`, `content`, `ctime`, `img`, `s_catalog`, `tags`, `img_oname` FROM ci_tweet_offline {$where} ORDER BY tid ASC $limit";
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
		$query_data="SELECT count(tid) as nums FROM ci_tweet_offline {$where}";
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
		$query_data="SELECT tags,tid FROM ci_tweet_offline WHERE tid=".$id;
		$result_data=$this->dbr->query($query_data);
		$row_data=$result_data->row_array();
		if($row_data['tid']>0){
			$result=$row_data;
		}else{
			$result='';
		}
		return $result;
	}

	/**
	 * 执行单条删除或取消删除操作
	 * @param str $ids_str 需要删除的id集合
	 * @return bool 是否执行成功
	 */
	public function one_del($id, $op) {
		$info['type'] = 'delete';
		$info['tid']  = $id;
		$info['op']   = $op;
		$param_str = http_build_query($info);
		$url = $this->mis_tweet['tweet'] . '?' . $param_str;
		$ret = $this->http2->get($url);
		$data = json_decode($ret, true);
		if ($data['errno'] == 0) {
			return true;
		}
		return false;
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
		$del_rule="UPDATE ci_mis_imgmgr SET is_deleted = 0 WHERE id IN('{$id_str}')";
		if($this->db->query($del_rule)){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 向数据表中写入一行数据
	 * @param arr $info 需要插入的数据
	 * @param bool 是否成功执行
	 */
	public function create_info($info){
		$insert_query=$this->db->insert_string('ci_mis_imgmgr',$info);
		if($this->db->query($insert_query)){
			return true;
		} else {
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
		$where="tid={$id}";
		$update_rule=$this->db->update_string('ci_tweet_offline', $info, $where);
		if($this->db->query($update_rule)){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 向数据表中写入一行数据
	 * @param arr $info 需要插入的数据
	 * @param bool 是否成功执行
	 */
	public function offline_create_info($info){
		$insert_query=$this->db->insert_string('ci_tweet',$info);
		if($this->db->query($insert_query)){
			return true;
		} else {
			return false;
		}
    }

    public function insert_resource_info($query) {
        $insert_query = $this->db->insert_string('ci_resource', $query);
        if (!$this->db->query($insert_query)) {
            return false;
        }

        return true;
    }
}
/* End of file this file */
?>
