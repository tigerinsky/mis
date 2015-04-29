<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class app extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
		$this->dbr=$this->load->database('dbr', TRUE);
	}
	
	//默认显示权限列表页
	function index(){
		$this->app_list();
	}
	
	//显示应用列表，同时有检索功能
	private function app_list(){
		
		$page=$this->input->get('page');
		$page = max(intval($page),1);		
		$dosearch=$this->input->get('dosearch');
				
		if($dosearch=='ok'){
						
			$search_filed_arr=array(1=>'title',2=>'url');			
			$search_field_id=intval($this->input->get('search_field_id'));
			$search_arr['search_field_id']=$search_field_id;
			$keywords=trim($this->input->get('keywords'));
			$search_arr['keywords']=$keywords;
			$search_field=$search_filed_arr[$search_field_id];
			
			if($search_field!='' and $keywords!=''){
				$where_array[]="{$search_field} like '%{$keywords}%'";		
			}
			
			if(trim($this->input->get('line'))!=''){
				$search_arr['line']=$this->input->get('line');
				$where_array[]="line='{$this->input->get('line')}'";
			}
			
			if(is_array($where_array) and count($where_array)>0){
				$where=' WHERE '.join(' AND ',$where_array);
			}
			
		}
		
		$pagesize=10;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";
		
		$query_access_num="SELECT id FROM ci_guide_url $where";
		$result_access_num=$this->dbr->query($query_access_num);
		$access_num=$result_access_num->num_rows();
		$pages=pages($access_num,$page,$pagesize);
		
		$query_access_group="SELECT id,kind,title,url,line,mobile FROM ci_guide_url $where $limit";
		$resule_access_group=$this->dbr->query($query_access_group);
		$list_access_group=$resule_access_group->result_array();
		
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_data',$list_access_group);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('admin/app_list.html');
	}
	
	//对应用进行批量上线属性变更
	function app_line(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="UPDATE ci_guide_url SET `line`=(`line`+1)%2 WHERE id in('{$ids_str}')";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//批量删除应用
	function app_del(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="DELETE FROM ci_guide_url WHERE id in('{$ids_str}') AND `line`!=1";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//单条删除应用
	function app_del_one_ajax(){
		$aid=intval($this->input->get('app_id'));
		if($aid>0){
			$del_query="DELETE FROM ci_guide_url WHERE id={$aid}";
			$this->db->query($del_query);		
			echo 1;
		}else{
			echo 0;
		}
	}
	
	//添加应用
	function app_add(){
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/app_add.html');
	}
	
	//执行添加应用
	function app_add_do(){
		$info=$this->input->post('info');
		$info['mobile']=intval($info['mobile']);
		//查找分组ID，并计算权限值
		if($info['title']!='' && $info['kind']!='' && $info['url']!=''){
			$insert_query=$this->db->insert_string('ci_guide_url',$info);
			$this->db->query($insert_query);
			show_tips('操作成功','','','add');
		}else{
			show_tips('数据不完整，请检测');
		}
	}
	
	//修改应用
	function app_edit(){
		$app_id=$this->input->get('app_id');
		$app_query="SELECT id,title,kind,url,mobile FROM ci_guide_url WHERE id={$app_id}";
		$result_app=$this->dbr->query($app_query);
		$info=$result_app->row_array();
		$this->smarty->assign('info',$info);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/app_edit.html');
	}
	
	//执行修改应用
	function app_edit_do(){
		$app_id=$this->input->post('app_id');
		if($app_id<1){show_tips('参数异常，请检测');}else{$where="id={$app_id}";}
		$info=$this->input->post('info');
		$info['mobile']=intval($info['mobile']);
		$insert_query=$this->db->update_string('ci_guide_url',$info,$where);
		if($this->db->query($insert_query)){
			show_tips('操作成功','','','edit');
		}else{
			show_tips('操作异常，请检测');
		}
	}
}

/*This file end*/