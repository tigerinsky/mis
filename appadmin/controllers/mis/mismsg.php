<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 推送信息管理
 * @author Faxhaidong
 * @version 20140702
 */
class mismsg extends CI_Controller{
        
	private static $common_config;
    function __construct(){
        parent::__construct();
        $this->rbac->check_access();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->load->model('mis/industry_model','industry_model');
        $this->load->model('mis/mismsg_model','mismsg_model'); 
		$this->load->config('common_config', TRUE);
		self::$common_config = $this->config->item('common_config');
    }
    
    //默认调用控制器
    function index(){
        $this->mismsg_list();
    }
    
    //显示推送信息列表，同时有检索功能
    private function mismsg_list(){
        $this->load->library('form');
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
                
        if($dosearch=='ok'){
                        
            $keywords=trim($this->input->get('keywords'));
            
            $time_start=$this->input->get('time_start');
            $time_end=$this->input->get('time_end');

            if($time_start !='' && $time_end !=''){
                $time1=strtotime($time_start);
                $time2=strtotime($time_end);
                $where_array[]="time_push>{$time1} AND time_push<{$time2}";
            }

            if(intval($this->input->get('industry_id'))!=''){
                $industry_id=$this->input->get('industry_id');
                $where_array[]="industry='{$industry_id}'";
            }

            if($keywords!=''){
                $search_arr['keywords']=$keywords;
                $where_array[]="title like '%{$keywords}%'";        
            }
            
            if($this->input->get('pushed')){
                $search_arr['pushed']=1;
                $where_array[]="pushed=1";
            }

            if($this->input->get('status')){
                $search_arr['status']=1;
                $where_array[]="status=1";
            }

            if(is_array($where_array) and count($where_array)>0){
                $where=' WHERE '.join(' AND ',$where_array);
            }
            
        }

        $pagesize=10;
        $offset = $pagesize*($page-1);
        $limit="LIMIT $offset,$pagesize";

        $mismsg_num=$this->mismsg_model->get_count_by_parm($where);
        $pages=pages($mismsg_num,$page,$pagesize);
        $list_data=$this->mismsg_model->get_data_by_parm($where,$limit);

        $industry_data=$this->industry_model->get_data_by_parm();
        if(is_array($industry_data) && count($industry_data)>0){
            $industry_list=array();
            foreach ($industry_data as $key=>$row){
                $industry_list[$row['id']]=$row['title'];
            }
        }

        $this->load->library('form');
        $search_arr['time_start']=$this->form->date('time_start',$time_start,1);
        $search_arr['time_end']=$this->form->date('time_end',$time_end,1);
        $search_arr['industry_sel']=$this->form->select($industry_list,$industry_id,'name="industry_id"','选择行业');

        $this->smarty->assign('industry_list',$industry_list);
        $this->smarty->assign('search_arr',$search_arr);
        $this->smarty->assign('list_data',$list_data);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('mis/mismsg_list.html');

    }

    
    //添加推送信息
    function mismsg_add(){
        $this->load->library('form');
        $industry_data=$this->industry_model->get_data_by_parm();
        if(is_array($industry_data) && count($industry_data)>0){
            $industry_list=array();
            foreach ($industry_data as $key=>$row){
                $industry_list[$row['id']]=$row['title'];
            }
        }

        $input_box['industry_sel']=$this->form->select($industry_list,$industry,'name="info[industry]"','请选择');
		debug_show(self::$common_config['push_type'], 'type_list');
        $input_box['type_sel']=$this->form->select(self::$common_config['push_type'],0,'name="info[type]"','请选择');
        $nowtime=date('Y-m-d H:i:s',time());
        $input_box['time_push']=$this->form->date('info[time_push]',$nowtime,1);
        $this->smarty->assign('input_box',$input_box);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('mis/mismsg_add.html');
    }
    
    //执行添加推送信息操作
    function mismsg_add_do(){
        $info=$this->input->post('info');
        //判断数据有效性
        if($info['industry']!='' && $info['title']!='' && $info['rel_id']!='' && $info['time_push']!=''){
            $info['time_push']=strtotime($info['time_push']);
            $info['time_create']=time();
            if($this->mismsg_model->create_info($info)){
                show_tips('操作成功','','','add');
            }else{
                show_tips('操作异常');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //修改推送信息
    function mismsg_edit(){
        $mismsg_id=$this->input->get('id');
        $info=$this->mismsg_model->get_info_by_id($mismsg_id);
        $this->load->library('form');

        $industry_data=$this->industry_model->get_data_by_parm();
        if(is_array($industry_data) && count($industry_data)>0){
            $industry_list=array();
            foreach ($industry_data as $key=>$row){
                $industry_list[$row['id']]=$row['title'];
            }
        }

        $input_box['industry_sel']=$this->form->select($industry_list,$info['industry'],'name="info[industry]"','');
		$input_box['type_sel']=$this->form->select(self::$common_config['push_type'],$info['type'],'name="info[type]"','请选择');
        $info['time_push']=date('Y-m-d h:i:s',$info['time_push']);
        $input_box['time_push']=$this->form->date('info[time_push]',$info['time_push'],1);
        $this->smarty->assign('info',$info);
        $this->smarty->assign('input_box',$input_box);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('mis/mismsg_edit.html');
    }
    
    //执行修改推送信息操作
    function mismsg_edit_do(){
        $id=$this->input->post('id');
        $info=$this->input->post('info');
        if($id>0 && $info['industry']!='' && $info['title']!='' && $info['rel_id']!='' && $info['time_push']!=''){
            $info['status']=$info['status']==1?1:0;
            $info['time_push']=strtotime($info['time_push']);
            if($this->mismsg_model->update_info($info,$id)){
                show_tips('操作成功','','','edit');
            }else{
                show_tips('操作异常，请检测');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //推送信息排序
    public function mismsg_order(){
        if(intval($_POST['dosubmit']==1)) {
            $listorders=$this->input->post('listorders');
            if(is_array($listorders) and count($listorders)>0){
                if($this->mismsg_model->change_info_order($listorders)){
                    show_tips('操作成功');
                }else{
                    show_tips('操作失败');
                }           
            }else{
                show_tips('参数有误，请重新提交');
            }
            
        } else {
            show_tips('访问异常');
        }
    }

    //对推送信息进行单条推荐属性变更
    function mismsg_flag_one(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->mismsg_model->change_info_flag($id)){
                show_tips('操作成功',HTTP_REFERER);
            }else{
                show_tips('操作异常');
            }
        } else {
            show_tips('操作异常');
        }
    }
    
    //对推送信息进行批量推荐属性变更
    function mismsg_flag(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->mismsg_model->change_info_flag($ids_str)){
                    show_tips('操作成功',HTTP_REFERER);
                }else{
                    show_tips('操作异常');
                }
            }else{
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }
    
    //对推送信息进行单条审核属性变更
    function mismsg_status_one(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->mismsg_model->change_info_status($id)){
                show_tips('操作成功',HTTP_REFERER);
            }else{
                show_tips('操作异常');
            }
        } else {
            show_tips('操作异常');
        }
    }
    
    //对推送信息进行批量审核属性变更
    function mismsg_status(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->mismsg_model->change_info_status($ids_str)){
                    show_tips('操作成功',HTTP_REFERER);
                }else{
                    show_tips('操作异常');
                }
            }else{
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }

    //批量删除推送信息
    function mismsg_del(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if($this->mismsg_model->del_info($ids)){
                show_tips('操作成功',HTTP_REFERER);
            }else{
                show_tips('操作异常',HTTP_REFERER);
            }
        } else {
            show_tips('操作异常');
        }
    }
    
    //单条删除推送信息
    function mismsg_del_one_ajax(){
        $mismsg_id=intval($this->input->get('id'));
        $ret=0;
        if($mismsg_id>0){
            if($this->mismsg_model->del_info($mismsg_id)){
                $ret=1;
            }
        }
        echo $ret;
    }
        
    
    //检测字段重复
    function check_filed_have_ajax(){
        $this->load->library('check_filed');
        $true_table_arr=array(
            'A'=>'ci_app_mismsg'
        );
        $this->check_filed->check_filed_have_ajax($true_table_arr);
    }
    
}
