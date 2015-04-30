<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * 蓝鲸app要闻数据表
 * curl post/get等使用demo参照D:\htdocs\ljapp\appadmin\controllers/test.php
 */
class Tweet_model extends CI_Model {
    
    private $dbr = null;
    function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database('dbr',TRUE,FALSE);
        $this->load->library('http2');
        $this->load->config('mis_tweet',TRUE);
        $this->mis_tweet=$this->config->item('mis_tweet');
    }
    
    /**
     * 查询出筛选条件下的数据
     * @param str $where 查询条件
     * @param str $limit 条数筛选 
     * @return int $data 分会符合条件二维数组
     */
    public function get_data_by_parm($where,$limit){
        $query_data="SELECT `tid`, `online_tid`, `uid`, `uname`, `title`, `content`, `img`, `industry`, `ctime`, `read_num`, `forward_num`, `dianzan_num`, `is_essence`, `is_del` FROM ci_tweet_offline {$where} ORDER BY ctime DESC,tid DESC {$limit}";
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
        $query_data="SELECT count(tid) as nums FROM ci_tweet_offline {$where}";
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
        $query_data="SELECT `tid`, `online_tid`, `uid`, `uname`, `title`, `content`, `img`, `industry`, `ctime`, `read_num`, `forward_num`, `dianzan_num`, `is_essence`, `is_del` FROM ci_tweet_offline WHERE tid=?";
        $result_data=$this->dbr->query($query_data,array($id));
        $row_data=$result_data->row_array();
        if($row_data['tid']>0){
            $result=$row_data;
        }else{
            $result='';
        }
        return $result;
    }
    
    /**
     * 对要闻进行单条推荐或取消推荐
     * @param int $id 需要推荐的id
     * @return bool 是否执行成功 
     */
    public function one_sug($id, $op) {
		$info['type'] = 'recommend';
		$info['tid']  = $id;
		$info['op']   = $op;
		$param_str = http_build_query($info);
		$url = $this->mis_tweet['tweet'] . '?' . $param_str;
		$ret = $this->http2->get($url);
        $data = json_decode($ret, true);
        if ($data['errno'] == 0) {
            return true;
        }
        return false;
    }

    /**
     * 批量推荐
     * @param str $ids_str 需要推荐的id集合
     * @return bool 是否执行成功 
     */
    public function tweet_sug($ids_str){
        //is_essence
        return $this->one_sug($ids_str);
    }
    
    /**
     * 批量取消推荐
     * @param str $ids_str 需要变更的id集合
     * @return bool 是否执行成功 
     */
    public function tweet_clear_sug($ids_str){
        $ret = $this->http2->post($this->mis_tweet['tweet']['cancel_sug_url'], array('ds' => $ids_str));
        $data = json_decode($ret, true);
        if ($data['errno'] == 0) {
            return true;
        }

        return false;
    }

    /**
     * 执行单条删除或取消删除操作
     * @param str $ids_str 需要删除的id集合
     * @return bool 是否执行成功 
     */
    public function one_del($id, $op) {
		$info['type'] = 'delete';
		$info['tid']  = $id;
		$info['op']   = $op;	
		$param_str = http_build_query($info);
		$url = $this->mis_tweet['tweet'] . '?' . $param_str;
        $ret = $this->http2->get($url);
        $data = json_decode($ret, true);
        if ($data['errno'] == 0) {
            return true;
        }
        return false;
    }

    /**
     * 批量执行删除
     * @param str $ids_str 需要删除的id集合
     * @return bool 是否执行成功 
     */
    public function tweet_del($ids_str){
        return $this->one_del($ids_str);
    }
    
    /**
     * 批量取消删除
     * @param str $ids_str 需要删除的id集合
     * @return bool 是否执行成功 
     */
    public function tweet_clear_del($ids_str){
        $ret = $this->http2->post($this->mis_tweet['tweet']['cancel_del_url'], array('ds' => $ids_str));
        $data = json_decode($ret, true);
        if ($data['errno'] == 0) {
            return true;
        }

        return false;
    }
    
    
    /**
     * 向数据表中写入一行数据
     * @param arr $info 需要插入的数据
     * @param bool 是否成功执行
     */
    public function create_info($info){
        /*print_r($info);
        eixt;
        unset($post_data);
        $post_data[]='source='.$this->weibo_config['appid'];
        $post_data[]='connections='.$connections;
        $post_data_str=array_tourl($post_data);
        $posturl=$this->mis_tweet['tweet']['httpurl'].'mis_proxy/message_add';
        $back_data=curl_post_contents($posturl,$post_data_str);
        $back_data=json_decode($back_data,true);
        $result=curl_post_contents($put_url,$post_data);
        exit;*/

		$info['type'] = 'new';
        $ret = $this->http2->post($this->mis_tweet['tweet'], $info);
        $data = json_decode($ret, true);
        if ($data['errno'] == 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 对数据表中的单行数据进行修改
     * @param arr $info 需要修改的键值对
     * @param int $id 被修改的id编号
     * @return bool 是否执行成功 
     */
    public function update_info($info,$id){
        //print_r($info);
		$info['type'] = 'edit';
		$info['tid'] = $info['online_tid'];
        $ret = $this->http2->post($this->mis_tweet['tweet'], $info);
        $data = json_decode($ret, true);
		debug_show($data, 'ret_info');
        if ($data['errno'] == 0) {
            return true;
        }
        return false;
    }
}
/* End of file this file */
?>
