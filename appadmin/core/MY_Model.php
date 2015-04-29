<?php
/**
 * @Copyright (c) 2015 Rd.Lanjinger.com. All Rights Reserved.
 * @author			Gao zhen'an<gaozhenan@lanjinger.com>
 * @version			$Id: MY_Model.php 211 2015-03-21 10:21:19Z gaozhenan@lanjinger.com $
 * @desc 
 */
class MY_Model extends CI_Model {

	public function  __call($name, $arguments) {
		//$this->_init();
		$this->load->library('redis');

		$r = call_user_func_array(array($this->redis, $name), $arguments);
		return $r;
	}

}

