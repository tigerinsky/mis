<?php
/**
 * @Copyright (c) 2015 Rd.Lanjinger.com. All Rights Reserved.
 * @author			Gao zhen'an<gaozhenan@lanjinger.com>
 * @version			$Id: Member_model.php 441 2015-03-27 09:54:24Z gaozhenan@lanjinger.com $
 * @desc            会员信息model
 */

class Member_model extends MY_Model {
	private $dbr;
	private $dbw;
	public function __construct() {
		parent::__construct();
		$this->dbr = $this->load->database("dbr", TRUE, TRUE);
		$this->dbw = $this->load->database('dbw',TRUE, TRUE);
		$this->table_name = 'ci_user';
		$this->table_name_short = 'user';
		$this->table2_name = 'ci_user_more';
		$this->table2_name_short = 'user_more';
		$this->load->model("phone/phonebook_model", "_phonebook");
	}

	private function get_user_key_pre() {
		
		return 'user_';
	}

	private function get_user_brief_key_pre() {
		return 'user_brief_';
	}

	public function update($info) {
		$this->dbw->where('id', $info['id']);
		$this->dbw->update($this->table_name_short, $info);	
		$this->redis_update($info);	
	}

	public function multi_update($info,  $field = 'id') {
		$this->dbw->update_batch($this->table_name_short, $info, $field);
		$this->multi_redis_update($info);
	}

	/**
	 * 单条通讯录上传
	 *
	 * @param array $r  array('id' => 1, 'ukind_verify' => 1)
	 */ 
	public function upload_phonebook($r) {
		$info = array();
		$userinfo = $this->get_member_info($r['id']);
		$info['name'] = $userinfo['sname'];
		$info['identity'] = $userinfo['ukind'];
		$info['industry'] = $userinfo['industry'];
		$info['company']  = $userinfo['company'];
		$info['job']    = $userinfo['company_job'];
		$info['mobile'] = $userinfo['uname'];
		$info['email']  = $userinfo['uemail'];
		$info['eq_uid'] = $r['id']; //用户uid也带进通讯录
		$info['remark'] = "来自用户表";
		$info['status'] = 1;
		$ret = $this->_phonebook->update_phone($info);
		debug_show($ret, 'insert_info');
		return $ret;
	}
	public function multi_upload_phonebook($data) {
		foreach ($data as $r) { //更新蓝鲸通讯录	
			$this->upload_phonebook($r);
		}
	}
	
	private function redis_update($info) {
		$pre = $this->get_user_key_pre();
		$key = $pre . $info['id'];
		$this->hSet($key, 'ukind_verify', $info['ukind_verify']);
	}

	private function multi_redis_update($info) {
		$key_pre = $this->get_user_key_pre();
		foreach ($info as $v) {
			$key = $key_pre . $v['id'];
			$this->hSet($key, 'ukind_verify', $v['ukind_verify']);
		}
	}

	public function get_member_info($uid) {
		$this->dbr->select('uname, sname, avatar, uemail, umobile, ukind')->where('id', $uid);
		$q = $this->dbr->get($this->table_name_short);	
		$userinfo = $q->row_array();
		$userinfo_more = $this->get_member_more_info($uid);
		return array_merge($userinfo, $userinfo_more);
	}

	public function get_member_more_info($uid) {
		$this->dbr->select('industry, city, company, company_job')->where('uid', $uid);
		$q = $this->dbr->get($this->table2_name_short);  
		$userinfo_more = $q->row_array();
		return $userinfo_more;
	}
	
}
