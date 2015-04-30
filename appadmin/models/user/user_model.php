<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 */
class User_model extends CI_Model {
	
    private $dbr = null;
    function __construct() {
        parent::__construct();
        $this->config->load('user_config', TRUE);
        $this->user_config = $this->config->item('user_config');
        $this->dbr = $this->load->database('dbr',TRUE);
        $this->db  = $this->load->database('dbw',TRUE);
    }
    
    /**
     * 获取当前登录用户信息
     *
     * @return array $result;
     */
    public function get_user_access(){
        $session_key=$this->user_config['user_auth_key'];
        $session_data=$this->session->userdata($session_key);
        if($session_data['uid']>0){
            $ret=1;
            $result['data']=$session_data;
        }else{
            $ret=0;
            $result['error_code']=20102;
        }
        $result['ret']=$ret;
        return $result;
    }
    
    /**
     * 注册生成新的用户信息
     * $info array 用户基本信息
     * $more array 用户扩展信息
     */
    public function insert_user($info,$more){
        $this->db->insert('user',$info);
        $insert_id=$this->db->insert_id();
        if($insert_id>0){
            $more['uid']=$insert_id;
            $more_tab=($info['ukind']==1)?'user_more':'user_more_dsg';
            if($this->db->insert($more_tab,$more)){
                //根据注册类型决定是否初始化登录及显示信息
        	    if($info['ufrom']==1 && $this->user_config['user_auto_login']=='on'){
        	       //根据账号自动登录
        	       $user_info=$this->get_user_by_uid($insert_id);
        	       $this->user_auto_login_by_info($user_info);
        	    }else{
        	       $this->load->model('common/mail_model');
        	       $mail_set=$this->mail_model->set_mail($insert_id,$info['uemail'],'A');
        	       if($mail_set['id']>0){
            	       //给用户发送激活邮件
            	       $activation_url=base_url('user/mail_verify?keyno='.$mail_set['id'].'&keycode='.$mail_set['hash_key']);
            	       $mail_to=$info['uemail'];
            	       $mail_subject=$this->user_config['pc_activation_sub'];
            	       $find=array('{url}');
            	       $replace=array($activation_url);
            	       $mail_msg=str_replace($find,$replace,$this->user_config['pc_activation_msg']);
            	       $this->mail_model->send_mail($mail_to,$mail_subject,$mail_msg);
        	       }
        	    }
                return true;
            }else{
                return false;
            }
        }
        
    }
    
    /**
     * 修改用户信息
     */
    public function edit_user_info($data,$where){
        if($this->db->update('user',$data,$where)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 修改用户信息
     */
    public function edit_user_more($data,$where,$ukind=1){
        $tab_name=$ukind==1?'user_more':'user_more_dsg';
        if($this->db->update($tab_name,$data,$where)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 根据用户ID返回用户所有信息，包括会员的附属信息
     * @param int $id 用户uid
     * @param int $safe 是否启用安全规范,当等于的情况下，将不返回所有与安全有关选项
     */
    public function get_userinfo($id,$safe=0){
        $param_arr = array('id'=>$id);
        $this->db->where($param_arr);
        $result=$this->db->get('user');
        $list_data=$result->row_array();
        if($list_data['id']>0){
            $more_table=$list_data['ukind']==1?'user_more':'user_more_dsg';
            $this->db->where(array('uid'=>$list_data['id']));
            $result_more=$this->db->get($more_table);
            $list_more_data=$result_more->row_array();
            $list_data=array_merge($list_data,$list_more_data);
        }
        if($safe==1){
            unset($list_data['pass_word']);
            unset($list_data['pass_mark']);
        }
        return $list_data;
    }
    
    /**
     * 根据用户账号返回用户信息
     * @param arr
     */
    public function get_user_by_all($uname,$type){
        $user_info=$this->get_user_by_uname($uname);
        if($user_info['id']<1){
            if($type==1){
                $user_info=$this->get_user_by_mobile($uname);
            }else{
                $user_info=$this->get_user_by_email($uname);
            }
        }
	    return $user_info;
    }
    
    /**
     * 根据用户ID返回用户信息
     * @param arr
     */
    public function get_user_by_uid($id,$filed=''){
        $param_arr = array('id'=>$id);
        if($filed!=''){$this->db->select($filed);}
        $this->db->where($param_arr);
        $query=$this->db->get('user');
	    $result_data=$query->row_array();
	    return $result_data;
    }
    
    /**
     * 根据用户账号返回用户信息
     * @param arr
     */
    public function get_user_by_uname($uname){
        $param_arr = array('uname'=>$uname);
        $this->db->where($param_arr);
        $query=$this->db->get('user');
	    $result_data=$query->row_array();
	    return $result_data;
    }
    
    /**
     * 根据用户昵称返回用户信息
     * @param arr
     */
    public function get_user_by_sname($sname){
        $param_arr = array('sname'=>$sname);
        $this->db->where($param_arr);
        $query=$this->db->get('user');
	    $result_data=$query->row_array();
	    return $result_data;
    }
    
    /**
     * 根据用户手机号返回用户信息
     * @param arr
     */
    public function get_user_by_mobile($mobile){
        $param_arr = array('umobile'=>$mobile);
        $this->db->where($param_arr);
        $query=$this->db->get('user');
	    $result_data=$query->row_array();
	    return $result_data;
    }
    
    /**
     * 根据用户邮箱返回用户信息
     * @param arr
     */
    public function get_user_by_email($email){
        $param_arr = array('uemail'=>$email);
        $this->db->where($param_arr);
        $query=$this->db->get('user');
	    $result_data=$query->row_array();
	    return $result_data;
    }
    
    /**
     * 根据用户ID自动登录，目前仅仅用到会员注册后登录
     * 可用作凭手机验证码直接登录等情况
     * @param $userinfo 用户登录相关信息
     */
    public function user_auto_login_by_info($login_user){
        if($login_user['id']>0){
            $user_login=array(
    			'uid'=>$login_user['id'],
    			'roleid'=>$login_user['role_id'],
    			'ukind'=>$login_user['ukind'],
    			'username'=>$login_user['uname'],
    			'nikename'=>$login_user['sname'],
    			'user_local'=>encrypt($login_user['uname'].$login_user['pass_word']),
    			'login_times'=>time(),
    			'ip'=>ip()
    		);
    		$user_hash=array(
    			'time'=>time(),
    			'val'=>random_string('alnum',8)
    		);
    		$this->session->set_userdata($this->user_config['user_auth_key'],$user_login);
    		$this->session->set_userdata('user_hash',$user_hash);
        }
    }
    
    
    /**
     * 写入用户登录日志
     * @param arr
     */
    public function user_login_log($user_log,$login_user){
        $this->db->insert('user_login_log',$user_log);
        if($user_log['result']==1){//登录成功
            if($login_user['locked']==1){//有锁定则解除锁定
                //变更用户表
                $this->db->update('user',array('locked'=>0,'lock_num'=>0,'lock_time'=>0), array('id' => $user_log['uid']));
                //变更登录日志表有效性
                $this->db->update('user_login_log',array('valid'=>0),array('uid' =>$user_log['uid'],'login_yday'=>$user_log['login_yday'],'valid'=>1,'result'=>0));
            }
        }else{//登录失败
            $this->db->where(array('uid'=>$user_log['uid'],'login_yday'=>$user_log['login_yday'],'valid'=>1,'result'=>0));
            $this->db->from('user_login_log');
            $err_nums=$this->db->count_all_results();
            //统计当天错误次数，若大于指定值，并且锁定时间不等于当天,锁定次数+1
            if($err_nums>=$this->user_config['user_err_lock']){
                $this->db->set(array('locked'=>1,'lock_time'=>$user_log['login_yday']));
                $this->db->set('lock_num', '`lock_num`+1', FALSE);
                $this->db->where(array('id' => $user_log['uid']));
                $this->db->update('user');
            }
        }
        $insert_id=$this->db->insert_id();
	    return $result_data;
    }
    
    /**
     * 检测用户登录状态，并刷新维持hash生命周期
     *
     */
    public function get_login_userinfo(){
        $ret=1;
        $login_info=$this->session->userdata($this->user_config['user_auth_key']);
	    $user_hash=$this->session->userdata('user_hash');
	    $new_time=time();
	    
	    if($login_info['uid']==''){
	        $this->session->sess_destroy();
	        $result=array(
               'ret'=>0,
               'err'=>100001,
               'msg'=>'当前操作需要会员登录'
            );
	        return $result;
	    }
	    
	    //检测IP地址
	    if($this->user_config['user_check_ip']=='on'){
			if(ip()!=$login_info['ip']){
			    $this->session->sess_destroy();
			    $result=array(
                   'ret'=>0,
                   'err'=>100002,
                   'msg'=>'IP地址校对失效'
                );
    	        return $result;
			}
	    }
	    
	    //维持hash
	    $user_hash['time']=$new_time;
	    $this->session->set_userdata('user_hash',$user_hash);
        
	    //检测并维持session状态
	    $keep_time=($this->user_config['user_keep_time']>0)?$this->user_config['user_keep_time']:1800;
	    $login_time=$login_info['login_times'];
		if($login_time!=''){
			if($new_time-$login_time>$keep_time){
				$this->session->sess_destroy();
				$result=array(
                   'ret'=>0,
                   'err'=>100003,
                   'msg'=>'登录超时'
                );
    	        return $result;
			}else{
				$login_info['login_times']=$new_time;
				$this->session->userdata($this->rbac_config['user_auth_key'],$login_info);
			}
		}
		
		//通过数据库密码进行检测
		if($this->user_config['check_by_strict']=='on'){
		    $login_user=$this->get_user_by_uid($login_info['uid']);
		    if(!is_array($login_user) || count($login_user)<1){show_msg('账号不存在');}			
    	    if($login_user['status']==0){show_msg('该用户为邮箱用户，请登录激活');}
    		if($login_user['locked']==1){
    		    if($login_user['lock_num']>2){
    		        $this->session->sess_destroy();
    		        $result=array(
                       'ret'=>0,
                       'err'=>100004,
                       'msg'=>'该用户处于永久锁定状态'
                    );
        	        return $result;
    		    }else{
    		        if($now_time['year'].$now_time['yday']==$login_user['lock_time']){
    		           $this->session->sess_destroy();
    		           $result=array(
                           'ret'=>0,
                           'err'=>100005,
                           'msg'=>'该用户处于临时锁定状态'
                        );
        	           return $result;
    		        }
    		    }
    		}
    		
    		if(encrypt($login_user['uname'].$login_user['pass_word'])!=$login_info['user_local']){
    		    $this->session->sess_destroy();
	            $result=array(
	               'ret'=>0,
	               'err'=>100006,
	               'msg'=>'密码变更，需要重新登录'
	            );
	            return $result;
    		}
    		$result['ret']=$ret;
    		$result['data']=$login_info;
    		return $result;
		}
		
    }
    /**
     * 判断用户是否已经注册，若注册成功，则直接登录；未注册则要求用户填写确认注册
     * 需要在$oauch_data中增加一个session的注册状态，若未注册，则置为有效，在注册成功后注销，防止用户直接提交到注册页面
     *
     * @param array $oauch_data //通过第三方接口所获取的用户资料
     */
    public function oauth_reg_check($oauth_data){
        //检测用户账号是否存在
        $param_arr = array('oauth_key'=>$oauth_data['oid'],'oauth_name'=>$oauth_data['site']);
        $this->db->where($param_arr);
        $user_query=$this->db->get('user');
	    $user_data=$user_query->row_array();
	    $user_have=$user_data['id']>0?1:0;
	    if($user_have==0){
	        $nickname=$this->oauth_create_sname($oauth_data['ousername']);
	        $result['allow_reg']=$oauth_data['allow_reg']=1;
	        $result['ousername']=$oauth_data['ousername']=$nickname;
	        $this->session->set_userdata('oauth_info',$oauth_data);
	    }else{
	        $result['data']=$user_data;
	    }
	    $result['ret']=$user_have;
	    return $result;
    }
    
    /**
     * 用户注册信息写入，需要自动产生用户编号；用户密码并填入相关数据
     */
    public function oauth_reg_save($info,$more){
        $this->db->insert('user',$info);
        $insert_id=$this->db->insert_id();
        if($insert_id>0){
            $more['uid']=$insert_id;
            $more_tab=($info['ukind']==1)?'user_more':'user_more_dsg';
            if($this->db->insert($more_tab,$more)){
                //根据注册类型决定是否初始化登录及显示信息
        	    if($this->user_config['user_auto_login']=='on'){
        	       //根据账号自动登录
        	       $user_info=$this->get_user_by_uid($insert_id);
        	       $this->user_auto_login_by_info($user_info);
        	    }
                return true;
            }else{
                return false;
            }
        }
    }
    
    /**
     * 根据用户昵称产生一个新的昵称供用户使用
     * @param str $sname //用户第三方账号昵称
     * @return str $new_name //新的唯一用户昵称
     */
    public function oauth_create_sname($sname,$ext=''){
        $new_name=$sname.$ext;
        $param_arr = array('sname'=>$new_name);
        $this->db->where($param_arr);
        $query=$this->db->get('user');
	    $user_data=$query->row_array();
	    if($user_data['id']>0){
	        return $this->oauth_create_sname($sname,random(4));
	    }else{
	        return $new_name;
	    }
    }
    
    /**
     * 根据第三方类型产生一个新的用户名
     * @param str $username //用户第三方账号
     * @return str $new_name //新的唯一用户账号
     */
    public function oauth_create_uname($oauth_name){
        $new_name=$oauth_name.time().random(4);
        $param_arr = array('uname'=>$new_name);
        $this->db->where($param_arr);
        $query=$this->db->get('user');
	    $user_data=$query->row_array();
	    if($user_data['id']>0){
	        return $this->oauth_create_sname($oauth_name);
	    }else{
	        return $new_name;
	    }
    }
}
/* End of file this file */
?>
