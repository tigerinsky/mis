<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['rbac_user_auth_on'] ='on';//是否开启模块认证方式
$config['rbac_user_auth_type'] =1;//1实时认证模式,2以session方式存放总权限
$config['rbac_check_user_ip']='off';//on,off针对用户进行IP检测，一旦IP发生变化，需要重新登录
$config['rbac_check_hash']='off';//检测用户登录时候的hash值，即是用户单次登录hash值有效
$config['rbac_user_auth_key'] ='authoruser';//用户认证session标记
$config['rbac_admin_auth_key'] ='log_infos';//管理认证session标记
$config['rbac_user_auth_gateway'] ='user/login';//默认认证网关
$config['rbac_not_auth_module'] ='public';//无需认证模块
$config['rbac_admin_user_id']=array(1);//超级管理员编号，数组中的成员无需认证，如果为空，则都需要经过权限判定
$config['rbac_section_num']=3;//2表示控制器不分组，3表示控制器分组
$config['rbac_keep_time']=12000;//默认session过期时间

/* End of file rbac.php */
/* Location: ./application/config/rbac.php */