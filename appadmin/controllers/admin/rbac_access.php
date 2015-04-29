<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class rbac_access extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
		$this->dbr=$this->load->database('dbr', TRUE);
	}
	
	//默认显示权限列表页
	function index(){
		$this->rbac_access_list();
	}
	
	//显示权限列表，同时有检索功能
	private function rbac_access_list(){
		
		$page=$this->input->get('page');
		$page = max(intval($page),1);		
		$dosearch=$this->input->get('dosearch');
				
		if($dosearch=='ok'){
						
			$search_filed_arr=array(1=>'ace_name',2=>'ace_group');			
			$search_field_id=intval($this->input->get('search_field_id'));
			$search_arr['search_field_id']=$search_field_id;
			$keywords=trim($this->input->get('keywords'));
			$search_arr['keywords']=$keywords;
			$search_field=$search_filed_arr[$search_field_id];
			
			if($search_field!='' and $keywords!=''){
				$where_array[]="{$search_field} like '%{$keywords}%'";		
			}
			
			if(trim($this->input->get('model_val'))!=''){
				$search_arr['model_val']=$this->input->get('model_val');
				$where_array[]="ace_model='{$this->input->get('model_val')}'";
			}
			
			if(trim($this->input->get('control_val'))!=''){
				$search_arr['control_val']=$this->input->get('control_val');
				$where_array[]="ace_control='{$this->input->get('control_val')}'";
			}
			
			if(trim($this->input->get('action_val'))!=''){
				$search_arr['action_val']=$this->input->get('action_val');
				$where_array[]="ace_action='{$this->input->get('action_val')}'";
			}
			
			if(trim($this->input->get('stop'))!=''){
				$search_arr['stop']=$this->input->get('stop');
				$where_array[]="stop='{$this->input->get('stop')}'";
			}
			
			if(is_array($where_array) and count($where_array)>0){
				$where=' WHERE '.join(' AND ',$where_array);
			}
			
		}
		
		$pagesize=10;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";
		
		$query_access_num="SELECT id,listorder,group_id,ace_name,ace_model,ace_control,ace_action,ace_group,stop FROM ci_rbac_access $where";
		$result_access_num=$this->dbr->query($query_access_num);
		$access_num=$result_access_num->num_rows();
		$pages=pages($access_num,$page,$pagesize);
		
		$query_access_group="SELECT id,listorder,group_id,ace_name,ace_model,ace_control,ace_action,ace_group,stop FROM ci_rbac_access $where $limit";
		$resule_access_group=$this->dbr->query($query_access_group);
		$list_access_group=$resule_access_group->result_array();
		
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_data',$list_access_group);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('admin/rbac_access_list.html');
	}
	
	//权限排序
	public function rbac_access_order(){
		if(intval($_POST['dosubmit']==1)) {
			$listorders=$this->input->post('listorders');
			if(is_array($listorders) and count($listorders)>0){
				$edit_query="UPDATE ci_rbac_access SET listorder=? WHERE id=?";
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
	
	//对权限进行批量伪删除属性变更
	function rbac_access_del_like(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="UPDATE ci_rbac_access SET `stop`=(`stop`+1)%2 WHERE id in('{$ids_str}')";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//批量删除权限
	function rbac_access_del(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				foreach($ids as $val_id){
					$update_group_mum="UPDATE ci_rbac_access_group SET `num`=(`num`-1) WHERE id in(SELECT group_id FROM ci_rbac_access WHERE  id={$val_id}) AND `num`>0";
					$this->db->query($update_group_mum);
				}
				$del_query="DELETE FROM ci_rbac_access WHERE id in('{$ids_str}') AND `lock`!=1 AND `num`=0";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//单条删除权限
	function rbac_access_del_one_ajax(){
		$aid=intval($this->input->get('aid'));
		if($aid>0){
			$update_group_mum="UPDATE ci_rbac_access_group SET `num`=(`num`-1) WHERE id in(SELECT group_id FROM ci_rbac_access WHERE  id =$aid) AND `num`>0";
			$this->db->query($update_group_mum);
			$del_query="DELETE FROM ci_rbac_access WHERE  id={$aid}";
			$this->db->query($del_query);		
			echo 1;
		}else{
			echo 0;
		}
	}
	
	//添加权限
	function rbac_access_add(){
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_access_add.html');
	}
	
	//执行添加权限
	function rbac_access_add_do(){
		$info=$this->input->post('info');
		$info['listorder']=(intval($info['listorder'])>0)?intval($info['listorder']):0;
	
		//查找分组ID，并计算权限值
		if($info['ace_name']!='' && $info['ace_model']!='' && $info['ace_control']!='' && $info['ace_action']!='' && $info['ace_group']!=''){
			$query_ace_group="SELECT id,peak,`lock` FROM ci_rbac_access_group WHERE group_ename='{$info['ace_group']}' LIMIT 1";
			$result_ace_group=$this->dbr->query($query_ace_group);
			$row_ace_group=$result_ace_group->row_array();
			if(!is_array($row_ace_group) || count($row_ace_group)<1){
				show_tips('权限分组填写有误，请检测');
			}else{
				if($row_ace_group['lock']=='1'){show_tips('被锁定分组不能增加权限');}
				if($row_ace_group['peak']>=60){show_tips('该组权限已满，请新建分组');}
				$info['ace_val']=pow(2,$row_ace_group['peak']);
				$info['group_id']=$row_ace_group['id'];
			}
			$insert_query=$this->db->insert_string('ci_rbac_access',$info);
			$this->db->query($insert_query);
			
			$update_group_mum="UPDATE ci_rbac_access_group SET `num`=(`num`+1),`peak`=(`peak`+1) WHERE id ={$info['group_id']}";
			$this->db->query($update_group_mum);
			
			$update_group_lock="UPDATE ci_rbac_access_group SET `lock`=1 WHERE `peak` >= 60 AND `lock` !=1";
			$this->db->query($update_group_lock);
			
			show_tips('操作成功','','','add');
		}else{
			show_tips('数据不完整，请检测');
		}
	}
	
	//修改权限
	function rbac_access_edit(){
		$ace_id=$this->input->get('ace_id');
		$ace_query="SELECT id,listorder,group_id,ace_name,ace_model,ace_control,ace_action,ace_group,stop FROM ci_rbac_access WHERE id={$ace_id}";
		$result_ace=$this->dbr->query($ace_query);
		$info=$result_ace->row_array();
		$this->smarty->assign('info',$info);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_access_edit.html');
	}
	
	//执行修改权限
	function rbac_access_edit_do(){
		$ace_id=$this->input->post('ace_id');
		if($ace_id<1){show_tips('参数异常，请检测');}else{$where="id={$ace_id}";}
		$info=$this->input->post('info');
		$info['listorder']=(intval($info['listorder'])>0)?intval($info['listorder']):0;
		$insert_query=$this->db->update_string('ci_rbac_access',$info,$where);
		if($this->db->query($insert_query)){
			show_tips('操作成功','','','edit');
		}else{
			show_tips('操作异常，请检测');
		}
	}
	
	//进行权限 model,controll,action 组合后唯一性进行检测
	function check_model_to_action_ajax(){
		$id=$this->input->get('id');
		$model=$this->input->get('ace_model');
		$control=$this->input->get('ace_control');
		$action=$this->input->get('ace_action');
		if($model!='' and $control!='' and $action!=''){
			$where[]="`ace_model` ='{$model}' AND `ace_control`='{$control}' AND `ace_action`='{$action}'";
		}else{
			echo 0;exit;
		}
		if($id>0){$where[]="`id` !={$id}";}
		$where_str=join(' AND ',$where);
		
		$query_str="SELECT id FROM ci_rbac_access WHERE {$where_str} LIMIT 1";
		$result_str=$this->dbr->query($query_str);
		$row_data=$result_str->row_array();
		if(!is_array($row_data) || count($row_data)<1){
			$data=1;
		}else{
			$data=($row_data['id']>0)?0:1;
		}		
		echo $data;
	}
	
	//进行权限分组值有效性的判断
	function check_ace_group_num_ajax(){
		$ace_group=$this->input->get('ace_group');
		if($ace_group==''){echo 0;exit;}
		$query_str="SELECT id,peak FROM ci_rbac_access_group WHERE group_ename='{$ace_group}' LIMIT 1";
		$result_str=$this->dbr->query($query_str);
		$row_data=$result_str->row_array();
		if(!is_array($row_data) || count($row_data)<1){
			$data=0;
		}else{
			$data=($row_data['peak']<60)?1:0;
		}		
		echo $data;
	}
	
	//显示权限分组列表，同时有检索功能
	function rbac_access_group_list(){
		
		$page=$this->input->get('page');
		$page = max(intval($page),1);		
		$dosearch=$this->input->get('dosearch');
				
		if($dosearch=='ok'){
			$lock=intval($this->input->get('lock'));
			$search_arr['lock']=$lock;
			$show=intval($this->input->get('show'));
			if($show==1 and $lock!=0){
				switch ($lock){
					case 1:
						$lock_val=0;
						break;
					case 2:
						$lock_val=1;
					break;
				}
				$where_array[]="`lock`={$lock_val}";
			}
			
			$search_filed_arr=array(1=>'group_name',2=>'group_ename');			
			$search_field_id=intval($this->input->get('search_field_id'));
			$search_arr['search_field_id']=$search_field_id;
			$keywords=$this->input->get('keywords');
			$search_arr['keywords']=$keywords;
			$search_field=$search_filed_arr[$search_field_id];
			
			if($search_field!='' and $keywords!=''){
				$where_array[]="{$search_field} like '%{$keywords}%'";
			}
			
			if(is_array($where_array) and count($where_array)>0){
				$where=' WHERE '.join(' AND ',$where_array);
			}
			
		}
		
		$pagesize=20;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";
		
		$query_access_num="SELECT id,listorder,group_name,group_ename,`lock` FROM ci_rbac_access_group $where";
		$result_access_num=$this->dbr->query($query_access_num);
		$access_num=$result_access_num->num_rows();
		$pages=pages($access_num,$page,$pagesize);
		
		$query_access_group="SELECT id,listorder,group_name,group_ename,`lock` FROM ci_rbac_access_group $where $limit";
		$resule_access_group=$this->dbr->query($query_access_group);
		$list_access_group=$resule_access_group->result_array();
		
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_data',$list_access_group);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('admin/rbac_access_group_list.html');
		
	}
	
	
	//添加权限时候选择权限分组，有检索功能
	function rbac_access_group_select(){
		
		$page=$this->input->get('page');
		$page = max(intval($page),1);		
		$dosearch=$this->input->get('dosearch');
		$where_array[]="`lock` !=1";
				
		if($dosearch=='ok'){
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
		
		if(is_array($where_array) and count($where_array)>0){
			$where=' WHERE '.join(' AND ',$where_array);
		}
		$pagesize=50;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";
		
		$query_access_num="SELECT group_name,group_ename FROM ci_rbac_access_group $where";
		$result_access_num=$this->dbr->query($query_access_num);
		$access_num=$result_access_num->num_rows();
		$pages=pages($access_num,$page,$pagesize);
		
		$query_access_group="SELECT group_name,group_ename FROM ci_rbac_access_group $where $limit";
		$resule_access_group=$this->dbr->query($query_access_group);
		$list_access_group=$resule_access_group->result_array();
		
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_data',$list_access_group);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('admin/rbac_access_group_list_select.html');
		
	}
	
	//添加菜单时候选择对应权限，有检索功能
	function rbac_access_select(){
		
		$page=$this->input->get('page');
		$type=$this->input->get('type');
		$type=trim($type)==''?'add':$type;
		$page = max(intval($page),1);
		$dosearch=$this->input->get('dosearch');
					
		if($dosearch=='ok'){
			$search_filed_arr=array(1=>'ace_name',2=>'ace_group');		
			$search_field_id=intval($this->input->get('search_field_id'));
			$search_arr['search_field_id']=$search_field_id;
			$keywords=trim($this->input->get('keywords'));
			$search_arr['keywords']=$keywords;
			$search_field=$search_filed_arr[$search_field_id];
			
			if($search_field!='' and $keywords!=''){
				$where_array[]="{$search_field} like '%{$keywords}%'";		
			}
			
			if(trim($this->input->get('model_val'))!=''){
				$search_arr['model_val']=$this->input->get('model_val');
				$where_array[]="ace_model='{$this->input->get('model_val')}'";
			}
			
			if(trim($this->input->get('control_val'))!=''){
				$search_arr['control_val']=$this->input->get('control_val');
				$where_array[]="ace_control='{$this->input->get('control_val')}'";
			}
			
			if(trim($this->input->get('action_val'))!=''){
				$search_arr['action_val']=$this->input->get('action_val');
				$where_array[]="ace_action='{$this->input->get('action_val')}'";
			}
						
			if(is_array($where_array) and count($where_array)>0){
				$where=' WHERE '.join(' AND ',$where_array);
			}
		}
		
		$pagesize=50;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";
		
		$query_access_num="SELECT id,ace_name FROM ci_rbac_access $where";
		$result_access_num=$this->dbr->query($query_access_num);
		$access_num=$result_access_num->num_rows();
		$pages=pages($access_num,$page,$pagesize);
		$query_access_data="SELECT id,ace_name FROM ci_rbac_access $where $limit";
		//echo $query_access_data;
		$resule_access_data=$this->dbr->query($query_access_data);
		$list_access_data=$resule_access_data->result_array();
		
		$this->smarty->assign('type',$type);
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_data',$list_access_data);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('admin/rbac_access_list_select.html');
		
	}
	
	//分组排序
	public function rbac_access_group_order(){
		if(intval($_POST['dosubmit']==1)) {
			$listorders=$this->input->post('listorders');
			if(is_array($listorders) and count($listorders)>0){
				$edit_query="UPDATE ci_rbac_access_group SET listorder=? WHERE id=?";
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
	
	//添加权限分组
	function rbac_access_group_add(){
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_access_group_add.html');
	}
	
	//执行添加权限分组
	function rbac_access_group_add_do(){
		
		$info=$this->input->post('info');
		$data=array(
			'listorder'=>$info['listorder'],
			'group_name'=>$info['name'],
			'group_ename'=>$info['ename']
		);
		$query_insert=$this->db->insert_string('ci_rbac_access_group',$data);
		$this->db->query($query_insert); 
		if($this->db->insert_id()){
			show_tips('添加成功','','','add');
		}else{
			show_tips('操作异常，请重新提交！');
		}
		
	}
	
	//修改权限分组
	function rbac_access_group_edit(){
		$id=intval($this->input->get('group_id'));
		$query_info="SELECT id,listorder,group_name,group_ename FROM ci_rbac_access_group WHERE id={$id}";
		$result_info=$this->dbr->query($query_info);
		$info=$result_info->row_array();
		if(!is_array($info) || count($info)<1){show_tips('参数异常!');}
		$this->smarty->assign('info',$info);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_access_group_edit.html');
	}
	
	//执行修改权限
	function rbac_access_group_edit_do(){
		$id=intval($this->input->post('group_id'));
		$info=$this->input->post('info');
		$data=array(
			'listorder'=>$info['listorder'],
			'group_name'=>$info['name']
		);
		$where="id={$id}";
		$query_update=$this->db->update_string('ci_rbac_access_group',$data,$where);
		if($this->db->query($query_update)){
			show_tips('修改成功','','','edit');
		}else{
			show_tips('修改异常，请检查后重新提交');;
		};
			
	}
	
	//删除权限分组
	function rbac_access_group_del(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="DELETE FROM ci_rbac_access_group WHERE id in('{$ids_str}') AND `lock`!=1 AND `num`=0";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//单条删除权限分组记录
	function rbac_access_group_del_one_ajax(){
		$aid=intval($this->input->get('aid'));
		if($aid>0){
			$del_query="DELETE FROM ci_rbac_access_group WHERE  id={$aid} AND `lock`!=1 AND `num`=0";
			$this->db->query($del_query);
			echo 1;
		}else{
			echo 0;
		}
	}
	
	//检测字段重复
	function check_filed_have_ajax(){
		$true_table_arr=array(
			'A'=>'ci_rbac_access_group',
			'B'=>'ci_rbac_access',
			'C'=>'ci_rbac_role',
			'D'=>'ci_rbac_user',
			'E'=>'ci_guide_url'
		);
		$table_name=$this->input->get('tb');
		$field_name=$this->input->get('field');		
		$id=$this->input->get('id');
		if($true_table_arr[$table_name]==''){echo 0;exit;}else{$true_table_name=$true_table_arr[$table_name];}
		
		//计算查询条件
		$field_val=$this->input->get($field_name);
		$where[]="`{$field_name}`='{$field_val}'";
		$field_extend=$this->input->get('field_extend');
		if($field_extend!=''){
			$field_extend_arr=explode('|',$field_extend);
			if(is_array($field_extend_arr) and count($field_extend_arr)>0){
				foreach ($field_extend_arr as $field_row){
					unset($field_val);
					$field_val=$this->input->get($field_row);
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