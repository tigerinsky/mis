<?php

class Phonebook_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database('dbr',TRUE);
        $this->dbw = $this->load->database('dbw',TRUE);

        $this->table_name = "ci_app_phone_book";
        $this->table_name_short = "app_phone_book";
    }

    public function get_phonebook_list($industry = 0, $type = 0, $page = 1, $offset = 10, $fields = array('id', 'name', 'type', 'mobile', 'industry', 'work', 'job', 'company', 'total_view', 'total_comment', 'status', 'uid', 'time_create')) {
        $where_arr = array();
        if ($industry > 0) {
            $where_arr[] = " industry = " . $industry;
        }

        if ($type > 0) {
            $where_arr[] = " type = " . $type;
        }

        if ( count($where_arr)) {

            $where = " WHERE " . implode(" AND ", $where_arr);
        }

        $limit = max(intval($page), 1) - 1;
        $offset = max(intval($offset), 10);
        
        $sql = "SELECT " . implode(',', $fields) . " FROM " . $this->table_name . $where . " LIMIT " . $limit . "," . $offset;
        $query = $this->dbr->query($sql);
        $result = $query->result_array();
        return $result;
    }

    /* *
     * $contact_type string -mobile\phone\email, default:mobile
     * return true;
     */
    public function update_contact($id, $contact, $contact_type = "mobile") {

        $this->dbw->where('id', $id); 
        $data = array($contact_type => $contact);
        return $this->dbw->update($this->table_name, $data); 
    }

	/**
	 * 通过反馈信息，更新通讯录号码
	 */
    public function update_contact_multi(Array $feedback_ids) {
        $this->load->model("phone/feedback_model", "_feedback");
        $r = $this->_feedback->get_contact_by_ids($feedback_ids);
        if (is_array($r) && count($r)) {
			$data = array();
			$updatedt = time();
            foreach($r as $v) {
                $time = $this->get_time_create_by_id($v['phone_id']);//phoneid 已在
				if ($time === FALSE) continue;
                $row['mobile'] = phone_encode($v['contact'], array('time_create'=>$time));
                $row['id'] = $v['phone_id'];
                $row['time_edit'] = $updatedt;
				$data[] = $row;
            }
			if (!empty($data)) {
				$this->dbw->update_batch($this->table_name_short, $data, 'id');
				return $this->dbw->affected_rows();
			}
        }
    }
	
	/**
	 * 根据通讯录id获取指定通讯录信息
	 */ 
    public function get_phonebook_by_id($id, $fields = array('id', 'name', 'type', 'mobile', 'industry', 'work', 'job', 'company', 'total_view', 'total_comment', 'status', 'uid', 'time_create')) {
        $sql = "SELECT " . implode(',', $fields) . " FROM " . $this->table_name .  " WHERE id = {$id}";
        $query = $this->dbr->query($sql);
        $result = $query->row_array();
        return $result;
    }

    public function get_time_create_by_id($id) {
        $this->dbr->where('id', $id);
        $q = $this->dbr->select("time_create,id")->get($this->table_name_short);
		if ($q->row()->id) {
			return $q->row()->time_create;
		} else {
			return false;
		}
    }
	/**
     * 添加通讯录
     * @param array $info
     * @return bool
     */
    public function insert_phone(Array $info) {
		$info['time_create'] = $info['time_edit'] = time();
		$info['uniq'] = phone_md5($info['mobile']);
		$info['mobile'] = phone_encode($info['mobile'], array('time_create' => $info['time_create']));
		$info = array_filter($info, 'strlen');
		$q = $this->dbr->select('id')->get_where($this->table_name_short, array('uniq' =>$info['uniq']));
		if ($q->row()->id) {
			return false;                                                                                                            
		}
        $sql = $this->dbw->insert_string($this->table_name, $info);
        if ($this->dbw->query($sql)) {
            return true;
        }
    }

	/**
	 * 认证时使用，通讯录没有改数据的情况下，插入该条记录，有的情况下，更新eq_uid
	 *
	 */
	public function update_phone(Array $info) {
		$info['uniq'] = phone_md5($info['mobile']);
		$q = $this->dbr->select('id')->get_where($this->table_name_short, array('uniq' =>$info['uniq']));
		$phone_id = $q->row()->id;
		if ($phone_id > 0 && !empty($info['eq_uid'])) {
			$this->dbw->where('id', $phone_id);
			$this->dbw->update($this->table_name_short, array('eq_uid' => $info['eq_uid']));
			if ($this->dbw->affected_rows()) {
				return true;
			}
		} else if(empty($phone_id)) {
			$info['time_create'] = $info['time_edit'] = time();
			$info['mobile'] = phone_encode($info['mobile'], array('time_create' => $info['time_create']));

			$info = array_filter($info, 'strlen');
			$sql = $this->dbw->insert_string($this->table_name, $info);
			$this->dbw->query($sql);
			if ($this->dbw->insert_id()) {
				return true;
			}
		}
	}
}
