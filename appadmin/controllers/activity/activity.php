<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 二级分类图片管理
 */
class activity extends MY_Controller{
        
    function __construct(){
        parent::__construct();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->activity_type = array('1'=>'只有图片', '2'=>'图文并茂');
        $this->load->model('activity/activity_model', 'activity_model');
    }
    
    //默认调用控制器
    function index(){
    	$this->activity_list();
    }
    
    //显示活动列表，同时有检索功能
    private function activity_list(){
        $this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr['is_deleted'] = 1;
        $where_array[] = "is_deleted=1";
        
        if($dosearch == 'ok'){
            
            $keywords = trim($this->input->get('keywords'));

            if($keywords != ''){
                $search_arr['keywords'] = $keywords;
                $where_array[] = "name like '%{$keywords}%'";        
            }

        }

        if(is_array($where_array) and count($where_array)>0){
            $where = ' WHERE '.join(' AND ',$where_array);
        }

        $pagesize = 10;
        $offset = $pagesize*($page-1);
        $limit = "LIMIT $offset,$pagesize";
        
        $type_list = $this->activity_type;
        
        $user_num = $this->activity_model->get_count_by_parm($where);
        $pages = pages($user_num,$page,$pagesize);
        $list_data = $this->activity_model->get_data_by_parm($where,$limit);

        $this->load->library('form');
        $this->smarty->assign('search_arr',$search_arr);
        $this->smarty->assign('type_list', $type_list);
        $this->smarty->assign('list_data',$list_data);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->display('activity/activity_list.html');
    }

    /**
     * 对外提供的接口
     * 
     */ 
    function get_activity_list(){
        $request = $this->request_array;
        $response = $this->response_array;
        
    	$result = array();
    	if (isset($timestamp) && $timestamp > $img_timestamp) {
    		$response['errno'] = 901;
    		$response['data']['content'] = $result;
    	} else {
	    	$where_array[]="is_deleted=1";
	    
	    	if(is_array($where_array) and count($where_array)>0){
	    		$where=' WHERE '.join(' AND ',$where_array);
	    	}
	    
	    	$row_num = $this->activity_model->get_count_by_parm($where);
	    	$limit = "LIMIT $row_num";
	    	$list_data = $this->activity_model->get_data_by_parm($where, $limit);
	    	
    		foreach($list_data as $img_data) {
    			$tmp_array = array(
    					'name' => $img_data['name'],
    					'type' => $img_data['type'],
    					'online_time' => $img_data['online_time'],
    					'position' => $img_data['position'],
    					'img_url' => $img_data['img_url'],
    					'jump_url' => $img_data['jump_url'],
    					);
    			$result[] = $tmp_array;
    		}
	    	
	    	$response['errno'] = 0;
	    	$response['data']['content'] = $result;
    	}

        $this->renderJson($response['errno'], $response['data']);
	
    }
    
    
    /**
     * ajax调用的函数
     *
     */
    function get_img_title_list_ajax(){
    	$request = $this->request_array;
    	$response = $this->response_array;
    
    	$img_type = $request['img_type'];
    
    	$result = array();
    	if (isset($this->mis_imgmgr['imgmgr_level_2'][$img_type])) {
    		$result = $this->mis_imgmgr['imgmgr_level_2'][$img_type];
    	}
    	
    	$response['errno'] = 0;
    	$response['data']['content'] = $result;
    	
    	$this->renderJson($response['errno'], $response['data']);
    
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

    // 删除活动
    function del_one_ajax(){
        if(intval($_GET['id'])>0) {
        	// 更新时间戳
        	//$this->redis->set($this->key_img, time());
            $id = $this->input->get('id');
            if($this->activity_model->del_info($id)){
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
    
    
    // 添加活动
    function activity_add(){
        $this->load->library('form');
        
        $type_list = $this->activity_type;
        $type_sel=Form::select($type_list, 1, 'name="info[type]"');
        $this->smarty->assign('type_sel', $type_sel);
        
        $this->smarty->assign('random_version', rand(100,999));
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('activity/activity_add.html');
    }
    
    // 执行添加活动操作
    function activity_add_do(){
        $info = $this->input->post('info');
    	$online_time = strtotime($this->input->post('online_time'));
    	$offline_time = strtotime($this->input->post('offline_time'));
    	$info['online_time'] = $online_time;
    	$info['offline_time'] = $offline_time;
    	
        $pic  = $this->input->post('pic');
        log_message('debug', '*****************[test]******************img_add_do');
        log_message('debug', $pic[0]);
		
        $info['img_url'] = $pic[0];
		
        if( $info['name']!='' && $info['jump_url'] != ''){
            if($this->activity_model->create_info($info)){
                show_tips('操作成功','','','add');
            }else{
                show_tips('操作异常');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    // 修改活动
    function activity_edit(){
        $this->load->library('form');
        $activity_id = $this->input->get('id');
        $info = $this->activity_model->get_info_by_id($activity_id);
		
        $type_list = $this->activity_type;
        $type_sel = Form::select($type_list, $info['type'], 'name="info[type]"');
        $this->smarty->assign('type_sel', $type_sel);
        
        $this->smarty->assign('info',$info);
        $this->smarty->assign('random_version', rand(100,999));
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('activity/activity_edit.html');
    }
    
    // 执行修改活动操作
    function activity_edit_do(){
        $id = $this->input->post('id');
        $info = $this->input->post('info');
		$this->load->library('oss');
		$pic = $this->input->post('pic');
        $info['img_url'] = $pic[0];
        
        $online_time = strtotime($this->input->post('online_time'));
        $offline_time = strtotime($this->input->post('offline_time'));
        $info['online_time'] = $online_time;
        $info['offline_time'] = $offline_time;
        
        if($info['name'] != '' && $info['jump_url'] != '') {
            if($this->activity_model->update_info($info, $id)){
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

    /*
     * 对外提供活动列表
     */
    function activityList()
    {
        $get_url = ULR_PROX.'/admin.php/activity/activity/get_activity_list';
        $this->load->library('http2');
        $ret = json_decode($this->http2->get($get_url),true);
        if($ret['errno'] == 0)
        {
            $top = $botm = array();
            foreach($ret['data']['content'] as $key=>$value)
            {
                $ret['data']['content'][$key]['online_time'] = date('Y-m-d H:i:S',$value['online_time']);
                if($value['type'] == 1)
                {
                    $top[]  = $ret['data']['content'][$key];
                }else {
                    $botm[] = $ret['data']['content'][$key];
                }
            }
            $this->smarty->assign('top',$top);
            $this->smarty->assign('botm',$botm);
            $this->smarty->display('activity/index.html');
        }
    }

}
