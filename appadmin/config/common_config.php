<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['phone_type'] = array(
		1 => "蓝鲸",
		2 => "采访",
		3 => "专家",
		4 => "私人"
);//通讯录类型

$config['status'] = array(
	// -1 => "已删除",
	0 => "未审核",
	1 => "已通过",
	2 => "未通过",
);

$config['feedback'] = array(
	1 => "采访：电话号码错误",
    2 => "采访：电话号码正确",
    3 => "采访：拒绝号码采访",
    4 => "采访：号码无人接听", 
);

$config['identity'] = array(
	1 => "记者",
	2 => "专家",
	3 => "企业",
);
$config['sex'] = array(
	0 => "女",
	1 => "男",
);
$config['push_type'] = array(
    1 => "首页",
    2 => "wap页",
    3 => "帖子",
);

