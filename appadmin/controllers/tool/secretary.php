<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 小秘书管理
 * @author Faxhaidong
 * @version 20150313
 */
class secretary extends CI_Controller{
        
    function __construct(){
        parent::__construct();
        $this->rbac->check_access();
        $this->admin_info=$this->rbac->get_admin();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->admin_uid=$this->admin_info['keyno'];
        $this->admin_uid_sec=2; 
    }
    
    //默认调用控制器
    public function index(){
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('tool/secretary_home.html');
    }
    
    /**
     * 小秘书私信历史
     * @return [type] [description]
     * 仅仅做小秘书uid的检测
     */
    public function history(){
        
        $this->load->model('tool/secretary_log_model','secretary_log_model');

        $is_new=$this->input->get('is_new');
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
        //$vtime=intval($this->input->get('vtime'));

        $where_array=array();

        if($is_new==1){
            $where_array[]="is_new=1";
            $search_arr['is_new']=1;
        }

        /*if($vtime>0){
           $where_array[]="time_sec<{$vtime}"; 
        }*/
        
        if($dosearch=='ok'){

            $keywords=trim($this->input->get('keywords'));
            if($keywords!=''){
                $search_arr['keywords']=$keywords;
                $where_array[]="uid = '{$keywords}'";        
            }
        }

        if(is_array($where_array) and count($where_array)>0){
            $where=' WHERE '.join(' AND ',$where_array);
        }

        $pagesize=8;
        $offset = $pagesize*($page-1);
        $order="ORDER BY time_sec DESC";
        $limit="LIMIT $offset,$pagesize";

        $result_num=$this->secretary_log_model->get_count_by_parm($where);
        $pages=pages($result_num,$page,$pagesize);
        $result_data=$this->secretary_log_model->get_data_by_parm($where,$order,$limit);
        if(is_array($result_data) && count($result_data)){
            $this->load->library('redis');
            $this->load->model('user/user_model','user_model');
            $last_time=0;
            foreach ($result_data as $key => $row) {
                //$last_time=$row['time_sec'];
                $user_info=$this->get_userinfo($row['uid']);
                $sec_info=$this->get_userinfo($row['uid_sec']);
                if($user_info['sname']!=''){
                    $row['name']=$user_info['sname'];
                    $row['avatar']=$user_info['avatar'];
                }else{
                    $row['name']='佚名用户';
                }
                if($sec_info['sname']){
                    $row['name_sec']=$sec_info['sname'];
                    $row['avatar_sec']=$user_info['avatar'];
                }else{
                    $row['name_sec']='小秘书';
                }
                $user_data[$row['time_sec']]=$row;
            }
        }

        $this->smarty->assign('search_arr',$search_arr);
        $this->smarty->assign('user_data',$user_data);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('tool/secretary_list.html');
        
    }

    /**
     * 用户消息展示
     * @return [type] [description]
     */
    public function user_message(){
        $uid=$this->input->get('uid');
        $uid_sec=$this->input->get('uid_sec');

        //$uid_sec=$this->admin_uid_sec;
        
        //获取相关用户头像信息
        $this->load->library('redis');
        $this->load->model('tool/secretary_msg_model','secretary_msg_model');
        $this->load->model('user/user_model','user_model');

        $user_info=$this->get_userinfo($uid);
        $sec_info=$this->get_userinfo($uid_sec);

        if($user_info['uid']>0){
            $user_list[$user_info['uid']]=$user_info;
        }

        if($sec_info['uid']>0){
            $user_list[$sec_info['uid']]=$sec_info;
        }else{
            $user_list[0]=array(
                'sname'=>'小秘书',
                'avatar'=>'http://app.lanjinger.com/avatar.png',
            );
        }

        $page=$this->input->get('page');
        $page = max(intval($page),1);

        $where_array=array();

        if($user_info['uid']>0 && $sec_info['uid']>0){
            $where_array[]="((from_uid={$user_info['uid']} AND to_uid={$sec_info['uid']}) OR (from_uid={$sec_info['uid']} AND to_uid={$user_info['uid']}))";
        }else if($user_info['uid']>0){
            $where_array[]="((from_uid={$user_info['uid']} AND to_uid=0) OR (from_uid=0 AND to_uid={$user_info['uid']}))";
        }

        if(is_array($where_array) and count($where_array)>0){
            $where=' WHERE '.join(' AND ',$where_array);
        }

        $pagesize=10;
        $offset = $pagesize*($page-1);
        $order="ORDER BY ctime DESC";
        $limit="LIMIT $offset,$pagesize";

        $message_num=$this->secretary_msg_model->get_count_by_parm($where);
        $pages=pages($message_num,$page,$pagesize);
        $result_data=$this->secretary_msg_model->get_data_by_parm($where,$order,$limit);

        $this->smarty->assign('user_list',$user_list);
        $this->smarty->assign('msg_data',$result_data);
        $this->smarty->assign('uid',$uid);
        $this->smarty->assign('uid_sec',$uid_sec);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('tool/secretary_msg_list.html');
    }

    /**
     * 显示消息具体消息的内容
     * @param int $id 消息id
     * @return  
     */
    public function user_message_one(){
        $id=$this->input->get('id');
        $this->load->model('tool/secretary_msg_model','secretary_msg_model');
        $msg_data=$this->secretary_msg_model->get_info_by_id($id);
        $this->smarty->assign('msg_data',$msg_data);
        $this->smarty->display('tool/secretary_msg_one.html');
    }

    //用户头像列表页面
    public function user_wait(){
        //清除相关redis键
        $this->load->library('redis');
        $redis_admin_show='admin_secretary_show_'.$this->admin_uid;//已经显示的集合
        $this->redis->del($redis_admin_show);
        $this->smarty->display('tool/secretary_user_wait.html');
    }

    //用户对话页面
    public function user_talk(){
        $uid=intval($this->input->get('uid'));
        //获取相关用户头像信息
        $this->smarty->assign('uid',$uid);
        $this->smarty->assign('uid_sec',$this->admin_uid_sec);
        $this->smarty->assign('sec_info',$sec_info);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('tool/secretary_user_talk.html');
    }


    /**
     * 获取用户咨询记录列表，并产生set集合
     * @param int $vtime 上次列表中最新记录的时间，初始化时候该值为空
     * @return 返回列表数组
     * $where_array[]="time_user<{$now_time}";//暂不查找历史数据，实现查找新数据即可
     * 最优方案为只查询上次查询之后的数据，但由于有提示消息的情况，所以新集合需要包含以后列表
     *
     */
    public function user_wait_ajax(){
        $this->load->model('tool/secretary_log_model','secretary_log_model');
        // $this->admin_uid;
        $vtime=intval($this->input->get('vtime'));

        $where_array[]='is_new=1';

        if($vtime>0){
           $where_array[]="time_user>{$vtime}"; 
        }
        $order="ORDER BY time_user ASC";

        if(is_array($where_array) and count($where_array)>0){
            $where=' WHERE '.join(' AND ',$where_array);
        }

        $limit="LIMIT 0,5";
        $result_data=$this->secretary_log_model->get_data_by_parm($where,$order,$limit);
        if(is_array($result_data) && count($result_data)>0){
            $result['errno']=0;
            $user_list=array();
            $this->load->library('redis');
            $this->load->model('user/user_model','user_model');
            //遍历获取用户头像和名称
            //判断redis中是否存在用户，无则进行降级查询
            $redis_admin_show='admin_secretary_show_'.$this->admin_uid;//已经显示的集合
            $redis_admin_list='admin_secretary_list_'.$this->admin_uid;//当前查询的集合

            //默认清空当前查询相关key
            $this->redis->del($redis_admin_data);
            $last_time=0;
            foreach ($result_data as $key => $row) {
                //if($last_time==0){
                //获取最大时间，以便后续查询
                $last_time=$row['time_user'];
                //}
                $user_one=$this->get_userinfo($row['uid']);
                if($user_one['avatar']==''){
                    $user_one['avatar']='http://123.56.89.16/statics/img/user_face.jpg';
                }
                $row=array_merge($row,$user_one);
                $user_data[$row['uid']]=$row;
                //写入当前记录uid
                $this->redis->sadd($redis_admin_list,$row['uid']);
            }

            //差集，用以追加新的数据
            $user_new=$this->redis->sdiff($redis_admin_list,$redis_admin_show);
            //交集，用以操作已显示用户
            $user_old=$this->redis->sunion($redis_admin_list,$redis_admin_show);
            //合并已显示和当前查询
            $this->redis->sunionstore($redis_admin_show,$redis_admin_list,$redis_admin_show);

            $result['data']=array(
                'last_time'=>$last_time,
                'total'=>count($user_data),
                'user_new'=>$user_new,
                'user_old'=>$user_old,
                'data'=>$user_data
            );
        }else{
            $result['errno']=20022;//未检索到数据
        }
        showjson($result);
    }

    /**
     * 显示小秘书历史沟通数据
     * @return json $result
     * 目前仅查找指定条数的记录用以显示
     */
    
    public function user_history_ajax(){
        $this->load->model('tool/secretary_log_model','secretary_log_model');
        // $this->admin_uid;
        $vtime=intval($this->input->get('vtime'));

        //$where_array[]='is_new=0';
        $where_array[]='time_sec!=0';

        if($vtime>0){
           $where_array[]="time_sec<{$vtime}"; 
        }
        $order="ORDER BY time_sec DESC";

        if(is_array($where_array) and count($where_array)>0){
            $where=' WHERE '.join(' AND ',$where_array);
        }

        $limit="LIMIT 0,5";
        $result_data=$this->secretary_log_model->get_data_by_parm($where,$order,$limit);
        if(is_array($result_data) && count($result_data)>0){
            $result['errno']=0;
            $user_data=array();
            $this->load->library('redis');
            $this->load->model('user/user_model','user_model');
            //遍历获取用户头像和名称
            $last_time=0;
            foreach ($result_data as $key => $row) {
                //if($last_time==0){
                //获取最小时间，以便后续查询
                $last_time=$row['time_sec'];
                //}
                $user_one=$this->get_userinfo($row['uid']);
                if($user_one['avatar']==''){
                    $user_one['avatar']='http://123.56.89.16/statics/img/user_face.jpg';
                }
                $row=array_merge($row,$user_one);
                $user_data[$row['uid']]=$row;
            }
            $result['data']=array(
                'last_time'=>$last_time,
                'total'=>count($user_data),
                'data'=>$user_data,
            );
        }else{
            $result['errno']=20022;//未检索到数据
        }
        showjson($result);
    }

    /**
     * 查询出当前用户与小秘书对话，目前暂定小秘书为唯一账号
     * 在需要的情况下，对每个管理员绑定一个小秘书
     * @param int $uid 用户uid
     * @param int $uid_sec 小秘书id，此参数与管理绑定
     * @param int $vtime 分割日期
     * @param int $type 查询类型，1新记录，0为历史记录
     * @return 返回用户记录信息
     * http://admin.app.lanjinger.com/admin.php/tool/secretary/user_talk_ajax?type=1&vtime=999992&num=8&uid=151&sec_uid=2
     */
    public function user_talk_ajax(){

        $uid=intval($this->input->get('uid'));
        //$uid_sec=intval($this->input->get('sec_uid'));
        $uid_sec=$this->admin_uid_sec;
        $vtime=intval($this->input->get('vtime'));
        $type=intval($this->input->get('type'));
        $num=intval($this->input->get('num'));
        $num=$num>10?10:($num<5?5:$num);
        
        //获取相关用户头像信息
        $this->load->library('redis');
        $this->load->model('tool/secretary_msg_model','secretary_msg_model');
        $this->load->model('user/user_model','user_model');

        $user_info=$this->get_userinfo($uid);
        $sec_info=$this->get_userinfo($uid_sec);

        //生成查询条件
        $where_array=array();
        $order="ORDER BY ctime DESC";

        if($user_info['uid']>0 && $sec_info['uid']>0){
            $where_array[]="((from_uid={$user_info['uid']} AND to_uid={$sec_info['uid']}) OR (from_uid={$sec_info['uid']} AND to_uid={$user_info['uid']}))";
        }else if($user_info['uid']>0){
            $where_array[]="((from_uid={$user_info['uid']} AND to_uid=0) OR (from_uid=0 AND to_uid={$user_info['uid']}))";
        }

        if($vtime>0){
           if($type==1){
                $where_array[]="ctime>{$vtime}";
                $order="ORDER BY ctime ASC";
           }else{
                $where_array[]="ctime<{$vtime}"; 
           }
        }

        if(is_array($where_array) and count($where_array)>0){
            $where=' WHERE '.join(' AND ',$where_array);
        }
        
        $limit="LIMIT 0,{$num}";
        $result_data=$this->secretary_msg_model->get_data_by_parm($where,$order,$limit);
        if(is_array($result_data) && count($result_data)>0){
            $result['errno']=0;
            if($vtime==0 && $type==1){krsort($result_data);}
            foreach ($result_data as $key => $row) {
                $result_list["'{$row['mid']}'"]=$row;
            }
            $result_data=$result_list;
            unset($result_list);
            $result['data']['list']=$result_data;

        }else{
            $result['errno']=20022;//未检索到数据
        }
        $result['uinfo']=$user_info;
        $result['sinfo']=$sec_info;
        showjson($result);
    }


    /**
     * 当小秘书手动将用户从列表中移除
     * @param int $uid 用户uid
     */
    public function user_wait_remove_ajax(){
        
        $result['errno']=0;
        $uid=intval($this->input->get('uid'));
        //从现实列表集中移除用户id
        if($uid>0){
           $this->load->library('redis');
           $this->redis->sRem('admin_secretary_show_'.$this->admin_uid,$uid);
        }
        showjson($result);
    }

    /**
     * 发送用户私信
     * @param string content 私信内容
     */
    public function send_msg(){
        $uid=$this->input->post('uid');
        $content=$this->input->post('content');
        $message['uid']=$this->admin_uid_sec;
        $message['to_uid']=$uid;
        $message['content']=$content;
        $this->load->model('tool/secretary_msg_model','secretary_msg_model');
        $result=$this->secretary_msg_model->insert_msg($message);

        //获取相关用户头像信息
        $this->load->library('redis');
        $this->load->model('user/user_model','user_model');
        $sec_info=$this->get_userinfo($this->admin_uid_sec);

        $result['sinfo']=$sec_info;
        showjson($result);
    }

    /**
     * 根据用户uid查询出用户信息
     * @param  [int] $uid 用户uid
     * @return arr $user_info 用户信息
     */
    private function get_userinfo($uid=0){
        $user_keys='user_'.$uid;
        $user_info=$this->redis->hmget($user_keys,array('uid','sname','avatar'));
        if(intval($user_info['uid'])==0){
            $user_info=$this->user_model->get_user_by_uid($uid,'id uid,sname,avatar');
        }
        return $user_info;
    }
}