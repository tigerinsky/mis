<?php
/**
 * cron脚本
 * @author gaozhenan@lanjinger.com
 */
class cron extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library("cli");
		$this->load->library("http2");
	}

	public function push() {
		$this->load->config('mis_tweet',TRUE);
        $this->mis_tweet = $this->config->item('mis_tweet');
        $this->load->model("mis/mismsg_model", "_mismsg");
        while(true) {
			//sleep(1);
			$this->_push();
        }
	}

	private function _push() {
        $current_time = time();
		$read_time = date("Y-m-d H:i:s");
        $r = $this->_mismsg->get_data_by_parm(" WHERE `removed`=0 AND `status`=1 AND `pushed`=0 AND `time_push` <= {$current_time} ");
        if (!is_array($r) || count($r) <=0) {
        	echo $read_time . " - 暂无消息推送\n";
        	exit;
        } else {
	        foreach ($r as $v) {
		        $ret = $this->http2->post($this->mis_tweet['message_push'], array(
		        	'push_task_id' => $v['id'],
		        	'tid' => $v['rel_id'],
		        	'title' => $v['title'],
		        	'content' => $v['content'],
		        	'industry' => $v['industry'],
		        	'type' => $v['type'],
		        	'url' => $v['wap_url'],
		        	'send_time' => $v['time_push'],
		        ));
		        $data = json_decode($ret, true);
		        if ($data['errno'] == 0) {
					$this->_mismsg->update_info(array('pushed'=>1), $v['id']);
		            echo $v['time_push'] . " - 消息推送成功\n";
		        }
	        }
        }
	}

	public function __destruct() {
		//echo "this is destruct function .";
	}

}
