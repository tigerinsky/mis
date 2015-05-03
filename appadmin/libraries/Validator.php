<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 用户输入校验类
 *
 */

class Validator {

	public $rule_arr;

	/**
	 * 设置默认正则数组
	 */
	public function __construct()
	{
		$this->rule_arr=array(
		  'email'=>'/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/',//邮箱
		  'mobile'=>'/^1[3|5|7|8|][0-9]{9}$/',//手机
		  'tel'=>'/^[1-9]{1}(\d+){5}$/',//固话
		  'zip'=>'/^[1-9]{1}(\d+){5}$/'//邮编
		);
	}
	
	/**
	 * 检测邮箱地址
	 * @param chars string //检测字符串
	 * @param rules string //正则表达式
	 * @return result bool //返回结果
	 */
	public function email($chars='',$rules=''){
	    if(trim($chars)==''){return false;}
	    if($rules==''){
	        $rules=$this->rule_arr['email'];
	    }
	    if(trim($rules)==''){return false;}
	    $result=preg_match($rules,$chars);
	    return $result;
	}
	
	/**
	 * 检测手机号
	 * @param chars string //检测字符串
	 * @param rules string //正则表达式
	 * @return result bool //返回结果
	 */
	public function mobile($chars='',$rules=''){
	    if(trim($chars)==''){return false;}
	    if($rules==''){
	        $rules=$this->rule_arr['mobile'];
	    }
	    if(trim($rules)==''){return false;}
	    $result=preg_match($rules,$chars);
	    return $result;
	}

}

/* End of file validator.php */