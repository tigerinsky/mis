<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
class checkcode extends CI_Controller{
	
	/**
	 * create code and save session
	 */
		
	function index(){
		$this->load->library('captcha');
		$this->captcha->doimage();
		$this->session->set_userdata('codenum',$this->captcha->get_code());
	}
	
}
// END Checkcode Class