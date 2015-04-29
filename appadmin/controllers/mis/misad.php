<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 广告信息管理
 * @author Faxhaidong
 * @version 20140702
 */
class misad extends CI_Controller{
        
    function __construct(){
        parent::__construct();
        $this->rbac->check_access();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->postion_list=array('1'=>'首屏轮播','2'=>'闪屏','3'=>'工作台');
        $this->load->model('mis/misad_model','misad_model'); 
    }
    
    //默认调用控制器
    function index(){
        $this->misad_list();
    }
    
    //显示广告列表，同时有检索功能
    private function misad_list(){
        $this->load->library('form');
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
                
        if($dosearch=='ok'){
                        
            $keywords=trim($this->input->get('keywords'));
            
            if($keywords!=''){
                $search_arr['keywords']=$keywords;
                $where_array[]="title like '%{$keywords}%'";        
            }
            
            if($this->input->get('flag')){
                $search_arr['flag']=1;
                $where_array[]="flag=1";
            }

            if(is_array($where_array) and count($where_array)>0){
                $where=' WHERE '.join(' AND ',$where_array);
            }
            
        }

        $pagesize=10;
        $offset = $pagesize*($page-1);
        $limit="LIMIT $offset,$pagesize";

        $misad_num=$this->misad_model->get_count_by_parm($where);
        $pages=pages($misad_num,$page,$pagesize);
        $list_data=$this->misad_model->get_data_by_parm($where,$limit);
        $this->smarty->assign('postion_list',$this->postion_list);
        $this->smarty->assign('search_arr',$search_arr);
        $this->smarty->assign('list_data',$list_data);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('mis/misad_list.html');

    }

    
    //添加广告
    function misad_add(){
        $this->load->library('form');
        $input_box['postion_sel']=$this->form->select($this->postion_list,$postion,'name="info[postion]"','选择版位');
        $nowtime=date('Y-m-d H:i:s',time());
        $input_box['time_line']=$this->form->date('info[time_line]',$nowtime,1);
        $this->smarty->assign('input_box',$input_box);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('mis/misad_add.html');
    }
    
    //执行添加广告操作
    function misad_add_do(){
        $info=$this->input->post('info');
        if ($_FILES['img']['name'] != "") {
			$this->load->library('oss');
            $pic_ret = $this->oss->upload('img', array('dir'=>'ads'));
            if (isset($pic_ret['error_code']) && intval($pic_ret['error_code'])) {
                show_tips($pic_ret['error_code']. ":" . $pic_ret['error']);
            }   
            $info['img_url'] = $pic_ret;
        }
        //判断数据有效性
        if($info['postion']!='' && $info['title']!='' && $info['rel_id']!='' && $info['img_url']!=''){
            $info['time_line']=strtotime($info['time_line']);
            $info['time_create']=time();
            if($this->misad_model->create_info($info)){
                show_tips('操作成功','','','add');
            }else{
                show_tips('操作异常');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //修改广告
    function misad_edit(){
        $misad_id=$this->input->get('id');
        $info=$this->misad_model->get_info_by_id($misad_id);
        $this->load->library('form');
        $info['time_line']=date('Y-m-d h:i:s',$info['time_line']);
        $input_box['postion_sel']=$this->form->select($this->postion_list,$info['postion'],'name="info[postion]"','选择版位');
        $input_box['time_line']=$this->form->date('info[time_line]',$info['time_line'],1);
        $this->smarty->assign('info',$info);
        $this->smarty->assign('input_box',$input_box);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('mis/misad_edit.html');
    }
    
    //执行修改广告操作
    function misad_edit_do(){
        $id   = $this->input->post('id');
        $info = $this->input->post('info');
		if ($_FILES['img']['name'] != "") {
			$this->load->library('oss');
            $pic_ret = $this->oss->upload('img', array('dir'=>'ads'));
            if (isset($pic_ret['error_code']) && intval($pic_ret['error_code'])) {
                show_tips($pic_ret['error_code']. ":" . $pic_ret['error']);
            }
            $info['img_url'] = $pic_ret;
        }
        if($id>0 && $info['postion']!='' && $info['title']!='' && $info['rel_id']!=''){
            $info['flag']=$info['flag']==1?1:0;
            if($this->misad_model->update_info($info,$id)){
                show_tips('操作成功','','','edit');
            }else{
                show_tips('操作异常，请检测');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //广告排序
    public function misad_order(){
        if(intval($_POST['dosubmit']==1)) {
            $listorders=$this->input->post('listorders');
            if(is_array($listorders) and count($listorders)>0){
                if($this->misad_model->change_info_order($listorders)){
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

    //对广告进行单条推荐属性变更
    function misad_flag_one(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->misad_model->change_info_flag($id)){
                show_tips('操作成功',HTTP_REFERER);
            }else{
                show_tips('操作异常');
            }
        } else {
            show_tips('操作异常');
        }
    }
    
    //对广告进行批量推荐属性变更
    function misad_flag(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->misad_model->change_info_flag($ids_str)){
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
    
    //对广告进行单条审核属性变更
    function misad_status_one(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->misad_model->change_info_status($id)){
                show_tips('操作成功',HTTP_REFERER);
            }else{
                show_tips('操作异常');
            }
        } else {
            show_tips('操作异常');
        }
    }
    
    //对广告进行批量审核属性变更
    function misad_status(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->misad_model->change_info_status($ids_str)){
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

    //批量删除广告
    function misad_del(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if($this->misad_model->del_info($ids)){
                show_tips('操作成功',HTTP_REFERER);
            }else{
                show_tips('操作异常',HTTP_REFERER);
            }
        } else {
            show_tips('操作异常');
        }
    }
    
    //单条删除广告
    function misad_del_one_ajax(){
        $misad_id=intval($this->input->get('id'));
        $ret=0;
        if($misad_id>0){
            if($this->misad_model->del_info($misad_id)){
                $ret=1;
            }
        }
        echo $ret;
    }
        
    
    //检测字段重复
    function check_filed_have_ajax(){
        $this->load->library('check_filed');
        $true_table_arr=array(
            'A'=>'ci_app_misad_info'
        );
        $this->check_filed->check_filed_have_ajax($true_table_arr);
    }
    
}
