<?php

class upload extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('oss');

	}
	public function index() {
		$this->load->library('oss');
		echo "aaaaa";
	}

	public function add() {
		if($_POST['verify']) {
			//$response = $this->oss->list_bucket();
			/*echo "<pre>";
			var_dump($response);exit;
			echo "</pre>";
			 */
    		//$this->_format($response);
			//$url = $this->oss->upload($_FILES['pic'], array('dir'=>'user','tag'=>'uptt'));
			$url = $this->oss->upload('pic', array('dir'=>'user'));
			print_r($url);exit;
		}
		$this->smarty->display("add.html");
	}

	private function _format($response) {
		echo "<pre>";
		var_dump($response);exit;
		echo "</pre>";
		
        echo '|-----------------------Start----------------------------------------------------------------------------------------------
    -----'."\n";
        echo '|-Status:' . $response->status . "\n";
        echo '|-Body:' ."\n"; 
        echo $response->body . "\n";
        echo "|-Header:\n";
        print_r ( $response->header );
        echo '-----------------------End-------------------------------------------------------------------------------------------------
    ----'."\n\n";
    	exit;
    }
}
