<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Rbac{
		
	private $CI;
	private $rbac_config;
	private $rbac_section_num;
	private $dbr;
	public $log_infos;
	function __construct(){
		$this->CI =& get_instance();
		$this->CI->config->load('config_rbac',TRUE);
		$this->rbac_config=$this->CI->config->item('config_rbac');
		$this->dbr=$this->CI->load->database('dbr', TRUE);
	}
	
	//检测前操作是否须要认证
	function check_access($rbac_section_num=0){
		$this->_check_login();
		//进行hash检测
		if($this->rbac_config['rbac_check_hash']=='on'){
			$this->check_hash();
		}
		//根据设定继续进行具体权限判断
		if($this->rbac_config['rbac_user_auth_on']=='on'){
			//取得获取段数配置方式
			$this->rbac_section_num=($rbac_section_num>0)?$rbac_section_num:$this->rbac_config['rbac_section_num'];
			//取得模块，控制器，方法名
			$model_info=$this->_get_mvc_name();
			//检测是否为超级管理员
			if($this->is_admin()){return true;}
			//检测是否为公共方法,以public开始的不需要认证
			if(strpos('A'.$model_info['action'],'public')>0){return true;}
			//获取当前对应的方法需要的权限
			$action_access=$this->get_action_access($model_info);
			if($action_access['status']=='none'){
				return true;
			}else{
				$this->check_action_access($action_access);
			}	
		}
	}
	
	//检测hash键值
	function check_hash(){
		$hash=$this->CI->session->userdata('wb_hash');
		$hash_get=$this->CI->input->get('wb_hash');
		if($hash_get==$hash['val']){return true;}
		$hash_post=$this->CI->input->post('wb_hash');
		if($hash_post==$hash['val']){return true;}
		show_tips('Hash值校对失败！!');
	}
	
	//查询出用户拥有当前模块的权限值
	function check_action_access($action_access){
		if($this->rbac_config['rbac_user_auth_type']==1){
			$access_group=$this->get_group_access_group($action_access);
		}else{
			$access_all=$this->CI->session->userdata('ace_all');
			$access_all=0;
			if(!is_array($access_all) || $access_all==''){
				$access_all=$this->get_group_access_all();
				$this->CI->session->set_userdata('ace_all',$access_all);
			}
			$access_group=intval($access_all['data'][$action_access['data']['ace_group']]);
			$access_group=($access_group>0)?$access_group:0;
		}
		
		$this->contrast_action_access($access_group,$action_access['data']['ace_val']);
		//把$access_group 和 $action_access 进行权限对比，以判定是否拥有权限
	}
	
	//对拥有权限和需要的权限值进行对比判断
	private function contrast_action_access($acc_group,$acc_val){
		$acc_group=intval($acc_group);
		$acc_val=intval($acc_val);
		if(($acc_group&$acc_val)>0){
			return true;
		}else{
			show_tips('此用户没有该模块的权限');
		}
	}
	
	//查询出用户分组的所有权限
	private function get_group_access_all(){
		$access['status']='none';
		$sql_all_access="SELECT user_access FROM ci_rbac_role WHERE id =? ";
		$query_all_access=$this->dbr->query($sql_all_access,array($this->log_infos['role_id']));
		$all_access=$query_all_access->row_array();
		if($all_access['user_access']!=''){
			$access['status']='have';
			$access['data']=unserialize($all_access['user_access']);
		}
		return $access;
	}
	
	//查询出用户分组特定权限分组的权限值
	private function get_group_access_group($action_access){
		$access['status']='none';
		$sql_group_access="SELECT ace_sumval FROM ci_rbac_role_access WHERE role_group =? and ace_group=?";
		$query_group_access=$this->dbr->query($sql_group_access,array($this->log_infos['role_id'],$action_access['data']['ace_group']));
		$group_access=$query_group_access->row_array();
		if(intval($group_access['ace_sumval'])>0){
			$access=intval($group_access['ace_sumval']);
		}else{
			$access=0;
		}
		return $access;
	}
	
	/**
	 *查询指定模块需要权限 
	 */
	private function get_action_access($model_info){
		$action_arr['status']='none';
		$sql_action_access="SELECT group_id,ace_group,ace_val FROM ci_rbac_access WHERE ace_model=? and ace_control=? and ace_action=?";
		$query_action_access=$this->dbr->query($sql_action_access,array($model_info['model'],$model_info['controll'],$model_info['action']));
		$action_access=$query_action_access->row_array();
		if(is_array($action_access) and count($action_access)>0){
			$action_arr['status']='have';
			$action_arr['data']=$action_access;
		}
		return $action_arr;
	}
	
	/**
	 * 检测是否为超级管理员
	 **/
	function is_admin(){
		$max_admin=false;
		$admin_arr=$this->rbac_config['rbac_admin_user_id'];
		$user_session=$this->log_infos;
		if(in_array($user_session['keyno'],$admin_arr)){
			$max_admin=true;
		}
		return $max_admin;
	}

	/**
	 * 获取用户登录信息
	 */
	function get_admin(){
		$log_infos=$this->CI->session->userdata($this->rbac_config['rbac_admin_auth_key']);
		return $log_infos;
	}
	/**
	 * 获取mvc
	 **/
	private function _get_mvc_name(){
		unset($mvc_name);
		$controll_directory=trim($this->CI->router->fetch_directory(),'/');
		$mvc_name['model']    = ($controll_directory!='')?$controll_directory:'none';
		$mvc_name['controll'] = $this->CI->router->fetch_class();
		$mvc_name['action']   = $this->CI->router->fetch_method();
		/*
		//如果采用分段的方式，则需要对参数个数进行检测核实
		//另外在使用rsegment时候，是统计路由后的路径，会造成其结果有偏差
		if($this->rbac_section_num==3){
			$mvc_name['model']    = $this->CI->uri->rsegment(1,0);
			$mvc_name['controll'] = $this->CI->uri->rsegment(2,0);
			$mvc_name['action']   = $this->CI->uri->rsegment(3,'index');
		}else{
			$mvc_name['model'] = 0;
			$mvc_name['controll']=$this->CI->uri->segment(1,0);
			$mvc_name['action']=$this->CI->uri->segment(2,'index');
		}
		*/
		return $mvc_name;
	}
	
	//检测用户是否已经登录
	
	function _check_login(){
		//在使用swfobject时,session失效修复方案
		$session_key=$this->CI->input->get_post('session_key');
		if($session_key!=''){
			$userid=$this->CI->input->get_post('userid');
			$login_str="SELECT id,uname,tname,pass_word,role_id,`lock`,`status` FROM ci_rbac_user WHERE id='".$userid."'";
			$query_user=$this->dbr->query($login_str);
			$login_user=$query_user->row_array();
			$user_local=encrypt($login_user['uname'].$login_user['pass_word']);
			if($user_local==$session_key){
				$user_login=array(
						'keyno'=>$login_user['id'],
						'user_name'=>$login_user['tname'],
						'user_local'=>encrypt($login_user['uname'].$login_user['pass_word']),
						'role_id'=>$login_user['role_id'],
						'ip'=>ip(),
						'in_times'=>time()
						);
				$this->CI->session->set_userdata($this->rbac_config['rbac_admin_auth_key'],$user_login);
			}else{
				show_tips('权限异常，请重新登录!',site_url('/user/login_out/'));
			}
		}
		$keep_time=($this->rbac_config['rbac_keep_time']>0)?$this->rbac_config['rbac_keep_time']:1800;
		$this->log_infos=$log_infos=$this->CI->session->userdata($this->rbac_config['rbac_admin_auth_key']);
		$this->check_user_ip();
		$login_time=$log_infos['in_times'];
		$userid=$log_infos['keyno'];
		$user_local=$log_infos['user_local'];
		//传递需要全部使用的session变量
		$hash=$this->CI->session->userdata('wb_hash');
		$admin_session=array(
			'username'=>$log_infos['user_name'],
			'wb_hash'=>$hash['val'],
		);
		$this->CI->smarty->assign('admin_session',$admin_session);
		
		//检测并维持session状态
		if($login_time!=''){
			$new_time=time();
			if($new_time-$login_time>$keep_time){
				$this->CI->session->sess_destroy();
				show_tips('登录超时，请重新登录!',site_url('/user/login_out/'));
			}else{
				if($userid=='' or $user_local==''){show_tips('登录失效，请重新登录!',site_url('/user/login_out/'));}
				$log_infos['in_times']=$new_time;
				$this->CI->session->userdata($this->rbac_config['rbac_admin_auth_key'],$log_infos);
			}
		}
		
		
		//通过数据库密码进行检测
		$login_str="SELECT id,uname,tname,pass_word,role_id,`lock`,`status` FROM ci_rbac_user WHERE id='".$userid."'";
		$query_user=$this->dbr->query($login_str);
		$login_user=$query_user->row_array();
		if(!is_array($login_user) || count($login_user)<1){show_tips('数据校对失败，请重新登录',site_url('/user/login_out/'));}
		if($user_local==encrypt($login_user['uname'].$login_user['pass_word'])){
			$user_login=array(
					'keyno'=>$login_user['id'],
					'user_name'=>$login_user['tname'],
					'user_local'=>encrypt($login_user['uname'].$login_user['pass_word']),
					'role_id'=>$login_user['role_id'],
					'ip'=>ip(),
					'in_times'=>time()
					);
			$this->CI->session->set_userdata($this->rbac_config['rbac_admin_auth_key'],$user_login);
		}else{
			show_tips('登录信息检测异常，请重新登录',site_url('/user/login_out/'));
		}
		
	} 
	
	private function check_user_ip(){
		if($this->rbac_config['rbac_check_user_ip']=='on'){
			if(ip()!=$this->log_infos['ip']){
				show_tips('登录信息校对失败，请重新登录!',site_url('/user/login_out/'));
			}
	    }
	}
	
}