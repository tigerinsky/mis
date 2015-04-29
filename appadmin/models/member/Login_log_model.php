<?php

class Login_log_model extends CI_Model {
    
    private $dbr;
    public function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database("dbr", TRUE);
    }

    public function get() {
    
    
    }

}
