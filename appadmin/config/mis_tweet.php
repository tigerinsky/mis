<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//mis后台baidu对应接口
/*
$config['tweet']=array(
    //'httpurl' => 'http://domain.com/',//接口请求地址
    'add_url' => 'http://www.xxx.xxx/mis_proxy/message_add',//要闻添加接口
    'mod_url' => 'http://www.xxx.xxx/mis_proxy/message_mod/',//要闻更新接口
    'sug_url' => 'http://www.xxx.xxx/mis_proxy/message_sug',//接口请求地址
    'cancel_sug_url' => 'http://www.xxx.xxx/mis_proxy/message_cancel_sug',//接口请求地址
    'del_url' => 'http://www.xxx.xxx/mis_proxy/message_del',//接口请求地址
    'cancel_del_url' => 'http://www.xxx.xxx/mis_proxy/message_cancel_del',//接口请求地址
    'cancel_del_url' => 'http://www.xxx.xxx/mis_proxy/message_cancel_del',//接口请求地址
);
 */
$config['tweet'] = 'http://123.57.249.33/mis_proxy/topic';
$config['message_push'] = 'http://123.57.249.33/mis_proxy/push';//接口请求地址

$config['newmsg'] = 'http://123.57.249.33/message/newmsg';
$config['notify_valid_user'] = 'http://123.57.249.33/mis_proxy/notify_valid_user';
/* End of file mis_tweet.php */
/* Location: ./appadmin/config/mis_tweet.php */
