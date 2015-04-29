<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class input_file extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->rbac->check_access();
		$this->load->helper('file');
		$this->load->library('oss');
	}
	
	//默认加载方法
	function index(){
		$by=$this->input->get('by');
		switch ($by){
			case 'swfupload':
				$this->swfupload();
				break;
			case 'upload':
				//$this->upload();
				$this->swfupload();
				break;
			default:
			echo '参数异常';
		}
	}
	
	//执行图片上传
	function swfupload_do(){
		$this->load->config('upload',TRUE);
		$this->load->helper('dir');
		$upload = $this->config->item('upload');
		$post_data = $this->input->post(NULL);
		if ($post_data['dosubmit']==1) {
            if($post_data['swf_auth_key'] != md5($upload['auth_key'].$post_data['SWFUPLOADSESSID'])){exit('0');}
            $setting_file=array(
                'module'=>$post_data['module'],
                'catid'=>$post_data['catid'],
                'siteid'=>0,
                'upload_dir'=>$post_data['module']
            );
            //$this->load->library('upfile',$setting_file);

            $filetype = explode("|", $post_data['filetype_post']);
            $this->oss->set_image_format($filetype);
            $dir = $post_data['module'] . "/" . $post_data['catid'];
            $file = $this->oss->upload($_FILES['Filedata'], $dir);

            //if($post_data['by']=='swfupload'){
                //$files = $this->upfile->upload('Filedata',$post_data['filetype_post'],'','',array($post_data['thumb_width'],$post_data['thumb_height']),$post_data['watermark_enable']);
                var_dump($files);exit;
                //todo
            //}else{
                //$files = $this->upfile->upload('Filedata',$post_data['filetype_post'],'','');
                //todo
            //}

            if(is_array($files) and count($files)>0){
                foreach($files as $key=>$picinfo){
                    $pinfo=$picinfo;
                    $pinfo['appid']=$post_data['appid'];
                    $pinfo['module']=$post_data['module'];
                    $pinfo['catid']=$post_data['catid'];
                    $pinfo['userid']=$post_data['userid'];
                }
                $pic_insert_str=$this->db->insert_string('ci_file_info',$pinfo);
                if($this->db->query($pic_insert_str)){
                    $fileext=$pinfo['fileext'];
                    if($pinfo['isimage']==1){
                        $fileext=1;
                    }else{
                        if($fileext == 'zip' || $fileext == 'rar') $fileext = 'rar';
                        elseif($fileext == 'doc' || $fileext == 'docx') $fileext = 'doc';
                        elseif($fileext == 'xls' || $fileext == 'xlsx') $fileext = 'xls';
                        elseif($fileext == 'ppt' || $fileext == 'pptx') $fileext = 'ppt';
                        elseif ($fileext == 'flv' || $fileext == 'swf' || $fileext == 'rm' || $fileext == 'rmvb') $fileext = 'flv';
                        else $fileext = 'do';
                    }
                    echo $this->db->insert_id().",{$pinfo['httpurl']},{$fileext},{$pinfo['filename']}," . $_SERVER['LJSRV_EXT_PUB_PATH'];
                }else{
                    echo '0,'.$this->upfile->error();
                }
            }
		}
	}
	
	//文件列表
	function file_list(){
		$this->load->library('form');	
		$file_upload_limit=$this->input->get('file_upload_limit');
		$page=$this->input->get('page');
		$page = max(intval($page),1);
		$dosearch=$this->input->get('dosearch');	
		if($dosearch=='ok'){	
			$keywords=trim($this->input->get('keywords'));
			$search_arr['keywords']=$keywords;
			if($keywords!=''){
				$where_array[]="filename like '%{$keywords}%'";		
			}
			$inputtime=$this->input->get('inputtime');
			if($inputtime!=''){
				$inputtime=strtotime($inputtime);
				$where_array[]="uploadtime > {$inputtime}";
			}
			if(is_array($where_array) and count($where_array)>0){
				$where=' WHERE '.join(' AND ',$where_array);
			}
		}
		
		$pagesize=8;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";
		
		$query_role_num="SELECT id,filename,httpurl,fileext,isimage FROM ci_file_info $where";
		$result_role_num=$this->db->query($query_role_num);
		$role_num=$result_role_num->num_rows();
		$pages=pages($role_num,$page,$pagesize);
		
		$query_role_group="SELECT id,filename,httpurl,fileext,isimage FROM ci_file_info $where $limit";
		$resule_role_group=$this->db->query($query_role_group);
		$list_role_group=$resule_role_group->result_array();
		
		$search_arr['inputtime']=$this->form->date('inputtime');
		$this->smarty->assign('search_arr',$search_arr);
		$this->smarty->assign('file_upload_limit',$file_upload_limit);
		$this->smarty->assign('list_data',$list_role_group);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/input_file_list.html');
	}
	
	//swf上传文件
	private function swfupload(){
		$by     = $this->input->get('by');
		$module = $this->input->get('module') ? $this->input->get('module') : 'modulename';
		$catid  = $this->input->get('catid') ? intval($this->input->get('catid')) : 0;
		$args   = $this->input->get('args');

        $this->load->config('upload',TRUE);
		$upload = $this->config->item('upload');
		$upload['file_size'] = sizecount($upload['upload_maxsize']*1024);

		$arg_arr = explode(',',$args);
		$arg_arr[1] = str_replace('|','、',$arg_arr[1]);
		$session_key = $this->rbac->log_infos['user_local'];
        $keyno = $this->rbac->log_infos['keyno'];
        $role_id = $this->rbac->log_infos['role_id'];

		$upload_swf_init = initupload($module, $catid,$args, $keyno, $role_id, 0, $upload, $session_key, $by);

		$this->smarty->assign('upload_config',$upload);
		$this->smarty->assign('args',$arg_arr);
		$this->smarty->assign('upload_swf_obj',$upload_swf_init);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->assign('show_validator','true');
		$this->smarty->display('admin/input_file_upswf.html');
	}
	
	
	//普通文件上传
	private function upload(){
		echo 'File up not codeing!';
	}
		
}
/*This file end*/
