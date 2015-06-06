<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class rbac_menu extends CI_Controller{
	
	private $menu_access='';
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
		$this->dbr=$this->load->database('dbr', TRUE);
	}
	
	//默认显示菜单列表
	function index(){
		$this->rbac_menu_list();
	}
	
	//显示菜单列表，同时有检索功能
	private function rbac_menu_list(){
		
		
		$show_by=$this->input->get('showby');
		$limit=$show_by=='min'?'LIMIT 100':'';
		
		if($show_by=='search'){
						
			$search_filed_arr=array(1=>'cname',2=>'ename');			
			$search_field_id=intval($this->input->get('search_field_id'));
			$search_arr['search_field_id']=$search_field_id;
			$keywords=trim($this->input->get('keywords'));
			$search_arr['keywords']=$keywords;
			$search_field=$search_filed_arr[$search_field_id];
			
			if($search_field!='' and $keywords!=''){
				$where_array[]="{$search_field} like '%{$keywords}%'";		
			}
			
			if(is_array($where_array) and count($where_array)>0){
				$where=' WHERE '.join(' AND ',$where_array);
			}
			
		}
		
		$this->load->library('tree');
		$parentid=$this->input->get('parentid');
		$menu_query="SELECT id,listorder,parentid,cname FROM ci_rbac_menu $where $limit";
		$menu_result=$this->dbr->query($menu_query);
		$menu_data=$menu_result->result_array();
		
		$menu_array=array();
		if(is_array($menu_data) && count($menu_data)>0){
			foreach ($menu_data as $r){
				$parentid_node=($r['parentid'])? ' class="child-of-node-'.$r['parentid'].'"' : '';
				$r['parentid_node']=$parentid_node;
				$r['str_manage'] = '<a href="javascript:add(\''.$r['id'].'\',\''.$r['cname'].'\')">添加子菜单</a> | <a href="javascript:edit(\''.$r['id'].'\',\''.$r['cname'].'\')">修改</a> | <span aid="'.$r['id'].'" style="cursor:pointer;" class="del_menu">删除</span> ';
				$menu_array[] = $r;
			}
		}
		
		$str="<tr id='node-\$id' \$parentid_node>
					<td align='center' width='80'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input-text-c'></td>
					<td align='center' width='100'>\$id</td>
					<td >\$spacer\$cname</td>
					<td align='center'>\$str_manage</td>
			 </tr>";
				
		$this->tree->init($menu_array);
		$list_menu = $this->tree->get_tree(0,$str);
		
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_menu',$list_menu);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('admin/rbac_menu_list.html');
	}
	
	//权限排序
	public function rbac_menu_order(){
		if(intval($_POST['dosubmit']==1)) {
			$listorders=$this->input->post('listorders');
			if(is_array($listorders) and count($listorders)>0){
				$edit_query="UPDATE ci_rbac_menu SET listorder=? WHERE id=?";
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
	
	
	//批量删除菜单,不做批量删除功能
	/*
	function rbac_menu_del(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="DELETE FROM ci_rbac_menu WHERE id in('{$ids_str}')";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	*/
	
	//单条删除菜单
	function rbac_menu_del_one_ajax(){
		$aid=intval($this->input->get('aid'));
		if($aid>0){
			$del_query="DELETE FROM ci_rbac_menu WHERE id={$aid}";
			$this->db->query($del_query);		
			echo 1;
		}else{
			echo 0;
		}
	}
	
	//添加菜单
	function rbac_menu_add(){
		$this->load->library('tree');
		$parentid=$this->input->get('parentid');
		$menu_query="SELECT id,parentid,cname FROM ci_rbac_menu WHERE 1=1";
		$menu_result=$this->dbr->query($menu_query);
		$menu_data=$menu_result->result_array();
		
		$menu_array=array();
		if(is_array($menu_data) && count($menu_data)>0){
			foreach ($menu_data as $r){
				$r['selected'] = ($r['id'] == $parentid) ? 'selected' : '';
				$menu_array[] = $r;
			}
		}
				
		$str  = "<option value='\$id' \$selected>\$spacer \$cname</option>";
		$this->tree->init($menu_array);
		$select_menu = $this->tree->get_tree(0,$str);
		
		$this->smarty->assign('select_menu',$select_menu);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_menu_add.html');
	}
	
	//执行添加菜单
	function rbac_menu_add_do(){
		
		$info=$this->input->post('info');
		$ace_id=intval($this->input->post('ace_id'));
		
		if($ace_id>0){
			$ace_info_query="SELECT id,ace_name,ace_model,ace_control,ace_action,ace_group,ace_val FROM ci_rbac_access WHERE id=?";
			$ace_info_result=$this->dbr->query($ace_info_query,array($ace_id));
			$ace_info_data=$ace_info_result->row_array();
			if($ace_info_data['id']<1){show_tips('权限对应值无效');}
			if(intval($info['parentid'])>0){
				$menu_info_query="SELECT id,level FROM ci_rbac_menu WHERE id=?";
				$menu_info_result=$this->dbr->query($menu_info_query,array($info['parentid']));
				$menu_info_data=$menu_info_result->row_array();
			}
			$info['real']=intval($info['real']);
			$info['display']=intval($info['display']);
			$info['target']=intval($info['target']);

			$levle_val=$menu_info_data['level']>0?$menu_info_data['level']:0;
			$info['ace_group']=$ace_info_data['ace_group'];
			$info['ace_val']=$ace_info_data['ace_val'];
			$info['model']=$ace_info_data['ace_model'];
			$info['control']=$ace_info_data['ace_control'];
			$info['action']=$ace_info_data['ace_action'];
			$info['level']=$levle_val+1;
			
			$insert_query=$this->db->insert_string('ci_rbac_menu',$info);
			if($this->db->query($insert_query)){
				show_tips('执行成功','','','add');
			}else{
				show_tips('数据录入异常');
			}
		}else{
			show_tips('请选择对应的权限');
		}
		
	}
	
	//修改菜单
	function rbac_menu_edit(){	
		$menu_id=$this->input->get('menu_id');
		$menu_query="SELECT id,parentid,listorder,cname,ename,exturl,target,display,`real` FROM ci_rbac_menu WHERE id={$menu_id}";
		$result_menu=$this->dbr->query($menu_query);
		$info=$result_menu->row_array();
		$this->load->library('tree');
		$parentid=$this->input->get('parentid');
		$menu_query="SELECT id,parentid,cname FROM ci_rbac_menu WHERE 1=1";
		$menu_result=$this->dbr->query($menu_query);
		$menu_data=$menu_result->result_array();
		
		$menu_array=array();
		if(is_array($menu_data) && count($menu_data)>0){
			foreach ($menu_data as $r){
				$r['selected'] = ($r['id'] == $info['parentid']) ? 'selected' : '';
				$menu_array[] = $r;
			}
		}
				
		$str  = "<option value='\$id' \$selected>\$spacer \$cname</option>";
		$this->tree->init($menu_array);
		$select_menu = $this->tree->get_tree(0,$str);
		
		$this->smarty->assign('select_menu',$select_menu);
		$this->smarty->assign('info',$info);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/rbac_menu_edit.html');
	}
	
	//执行修改菜单
	function rbac_menu_edit_do(){
		
		$info=$this->input->post('info');
		$ace_id=intval($this->input->post('ace_id'));
		$menu_id=intval($this->input->post('menu_id'));
		
		if($menu_id>0){
			$info['real']=intval($info['real']);
			$info['display']=intval($info['display']);
			$info['target']=intval($info['target']);
			$where="id=$menu_id";			
			if($ace_id>0){
				$ace_info_query="SELECT id,ace_name,ace_model,ace_control,ace_action,ace_group,ace_val FROM ci_rbac_access WHERE id=?";
				$ace_info_result=$this->dbr->query($ace_info_query,array($ace_id));
				$ace_info_data=$ace_info_result->row_array();
				if($ace_info_data['id']<1){show_tips('权限对应值无效');}
				
				$info['ace_group']=$ace_info_data['ace_group'];
				$info['ace_val']=$ace_info_data['ace_val'];
				$info['model']=$ace_info_data['ace_model'];
				$info['control']=$ace_info_data['ace_control'];
				$info['action']=$ace_info_data['ace_action'];
			}
			
			if(intval($info['parentid'])>0){
				$menu_info_query="SELECT id,level FROM ci_rbac_menu WHERE id=?";
				$menu_info_result=$this->dbr->query($menu_info_query,array($info['parentid']));
				$menu_info_data=$menu_info_result->row_array();
				$levle_val=$menu_info_data['level']>0?$menu_info_data['level']:0;
				$info['level']=$levle_val+1;
			}
			
			$update_query=$this->db->update_string('ci_rbac_menu',$info,$where);
			if($this->db->query($update_query)){
				show_tips('执行成功','','','edit');
			}else{
				show_tips('数据变更异常');
			}
		}else{
			show_tips('参数异常');
		}
	
	}
	
	
	/**
	* 菜单查询
	*
	* 根据传入的ID返回其下边所属的分类菜单
	*
	* @param	id 菜单ID
	* @param   level 查询深度
	* @param	type 返回类型(0,1,2,3)
	* @return	data 返回Html内容 
	* example /?id=3&type=3&level=4&stop=1
	*/
	
	function get_menu_by_id_ajax(){
		$id=intval($this->input->get('id'));
		$level=intval($this->input->get('level'));
		$type=intval($this->input->get('type'));
		$stop=intval($this->input->get('stop'));
		$def=intval($this->input->get('def'));
		$menu_arr=$this->get_menu_by_id($id,$level);
		$result['ret']=count($menu_arr)>0?1:0;
		$menu_str='';
		switch ($type){
			case 1://系统导航
				foreach($menu_arr as $val){
					$menu_str.='<li style="margin:0"><a href="javascript:site_select('.$val['id'].',\''.$val['cname'].'\')" menuid="'.$val['id'].'">'.$val['cname'].'</a></li>';
				}
				$result['data']=$menu_str;
				break;
			case 2://栏目导航
				$menu_str.='<li id="_M0" class="top_menu"><a href="javascript:_M(0,\'控制面板\',1)"  hidefocus="true" style="outline:none;">控制面板</a></li>';
				foreach($menu_arr as $val){
					$menu_str.='<li id="_M'.$val['id'].'" class="top_menu"><a href="javascript:_M('.$val['id'].',\''.$val['cname'].'\')"  hidefocus="true" style="outline:none;">'.$val['cname'].'</a></li>';
				}
				$result['data']=$menu_str;
				break;
			case 3://左侧导航
				if($def==1){
					$menu_str.='<h3 class="f14"><span title="展开与收缩" class="switchs cu on"></span>个人中心</h3>';
					$menu_str.='<ul>';
					$menu_str.='<li class="sub_menu" id="_MP0"><a style="outline:none;" hidefocus="true" href="javascript:_MP(0,\''.site_url('admin/rbac_user/public_edit_myword').'\',0);">修改密码</a></li>';
					$menu_str.='</ul>';
				}else{
					foreach($menu_arr as $val){
						$menu_str.='<h3 class="f14"><span title="展开与收缩" class="switchs cu on"></span>'.$val['cname'].'</h3>';
						if(is_array($val['child']) and count($val['child'])>0){
							$menu_str.='<ul>';
							foreach ($val['child'] as $row_menu){
								$row_url=site_url($row_menu['model'].'/'.$row_menu['control'].'/'.$row_menu['action']).'/'.$row_menu['exturl'];
								$menu_str.='<li class="sub_menu" id="_MP'.$row_menu['id'].'"><a style="outline:none;" hidefocus="true" href="javascript:_MP('.$row_menu['id'].',\''.$row_url.'\','.$row_menu['target'].');">'.$row_menu['cname'].'</a></li>';
							}
							$menu_str.='</ul>';
						}
					}

// 					$menu_str.='<h3 class="f14"><span title="展开与收缩" class="switchs cu on"></span>系统推送</h3>';
// 					$menu_str.='<ul>';
// 					$menu_str.='<li class="sub_menu" id="_MP0"><a style="outline:none;" hidefocus="true" href="javascript:_MP(20,\''.site_url('push/push/index/').'\',0);">系统推送</a></li>';
// 					$menu_str.='</ul>';
					 					$menu_str.='<h3 class="f14"><span title="展开与收缩" class="switchs cu on"></span>批量上传</h3>';
					 					$menu_str.='<ul>';
					 					$menu_str.='<li class="sub_menu" id="_MP0"><a style="outline:none;" hidefocus="true" href="javascript:_MP(20,\''.site_url('uploadbatch/uploadbatch/index/').'\',0);">批量上传</a></li>';
					 					$menu_str.='</ul>';
				}
				if($stop==1){
					echo $menu_str;
					exit;
				}else{
					$result['data']=$menu_str;
				}
				break;
			default://以json格式输出
				$result['data']=$menu_arr;
		}
		showjson($result);
	}
	
	/**
	* 遍历菜单查询
	*
	* 根据传入的ID返回其下边所属的分类菜单
	*
	* @param	id 菜单ID
	* @param   level 查询深度,查询到某个菜单深度即终止遍历，如放弃此参数，则只返回一层的数值
	* @return	data 返回Html内容 
	*/
	function get_menu_by_id($id,$level){
		$menu_query="SELECT id,cname,ace_group,ace_val,level,model,control,action,exturl,target,`real` FROM ci_rbac_menu WHERE display=1 AND parentid=? ORDER BY listorder DESC";
		$menu_result=$this->dbr->query($menu_query,array($id));
		$menu_data=$menu_result->result_array();
		foreach ($menu_data as $key=>$menu_row){
			//进行权限菜单显示权限的判定
			$have_ace=$this->check_menu_access($menu_row['ace_group'],$menu_row['ace_val']);
			if(!$have_ace){unset($menu_data[$key]);continue;}
			$menu_data[$key]=$menu_row;
			if($level>$menu_row['level']){
				$menu_data[$key]['child']=$this->get_menu_by_id($menu_row['id'],$level);
			}
		}
		return $menu_data;
	}
	
	/**
	* l菜单权限检测
	* @param	ace_group 权限分组
	* @param   ace_val 权限值
	* @return	通过返回true,否则返回false
	*/
	private function check_menu_access($group,$val){
		
		if($this->rbac->is_admin()){return true;}
		
		if(!is_array($this->menu_access)){
			$this->rbac_config=$this->config->item('config_rbac');
			$userinfo=$this->session->userdata($this->rbac_config['rbac_admin_auth_key']);
			$user_role=intval($userinfo['role_id']);
			$role_ace_query="SELECT user_access FROM ci_rbac_role WHERE id=?";
			$role_ace_result=$this->db->query($role_ace_query,array($user_role));
			$role_ace_info=$role_ace_result->row_array();
			if($role_ace_info['user_access']!=''){
				$role_ace_info=unserialize($role_ace_info['user_access']);
			}
			$this->menu_access=$role_ace_info;
		}
				
		$acc_group=intval($this->menu_access[$group]);
		$acc_val=intval($val);
		if(($acc_group&$acc_val)>0){
			return true;
		}else{
			return false;
		}
	}
	
}
