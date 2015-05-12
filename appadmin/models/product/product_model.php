<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * 二级图片分类管理model
 */
class Product_model extends CI_Model {
    
    private $dbr = null;
    private $table_name = 'ci_tweet';
    function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database('dbr',TRUE,FALSE);
        $this->load->library('http2');
    }
    
    /**
     * 查询出筛选条件下的数据
     * @param str $where 查询条件
     * @param str $limit 条数筛选 
     * @return int $data 分会符合条件二维数组
     */
    public function get_data_by_parm($where,$limit){
        $query_data = "SELECT `tid`, `uid`, `type`, `f_catalog`, `s_catalog`, `img`, `content`, `tags`, `ctime`, `is_del`, `dtime` FROM {$this->table_name} {$where} ORDER BY ctime DESC,tid DESC {$limit}";
        $result_data = $this->dbr->query($query_data);
        $list_data = $result_data->result_array();
        // 格式化数据
        $list_data = $this->format_product_data($list_data);
        return $list_data;
    }
    
    /**
     * 格式化函数
     * ＠param array $list_data
     * @return array $format_list_data
     */
    private function format_product_data($list_data) {
    	$format_list_data = array();
    	foreach ($list_data as $item) {
    		$tmp_item = array();
    		$tmp_item['tid'] = $item['tid'];
    		if ($item['uid'] == 0 || $item['uid'] == '0') {
    			$tmp_item['uid'] = "dashan";
    		} else {
    			$tmp_item['uid'] = $item['uid'];
    		}
    		if ($item['type'] == 1 || $item['type'] == '1') {
    			$tmp_item['type'] = "是";
    		} else {
    			$tmp_item['type'] = "否";
    		}
    		$tmp_item['f_catalog'] = $item['f_catalog'];
    		$tmp_item['s_catalog'] = $item['s_catalog'];
    		
    		$tmp_item['img_list'] = array();
    		if (isset($item['img'])) {
    			try {
	    			$json_data_array = json_decode($item['img'], true);
	    			if (!is_array($json_data_array)) {
	    				$json_data_array = array();
	    			}
	    			$index = 1;
	    			foreach($json_data_array as $img_data) {
	    				$img_url = isset($img_data['n']['url']) ? $img_data['n']['url'] : '';
	    				$tmp_item_mis = array(
				    					'img_index' => 'pc'.$index,
				    					'img_url'   => $img_url,
	    							);
	    				$tmp_item['img_list'][] = $tmp_item_mis;
	    				$index++;
	    			}
    			} catch (Exception $e) {
    				echo $e->getMessage(),"\n";
    			}
    		}
    		
    		$tmp_item['content'] = $item['content'];
    		$tmp_item['tags'] = $item['tags'];
    		$tmp_item['ctime'] = date("Y-m-d H:i:s", $item['ctime']);
    		
    		$format_list_data[] = $tmp_item;
    	}
    	return $format_list_data;
    }
    
    /**
     * 计算出筛选条件下的数据的条数
     * @param int $where 查询条件
     * @return int $data 返回数据的条数
     */
    public function get_count_by_parm($where){
        $query_data="SELECT count(tid) as nums FROM {$this->table_name} {$where}";
        $result_data=$this->dbr->query($query_data);
        $row_data=$result_data->row_array();
        return $row_data['nums'];
    }
    
    /**
     * 查询出指定的编号数据
     * @param int $id 数据ID编号
     * @return int $data 返回数据内容
     */
    public function get_info_by_tid($tid){
        $query_data = "SELECT `tid`, `uid`, `type`, `f_catalog`, `s_catalog`, `img`, `content`, `tags`, `ctime`, `is_del`, `dtime` FROM {$this->table_name} WHERE tid=?";
        $result_data = $this->dbr->query($query_data, array($tid));
        $row_data = $result_data->row_array();
        if($row_data['tid']>0){
            $result = $row_data;
        }else{
            $result = '';
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
     * 对指定编号数据进行删除
     * @param arr $ids 需要删除的数据的id，可以是单个id，也可以是id的数组
     * @return bool 返回执行结果
     */
    public function del_info($ids){
        if(is_array($ids) && count($ids)){
            $id_str = join("','",$ids);
        }else{
            $id_str = $ids;
        }
        $dtime = time();
        $del_rule="UPDATE ci_tweet SET is_del = 1, dtime = '{$dtime}' WHERE tid IN('{$id_str}')";
        if($this->db->query($del_rule)){
            return true;
        }else{
            return false;
        }
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
        $insert_query=$this->db->insert_string('ci_mis_imgmgr',$info);
        if($this->db->query($insert_query)){
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 对数据表中的单行数据进行修改
     * @param arr $info 需要修改的键值对
     * @param int $id 被修改的id编号
     * @return bool 是否执行成功 
     */
    public function update_info($info, $tid){
        $where="tid={$tid}";
        $update_rule=$this->db->update_string('ci_tweet', $info, $where); 
        if($this->db->query($update_rule)){
            return true;
        }else{
            return false;
        }
    }
}
/* End of file this file */
?>
