<?php if (!defined('BASEPATH')) exit('No direct script access allowed');  
/**
 * 消息输出
 * @time Sat Oct 25 15:32:08 CST 2014
 */ 
class Message {

    private $CI;
    private $error_info = array();
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->config("error_code", TRUE);
        $this->error_info = $this->CI->config->item('error_code', 'error_code');
    }

    public function show_error($error_code, $msg = "", $param = "") {
        $data['ret'] = 0;
        $data['error_code'] = $error_code ; 
        $data['error'] = $msg; 
        if(is_int($msg) && isset($this->error_info[$error_code])) {                                                                                       
            $error_arr = $this->error_info[$error_code];
            if(is_array($param) && count($param)>0){
                $error_arr['en'] = vsprintf($error_arr['en'],$param);
                $error_arr['cn'] = vsprintf($error_arr['cn'],$param);
            }
            switch($msg) {
                case 2:
                    $data['error'] = $error_arr['en'];
                    break;
                case 3:
                    $data['error'] = $error_arr['cn'];
                    break;
                default:
            }
        }
        $this->showjson($data);
    }

    public function show_success($data, $other_data = array()) {
        $result = array('ret' => 1);
        if (is_array($other_data) && !empty($other_data)) {
            foreach ($other_data as $k => $val) {
                if (!in_array($k, $result, TRUE)) {
                    $result[$k] = $val;
                }
            } 
        }
        $result['data'] = $data;
        $this->showjson($result);
    }
    
    public function showjson($data) {                                                                                                            
        $json = json_encode($data);
        $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
        if (preg_match("/^[a-zA-Z][a-zA-Z0-9_\.]+$/", $callback)) {
            if(isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') { //POST
                header("Content-Type: text/html");
                $refer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : array();
                if(!empty($refer) && (substr($refer['host'], -13, 13)=='lanjinger.com')){
                    $result = '<script>document.domain="lanjinger.com";';
                }
                $result .= "parent.{$callback}({$json});</script>";
                echo $result;
            } else {
                if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE|Trident/', $_SERVER['HTTP_USER_AGENT'])) {
                    header('Content-Type: text/javascript; charset=UTF-8');
                } else {
                    header('Content-Type: application/javascript; charset=UTF-8');
                }
                echo "{$callback}({$json});";
            }
        } elseif ($callback) {
            header('Content-Type: text/html; charset=UTF-8');
            echo 'callback参数包含非法字符！';
        } else {
            if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/MSIE|Trident/', $_SERVER['HTTP_USER_AGENT'])) {
                header('Content-Type: text/plain; charset=UTF-8');
            } else {
                header('Content-Type: application/json; charset=UTF-8');
            }
            echo $json;
        }
        exit;
    }
}
