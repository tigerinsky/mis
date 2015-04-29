<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class rbac_user extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
		$this->dbr=$this->load->database('dbr', TRUE);
	}
	
	//默认显示会员列表页
	function index(){
		$this->rbac_user_list();
	}
	
	//显示会员列表，同时有检索功能
	private function rbac_user_list(){
		
		$page=$this->input->get('page');
		$role_id=$this->input->get('role_id');
		$page = max(intval($page),1);		
		$dosearch=$this->input->get('dosearch');
					
		if($dosearch=='ok'){
						
			$search_filed_arr=array(1=>'uname',2=>'tname');			
			$search_field_id=intval($this->input->get('search_field_id'));
			$search_arr['search_field_id']=$search_field_id;
			$keywords=trim($this->input->get('keywords'));
			$search_arr['keywords']=$keywords;
			$search_field=$search_filed_arr[$search_field_id];

			if($search_field!='' and $keywords!=''){
				$where_array[]="U.{$search_field} like '%{$keywords}%'";		
			}
			
		}
		
		if($role_id>0){
			$search_arr['role_id']=$role_id;
			$where_array[]=" U.role_id = $role_id";
		}else{
			$where_array[]=" 1 = 1";
		}
		
		if(is_array($where_array) and count($where_array)>0){
			$where=' WHERE '.join(' AND ',$where_array);
		}
		
		$pagesize=10;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";
		
		$query_user_num="SELECT U.id,U.uname,U.tname,U.role_id,U.`status`,U.`lock`,U.lock_time,U.create_time,G.role_name FROM ci_rbac_user AS U left join ci_rbac_role AS G ON U.role_id=G.id $where";
		$result_user_num=$this->dbr->query($query_user_num);
		$user_num=$result_user_num->num_rows();
		$pages=pages($user_num,$page,$pagesize);
		
		$query_user_list="SELECT U.id,U.uname,U.tname,U.role_id,U.`status`,U.`lock`,U.lock_time,U.create_time,G.role_name FROM ci_rbac_user AS U left join ci_rbac_role AS G ON U.role_id=G.id $where $limit";
		
		$resule_user_list=$this->dbr->query($query_user_list);
		$list_user=$resule_user_list->result_array();
		
		$role_array=$this->role_list();
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('role_list',$role_array);
		$this->smarty->assign('list_data',$list_user);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		
		$this->smarty->display('admin/rbac_user_list.html');
	}
	
	
	private function role_list(){
		$role_query="SELECT id,role_name FROM ci_rbac_role ORDER BY listorder DESC";
		$result_role=$this->dbr->query($role_query);
		$role_array=$result_role->result_array();
		return $role_array;
	}
	
	//对用户进行批量审核状态变更
	function rbac_user_change_status(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="UPDATE ci_rbac_user SET `status`=(`status`+1)%2 WHERE id in('{$ids_str}')";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//对用户进行批量锁定状态变更
	function rbac_user_change_lock(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="UPDATE ci_rbac_user SET `lock`=(`lock`+1)%2 WHERE id in('{$ids_str}')";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//批量删除用户
	function rbac_user_del(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				foreach($ids as $val_id){
					$update_role_mum="UPDATE ci_rbac_role SET `num`=(`num`-1) WHERE id in(SELECT id FROM ci_rbac_user WHERE id={$val_id}) AND `num`>0";
					$this->db->query($update_role_mum);
				}
				$del_query="DELETE FROM ci_rbac_user WHERE id in('{$ids_str}')";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//单条删除用户
	function rbac_user_del_one_ajax(){
		$uid=intval($this->input->get('uid'));
		if($uid>0){
			$update_user_mum="UPDATE ci_rbac_role SET `num`=(`num`-1) WHERE id in(SELECT id FROM ci_rbac_user WHERE  id =$uid) AND `num`>0";
			$this->db->query($update_user_mum);
			$del_query="DELETE FROM ci_rbac_user WHERE  id={$uid}";
			$this->db->query($del_query);		
			echo 1;
		}else{
			echo 0;
		}
	}
	
	//添加用户
	function rbac_user_add(){
		$role_array=$this->role_list();
		$this->smarty->assign('role_list',$role_array);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_user_add.html');
	}
	
	//执行添加用户
	function rbac_user_add_do(){
		$info=$this->input->post('info');
		
		if($info['uname']!='' && $info['tname']!='' && $info['pass_word']!='' && $info['role_id']!=''){
			$info['create_time']=time();
			$info['pass_word']=encrypt(trim($info['pass_word']));
			$insert_query=$this->db->insert_string('ci_rbac_user',$info);
			
			if($this->db->query($insert_query)){
				$update_group_mum="UPDATE ci_rbac_role SET `num`=(`num`+1) WHERE id ={$info['role_id']}";
				if($this->db->query($update_group_mum)){
					show_tips('操作成功','','','add');
				}else{
					show_tips('操作异常');
				}
				
			}else{
				show_tips('操作异常');
			}
			
		}else{
			show_tips('数据不完整，请检测');
		}
	}
	
	//修改用户
	function rbac_user_edit(){
		$uid=$this->input->get('uid');
		if($uid==0){show_tips('参数异常');}
		$user_query="SELECT id,tname,uname,pass_word FROM ci_rbac_user WHERE id={$uid}";
		$result_user=$this->dbr->query($user_query);
		$info=$result_user->row_array();
		$role_array=$this->role_list();
		
		$this->smarty->assign('role_list',$role_array);
		$this->smarty->assign('info',$info);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_user_edit.html');
	}
	
	//执行修改用户
	function rbac_user_edit_do(){
		$user_id=$this->input->post('user_id');
		if($user_id<1){show_tips('参数异常，请检测');}else{$where="id={$user_id}";}
		$info=$this->input->post('info');
		if($info['pass_word']!=''){
			$info['pass_word']=encrypt(trim($info['pass_word']));
		}else{
			unset($info['pass_word']);
		}
		$insert_query=$this->db->update_string('ci_rbac_user',$info,$where);
		if($this->db->query($insert_query)){
			show_tips('操作成功','','','edit');
		}else{
			show_tips('操作异常，请检测');
		}
	}
	
	function public_edit_myword(){
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_user_myedit.html');
	}
	
	//执行修改用户
	function rbac_user_myedit_do(){
		
		$pwd_word=trim($this->input->post('pwd_word'));
		$pass_word=trim($this->input->post('pass_word'));
		if($pwd_word=='' || $pass_word==''){show_tips('数据不完整，请检测');}
		
		$this->rbac_config=$this->config->item('config_rbac');
		$userinfo=$this->session->userdata($this->rbac_config['rbac_admin_auth_key']);
		$user_id=intval($userinfo['keyno']);

		$user_query="SELECT id,pass_word FROM ci_rbac_user WHERE id=?";
		$user_result=$this->db->query($user_query,array($user_id));
		$user_info=$user_result->row_array();
		
		if(encrypt($pwd_word)==$user_info['pass_word']){
			$info['pass_word']=encrypt($pass_word);
			$where="id={$user_id}";
			$update_query=$this->db->update_string('ci_rbac_user',$info,$where);
			if($this->db->query($update_query)){show_tips('操作成功');}
		}else{
			show_tips('旧密码输入错误，请重新输入');
		}
	}
	
	
}
/*This file end*/
