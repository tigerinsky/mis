<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class MY_Controller extends CI_Controller {
    protected $table_name = "";
    protected $user_info = "";
    protected $request_array = array();
    protected $result_array = array();
    
    public function __construct() {
        parent::__construct();
	
	date_default_timezone_set('Asia/Shanghai');
        $_POST += $_GET;
        $this->request_array = $_POST;

        $this->response_array = array(
            'errno' => 0,
            'data' => array(),    
        );

        //$this->load->library("log");
        $this->rbac_config = $this->config->item('config_rbac');
        //$this->ip = ip();
        //$this->user_info = $this->session->userdata($this->rbac_config['rbac_admin_auth_key']);
    }

    /**
     *
     * @param   int	
     * @param   array	
     * @return	string
     */
    protected function renderJson($intStatus, $arrData = array()) {
        header("Content-Type:application/json;charset=utf-8");
        if(0 != $intStatus) {
            //log_message('error', 'ip['.$_SERVER[ 'REMOTE_ADDR' ] .'] req['.$_SERVER[ 'REQUEST_URI' ].'] errno['.$intStatus.']');
        }
        $result = array(
            'errno' => $intStatus,
        );
        /*
        if(empty($arrData)){
            $result['data'] = (object)$arrData;
        }else {
            $result['data'] = $arrData;
        }
         */
        if(!empty($arrData)) {
            $result['data'] = $arrData;
        }
        if (empty($this->request_array['callback'])) {
            echo json_encode($result);
        } else {
            echo '/**/'. $this->request_array['callback'] . ' && ' .
            $this->request_array['callback'] . '(' .
            json_encode($result) . ');';
        }
        return;
    }
    
    //批量审核属性变更
    function status(){
        $this->_check_table_name();
        if(intval($_POST['dosubmit']) == 1 ) {
            $ids    = $this->input->post('ids');
            $status = $this->input->get('status');
            if(is_array($ids) and count($ids) > 0) {
                $ids_str = join("','",$ids);
                $query   = "UPDATE {$this->table_name}  SET `status`= {$status} WHERE id in('{$ids_str}')";                         
                $rs = $this->db->query($query);
                //$this->log->write($this->user_info['user_name'], $this->ip, $ids_str, $this->table_name . ":status", $rs);
                show_tips('操作成功',HTTP_REFERER);
            } else {
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }

    //批量审核属性变更
    function locked(){
        $this->_check_table_name();
        if(intval($_POST['dosubmit']) == 1 ) {
            $ids    = $this->input->post('ids');
            $locked = $this->input->get('locked');
            if(is_array($ids) and count($ids) > 0) {
                $ids_str = join("','",$ids);
                $query   = "UPDATE {$this->table_name}  SET `locked`= {$locked} WHERE id in('{$ids_str}')";                         
                $rs = $this->db->query($query);
                //$this->log->write($this->user_info['user_name'], $this->ip, $ids_str, $this->table_name . ":status", $rs);
                show_tips('操作成功',HTTP_REFERER);
            } else {
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }

	public function __call($name, $argument) {
		$this->load->library('redis');
		$r = call_user_func_array(array($this->redis, $name), $argument);
		return $r;
	}
    //检测字段重复
    function check_filed_have_ajax(){
        $this->_check_table_name();
        $this->load->library('check_filed');
        $true_table_arr=array(
            'A' => $this->table_name,
        );
        $this->check_filed->check_filed_have_ajax($true_table_arr);
    }
    protected function _check_table_name() {
        if ($this->table_name == '') {
            show_tips('请指定表名');
        }
    }    
}
