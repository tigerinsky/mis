<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 邀请码管理
 * @author Faxhaidong
 * @version 20140702
 */
class invite extends CI_Controller{
        
    function __construct(){
        parent::__construct();
        $this->rbac->check_access();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->load->model('user/invite_model','invite_model'); 
    }
    
    //默认调用控制器
    function index(){
        $this->invite_list();
    }
    
    //显示邀请码列表，同时有检索功能
    private function invite_list(){

        $this->load->library('form');
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
                
        if($dosearch=='ok'){
            /*
            $keywords=trim($this->input->get('keywords'));
            
            if($keywords!=''){
                $search_arr['keywords']=$keywords;
                $where_array[]="uid = '{$keywords}'";        
            }
            */
            if($this->input->get('valid')){
                $search_arr['valid']=1;
                $where_array[]="valid=1";
            }else{
                $search_arr['valid']=0;
                $where_array[]="valid=0";
            }
            
        }else{
            $search_arr['valid']=1;
            $where_array[]="valid=1";
        }

        if(is_array($where_array) and count($where_array)>0){
            $where=' WHERE '.join(' AND ',$where_array);
        }

        $pagesize=10;
        $offset = $pagesize*($page-1);
        $limit="LIMIT $offset,$pagesize";

        $invite_num=$this->invite_model->get_count_by_parm($where);
        $pages=pages($invite_num,$page,$pagesize);
        $list_data=$this->invite_model->get_data_by_parm($where,$limit);
        $this->smarty->assign('postion_list',$this->postion_list);
        $this->smarty->assign('search_arr',$search_arr);
        $this->smarty->assign('list_data',$list_data);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('tool/invite_list.html');

    }

    
    //添加邀请码
    function invite_add(){
        $this->load->library('form');
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('tool/invite_add.html');
    }
    
    //执行添加邀请码操作
    function invite_add_do(){
        $data_num=$this->input->post('data_num');
        //判断数据有效性
        if($data_num < 10 || $data_num > 50){
            show_tips('数值应该在10-50之间');
        }else{
            if($this->invite_model->create_info_batch(0,$data_num)){
                show_tips('操作成功','','','add');
            }else{
                show_tips('操作异常');
            }
        }
    }
    
}