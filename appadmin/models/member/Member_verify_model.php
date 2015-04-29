<?php
/**
 * @Copyright (c) 2015 Rd.Lanjinger.com. All Rights Reserved.
 * @author			Gao zhen'an<gaozhenan@lanjinger.com>
 * @version			$Id: Member_verify_model.php 439 2015-03-27 09:41:40Z gaozhenan@lanjinger.com $
 * @desc			Member_verify_model
 */
class Member_verify_model extends CI_Model {
	
	public function __construct() {
		//$this->dbr = $this->load->database("dbr", TRUE);
		$this->dbw = $this->load->database("dbw", TRUE);
		$this->table_name = 'ci_user_verify';
		$this->table_name_short = 'user_verify';
		$this->load->config('mis_tweet',TRUE);
		$this->mis_api = $this->config->item('mis_tweet');
	}

	public function update_verify_status($idsarr, $status) {
		$this->db->where_in('id', $idsarr);
		$t = $this->db->update($this->table_name_short, array('verify_sure' => $status));
		return $t;
	}

	/**
	 *  获取小秘书id
	 */
	public static function get_secretary() {
		return 2;
	}
	public function verify_notify($id, $status) {
		$secretary_id = self::get_secretary();	
		$this->load->library('http2');//newmsg
		if ($status == 1) {
			$r = $this->http2->post($this->mis_api['notify_valid_user'], array(
				'mis_id' => $secretary_id,
				'uid' => $id,
			));
		} else {
			$r = $this->http2->post($this->mis_api['newmsg'], array(
				'uid' => $secretary_id,
				'to_uid' => $id,
				'content' => '认证失败：您提供的资料有误，请重新提交。',
				'flag' => 1,
			));
		}
		debug_show($r ,'notify_ret_info');
		if (isset($r['errno']) && $r['errno'] == 0) {
			return TRUE;
		}
	}

}
