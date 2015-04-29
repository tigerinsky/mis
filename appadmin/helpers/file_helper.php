<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
	 * 返回附件类型图标
	 * @param $file 附件名称
	 * @param $type png为大图标，gif为小图标
	 */
	function file_icon($file,$type = 'png') {
		$ext_arr = array('doc','docx','ppt','xls','txt','pdf','mdb','jpg','gif','png','bmp','jpeg','rar','zip','swf','flv');
		$ext = fileext($file);
		if($type == 'png') {
			if($ext == 'zip' || $ext == 'rar') $ext = 'rar';
			elseif($ext == 'doc' || $ext == 'docx') $ext = 'doc';
			elseif($ext == 'xls' || $ext == 'xlsx') $ext = 'xls';
			elseif($ext == 'ppt' || $ext == 'pptx') $ext = 'ppt';
			elseif ($ext == 'flv' || $ext == 'swf' || $ext == 'rm' || $ext == 'rmvb') $ext = 'flv';
			else $ext = 'do';
		}
		if(in_array($ext,$ext_arr)) return SITE_PUB_URL.'images/ext/'.$ext.'.'.$type;
		else return SITE_PUB_URL.'images/ext/blank.'.$type;
	}
	
	/**
	 * 附件目录列表，暂时没用
	 * @param $dirpath 目录路径
	 * @param $currentdir 当前目录
	 */
	function file_list($dirpath,$currentdir) {
		$filepath = $dirpath.$currentdir;
		$list['list'] = glob($filepath.DIRECTORY_SEPARATOR.'*');
		if(!empty($list['list'])) rsort($list['list']);
		$list['local'] = str_replace(array(SITE_PUB_URL, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR), array('',DIRECTORY_SEPARATOR), $filepath);
		return $list;
	}
	
	/**
	 * flash上传初始化
	 * 初始化swfupload上传中需要的参数
	 * @param $module 模块名称
	 * @param $catid 栏目id
	 * @param $args 传递参数
	 * @param $userid 用户id
	 * @param $groupid 用户组id
	 * @param $isadmin 是否为管理员模式
	 */
	function initupload($module,$catid,$args,$userid, $groupid = '8',$appid=0,$site_setting='',$session_key,$by='swfupload'){
		$file_setting=getswfinit($args,$site_setting);
		extract($file_setting);
		$file_size_limit = $upload_maxsize;
		$sess_id = time();
		$swf_auth_key = md5($auth_key.$sess_id);
		$init =  'var swfu = \'\';
		$(document).ready(function(){
		swfu = new SWFUpload({
			flash_url:"'. rtrim($_SERVER['LJSRV_EXT_PUB_PATH'], '/') . '/'.'js/swfupload/swfupload.swf?"+Math.random(),
			upload_url:"'. rtrim($_SERVER['LJSRV_HOST_NAME'], '/') . '/' . 'admin.php/admin/input_file/swfupload_do/",
			file_post_name : "Filedata",
			post_params:{"SWFUPLOADSESSID":"'.$sess_id.'","module":"'.$module.'","catid":"'.$catid.'","userid":"'.$userid.'","appid":"'.$appid.'","dosubmit":"1","thumb_width":"'.$thumb_width.'","thumb_height":"'.$thumb_height.'","watermark_enable":"'.$watermark_enable.'","filetype_post":"'.$file_types_post.'","swf_auth_key":"'.$swf_auth_key.'","groupid":"'.$groupid.'","session_key":"'.$session_key.'","by":"'.$by.'"},
			file_size_limit:"'.$file_size_limit.'",
			file_types:"'.$file_types.'",
			file_types_description:"All Files",
			file_upload_limit:"'.$file_upload_limit.'",
			custom_settings : {progressTarget : "fsUploadProgress",cancelButtonId : "btnCancel"},
	 
			button_image_url: "",
			button_width: 75,
			button_height: 28,
			button_placeholder_id: "buttonPlaceHolder",
			button_text_style: "",
			button_text_top_padding: 3,
			button_text_left_padding: 12,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,

			file_dialog_start_handler : fileDialogStart,
			file_queued_handler : fileQueued,
			file_queue_error_handler:fileQueueError,
			file_dialog_complete_handler:fileDialogComplete,
			upload_progress_handler:uploadProgress,
			upload_error_handler:uploadError,
			upload_success_handler:uploadSuccess,
			upload_complete_handler:uploadComplete
			});
		})';
		return $init;
	}		

	/**
	 * 产生swfupload配置参数
	 * @param array $setting 默认配置信息
	 * @param array $args 当前配置参数
	 */
	function getswfinit($args,$site_setting='') {
		if($site_setting==''){
			$site_setting=array(
				'auth_key'=>'PAMHVz6B8fYAUzcR',
				'upload_maxsize'=>2048,
				'upload_allowext'=>'jpg|jpeg|gif|bmp|png|doc|docx|xls|xlsx|ppt|pptx|pdf|txt|rar|zip|swf',
				'watermark_enable'=>1,
				'watermark_minwidth' => 300,
				'watermark_minheight' => 300,
				'watermark_img' => SITE_PUB_URL.'images/water/mark.png',
				'watermark_pct' => 85,
				'watermark_quality' => 80,
				'watermark_pos' => 9
			);
		}
		$site_allowext = $site_setting['upload_allowext'];
		$args = explode(',',$args);
		$arr['file_upload_limit'] = intval($args[0]) ? intval($args[0]) : '8';
		$args['1'] = ($args[1]!='') ? $args[1] : $site_allowext;
		$arr_allowext = explode('|', $args[1]);
		foreach($arr_allowext as $k=>$v) {
			$v = '*.'.$v;
			$array[$k] = $v;
		}
		$upload_allowext = implode(';', $array);
		$arr['upload_maxsize']=$site_setting['upload_maxsize'];
		$arr['file_types'] = $upload_allowext;
		$arr['file_types_post'] = $args[1];
		$arr['allowupload'] = intval($args[2]);
		$arr['thumb_width'] = intval($args[3]);
		$arr['thumb_height'] = intval($args[4]);
		$arr['watermark_enable'] = ($args[5]=='') ? 1 : intval($args[5]);
		$arr['auth_key']=$site_setting['auth_key'];
		return $arr;
	}	
	/**
	 * 判断是否为图片
	 */
	function is_image($file) {
		$ext_arr = array('jpg','gif','png','bmp','jpeg','tiff');
		$ext = fileext($file);
		return in_array($ext,$ext_arr) ? $ext_arr :false;
	}
	
	/**
	 * 判断是否为视频
	 */
	function is_video($file) {
		$ext_arr = array('rm','mpg','avi','mpeg','wmv','flv','asf','rmvb');
		$ext = fileext($file);
		return in_array($ext,$ext_arr) ? $ext_arr :false;
	}
	
	/**
	* 转换字节数为其他单位
	*
	*
	* @param	string	$filesize	字节大小
	* @return	string	返回大小
	*/
	function sizecount($filesize) {
		if ($filesize >= 1073741824) {
			$filesize = round($filesize / 1073741824 * 100) / 100 .' GB';
		} elseif ($filesize >= 1048576) {
			$filesize = round($filesize / 1048576 * 100) / 100 .' MB';
		} elseif($filesize >= 1024) {
			$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
		} else {
			$filesize = $filesize.' Bytes';
		}
		return $filesize;
	}
	
	/**
	 * 取得文件扩展
	 *
	 * @param $filename 文件名
	 * @return 扩展名
	 */
	function fileext($filename) {
		return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
	}
?>
