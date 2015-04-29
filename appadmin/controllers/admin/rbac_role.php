<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class rbac_role extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
		$this->dbr=$this->load->database('dbr', TRUE);
	}
	
	//默认显示角色列表
	function index(){
		$this->rbac_role_list();
	}
	
	//显示角色列表，同时有检索功能
	private function rbac_role_list(){
		
		$page=$this->input->get('page');
		$page = max(intval($page),1);
		$dosearch=$this->input->get('dosearch');
				
		if($dosearch=='ok'){
						
			$keywords=trim($this->input->get('keywords'));
			$search_arr['keywords']=$keywords;
			
			if($keywords!=''){
				$where_array[]="role_name like '%{$keywords}%'";		
			}
			
			if(is_array($where_array) and count($where_array)>0){
				$where=' WHERE '.join(' AND ',$where_array);
			}
			
		}
		
		$pagesize=10;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";
		
		$query_role_num="SELECT id,listorder,num,role_name FROM ci_rbac_role $where";
		$result_role_num=$this->dbr->query($query_role_num);
		$role_num=$result_role_num->num_rows();
		$pages=pages($role_num,$page,$pagesize);
		
		$query_role_group="SELECT id,listorder,num,role_name FROM ci_rbac_role $where $limit";
		$resule_role_group=$this->dbr->query($query_role_group);
		$list_role_group=$resule_role_group->result_array();
		
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_data',$list_role_group);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		
		$this->smarty->display('admin/rbac_role_list.html');
	}
	
	//角色排序
	public function rbac_role_order(){
		if(intval($_POST['dosubmit']==1)) {
			$listorders=$this->input->post('listorders');
			if(is_array($listorders) and count($listorders)>0){
				$edit_query="UPDATE ci_rbac_role SET listorder=? WHERE id=?";
				foreach($listorders as $id => $listorder) {
					$this->db->query($edit_query,array($listorder,$id));
				}
				show_tips('操作成功');
			}else{
				show_tips('参数有误，请重新提交');
			}
			
		} else {
			show_tips('访问异常');
		}
	}
	
	
	//批量删除角色
	function rbac_role_del(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="DELETE FROM ci_rbac_role WHERE id in('{$ids_str}') AND `num`=0";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//单条删除角色
	function rbac_role_del_one_ajax(){
		$aid=intval($this->input->get('aid'));
		if($aid>0){
			$del_query="DELETE FROM ci_rbac_role WHERE  id={$aid} AND `num`=0";
			$this->db->query($del_query);		
			echo 1;
		}else{
			echo 0;
		}
	}
	
	//添加角色
	function rbac_role_add(){
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_role_add.html');
	}
	
	//执行添加权限
	function rbac_role_add_do(){
		$info=$this->input->post('info');
		$info['listorder']=(intval($info['listorder'])>0)?intval($info['listorder']):0;
	   
		//封装sql语句，进行数据插入
		if($info['role_name']!=''){
			$insert_query=$this->db->insert_string('ci_rbac_role',$info);
			if($this->db->query($insert_query)){
				show_tips('操作成功','','','add');
			}else{
				show_tips('操作异常');
			}
		}else{
			show_tips('数据不完整，请检测');
		}
	}
	
	//修改权限
	function rbac_role_edit(){
		$role_id=$this->input->get('role_id');
		$role_query="SELECT id,listorder,role_name,menu_guide,menu_left,num FROM ci_rbac_role WHERE id={$role_id}";
		$result_role=$this->dbr->query($role_query);
		$info=$result_role->row_array();
		$this->smarty->assign('info',$info);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_role_edit.html');
	}
	
	//执行修改权限
	function rbac_role_edit_do(){
		$role_id=$this->input->post('role_id');
		if($role_id<1){show_tips('参数异常，请检测');}else{$where="id={$role_id}";}
		$info=$this->input->post('info');
		$info['listorder']=(intval($info['listorder'])>0)?intval($info['listorder']):0;
		$insert_query=$this->db->update_string('ci_rbac_role',$info,$where);
		if($this->db->query($insert_query)){
			show_tips('操作成功','','','edit');
		}else{
			show_tips('操作异常，请检测');
		}
	}
	
	
	/**
	 *权限赋值选择列表,由于权限可能比较多，所以按权限分组，循环展示，勾选实现赋值
	*/
	
	function rbac_role_give_access(){
		
		$role_id=intval($this->input->get('role_id'));
		
		$show_by=$this->input->get('showby');
		$limit=$show_by=='min'?'LIMIT 100':'';
						
		if($show_by=='search'){
						
			$search_filed_arr=array(1=>'group_name',2=>'group_ename');			
			$search_field_id=intval($this->input->get('search_field_id'));
			$search_arr['search_field_id']=$search_field_id;
			$keywords=$this->input->get('keywords');
			$search_arr['keywords']=$keywords;
			$search_field=$search_filed_arr[$search_field_id];
			
			if($search_field!='' and $keywords!=''){
				$where_array[]="{$search_field} like '%{$keywords}%'";		
			}
			
		}
		
		$where_array[]='`num` > 0';
		if(is_array($where_array) and count($where_array)>0){
			$where='WHERE '.join(' AND ',$where_array);
		}
		
		//获取当前用户的权限，用以在接下来进行对比
		if($role_id<1){show_tips('参数异常，请检查');}{
			$role_all_ace_sql="SELECT id,user_access FROM ci_rbac_role WHERE id={$role_id}";
			$role_all_ace_result=$this->dbr->query($role_all_ace_sql);
			$role_all_ace_row=$role_all_ace_result->row_array();
			if($role_all_ace_row['id']>0 and $role_all_ace_row['user_access']!=''){
				$user_access=unserialize($role_all_ace_row['user_access']);
			}else{
				$user_access=array();
			}
		}
				
		$query_access_group="SELECT id,group_name,group_ename FROM ci_rbac_access_group $where $limit ORDER BY listorder DESC";
		$resule_access_group=$this->dbr->query($query_access_group);
		$list_access_group=$resule_access_group->result_array();
		
		if(is_array($list_access_group) && count($list_access_group)>0){
			$menu_str='';
			foreach($list_access_group as $val){
				$parentid_node=($val['parentid'])? ' class="child-of-node-'.$val['parentid'].'"' : '';
				$ace_str='none|none|none|none|0|0';
				$checked_str=$this->check_group_select($val['group_ename'],$user_access);
				$result[]=array('id'=>$val['id'],'parentid'=>0,'level'=>0,'name'=>$val['group_name'],'ace_val'=>$ace_str,'parentid_node'=>$parentid_node,'checked'=>$checked_str);
				$group_acc_arr[]=$val['id'];
			}
			
			
			$where_str="WHERE group_id IN('".join("','",$group_acc_arr)."')";
			
			$query_access="SELECT id,group_id,ace_name,ace_model,ace_control,ace_action,ace_group,ace_val FROM ci_rbac_access $where_str";
			$resule_access=$this->dbr->query($query_access);
			$list_access=$resule_access->result_array();
			
			if(is_array($list_access) && count($list_access)>0){
				foreach ($list_access as $ace_val){
				$parentid_node=($ace_val['group_id'])? ' class="child-of-node-'.$ace_val['group_id'].'"' : '';
				$checked_str=$this->check_access_select($ace_val['ace_val'],$ace_val['ace_group'],$user_access);
				$ace_str=$ace_val['ace_model'].'|'.$ace_val['ace_control'].'|'.$ace_val['ace_action'].'|'.$ace_val['ace_group'].'|'.$ace_val['id'].'|'.$ace_val['ace_val'];
					$result[]=array('id'=>$ace_val['id'],'parentid'=>$ace_val['group_id'],'level'=>1,'name'=>$ace_val['ace_name'],'ace_val'=>$ace_str,
					'parentid_node'=>$parentid_node,'checked'=>$checked_str);
				}
				
			}
			
			
			$this->load->library('tree');
			$this->tree->icon = array('│ ','├─ ','└─ ');
			$this->tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			
			$str = "<tr id='access-\$id' \$parentid_node>
					<td style='padding-left:30px;'>\$spacer<input type='checkbox' name='access_id[]' value='\$ace_val' level='\$level' \$checked onclick='javascript:checknode(this);'> \$name</td>
				</tr>";
			
			$str_group = "<tr id='node-\$id'>
					<td style='padding-left:30px;'>\$spacer<input type='checkbox' name='node_id[]' value='0' level='\$level' \$checked onclick='javascript:checknode(this);'> \$name</td>
				</tr>";
						
			$this->tree->init($result);
			$show_access_list = $this->tree->get_trees(0,$str,0,'',$str_group);
			
			$this->smarty->assign('role_id',$role_id);
			$this->smarty->assign('search_arr',$search_arr);
			$this->smarty->assign('list_data',$show_access_list);
			$this->smarty->assign('show_dialog','true');
			$this->smarty->display('admin/rbac_role_give_access.html');
			
		}else{
			show_tips('请配置权限库数据');
		}
		
		
	}
	
	/**
	 * 执行权限的赋值操作
	 */
	function rbac_access_give_group_do(){
		$role_id=intval($this->input->post('role_id'));
		if($role_id<1){show_tips('参数异常，请重新提交');}
		if($this->rbac_acess_proof_one($role_id)){
			show_tips('执行成功','',1250,'role_access');
		}else{
			show_tips('权限更新出现异常');
		}
	}
	
	/**
	 * 对所有用户组的权限进行整理
	 */
	function rbac_acess_proof(){
		$role_query="SELECT id,role_name FROM ci_rbac_role";
		$role_result=$this->dbr->query($role_query);
		$role_data=$role_result->result_array();
		if(is_array($role_data) and count($role_data)){
			foreach ($role_data as $vals){
				$this->rbac_acess_proof_one($vals['id']);
			}
			show_tips('权限整理成功');
		}else{
			show_tips('请先建立用户组');
		}
	}
	
	//判断权限分组选中状态
	private function check_group_select($ename,$user_access){
		$access=intval($user_access[$ename]);
		if($access>0){
			return ' checked ';
		}else{
			return '';
		}
	}
	
	//判断权限选中状态
	private function check_access_select($aceval,$ename,$user_access){
		
		$access=intval($user_access[$ename]);
		$aceval=intval($aceval);
		//echo $aceval,'&',$access,'=',$aceval&$access,'<br>';
		if($access>0 and $aceval>0){
			if(($access&$aceval)>0){
				return ' checked';	
			}else{
				return '';
			}
		}else{
			return '';
		}
	}
	
	/**
	 * 对指定分组的权限进行整理
	 */
	private function rbac_acess_proof_one($roleid=0){
		
		if($roleid>0){
			$where_ace="WHERE `role_group`={$roleid}";
			$where_role="WHERE `role_group`={$roleid}";
		}else{
			return false;
		}
		
		$query_list="SELECT role_group,ace_group,sum(ace_val) as avl_sum FROM `ci_rbac_role_access_list` $where_ace GROUP BY role_group,ace_group";
		$result_list=$this->dbr->query($query_list);
		$data_list=$result_list->result_array();
		if(is_array($data_list) and count($data_list)>0){
			
			//删除角色分组权限
			$update_sql="DELETE FROM ci_rbac_role_access $where_ace";
			if(!$this->db->query($update_sql)){return false;}
			foreach ($data_list as $val){
				//循环插入数据	
				$info=array(
					'role_group'=>$val['role_group'],
					'ace_group'=>$val['ace_group'],
					'ace_sumval'=>$val['avl_sum']
				);
				$insert_sql=$this->db->insert_string('ci_rbac_role_access',$info);
				if(!$this->db->query($insert_sql)){return false;}
				
			}
		}else{
			return false;
		}
		
		$role_ace_sql="SELECT role_group,ace_group,ace_sumval FROM ci_rbac_role_access $where_role";
		$role_ace_result=$this->dbr->query($role_ace_sql);
		$role_ace_data=$role_ace_result->result_array();
		if(is_array($role_ace_data) and count($role_ace_data)>0){
			unset($all_access);
			foreach ($role_ace_data as $ace_val){
				$all_access[$ace_val['ace_group']]=$ace_val['ace_sumval'];
			}			
			$all_access_str=serialize($all_access);
			$update_sql="UPDATE ci_rbac_role SET user_access=? WHERE id={$roleid}";
			if(!$this->db->query($update_sql,array($all_access_str))){
				return false;
			}
		}
		
		return true;
	}
	
	
	function rbac_role_add_access_ajax(){
		$data['ret']=0;
		$par_str=$this->input->post('par_str');
		$role_id=intval($this->input->post('role_id'));
		if($par_str!=''){
			$ace_arr=explode('|',$par_str);
		}
		if(count($ace_arr)==6){
			$info['ace_model']=$ace_arr[0];
			$info['ace_control']=$ace_arr[1];
			$info['ace_action']=$ace_arr[2];
			$info['ace_group']=$ace_arr[3];
			$info['ace_id']=$ace_arr[4];
			$info['ace_val']=$ace_arr[5];
			$info['role_group']=$role_id;
			$where_arr[]="ace_id='{$info['ace_id']}'";
			$where_arr[]="role_group='{$info['role_group']}'";
			$where_arr[]="ace_group='{$info['ace_group']}'";
			$where_str='WHERE '.join(' AND ',$where_arr);
			$select_query="SELECT id FROM ci_rbac_role_access_list $where_str";
			$select_result=$this->dbr->query($select_query);
			$row_data=$select_result->row_array();
			if($row_data['id']>0){
				$data['ret']=1;
			}else{
				$insert_query=$this->db->insert_string('ci_rbac_role_access_list',$info);
				if($this->db->query($insert_query)){
					$data['ret']=1;
				}
			}
			echo $data['ret'];
		}
	}
	
	function rbac_role_clear_access_ajax(){
		$data['ret']=0;
		$par_str=$this->input->post('par_str');
		$role_id=intval($this->input->post('role_id'));
		if($par_str!=''){
			$ace_arr=explode('|',$par_str);
		}
		if(count($ace_arr)==6){
			$info['ace_model']=$ace_arr[0];
			$info['ace_control']=$ace_arr[1];
			$info['ace_action']=$ace_arr[2];
			$info['ace_group']=$ace_arr[3];
			$info['ace_id']=$ace_arr[4];
			$info['ace_val']=$ace_arr[5];
			$info['role_group']=$role_id;
			$where_arr[]="ace_id='{$info['ace_id']}'";
			$where_arr[]="role_group='{$info['role_group']}'";
			$where_arr[]="ace_group='{$info['ace_group']}'";
			$where_str='WHERE '.join(' AND ',$where_arr);
			$delete_query="DELETE FROM ci_rbac_role_access_list $where_str";
			if($this->db->query($delete_query)){
				$data['ret']=1;
			}
			echo $data['ret'];
		}
	}
}
