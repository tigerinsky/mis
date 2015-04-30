<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 在后台调用按指定条件调用tips网站标签
 *
 */
class Tips_model extends CI_Model {

	public function __construct(){
        parent::__construct();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->load->config('tips_category',TRUE);
		$this->conf_tips=$this->config->item('tips_category');
    }
	
    /**
     * 根据条件查询出指定的标签信息
     *
     * @param int $kind 所属分类ID
     * @param int $limit 记录条数
     * @param string $order 排序
     * @example 
     *  $where=array('kind'=>1,'top'=>1,'flag'=>1);
     *  $this->tips_model->get_tips($where,10,'top desc,flag desc,listorder desc')
     */
	public function get_tips($where,$limit=0,$order=''){
	    $where['status']=1;
	    $this->db->where($where);
	    if($order!=''){
	        $this->db->order_by($order);
	    }
	    if($limit>0){
	        $this->db->limit($limit);
	    }
	    $tips_data=$this->db->get('app_tips_info');
        $tips_list=$tips_data->result_array();
        return $tips_list;
	}

    public function get_tips_for_selectbox($where, $limit = 0, $order = '') {
	    $this->db->where($where);
	    if($order != ''){
	        $this->db->order_by($order);
	    }
	    if($limit > 0){
	        $this->db->limit($limit);
	    }
	    $tips_re   = $this->db->select(array('id', 'tips_name'))->get('app_tips_info');
        $tips_list = $tips_re->result_array();
        $tips_selectbox = array();
        foreach ($tips_list as $v) {
            $tips_selectbox[$v['id']] = $v['tips_name'];
        }
        return $tips_selectbox;
	}
    
}
