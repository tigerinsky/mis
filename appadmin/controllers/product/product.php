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
        $this->load->model('product/product_model', 'product_model');
        $this->load->model('imgmgr/imgmgr_model', 'imgmgr_model');
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
            			'1'=>"f_catalog='".$this->mis_imgmgr['imgmgr_level_1']['1']."'",
            			'2'=>"f_catalog='".$this->mis_imgmgr['imgmgr_level_1']['2']."'",
            			'3'=>"f_catalog='".$this->mis_imgmgr['imgmgr_level_1']['3']."'",
            			'4'=>"f_catalog='".$this->mis_imgmgr['imgmgr_level_1']['4']."'",
            			'5'=>"f_catalog='".$this->mis_imgmgr['imgmgr_level_1']['5']."'",
            			'6'=>"f_catalog='".$this->mis_imgmgr['imgmgr_level_1']['6']."'",
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
     * 测试接口
     *
     */
    function get_product_by_tid(){
    	
    	// 获取tweet id
//     	$this->load->library('uidclient');
//     	$tid = strval($this->uidclient->get_id());
//     	echo $tid;
    	
    	header("Content-type:text/html;charset=utf-8");
    	$request = $this->request_array;
    	$response = $this->response_array;
    	
    	$tid = $request['tid'];
    	
    	
    	$info = $this->product_model->get_info_by_tid($tid);
    	print_r($info);
    	exit;
    	
    	$f_catalog = $info['f_catalog'];
    	$s_catalog = $info['s_catalog'];
    	
    	//$info = $this->format_one($info);
    	//获取分类
    	$this->load->helper('extends');
    	$catalog_data = json_decode(curl_get_contents("http://api.meiyuanbang.com/catalog/get"), true);
    	$catalog_data = $catalog_data['data'];
    	//$catalog_data = json_encode($catalog_data);
    	
    	foreach ($catalog_data as $key1=>$value1) {
    		if ($value1['name'] == $f_catalog) {
    			$catalog_data_2 = $value1['catalog'];
    			foreach ($catalog_data_2 as $key2=>$value2) {
    				if ($value2['name'] == $s_catalog) {
    					$tag_group = $value2['tag_group'];
    					goto end;
    				}
    			}
    		}
    	}
    	
    	end:
    	// 处理tag
    	$tag_group = json_encode($tag_group);
    	//print_r($tag_group);
//     	foreach ($tag_group as $k=>$v) {
//     		$tag = $v['tag'];
//     		print_r($tag);
//     	}
    	
    	
//     	$result = array();
    	
//     	$result = $catalog_data;
    	
//     	$response['errno'] = 0;
//     	$response['data']['content'] = $result;
    	
//     	$this->renderJson($response['errno'], $response['data']);
    	
    }
    
    
    /**
     * 测试接口
     *
     */
    function get_product_by_rid(){
    	header("Content-type:text/html;charset=utf-8");
    	$request = $this->request_array;
    	$response = $this->response_array;
    	
    	$rid = $request['rid'];
    	
    	
    	$info = $this->product_model->get_data_by_rid($rid);
    	print_r($info);
    	exit;
    	
//     	$response['errno'] = 0;
//     	$response['data']['content'] = $result;
    	
//     	$this->renderJson($response['errno'], $response['data']);
    	
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

    //作品删除操作
    function del_one_ajax() {
        if(intval($_GET['id'])>0) {
            $id = $this->input->get('id');
            if($this->product_model->del_info($id)){
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
    
    
    /**
     * 测试接口
     */
    function get_model_data() {
    	$tid = 13627;
    	$info = $this->product_model->get_info_by_tid($tid);
    	$info = $this->format_one($info);
    	
    	
    	$imgmgr_id = 5;
    	$info_imgmgr = $this->imgmgr_model->get_info_by_id($imgmgr_id);
    	
    	$result['product'] = $info;
    	$result['imgmgr'] = $info_imgmgr;
    	$response['errno'] = 0;
    	$response['data']['content'] = $result;
    	
    	$this->renderJson($response['errno'], $response['data']);
    }
    
    /**
     * 格式化函数
     * ＠param array $info
     * @return array $format_info
     */
    private function format_one($info) {
    	$format_info = array();
    	$format_info['tid'] = $info['tid'];
    	$format_info['uid'] = $info['uid'];
    	$format_info['type'] = $info['type'];
    	
    	$img_type_list = $this->mis_imgmgr['imgmgr_level_1'];
    	foreach ($img_type_list as $key=>$value) {
    		if ($value == $info['f_catalog']) {
    			$format_info['img_type'] = $key;
    			break;
    		}
    	}
    	$format_info['title'] = $info['s_catalog'];
    	
    	$radio_data = array();
    	
    	/*
    	
    	$tag_list = explode(',', $info['tags']);
    	if (in_array("男", $tag_list)) {
    		$radio_data['sex'] = "男";
    	} elseif (in_array("女", $tag_list)) {
    		$radio_data['sex'] = "女";
    	}
    	if (in_array("青年", $tag_list)) {
    		$radio_data['age'] = "青年";
    	} elseif (in_array("中年", $tag_list)) {
    		$radio_data['age'] = "中年";
    	} elseif (in_array("老年", $tag_list)) {
    		$radio_data['age'] = "老年";
    	}
    	if (in_array("正面", $tag_list)) {
    		$radio_data['angle'] = "正面";
    	} elseif (in_array("侧面", $tag_list)) {
    		$radio_data['angle'] = "侧面";
    	} elseif (in_array("1/3", $tag_list)) {
    		$radio_data['angle'] = "1/3";
    	} elseif (in_array("3/4", $tag_list)) {
    		$radio_data['angle'] = "3/4";
    	}
    	 */
    	$format_info['radio_data'] = $radio_data;
    	
    	
    	return $format_info;
    }
    
    
    //修改作品分类
    function product_edit(){
        $this->load->library('form');
        $tid = $this->input->get('id');
        
//     	$request = $this->request_array;
//     	$response = $this->response_array;
//         $tid = $request['id'];
        
        $info = $this->product_model->get_info_by_tid($tid);
        
        $f_catalog = $info['f_catalog'];
        $s_catalog = $info['s_catalog'];
        $tag_list = explode(',', $info['tags']);
        
        $info = $this->format_one($info);
        //echo json_encode($info);
        
        
        //获取分类
        $this->load->helper('extends');
        $catalog_data = json_decode(curl_get_contents("http://api.meiyuanbang.com/catalog/get"), true);
        $catalog_data = $catalog_data['data'];
        //$catalog_data = json_encode($catalog_data);
         
        foreach ($catalog_data as $key1=>$value1) {
        	if ($value1['name'] == $f_catalog) {
        		$catalog_data_2 = $value1['catalog'];
        		foreach ($catalog_data_2 as $key2=>$value2) {
        			if ($value2['name'] == $s_catalog) {
        				$tag_group = $value2['tag_group'];
        				goto end;
        			}
        		}
        	}
        }
        
        end:
        // 处理tag
        $tag_group = $tag_group;
        $tag_count = count($tag_group);

        //$img_type_list = array('1'=>'素描','2'=>'色彩','3'=>'速写','4'=>'设计','5'=>'创作','6'=>'照片');
        $img_type_list = $this->mis_imgmgr['imgmgr_level_1'];

        $img_type_sel=Form::select($img_type_list, $info['img_type'], 'id="img_type" name="info[img_type]"', '一级分类');
        $this->smarty->assign('info',$info);
        $this->smarty->assign('tag_group',$tag_group);
        $this->smarty->assign('tag_count',$tag_count);
        $this->smarty->assign('tag_list',$tag_list);
        $this->smarty->assign('img_type_sel',$img_type_sel);
        $this->smarty->assign('random_version', rand(100,999));
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display('product/product_edit.html');
    }
    
    //执行修改作品操作
    function product_edit_do(){
        $tid = $this->input->post('tid');
//         $sex = $this->input->post('sex');
//         $age = $this->input->post('age');
//         $angle = $this->input->post('angle');
        $info = $this->input->post('info');
        
        $tag_array = array();
        $tag_count = $this->input->post('tag_count');
        $data_list = range(1,intval($tag_count));
        foreach ($data_list as $index) {
        	$item = "tag".strval($index);
        	$tmp_tag = $this->input->post($item);
        	if ($tmp_tag != '-1') {
        		array_push($tag_array, $tmp_tag);
        	}
        }
        
        
        $new_info['f_catalog'] = $this->mis_imgmgr['imgmgr_level_1'][$info['img_type']];
        $new_info['s_catalog'] = $info['title'];
        $new_info['type'] = $info['type'];
        
        $new_info['tags'] = implode(',', $tag_array);
        
        if($new_info['f_catalog'] != '' && $new_info['s_catalog'] != '') {
            if($this->product_model->update_info($new_info, $tid)){
                show_tips('操作成功','','','edit');
            }else{
                show_tips('操作异常，请检测');
            }
        }else{
            show_tips('数据不完整，请检测');
        }
        
    }
    
    
    //单条删除要闻
    function tweet_del_one_ajax() {
        $tweet_id = intval($this->input->get('id'));
        $ret=0;
        if($tweet_id>0){
            if($this->tweet_model->del_info($tweet_id)){
                $ret=1;
            }
        }
        echo $ret;
    }

}
