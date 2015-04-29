<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');                                                          
/**
 * 上传图片
 * @author Faxhaidong
 * @version 20140620
 */
class upload extends MY_Controller {

    protected $table_name = "ci_common_pics";
    public function __construct(){
        parent::__construct();
        $this->rbac->check_access();
        $this->load->library("oss");                                                                                                 
    }
 
    public function index() {
       $this->upload_list(); 
    }

    public function upload_list() {
        $this->load->library('form');
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
        
        if($dosearch=='ok'){

            $keywords=trim($this->input->get('keywords'));
            $search_arr['keywords']=$keywords;
            
            if($keywords!=''){
                $where_array[]="title like '%{$keywords}%'";        
            }
            
            if(intval($this->input->get('status'))!=''){
                $search_arr['status']=$this->input->get('status');
                $where_array[]="status='{$this->input->get('status')}'";
            }

            if(is_array($where_array) and count($where_array)>0){
                $where=' WHERE '.join(' AND ',$where_array);
            }

        }

        $pagesize = 10;
        $offset = $pagesize*($page-1);
        $limit = " LIMIT $offset,$pagesize";

        $query_upload_num = "SELECT id FROM {$this->table_name} $where";
        $result_upload_num = $this->db->query($query_upload_num);
        $upload_num = $result_upload_num->num_rows();
        $pages = pages($upload_num,$page,$pagesize);
        
        $query_upload_group="SELECT `id`, `title`, `pic`, `status`, `createdt`, `updatedt` FROM {$this->table_name} $where $limit"; 
        $resule_upload_group=$this->db->query($query_upload_group);
        $list_upload_group=$resule_upload_group->result_array();
        $this->smarty->assign('search_arr',$search_arr);
        $this->smarty->assign('list_data',$list_upload_group);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('common/upload_list.html');
    }

    public function upload_status_one() {
        $this->status_one();
    }

    public function upload_status() {
        $this->status();
    }

    public function upload_add() {
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('common/upload_add.html');
    }

    public function upload_add_do() {
        $info = $this->input->post('info');
        $pic_url = $this->oss->upload($_FILES['pic'], 'common');
        //判断数据有效性                                                                                                             
        if ($info['title'] != '' && $pic_url !== FALSE) {                                                                            
            $info['pic'] = $pic_url;                                                                                               
            $info['createdt'] = $info['updatedt'] = time();
            $insert_query=$this->db->insert_string($this->table_name, $info);
            $this->db->query($insert_query);
            show_tips('操作成功','','','add');
        }else{
            show_tips('数据不完整，请检测');
        }
    }

    public function upload_edit() {
        $this->load->library('form');
        $upload_id = $this->input->get('id');
        $upload_query = "SELECT `id`, `title`, `pic`, `status`, `createdt`, `updatedt` FROM {$this->table_name} WHERE id={$upload_id}"; 
        $result_upload = $this->db->query($upload_query);
        $info = $result_upload->row_array();
        $this->smarty->assign('info',$info);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('common/upload_edit.html');
    }

    public function upload_edit_do(){
        $upload_id = $this->input->post('id');
        if($upload_id<1) {show_tips('参数异常，请检测');} else {$where = "id={$upload_id}";}                                                  
        $info=$this->input->post('info');                                                                                            
        if ($_FILES['pic']['error'] == 0) {
            $info['pic'] = $this->oss->upload($_FILES['pic'], 'common');
        } else {
            unset($info['pic']);
        }
        if ($info['title'] != '' && (!isset($info['pic']) || $info['pic'] !== FALSE)) {
            $info['updatedt'] = time();
            $update_query=$this->db->update_string($this->table_name, $info, $where);
            if($this->db->query($update_query)){
                show_tips('操作成功','','','edit');
            }else{
                show_tips('操作异常，请检测');
            }
        } else {
            show_tips('数据异常，请检测');
        }
    }

    public function upload_del_one_ajax() {
   
        $album_id=intval($this->input->get('id'));                                                                                   
        $ret=0;
        if($album_id>0){
            $del_query="DELETE FROM {$this->table_name} WHERE id={$album_id} AND status=0";
            if($this->db->query($del_query)){
                $ret=1;
            }
        }
        echo $ret;
    }

    public function upload_del() {
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                $del_query="DELETE FROM {$this->table_name} WHERE id in('{$ids_str}') AND status=0";
                if($this->db->query($del_query)){
                    show_tips('操作成功',HTTP_REFERER);                                                                              
                }else{
                    show_tips('操作异常',HTTP_REFERER);
                }   
            }else{
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }

	public function uploadify() {
		$this->load->library('message');
        $pic_ret = $this->oss->upload('pic', array('dir'=>'mis'));
		if ($pic_ret === null) {
            $this->message->show_error(10001, "system error");
		}
        if (isset($pic_ret['error_code']) && intval($pic_ret['error_code'])) {
            //show_tips($pic_ret['error_code']. ":" . $pic_ret['error']);
		//	echo $pic_ret['error'];exit;
            $this->message->show_error($pic_ret['error_code'], $pic_ret['error']);
        }
        $info['pic'] = $pic_ret;
        //$info['updatedt'] = $info['createdt'] = time();
        //$insert_query=$this->db->insert_string($this->table_name,$info);     
        //$this->db->query($insert_query);          
        //$info['id'] = $this->db->insert_id();
        $this->message->show_success($info);	
		//echo $pic_ret;
	}
}
