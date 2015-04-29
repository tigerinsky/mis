<?php

class MY_Log extends CI_Log {

    protected static $admin_log_file = "log";

    public function __construct() {
        parent::__construct();
    }
    public function write_admin_log($msg) {
    
        if ($this->_enabled === FALSE)
        {
            return FALSE;
        }

        $filepath = $this->_log_path . self::$admin_log_file;

        if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
        {
            return FALSE;                                                                                                            
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $msg);
        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($filepath, FILE_WRITE_MODE);    
        return TRUE;
    }

     /**
     * 日志写入，默认写入文件
     * 各自项目可以创建数据表继承重写本函数
     * @param $user     操作者
     * @param $ip       操作IP
     * @param $pk       操作数据主键
     * @param $action   操作动作
     * @param $status   操作结果状态 0|1
     * @param $desc     描述
     * @return void
     */
    public function write($user, $ip, $pk, $action, $status, $desc = '') {
        $time = date("Y-m-d H:i:s");
        $str = $time . "\t" . $user . "\t" . $ip . "\t" . $pk . "\t" . $action . "\t" . $status . "\t" . $desc . "\n";
        $this->write_admin_log($str);
    }
}
