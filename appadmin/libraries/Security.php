<?php
/**
 * php安全开发类库
 * 
 * @author  
 * @date 2015/03/08
 */

class CI_Security {

	//威胁url安全检查正则数组
    var $black_regx_arr = array('<', '>', 'document\.', '(.)?([a-zA-Z]+)?(Element)+(.*)?(\()+(.)*(\))+', '(<script)+[\s]?(.)*(>)+', 'src[\s]?(=)+(.)*(>)+', '[\s]+on[a-zA-Z]+[\s]?(=)+(.)*', 'new[\s]+XMLHttp[a-zA-Z]+', '\@import[\s]+(\")?(\')?(http\:\/\/)?(url)?(\()?(javascript:)?');

	//referer
    var $referer;
    private $_alow_referer='lanjinger.com';

	public function sec() {
        $this->__construct();
    }

    /**
     * 构造函数,暂不做处理
     */
    public function __construct() {}

    /**
     * referer 安全检查,$whites为空时,只允许当前域名
     * @param $whites  String / Array  允许发起请求的域或者url
     * @return bool true 符合条件,false 非法的域或url
     *
     */
    public function checkReferer() {
        $this->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (empty($this->referer)) { //referer为空情况时,不允许通过
			// edit 2012/07/30 浏览器 https 跳转到 http页面 不发送referer,对于不支持referer的浏览器或者客户端建议使用token
            return false;
        }
        if (!$this->url_safe_check()) { //对url的格式进行正则匹配,防止非法提交
            return false;
        }
         //默认允许当前域名
        $whites = $this->_alow_referer;

        if (!empty($whites)) {
            if (is_string($whites)) {
                if ($this->check_whites($whites)) {//通过
                    return true;
                }
            } elseif (is_array($whites) && !empty($whites)) {
                foreach ($whites as $white) {
                    if ($this->check_whites($white)) { //通过
                        return true;
                    }
                }
            }
        }
        return false;
    }
    /**
     *
     * 对单个白名单进行检查
     * @param $white string
     * @return  bool true/false
     */
    private function check_whites($white) {

        $refhost_arr     = parse_url($this->referer);
        $refhost         = $refhost_arr['host'];
        if ( strpos( $refhost , $white ) !== false) { //判断域名是否相等
            return true;
        }
        return false;
    }
    /**
     * 对url规则进行匹配,检查常见的反射型xss url
     * @return bool true/false
     */
    private function url_safe_check() {
        if (empty($this->black_regx_arr)) { //黑名单正则为空,不继续检查
            return true;
        }
        foreach ($this->black_regx_arr as $regx) {
            //echo $this->safe_html($regx),"<br/>";
            if (preg_match('/' . $regx . '/i', $this->referer)) {
                return false;
            }
        }
        return true;
    }
}
