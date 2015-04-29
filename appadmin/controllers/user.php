<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Parser Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Parser
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/parser.html
 */
class user extends CI_Controller{

    private $rbac_config;
    public function __construct() {
        parent::__construct();
        $this->rbac_config=$this->config->item('config_rbac');
    }
    //默认控制器
    public function index(){
        $userinfo = $this->session->userdata($this->rbac_config['rbac_admin_auth_key']);
        $wb_hash = $this->session->userdata('wb_hash');
        if ($userinfo && $wb_hash) {
            redirect(site_url('/admin/main/?t=' .time().random(3).random(3)));
        } else {
            $this->login_out();
        } 
        //$this->smarty->view('index.html');
    }

    public function check_login(){
        $yzm=$this->input->post('code',TRUE);
        if($yzm!=$this->session->userdata('codenum')){show_tips('验证码输入错误');}
        $username=trim($this->input->post('username',TRUE));
        $password=trim($this->input->post('password',TRUE));
        if($username=='' || $password=''){show_tips('用户名和密码不能为空');}
        show_tips('用户名或密码错误');
        exit;
    }

    function login_out(){
        $this->smarty->view('login.html');
    }

    public function chklogin_true(){
        $dbr=$this->load->database('dbr', TRUE);
        $yzm=$this->input->post('code',TRUE);
        //if($yzm!=$this->session->userdata('codenum')){show_tips('验证码输入错误');}
        $username=trim($this->input->post('username',TRUE));
        $password=encrypt(trim($this->input->post('password',TRUE)));
        if($username=='' || $password==''){show_tips('用户名和密码不能为空');}
        $login_str="SELECT id,uname,tname,pass_word,role_id,`lock`,`status` FROM ci_rbac_user WHERE uname='".$username."'";
        $query_user=$dbr->query($login_str);
        $login_user=$query_user->row_array();
        if(!is_array($login_user) || count($login_user)<1){show_tips('用户名或密码错误');}			
        if($login_user['status']==0){show_tips('该用户被管理员锁定');}
            //if($login_user['lock']==1){show_tips('该用户处于临时锁定状态');}
            if($password==$login_user['pass_word']){
                $user_login=array(
                    'keyno'=>$login_user['id'],
                    'user_name'=>$login_user['tname'],
                    'user_local'=>encrypt($login_user['uname'].$login_user['pass_word']),
                    'role_id'=>$login_user['role_id'],
                    'ip'=>ip(),
                    'in_times'=>time()
                );
                $hash=array(
                    'time'=>time(),
                    'val'=>random(6,'abcdefghigklmnopqrstuvwxwyABCDEFGHIGKLMNOPQRSTUVWXWY0123456789')
                );
                $this->session->set_userdata($this->rbac_config['rbac_admin_auth_key'],$user_login);
                $this->session->set_userdata('wb_hash',$hash);
                show_tips('登录成功',site_url('/admin/main/'));
            }else{
                $this->session->sess_destroy();
                show_tips('用户名或密码错误', $url_forward = 'goback');
            }
    }

    function user_out(){
        $this->session->sess_destroy();
        show_tips('注销成功',site_url('/user/login_out/'));
    }


}
