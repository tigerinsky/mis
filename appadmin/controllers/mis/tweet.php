<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 蓝鲸APP要闻信息存放
 * @author Faxhaidong
 * @version 20140702
 */
class tweet extends CI_Controller{
        
    function __construct(){
        parent::__construct();
        $this->rbac->check_access();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->load->config('mis_tweet',TRUE);
        $this->mis_tweet=$this->config->item('mis_tweet');
        $this->load->model('mis/industry_model','industry_model');
        $this->load->model('mis/tweet_model','tweet_model');
    }
    
    //默认调用控制器
    function index(){
        $this->tweet_list();
    }
    
    //显示新闻列表，同时有检索功能
    private function tweet_list(){
        $this->load->library('form');
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
        if($dosearch=='ok'){
            
            $search_filed=array(
                'state'=>array(
                        '1'=>'is_essence=0 AND is_del=0',
                        '2'=>'is_essence=1',
                        '3'=>'is_del=1'
                    ),
                'filter'=>array(
                        '1'=>"title like '%#keyword#%'",
                        '2'=>"uname='#keyword#'",
                        '3'=>"content='#keyword#'"
                    )
            );

            $time_start=$this->input->get('time_start');
            $time_end=$this->input->get('time_end');

            if($time_start !='' && $time_end !=''){
                $time1=strtotime($time_start);
                $time2=strtotime($time_end);
                $where_array[]="ctime>{$time1} AND ctime<{$time2}";
            }

            if(intval($this->input->get('industry_id'))!=''){
                $industry_id=$this->input->get('industry_id');
                $where_array[]="industry='{$industry_id}'";
            }
            
            if(intval($this->input->get('state_id'))!=''){
                $state_id=$this->input->get('state_id');
                if($search_filed['state'][$state_id]!=''){
                    $where_array[]=$search_filed['state'][$state_id];
                }
            }

            $keywords=trim($this->input->get('keywords'));
            $search_arr['keywords']=$keywords;
            $filter_id=$this->input->get('filter_id');

            if($keywords!='' && $filter_id>0){
                $filter_str=$search_filed['filter'][$filter_id];
                $filter_str=str_replace('#keyword#',$keywords,$filter_str);
                $where_array[]=$filter_str;        
            }
            
            if(is_array($where_array) and count($where_array)>0){
                $where=' WHERE '.join(' AND ',$where_array);
            }
            
        }

        $pagesize=10;
        $offset = $pagesize*($page-1);
        $limit="LIMIT $offset,$pagesize";
        
        $user_num=$this->tweet_model->get_count_by_parm($where);
        $pages=pages($user_num,$page,$pagesize);
        $list_data=$this->tweet_model->get_data_by_parm($where,$limit);
        $industry_data=$this->industry_model->get_data_by_parm();
        if(is_array($industry_data) && count($industry_data)>0){
            $industry_list=array();
            foreach ($industry_data as $key=>$row){
                $industry_list[$row['id']]=$row['title'];
            }
        }
        $this->load->library('form');
        $state_list=array('1'=>'正常','2'=>'推荐','3'=>'删除');
        $filter_list=array('1'=>'标题','2'=>'发布人','3'=>'内容');
        $search_arr['time_start']=$this->form->date('time_start',$time_start,1);
        $search_arr['time_end']=$this->form->date('time_end',$time_end,1);
        $search_arr['industry_sel']=$this->form->select($industry_list,$industry_id,'name="industry_id"','选择行业');
        $search_arr['state_sel']=$this->form->select($state_list,$state_id,'name="state_id"','选择状态');
        $search_arr['filter_sel']=$this->form->select($filter_list,$filter_id,'name="filter_id"','搜索条件');
        $this->smarty->assign('search_arr',$search_arr);
        $this->smarty->assign('industry_list',$industry_list);
        $this->smarty->assign('list_data',$list_data);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('mis/tweet_list.html');
    }

    
    //对要闻进行单条推荐
    function sug_one_ajax(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->tweet_model->one_sug($id, 1)){
				echo 1;
            }else{
				echo 0;
            }
        } else {
			echo 0;
        }
    }
    
    //对要闻闻进行批量推荐属性设置
    function tweet_sug(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->tweet_model->tweet_sug($ids_str)){
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
    
    //对要闻进行单条取消推荐
    function sug_one_cancel_ajax() {
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->tweet_model->one_sug($id, 0)){
				echo 1;
            }else{
				echo 0;
            }
        } else {
			echo 0;
        }
    }
    //对要闻闻进行批量推荐属性取消
    function tweet_clear_sug(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->tweet_model->tweet_clear_sug($ids_str)){
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

    //对要闻进行单条删除属性变更
    function del_one_ajax(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->tweet_model->one_del($id, 1)){
				echo 1;
            }else{
				echo 0;
            }
        } else {
			echo 0;
        }
    }
    
	//对要闻进行单条取消删除
    function del_one_cancel_ajax(){
        if(intval($_GET['id'])>0) {
            $id=$this->input->get('id');
            if($this->tweet_model->one_del($id, 0)){
				echo 1;
            }else{
				echo 0;
            }
        } else {
			echo 0;
        }
    }
    //对要闻闻进行批量删除属性设置
    function tweet_del(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->tweet_model->tweet_del($ids_str)){
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

    //对要闻闻进行批量删除属性取消
    function tweet_clear_del(){
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids)>0){
                $ids_str=join("','",$ids);
                if($this->tweet_model->tweet_clear_del($ids_str)){
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
    
    
    //添加要闻
    function tweet_add(){
        $this->load->library('form');
        $industry_data=$this->industry_model->get_data_by_parm();
        if(is_array($industry_data) && count($industry_data)>0){
            $industry_list=array();
            foreach ($industry_data as $key=>$row){
                $industry_list[$row['id']]=$row['title'];
            }
        }

        $industry_sel=Form::select($industry_list,0,'name="info[industry]"','全行业');
		$mask_list = array(1=>'蓝鲸小秘书');
		$mask_sel = Form::select($mask_list,0,'id="mask" name="info[uid]"','请选择');
        $this->smarty->assign('industry_sel',$industry_sel);
        $this->smarty->assign('mask_sel',$mask_sel);
        $this->smarty->assign('random_version', rand(100,999));
        $this->smarty->assign('input_box',$input_box);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('mis/tweet_add.html');
    }
    
    //执行添加要闻操作
    function tweet_add_do(){
        $info = $this->input->post('info');
        $pic  = $this->input->post('pic');
        //判断数据有效性
		/*
		$this->load->library('oss');
		if ($_FILES['img']['name'] != "") {
			$pic_ret = $this->oss->upload('img', array('dir'=>'tweet'));
			if (isset($pic_ret['error_code']) && intval($pic_ret['error_code'])) {
				show_tips($pic_ret['error_code']. ":" . $pic_ret['error']);
			}	
			$info['img'] = $pic_ret;
		}
		 */

        if( $info['content']!='' && $info['uid'] != ''){
			$info['img'] = !empty($pic) ? json_encode($pic) : '';
            $info['is_essence']=$info['is_essence']==1?1:0;
            if($this->tweet_model->create_info($info)){
                show_tips('操作成功','','','add');
            }else{
                show_tips('操作异常');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //修改要闻
    function tweet_edit(){
        $this->load->library('form');
        $tweet_id = $this->input->get('id');
        $info = $this->tweet_model->get_info_by_id($tweet_id);
		$info['img'] = !empty($info['img']) ? json_decode($info['img']) : array();
        $industry_data = $this->industry_model->get_data_by_parm();
        if(is_array($industry_data) && count($industry_data)>0){
            $industry_list=array();
            foreach ($industry_data as $key=>$row){
                $industry_list[$row['id']]=$row['title'];
            }
        }
        $industry_sel=Form::select($industry_list,$info['industry'],'name="info[industry]"','全行业');
		$mask_list = array(1=>'蓝鲸小秘书');
		$mask_sel = Form::select($mask_list, $info['uid'],'id="mask" name="info[uid]"','请选择');
        $this->smarty->assign('info',$info);
        $this->smarty->assign('industry_sel',$industry_sel);
        $this->smarty->assign('mask_sel',$mask_sel);
        $this->smarty->assign('random_version', rand(100,999));
        $this->smarty->assign('input_box',$input_box);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('mis/tweet_edit.html');
    }
    
    //执行修改要闻操作
    function tweet_edit_do(){
        $id = $this->input->post('id');
        $info = $this->input->post('info');
		$this->load->library('oss');
		$pic = $this->input->post('pic');
		/*
		if ($_FILES['img']['name'] != "") {
			$pic_ret = $this->oss->upload('img', array('dir'=>'tweet'));
			if (isset($pic_ret['error_code']) && intval($pic_ret['error_code'])) {
				show_tips($pic_ret['error_code']. ":" . $pic_ret['error']);
			}	
			$info['img'] = $pic_ret;
		}
		 */
        if($info['content']!=''){
			$info['img'] = !empty($pic) ? json_encode($pic) : '';
            $info['is_essence'] = $info['is_essence']==1?1:0;
            if($this->tweet_model->update_info($info,$id)){
                show_tips('操作成功','','','edit');
            }else{
                show_tips('操作异常，请检测');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    
    //单条删除要闻
    function tweet_del_one_ajax(){
        $tweet_id=intval($this->input->get('id'));
        $ret=0;
        if($tweet_id>0){
            if($this->tweet_model->del_info($tweet_id)){
                $ret=1;
            }
        }
        echo $ret;
    }

}
