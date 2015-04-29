<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
class Check_filed{
		
	private $CI;
	function __construct(){
		$this->CI =& get_instance();
		$this->dbr=$this->CI->load->database('dbr', TRUE);
	}
	
	//检测字段重复
	function check_filed_have_ajax($true_table_arr){
		
		$table_name=$this->CI->input->get('tb');
		$field_name=$this->CI->input->get('field');
		$id=$this->CI->input->get('id');
		if($true_table_arr[$table_name]==''){echo 0;exit;}else{$true_table_name=$true_table_arr[$table_name];}
		//计算查询条件
		$field_val=$this->CI->input->get($field_name);
		$where[]="`{$field_name}`='{$field_val}'";
		$field_extend=$this->CI->input->get('field_extend');
		if($field_extend!=''){
			$field_extend_arr=explode('|',$field_extend);
			if(is_array($field_extend_arr) and count($field_extend_arr)>0){
				foreach ($field_extend_arr as $field_row){
					unset($field_val);
					$field_val=$this->CI->input->get($field_row);
					$where[]="`{$field_row}`='{$field_val}'";		
				}
			}
		}
		if($id>0){$where[]="`id` !={$id}";}
		$where_str=join(' AND ',$where);
		$query_str="SELECT id FROM {$true_table_name} WHERE {$where_str} LIMIT 1";
		$result_str=$this->dbr->query($query_str);
		$row_data=$result_str->row_array();
		$data=($row_data['id']>0)?0:1;
		echo $data;
	}
	
}