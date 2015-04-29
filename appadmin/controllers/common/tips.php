<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 标签分类管理，对应处理热门目的地，名人分类等功能，类似栏目功能
 * @author Faxhaidong
 * @version 20140620
 */
class tips extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
		$this->dbr=$this->load->database('dbr',TRUE);
		$this->load->config('tips_category',TRUE);
		$this->conf_tips=$this->config->item('tips_category');
	}
	
	//显示标签分类列表
	function index(){
		$this->tip_list();
	}
	
	//显示标签列表，同时有检索功能
	private function tip_list(){
		$this->load->library('form');
		$page=$this->input->get('page');
		$page = max(intval($page),1);
		$dosearch=$this->input->get('dosearch');
				
		if($dosearch=='ok'){
						
			$keywords=trim($this->input->get('keywords'));
			$search_arr['keywords']=$keywords;
			
			if($keywords!=''){
				$where_array[]="tips_name like '%{$keywords}%'";		
			}
			
			if(intval($this->input->get('kind'))!=''){
				$search_arr['kind']=$this->input->get('kind');
				$where_array[]="kind='{$this->input->get('kind')}'";
			}
			
			if(intval($this->input->get('status'))!=''){
				$search_arr['status']=$this->input->get('status');
				$where_array[]="status='{$this->input->get('status')}'";
			}
			
			if(intval($this->input->get('top'))!=''){
				$search_arr['top']=$this->input->get('top');
				$where_array[]="top='{$this->input->get('top')}'";
			}
			
			if(intval($this->input->get('flag'))!=''){
				$search_arr['flag']=$this->input->get('flag');
				$where_array[]="flag='{$this->input->get('flag')}'";
			}
			
			if(is_array($where_array) and count($where_array)>0){
				$where=' WHERE '.join(' AND ',$where_array);
			}
			
		}
		
		
		$pagesize=10;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";
		
		$query_role_num="SELECT id FROM ci_app_tips_info $where";
		$result_role_num=$this->dbr->query($query_role_num);
		$role_num=$result_role_num->num_rows();
		$pages=pages($role_num,$page,$pagesize);
		
		$query_role_group="SELECT `id`,`listorder`,`kind`,`tips_name`,`bind_val`,`status`,`top`,`flag` FROM ci_app_tips_info $where $limit";
		$resule_role_group=$this->dbr->query($query_role_group);
		$list_role_group=$resule_role_group->result_array();
		$conf_tips=$this->conf_tips['tips'];
		$kind_sel=Form::select($conf_tips,$search_arr['kind'],'name="kind"','不做限定');
		$this->smarty->assign('kind_sel',$kind_sel);
		$this->smarty->assign('conf_tips',$conf_tips);
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('list_data',$list_role_group);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('common/tips_list.html');
	}
	
	//标签排序
	public function tips_order(){
		if(intval($_POST['dosubmit']==1)) {
			$listorders=$this->input->post('listorders');
			if(is_array($listorders) and count($listorders)>0){
				$edit_query="UPDATE ci_app_tips_info SET listorder=? WHERE id=?";
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

	//对标签进行单条审核属性变更
	function tips_status_one(){
		if(intval($_GET['id'])>0) {
			$id=$this->input->get('id');
			$del_query="UPDATE ci_app_tips_info SET `status`=(`status`+1)%2 WHERE id ={$id}";
			$this->db->query($del_query);
			show_tips('操作成功',HTTP_REFERER);
		} else {
			show_tips('操作异常');
		}
	}
	
	//对标签进行批量审核属性变更
	function tips_status(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="UPDATE ci_app_tips_info SET `status`=(`status`+1)%2 WHERE id in('{$ids_str}')";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//对标签进行单条置顶属性变更
	function tips_top_one(){
		if(intval($_GET['id'])>0) {
			$id=$this->input->get('id');
			$del_query="UPDATE ci_app_tips_info SET `top`=(`top`+1)%2 WHERE id ={$id}";
			$this->db->query($del_query);
			show_tips('操作成功',HTTP_REFERER);
		} else {
			show_tips('操作异常');
		}
	}
	
	//对标签进行批量置顶属性变更
	function tips_top(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="UPDATE ci_app_tips_info SET `top`=(`top`+1)%2 WHERE id in('{$ids_str}')";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//对标签进行单条推荐属性变更
	function tips_flag_one(){
		if(intval($_GET['id'])>0) {
			$id=$this->input->get('id');
			$del_query="UPDATE ci_app_tips_info SET `flag`=(`flag`+1)%2 WHERE id ={$id}";
			$this->db->query($del_query);
			show_tips('操作成功',HTTP_REFERER);
		} else {
			show_tips('操作异常');
		}
	}
	
	//对标签进行批量推荐属性变更
	function tips_flag(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){
				$ids_str=join("','",$ids);
				$del_query="UPDATE ci_app_tips_info SET `flag`=(`flag`+1)%2 WHERE id in('{$ids_str}')";
				$this->db->query($del_query);
				show_tips('操作成功',HTTP_REFERER);
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//批量删除标签
	function tips_del(){
		if(intval($_POST['dosubmit'])==1) {
			$ids=$this->input->post('ids');
			if(is_array($ids) and count($ids)>0){				
				$ids_str=join("','",$ids);
				$del_query="DELETE FROM ci_app_tips_info WHERE id in('{$ids_str}') and status=0";
				if($this->db->query($del_query)){
					show_tips('操作成功',HTTP_REFERER);
				}else{
					show_tips('操作异常',HTTP_REFERER);
				}	
			}else{
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	//单条删除标签
	function tips_del_one_ajax(){
		$album_id=intval($this->input->get('id'));
		$ret=0;
		if($album_id>0){
			$del_query="DELETE FROM ci_app_tips_info WHERE id={$album_id} and status=0";
			if($this->db->query($del_query)){
				$ret=1;
			}
		}
		echo $ret;
	}
	
	//添加标签
	function tips_add(){
		$this->load->library('form');
		$conf_tips=$this->conf_tips['tips'];
		$kind_sel=Form::select($conf_tips,$search_arr['kind'],'name="info[kind]" id="kind"');
		$this->smarty->assign('kind_sel',$kind_sel);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('common/tips_add.html');
	}
	
	//执行添加标签
	function tips_add_do(){
		$info=$this->input->post('info');
		//判断数据有效性
		if($info['tips_name']!='' && $info['kind']!=''){
			$insert_query=$this->db->insert_string('ci_app_tips_info',$info);
			$this->db->query($insert_query);
			show_tips('操作成功','','','add');
		}else{
			show_tips('数据不完整，请检测');
		}
	}
	
	//修改标签
	function tips_edit(){
		$this->load->library('form');
		$tips_id=$this->input->get('id');
		$tips_query="SELECT `id`,`listorder`,`kind`,`tips_name`,`bind_val`,`status`,`top`,`flag` FROM ci_app_tips_info WHERE id={$tips_id}";
		$result_tips=$this->dbr->query($tips_query);
		$info=$result_tips->row_array();
		$conf_tips=$this->conf_tips['tips'];
		$kind_sel=Form::select($conf_tips,$info['kind'],'name="info[kind]" id="kind"');
		$this->smarty->assign('kind_sel',$kind_sel);
		$this->smarty->assign('info',$info);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('common/tips_edit.html');
	}
	
	//执行修改标签
	function tips_edit_do(){
		$tips_id=$this->input->post('id');
		if($tips_id<1){show_tips('参数异常，请检测');}else{$where="id={$tips_id}";}
		$info=$this->input->post('info');
		$update_query=$this->db->update_string('ci_app_tips_info',$info,$where);
		if($this->db->query($update_query)){
			show_tips('操作成功','','','edit');
		}else{
			show_tips('操作异常，请检测');
		}
	}
	
	//检测字段重复
	function check_filed_have_ajax(){
		$this->load->library('check_filed');
		$true_table_arr=array(
			'A'=>'ci_app_tips_info',
		);
		$this->check_filed->check_filed_have_ajax($true_table_arr);
	}
	
}
