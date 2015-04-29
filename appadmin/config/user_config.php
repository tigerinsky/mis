<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['user_allow_reg']     = 'on';//on,off是否允许会员注册
$config['user_auto_login']    = 'on';//是否注册成功后自动登录
$config['check_by_strict']    = 'on';//开启则每次刷新页面都会与数据库做校对，否则仅仅判断session中的uid不为空即可
$config['user_check_ip']      = 'on';//on,off针对用户进行IP检测，一旦IP发生变化，需要重新登录
//$config['user_safe_pre']    = 'x73f';//会员密码加密前缀,不能随意修改，否则旧密码会失效
//$config['user_safe_end']    = 'e2x8yd';//同上
$config['user_auto_lock']     = 'on';//用户密码错误达到一次次数自动锁定
$config['user_err_lock']      = 4;//用户密码错误几次锁定
$config['user_auth_key']      = 'homeuser';//用户认证session标记
$config['user_keep_time']     = 1800;//默认session过期时间
$config['user_auth_gateway']  = 'user/login';//检测未登陆，默认跳转网

$config['pc_activation_sub']    ='还差一步，激活成功';//激活邮件标题
$config['pc_activation_msg']    ='欢迎注册，请点击链接激活!点击链接 <a href="{url}" target="_blank">激活账号</a>，若不能点击，请复制以下链接<br> {url}';//激活邮件内容
$config['pc_reg_success_sub']   ='尊敬的用户，感谢注册本网站!';//用户注册成功邮件标题
$config['pc_reg_success_msg']   ='感谢你选择成为本站用户，欢迎对我们提供建议和反馈!';//用户注册成功邮件内容
$config['pc_forget_pwd_sub']    ='你刚刚在某某网站进行了密码找回操作，如非本人操作，请忽略';//PC找回密码邮件标题
$config['pc_forget_pwd_msg']    ='尊敬的用户某某你好，找回密码，请点击链接 <a href="{url}" target="_blank">找回密码</a> 若不能点击，请复制以下链接<br> {url}';//PC找回密码邮件内容
$config['phone_vlidator_msg']   ='你刚刚在某网站申请了注册，手机验证码为：{yzm}';//手机验证码
$config['phone_forget_msg']     ='你刚刚在某某网通过手机进行了密码找回操作，验证码：{yzm}';//手机找回密码验证码

/* End of file rbac.php */
/* Location: ./application/config/rbac.php */