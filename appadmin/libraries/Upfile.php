<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
* 通用的树型类，可以生成任何树型结构
*/
class Upfile {
	
	var $module;
	var $catid;
	var $field;
	var $imageexts = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
	var $uploadedfiles = array();
	var $downloadedfiles = array();
	var $error;
	var $upload_root;
	var $siteid;
	var $site = array();
	
	function __construct($params=array()) {
		$this->CI =& get_instance();
		$this->CI->config->load('upload',TRUE);
		$this->site_setting=$this->CI->config->item('upload');
		$this->catid = intval($params['catid']);
		$this->siteid = intval($params['siteid'])== 0 ? 1 : intval($params['siteid']);
		$this->module = $params['module'] ? $params['module'] : 'system';
		$this->CI->load->helper('dir');
		$this->upload_root = FCPATH.$this->site_setting['upload_dir'];
		$this->upload_func = 'copy';
		$this->upload_dir = '/'.$params['upload_dir'].'/';
	}
	/**
	 * 附件上传方法
	 * @param $field 上传字段
	 * @param $alowexts 允许上传类型
	 * @param $maxsize 最大上传大小
	 * @param $overwrite 是否覆盖原有文件
	 * @param $thumb_setting 缩略图设置
	 * @param $watermark_enable  是否添加水印
	 */
	function upload($field, $alowexts = '', $maxsize = 0, $overwrite = 0,$thumb_setting = array(), $watermark_enable = 0) {
		
		if(!isset($_FILES[$field])) {
			$this->error = UPLOAD_ERR_OK;
			return false;
		}
		if(empty($alowexts) || $alowexts == '') {
			$alowexts = $this->$site_setting['upload_allowext'];
		}
		
		$fn = $_GET['CKEditorFuncNum'] ? $_GET['CKEditorFuncNum'] : '1';
			
		$this->field = $field;
		$this->savepath = $this->upload_root.$this->upload_dir.date('Y/md/');
		$this->httppath	= SITE_URL.$this->site_setting['upload_dir'].$this->upload_dir.date('Y/md/');
		$this->alowexts = $alowexts;
		$this->maxsize = $maxsize;
		$this->overwrite = $overwrite;
		$uploadfiles = array();
		$description = isset($GLOBALS[$field.'_description']) ? $GLOBALS[$field.'_description'] : array();
		if(is_array($_FILES[$field]['error'])) {
			$this->uploads = count($_FILES[$field]['error']);
			foreach($_FILES[$field]['error'] as $key => $error) {
				if($error === UPLOAD_ERR_NO_FILE) continue;
				if($error !== UPLOAD_ERR_OK) {
					$this->error = $error;
					return false;
				}
				$uploadfiles[$key] = array('tmp_name' => $_FILES[$field]['tmp_name'][$key], 'name' => $_FILES[$field]['name'][$key], 'type' => $_FILES[$field]['type'][$key], 'size' => $_FILES[$field]['size'][$key], 'error' => $_FILES[$field]['error'][$key], 'description'=>$description[$key],'fn'=>$fn);
			}
		} else {
			$this->uploads = 1;
			if(!$description) $description = '';
			$uploadfiles[0] = array('tmp_name' => $_FILES[$field]['tmp_name'], 'name' => $_FILES[$field]['name'], 'type' => $_FILES[$field]['type'], 'size' => $_FILES[$field]['size'], 'error' => $_FILES[$field]['error'], 'description'=>$description,'fn'=>$fn);
		}
		
		if(!dir_create($this->savepath)) {
			$this->error = '8';
			return false;
		}
		if(!is_dir($this->savepath)) {
			$this->error = '8';
			return false;
		}
		@chmod($this->savepath, 0777);

		if(!is_writeable($this->savepath)) {
			$this->error = '9';
			return false;
		}
		if(!$this->is_allow_upload()) {
			$this->error = '13';
			return false;
		}
		$aids = array();
		foreach($uploadfiles as $k=>$file) {
			$fileext = fileext($file['name']);
			if($file['error'] != 0) {
				$this->error = $file['error'];
				return false;		
			}
			if(!preg_match("/^(".$this->alowexts.")$/", $fileext)) {
				$this->error = '10';
				return false;
			}
			if($this->maxsize && $file['size'] > $this->maxsize) {
				$this->error = '11';
				return false;
			}
			if(!$this->isuploadedfile($file['tmp_name'])) {
				$this->error = '12';
				return false;
			}
			$temp_filename = $this->getname($fileext);
			$savefile = $this->savepath.$temp_filename;
			$httpfile = $this->httppath.$temp_filename;
			$savefile = preg_replace("/(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)(\.|$)/i", "_\\1\\2", $savefile);
			$filepath = preg_replace(new_addslashes("|^".$this->upload_root."|"), "", $savefile);
			if(!$this->overwrite && file_exists($savefile)) continue;
			$upload_func = $this->upload_func;
			if(@$upload_func($file['tmp_name'], $savefile)) {
				$this->uploadeds++;
				@chmod($savefile, 0644);
				@unlink($file['tmp_name']);
				//$file['name'] = iconv("utf-8",'GBK',$file['name']);
				$uploadedfile = array('filename'=>$file['name'], 'filepath'=>$filepath, 'filesize'=>$file['size'], 'fileext'=>$fileext, 'fn'=>$file['fn']);
				$uploadedfile['httpfile']=$httpfile;
				$thumb_enable = is_array($thumb_setting) && ($thumb_setting[0] > 0 || $thumb_setting[1] > 0 ) ? 1 : 0;
				$this->CI->load->library('images',array('thumb_enable'=>$thumb_enable));
				if($thumb_enable) {
					$this->CI->images->thumb($savefile,'',$thumb_setting[0],$thumb_setting[1],'_thumb');
				}
				if($watermark_enable) {
					$this->CI->images->watermark($savefile,$savefile);
				}
				$files[] = $this->file_info($uploadedfile);
			}
		}
		return $files;
	}
	
	/**
	 * 获取附件信息
	 * @param $uploadedfile 附件信息
	 */
	function file_info($uploadedfile) {
		$files['ip'] = myip2long(ip());
		$files['uploadtime'] = time();
		$files['status'] = 0;
		$files['filename'] = strlen($uploadedfile['filename'])>49 ? $this->getname($uploadedfile['fileext']) : $uploadedfile['filename'];
		$files['httpurl'] = $uploadedfile['httpfile'];
		$files['status'] = 0;
		$files['fileext'] = $uploadedfile['fileext'];
		$files['isimage'] = in_array($uploadedfile['fileext'], $this->imageexts) ? 1 : 0;
		return $files;
	}
	
	/**
	 * 获取缩略图地址..
	 * @param $image 图片路径
	 */
	function get_thumb($image){
		return str_replace('.', '_thumb.', $image);
	}
	

	/**
	 * 获取附件名称
	 * @param $fileext 附件扩展名
	 */
	function getname($fileext){
		return date('Ymdhis').rand(100, 999).'.'.$fileext;
	}

	/**
	 * 返回附件大小
	 * @param $filesize 图片大小
	 */
	
	function size($filesize) {
		if($filesize >= 1073741824) {
			$filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
		} elseif($filesize >= 1048576) {
			$filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
		} elseif($filesize >= 1024) {
			$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
		} else {
			$filesize = $filesize . ' Bytes';
		}
		return $filesize;
	}
	
	/**
	 * 是否允许上传
	 */
	function is_allow_upload() {
        if($_groupid == 1) return true;
		$starttime = SYS_TIME-86400;
		$site_setting = $this->site_setting;
		return ($uploads < $site_setting['upload_maxsize']);
	}
	
	/**
	* 判断文件是否是通过 HTTP POST 上传的
	*
	* @param	string	$file	文件地址
	* @return	bool	所给出的文件是通过 HTTP POST 上传的则返回 TRUE
	*/
	function isuploadedfile($file) {
		return is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file));
	}
	
	/**
	 * 返回错误信息
	 */
	function error() {
		$UPLOAD_ERROR = array(
		0 => L('att_upload_succ'),
		1 => L('att_upload_limit_ini'),
		2 => L('att_upload_limit_filesize'),
		3 => L('att_upload_limit_part'),
		4 => L('att_upload_nofile'),
		5 => '',
		6 => L('att_upload_notemp'),
		7 => L('att_upload_temp_w_f'),
		8 => L('att_upload_create_dir_f'),
		9 => L('att_upload_dir_permissions'),
		10 => L('att_upload_limit_ext'),
		11 => L('att_upload_limit_setsize'),
		12 => L('att_upload_not_allow'),
		13 => L('att_upload_limit_time'),
		);
		return iconv(CHARSET,"utf-8",$UPLOAD_ERROR[$this->error]);
	}
}
// END Smarty Class
