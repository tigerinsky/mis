<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 用户私信模型
 */
class Secretary_msg_model extends CI_Model {

    public function __construct(){
        parent::__construct();
        $this->dbr = $this->load->database('dbr',TRUE);
    }
    
    /**
     * 查询出筛选条件下的数据
     * @param str $where 查询条件
     * @param str $limit 条数筛选 
     * @return int $data 分会符合条件二维数组
     */
    public function get_data_by_parm($where,$order,$limit){
        $query_data="SELECT `mid`, `from_uid`, `content`, `ctime`, `to_uid` FROM ci_message {$where} {$order} {$limit}";
        $result_data=$this->dbr->query($query_data);
        $list_data=$result_data->result_array();
        return $list_data;
    }
    
    /**
     * 计算出筛选条件下的数据的条数
     * @param int $where 查询条件
     * @return int $data 返回数据的条数
     */
    public function get_count_by_parm($where){
        $query_data="SELECT count(mid) as nums FROM ci_message {$where}";
        $result_data=$this->dbr->query($query_data);
        $row_data=$result_data->row_array();
        return $row_data['nums'];
    }

    /**
     * 查询出指定的编号数据
     * @param int $id 数据ID编号
     * @return int $data 返回数据内容
     */
    public function get_info_by_id($id){
        $query_data="SELECT `mid`, `from_uid`, `content`, `ctime`, `to_uid` FROM ci_message WHERE mid=?";
        $result_data=$this->dbr->query($query_data,array($id));
        $row_data=$result_data->row_array();
        if($row_data['mid']>0){
            $result=$row_data;
        }else{
            $result='';
        }
        return $result;
    }

    /**
     * 产生一条新的对话消息
     * $info array 消息基本信息
     */
    public function insert_msg($info){
        $errno=0;
        //短消息数据
        //$info['uid']=0;//小秘书会员账号
        //$info['content']='测试消息';
        //$info['to_uid']=1;//用户UID
        if($info['content']!='' || $info['uid']>0){
            $post_url='http://123.57.249.33/message/newmsg';
            $post_data=array_key_tostr($info);
            $result_msg=curl_post_contents($post_url,$post_data);
            $result_data=json_decode($result_msg,TRUE);
            if($result_data['errno']==0){
                $result_data['data']['content']=$info['content'];
                $result=$result_data;
                $this->load->model('tool/secretary_log_model','secretary_log_model');
                $seclog['uid']=$info['to_uid'];
                $seclog['uid_sec']=$info['uid'];
                $seclog['time_sec']=time();
                $this->secretary_log_model->set_seclog($seclog);
            }else{
                $result['errno']=20701;//私信发送失败
            }
        }else{
            $result['errno']=10011;//必要参数错误
        }
        
        return $result;
    }
}