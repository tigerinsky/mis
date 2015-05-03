<?php
/**
 * 
 * @authors haidong
 * @date    2015-01-20 11:28:42
 */

class mcrypt_3des {
    
    const DEFAULT_KEY = "UXdcBCq8BwvXjd73z2d2CyNKm6AyQZ8fw42mEDSQ3abNMGff";
   
    private $td;
    private $iv;

    public function __construct(){
    
        /* 打开加密算法和模式 */
        $this->td = mcrypt_module_open('tripledes', '', 'ecb', ''); //打开算法和模式对应的模块
        #$td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
        //mcrypt_create_iv从随机源创建初始向量
        //mcrypt_enc_get_iv_size:返回打开的算法的初始向量大小
        $this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td), MCRYPT_DEV_RANDOM);
    }

    public function en3des($decrypted, Array $cfg){
        /* 加密数据 */
        $this->_set($cfg);
        $encrypted = base64_encode(mcrypt_generic($this->td, $decrypted));
        return $encrypted;
    }
    
    public function de3des($encrypted, Array $cfg){
        /* 解密数据 */
        $this->_set($cfg);
        $decrypted = rtrim(mdecrypt_generic($this->td, base64_decode($encrypted)), "\0");
        return $decrypted;
    }

    private function _set(Array $key_arr) {

        $key_str = implode('_', $key_arr);

        $key = $key_str != '' ? $key_str : self::DEFAULT_KEY;
        /* 创建密钥 */
        $key = substr(md5($key), 0, mcrypt_enc_get_key_size($this->td));
        /* 初始化加密 */
        if (mcrypt_generic_init($this->td, $key, $this->iv) == -1) {
            return false;
        }
    }

    public function __destruct(){
        /* 结束解密，执行清理工作，并且关闭模块 */
        mcrypt_generic_deinit($this->td);
        mcrypt_module_close($this->td);
    }

}

//$mcrypt_3des=new mcrypt_3des();
/*
$data='my name is haidong';
$endata=$mcrypt_3des->en3des($data);
$dedata=$mcrypt_3des->de3des($endata);
echo $dedata;*/
?>