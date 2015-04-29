<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 行业信息存放
 * @author Faxhaidong
 * @version 20140702
 */
class industry extends CI_Controller{
        
    function __construct(){
        parent::__construct();
        $this->rbac->check_access();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->load->model('mis/industry_model','industry_model'); 
    }
    
    //默认调用控制器
    function index(){
        $this->industry_list();
    }
    
    //显示行业列表，同时有检索功能
    private function industry_list(){
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
            
            if(is_array($where_array) and count($where_array)>0){
                $where=' WHERE '.join(' AND ',$where_array);
            }
            
        }

        $pagesize=10;
        $offset = $pagesize*($page-1);
        $limit="LIMIT $offset,$pagesize";

        $user_num=$this->industry_model->get_count_by_parm($where);
        $pages=pages($user_num,$page,$pagesize);
        $list_data=$this->industry_model->get_data_by_parm($where,$limit);

        $this->smarty->assign('search_arr',$search_arr);
        $this->smarty->assign('list_data',$list_data);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('mis/industry_list.html');
    }

    
    //添加行业
    function industry_add(){
        $this->load->library('form');
        $list_data=$this->industry_model->get_data_by_parm($where,$limit);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('mis/industry_add.html');
    }
    
    //执行添加行业操作
    function industry_add_do(){
        $info=$this->input->post('info');
        //判断数据有效性
        if($info['title']!=''){
            if($this->industry_model->create_info($info)){
                show_tips('操作成功','','','add');
            }else{
                show_tips('操作异常');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //修改行业
    function industry_edit(){
        $this->load->library('form');
        $industry_id=$this->input->get('id');
        $info=$this->industry_model->get_info_by_id($industry_id);
        $this->smarty->assign('info',$info);
        $this->smarty->assign('input_box',$input_box);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('mis/industry_edit.html');
    }
    
    //执行修改行业操作
    function industry_edit_do(){
        $id=$this->input->post('id');
        $info=$this->input->post('info');
        if($info['title']!=''){
            if($this->industry_model->update_info($info,$id)){
                show_tips('操作成功','','','edit');
            }else{
                show_tips('操作异常，请检测');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //批量删除行业
    /*function industry_del(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if($this->industry_model->del_info($ids)){
                show_tips('操作成功',HTTP_REFERER);
            }else{
                show_tips('操作异常',HTTP_REFERER);
            }
        } else {
            show_tips('操作异常');
        }
    }*/
    
    //单条删除行业
    /*function industry_del_one_ajax(){
        $industry_id=intval($this->input->get('id'));
        $ret=0;
        if($industry_id>0){
            if($this->industry_model->del_info($industry_id)){
                $ret=1;
            }
        }
        echo $ret;
    }*/
        
    
    //检测字段重复
    function check_filed_have_ajax(){
        $this->load->library('check_filed');
        $true_table_arr=array(
            'A'=>'ci_app_industry'
        );
        $this->check_filed->check_filed_have_ajax($true_table_arr);
    }
    
}