<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['error_code']=array(
    
    //系统级错误
    '10001'=>array('error_code'=>'10001','en'=>'System error','cn'=>'系统错误'),
    '10002'=>array('error_code'=>'10002','en'=>'Service unavailable','cn'=>'服务暂停'),
    '10003'=>array('error_code'=>'10003','en'=>'Remote service error','cn'=>'远程服务错误'),
    '10004'=>array('error_code'=>'10004','en'=>'IP limit','cn'=>'IP限制不能请求该资源'),
    '10005'=>array('error_code'=>'10005','en'=>'Illegal request','cn'=>'非法请求'),
    '10006'=>array('error_code'=>'10006','en'=>'Invalid web user','cn'=>'不合法的网站用户'),
    '10007'=>array('error_code'=>'10007','en'=>'Miss required parameter (%s) , see doc for more info','cn'=>'缺失必选参数 (%s)，请参考API文档'),
    '10008'=>array('error_code'=>'10008','en'=>'Parameter (%s)\'s value invalid, expect (%s) , but get (%s) , see doc for more info','cn'=>'参数值(%s)非法，需为 (%s)，实际为 (%s)，请参考API文档'),
    '10009'=>array('error_code'=>'10009','en'=>'HTTP method is not suported for this request','cn'=>'请求的HTTP METHOD不支持，请检查是否选择了正确的POST/GET方式'),
    '10010'=>array('error_code'=>'10010','en'=>'Permission denied, need a high level appkey','cn'=>'该资源需要appkey拥有授权'),
    '10011'=>array('error_code'=>'10011','en'=>'Param error, see doc for more info','cn'=>'参数错误，请参考API文档'),
    
    //服务级错误(通用)
    '20001'=>array('error_code'=>'20001','en'=>'IDs is null','cn'=>'IDs参数为空'),
    '20002'=>array('error_code'=>'20002','en'=>'Uid parameter is null','cn'=>'uid参数为空'),
    '20003'=>array('error_code'=>'20003','en'=>'User does not exists','cn'=>'用户不存在'),
    '20004'=>array('error_code'=>'20004','en'=>'Unsupported image type, only suport JPG, GIF, PNG','cn'=>'不支持的图片类型，仅仅支持JPG、GIF、PNG'),
    '20005'=>array('error_code'=>'20005','en'=>'Image size too large','cn'=>'图片太大'),
    '20006'=>array('error_code'=>'20006','en'=>'Content is null','cn'=>'内容为空'),
    '20007'=>array('error_code'=>'20007','en'=>'IDs is too many','cn'=>'IDs参数太长了'),
    '20008'=>array('error_code'=>'20008','en'=>'Text too long, please input text less than 140 characters','cn'=>'输入文字太长，请确认不超过140个字符输入文字太长，请确认不超过140个字符'),
    '20009'=>array('error_code'=>'20009','en'=>'Text too long, please input text less than 300 characters','cn'=>'输入文字太长，请确认不超过300个字符'),
    '20010'=>array('error_code'=>'20010','en'=>'Param is error, please try again','cn'=>'安全检查参数有误，请再调用一次'),
    '20011'=>array('error_code'=>'20011','en'=>'Account or ip or app is illgal, can not continue','cn'=>'账号、IP或应用非法，暂时无法完成此操作'),
    '20012'=>array('error_code'=>'20012','en'=>'Out of limit','cn'=>'发布内容过于频繁'),
    '20013'=>array('error_code'=>'20013','en'=>'Repeat content','cn'=>'提交相似的信息'),
    '20014'=>array('error_code'=>'20014','en'=>'Contain illegal website','cn'=>'包含非法网址'),
    '20015'=>array('error_code'=>'20015','en'=>'Repeat conetnt','cn'=>'提交相同的信息'),
    '20016'=>array('error_code'=>'20016','en'=>'Contain advertising','cn'=>'包含广告信息'),
    '20017'=>array('error_code'=>'20017','en'=>'Content is illegal','cn'=>'包含非法内容'),
    '20018'=>array('error_code'=>'20018','en'=>'Your ip\'s behave in a comic boisterous or unruly manner','cn'=>'此IP地址上的行为异常'),
    '20019'=>array('error_code'=>'20019','en'=>'Test and verify','cn'=>'需要验证码'),
    '20020'=>array('error_code'=>'20020','en'=>'Update success, while server slow now, please wait 1-2 minutes','cn'=>'发布成功，目前服务器可能会有延迟，请耐心等待1-2分钟'),
    '20021'=>array('error_code'=>'20021','en'=>'The model does not exist','cn'=>'指定模型不存在'),
    '20022'=>array('error_code'=>'20022','en'=>'No find any information','cn'=>'未检索到指定信息'),
    //数据库提示
    '20401'=>array('error_code'=>'20401','en'=>'(%s) failed to add','cn'=>'(%s)新增失败'),
    '20402'=>array('error_code'=>'20402','en'=>'(%s) failed to delete','cn'=>'(%s)删除失败'),
    '20403'=>array('error_code'=>'20403','en'=>'(%s) update failed','cn'=>'(%s)更新失败'),
    '20404'=>array('error_code'=>'20404','en'=>'(%s) nothing changed','cn'=>'(%s)没有发生变化'),
    
    //用户模块
    '20101'=>array('error_code'=>'20101','en'=>'ownership of information is not correct','cn'=>'信息归属不正确'),
    '20102'=>array('error_code'=>'20102','en'=>'Uid parameter is null','cn'=>'uid参数为空 '),
    
    //邮件模块
    '20201'=>array('error_code'=>'20201','en'=>'This mail address is not valid','cn'=>'邮箱地址不合法'),
    '20202'=>array('error_code'=>'20202','en'=>'Email authentication information does not exist','cn'=>'邮件验证信息不存在'),
    '20203'=>array('error_code'=>'20203','en'=>'Email authentication information failure','cn'=>'邮件验证信息失效'),
    '20204'=>array('error_code'=>'20204','en'=>'Email authentication information is not correct','cn'=>'邮件验证信息不正确'),
    '20205'=>array('error_code'=>'20205','en'=>'This mail address is empty','cn'=>'邮箱地址不能为空'),
    
    //sms模块
    '20301'=>array('error_code'=>'20301','en'=>'This phone number format error','cn'=>'手机号码格式错误'),
    '20302'=>array('error_code'=>'20302','en'=>'Sms authentication information does not exist','cn'=>'邮件验证信息不存在'),
    '20303'=>array('error_code'=>'20303','en'=>'Sms authentication information failure','cn'=>'邮件验证信息失效'),
    '20304'=>array('error_code'=>'20304','en'=>'Sms authentication information is not correct','cn'=>'邮件验证信息不正确'),
    '20305'=>array('error_code'=>'20305','en'=>'On the same day to use text messaging have reached the maximum number of times','cn'=>'当天短信使用次数已达上限'),
    '20306'=>array('error_code'=>'20306','en'=>'Do not meet the message using the minimum interval','cn'=>'不满足短信使用最小间隔'),
    '20307'=>array('error_code'=>'20307','en'=>'This phone number is empty','cn'=>'手机号码不能为空'),
    '20308'=>array('error_code'=>'20308','en'=>'Token failed to get','cn'=>'token获取失败'),
    
    //上传错误
    '20500'=>array('error_code'=>'20500','en'=>'unknown error','cn'=>'未知的错误'),
    '20501'=>array('error_code'=>'20501','en'=>'The uploaded file exceeds the upload_max_filesize directive in php.ini','cn'=>'上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。'),
    '20502'=>array('error_code'=>'20502','en'=>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form ','cn'=>'上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值 '),
    '20503'=>array('error_code'=>'20503','en'=>'The uploaded file was only partially uploaded. ','cn'=>'文件只有部分被上传'),
    '20504'=>array('error_code'=>'20504','en'=>'No file was uploaded. ','cn'=>'没有文件被上传 '),
    '20505'=>array('error_code'=>'20505','en'=>'','cn'=>''),
    '20506'=>array('error_code'=>'20506','en'=>'Missing a temporary folder. ','cn'=>'找不到临时文件夹'),
    '20507'=>array('error_code'=>'20507','en'=>'Failed to write file to disk ','cn'=>'文件写入失败 '),
    '20508'=>array('error_code'=>'20508','en'=>'A PHP extension stopped the file upload.','cn'=>'缺少php扩展'),
    '20509'=>array('error_code'=>'20509','en'=>'Unsupported image type, only suport JPG, GIF, PNG ','cn'=>'不支持的图片类型，仅仅支持JPG、GIF、PNG '),
    '20510'=>array('error_code'=>'20510','en'=>'Image size too large ','cn'=>'图片太大 '),
    '20511'=>array('error_code'=>'20511','en'=>'Image style doesnot exists','cn'=>'图片样式不存在 '),
    '20512'=>array('error_code'=>'20512','en'=>'file_get_contents function failed','cn'=>'file_get_contents 函数获取图片内容失败'),

    //'99999'=>array('error_code'=>'00000','en'=>'ccccc','cn'=>'ddddd'),
);
/* End of file error_code.php */
