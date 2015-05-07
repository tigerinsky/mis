<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 作品管理
 */
class product extends MY_Controller{
    
    function __construct(){
        parent::__construct();
        $this->dbr=$this->load->database('dbr',TRUE);
        $this->load->config('mis_imgmgr',TRUE);
        $this->mis_imgmgr = $this->config->item('mis_imgmgr');
        // $this->mis_imgmgr['imgmgr_level_1']
        $this->load->library('redis');
        $this->key_img = 'mis_img_timestamp';
        $this->load->model('product/product_model', 'product_model');
    }
    
    //默认调用控制器
    function index(){
    	$this->product_list();
    }
    
    //显示图片列表，同时有检索功能
    private function product_list(){
        $this->load->library('form');
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
        
        $search_arr['is_del']=0;
        $where_array[]="is_del=0";
        
        if($dosearch=='ok'){
            
            $search_filed=array(
            	'product_type'=>array(
            			'1'=>'type!=0',
            			'2'=>'type=1',
            			'3'=>'type=0',
            		),
            	'img_type'=>array(
            			'1'=>'f_catalog=1',
            			'2'=>'f_catalog=2',
            			'3'=>'f_catalog=3',
            			'4'=>'f_catalog=4',
            			'5'=>'f_catalog=5',
            			'6'=>'f_catalog=6',
            		),
            );
            
            if(intval($this->input->get('product_type_id'))!=''){
            	$product_type_id=$this->input->get('product_type_id');
            	if($search_filed['product_type'][$product_type_id]!=''){
            		$where_array[]=$search_filed['product_type'][$product_type_id];
            	}
            }
            
            if(intval($this->input->get('img_type_id'))!=''){
            	$img_type_id=$this->input->get('img_type_id');
            	if($search_filed['img_type'][$img_type_id]!=''){
            		$where_array[]=$search_filed['img_type'][$img_type_id];
            	}
            }
            

            $img_title = trim($this->input->get('img_title'));
            if($img_title != '') {
                $search_arr['$img_title'] = $img_title;
                $where_array[] = "s_catalog = '{$img_title}'";
            }
            
            $keywords=trim($this->input->get('keywords'));
            if($keywords!=''){
                $search_arr['keywords']=$keywords;
                $where_array[]="tags like '%{$keywords}%'";
            }

        }

        if(is_array($where_array) and count($where_array)>0){
            $where=' WHERE '.join(' AND ',$where_array);
        }

        $pagesize = 10;
        $offset = $pagesize*($page-1);
        $limit = "LIMIT $offset,$pagesize";
        
        $product_num = $this->product_model->get_count_by_parm($where);
        $pages = pages($product_num, $page, $pagesize);
        $list_data = $this->product_model->get_data_by_parm($where, $limit);

        $this->load->library('form');
        
        $product_type_list = array('1'=>'全部', '2'=>'素材', '3'=>'非素材');
        $search_arr['product_type_sel'] = $this->form->select($product_type_list, $product_type_id, 'name="product_type_id"');
        //$img_type_list=array('1'=>'素描','2'=>'色彩','3'=>'速写','4'=>'设计','5'=>'创作','6'=>'照片');
        $img_type_list = $this->mis_imgmgr['imgmgr_level_1'];
        $search_arr['img_type_sel']=$this->form->select($img_type_list, $img_type_id, 'id="img_type" name="img_type_id"', '一级分类');
        
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('img_type_list', $img_type_list);
        $this->smarty->assign('img_title', $img_title);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true');
        $this->smarty->display('product/product_list.html');
    }

    /**
     * 测试接口
     * 
     */ 
    function get_product_list(){
        $request = $this->request_array;
        $response = $this->response_array;
        
        $search_arr['is_del']=0;
        $where_array[]="is_del=0";
        
        if(is_array($where_array) and count($where_array)>0){
        	$where=' WHERE '.join(' AND ',$where_array);
        }
        
        $pagesize = 10;
        $page = 1;
        $offset = $pagesize*($page-1);
        $limit = "LIMIT $offset,$pagesize";
        
        $result = array();
        $product_num = $this->product_model->get_count_by_parm($where);
        $pages = pages($product_num, $page, $pagesize);
        $list_data = $this->product_model->get_data_by_parm($where, $limit);
        
        $result = $list_data;
        
        $response['errno'] = 0;
        $response['data']['content'] = $result;

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

    //对要闻进行单条删除属性变更
    function del_one_ajax(){
        if(intval($_GET['id'])>0) {
        	// 更新时间戳
        	$this->redis->set($this->key_img, time());
            $id=$this->input->get('id');
            if($this->imgmgr_model->del_info($id)){
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
    
    
    //添加图片
    function imgmgr_add(){
        $this->load->library('form');
        //$img_type_list = array('1'=>'素描','2'=>'色彩','3'=>'速写','4'=>'设计','5'=>'创作','6'=>'照片');
        $img_type_list = $this->mis_imgmgr['imgmgr_level_1'];
        $img_type_sel=Form::select($img_type_list,$info['img_type'],'id="img_type" name="info[img_type]"','请选择');

        $this->smarty->assign('img_type_sel',$img_type_sel);
        $this->smarty->assign('img_title_sel',$img_title_sel);
        $this->smarty->assign('random_version', rand(100,999));
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('imgmgr/imgmgr_add.html');
    }
    
    //执行添加图片操作
    function imgmgr_add_do(){
    	$this->redis->set($this->key_img, time());
        $info = $this->input->post('info');
        $pic  = $this->input->post('pic');
        log_message('debug', '*****************[test]******************img_add_do');
        log_message('debug', $pic[0]);
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

        //$info['img_url'] = 'http://www.qqw21.com/article/UploadPic/2012-12/2012123185857829.jpg';
        $info['img_url'] = $pic[0];

        if( $info['listorder']!='' && $info['title'] != ''){
			//$info['img'] = !empty($pic) ? json_encode($pic) : '';
            if($this->imgmgr_model->create_info($info)){
                show_tips('操作成功','','','add');
            }else{
                show_tips('操作异常');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
    }
    
    //修改要闻
    function imgmgr_edit(){
        $this->load->library('form');
        $imgmgr_id = $this->input->get('id');
        $info = $this->imgmgr_model->get_info_by_id($imgmgr_id);
		//$info['img'] = !empty($info['img']) ? json_decode($info['img']) : array();

        //$img_type_list = array('1'=>'素描','2'=>'色彩','3'=>'速写','4'=>'设计','5'=>'创作','6'=>'照片');
        $img_type_list = $this->mis_imgmgr['imgmgr_level_1'];

        $img_type_sel=Form::select($img_type_list,$info['img_type'],'id="img_type" name="info[img_type]"','请选择');
        $this->smarty->assign('info',$info);
        $this->smarty->assign('img_type_sel',$img_type_sel);
        $this->smarty->assign('random_version', rand(100,999));
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('imgmgr/imgmgr_edit.html');
    }
    
    //执行修改要闻操作
    function imgmgr_edit_do(){
    	$this->redis->set($this->key_img, time());
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
        $info['img_url'] = $pic[0];
        if($info['listorder'] != '' && $info['title'] != '') {
			//$info['img'] = !empty($pic) ? json_encode($pic) : '';
            if($this->imgmgr_model->update_info($info, $id)){
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
