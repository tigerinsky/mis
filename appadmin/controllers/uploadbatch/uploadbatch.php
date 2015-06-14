<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 二级分类图片管理
 */
class uploadbatch extends MY_Controller{

	public static $cls;

	function __construct(){
		parent::__construct();
		$this->dbr=$this->load->database('dbr',TRUE);
//		$this->load->config('mis_imgmgr',TRUE);
//		$this->mis_imgmgr = $this->config->item('mis_imgmgr');
		// $this->mis_imgmgr['imgmgr_level_1']
		//获取分类
		$this->load->helper('extends');
		if(!self::$cls){
			$class = json_decode(curl_get_contents("http://api.meiyuanbang.com/catalog/get"),true);
			self::$cls = $class['data'];
		}
		$this->load->library('uidclient');
		$this->load->library('redis');
		$this->key_img = 'mis_img_timestamp';
		$this->load->model('uploadbatch/uploadbatch_model','uploadbatch_model');
	}

	//默认调用控制器
	function index(){
		$this->upload_list();
	}

	private function upload_list(){
		$this->load->library('form');
		$page=$this->input->get('page');
		$page = max(intval($page),1);
		$dosearch=$this->input->get('dosearch');

		$search_arr['is_del']=0;
		$where_array[]="is_ok=0";

		if($dosearch=='ok'){

			$keywords=trim($this->input->get('keywords'));

			if($keywords!=''){
				$search_arr['keywords']=$keywords;
				$where_array[]="content like '%{$keywords}%'";
			}
		}

		if(is_array($where_array) and count($where_array)>0){
			$where=' WHERE '.join(' AND ',$where_array);
		}

		$pagesize=20;
		$offset = $pagesize*($page-1);
		$limit="LIMIT $offset,$pagesize";

		$user_num=$this->uploadbatch_model->get_count_by_parm($where);
		$pages=pages($user_num,$page,$pagesize);
		$list_data=$this->uploadbatch_model->get_data_by_parm($where,$limit);
		if(!empty($list_data))
		{
			foreach($list_data as $key => $value)
			{
				$list_data[$key]['img'] = json_decode(stripslashes($value['img']),true);
			}
		}
		//一二三级分类
		$class_1 = array();
		foreach(self::$cls as $k=>$v)
		{
			array_push($class_1,array('id'=>$v['id'],'name'=>$v['name']));
		}
		$this->load->library('form');
		$img_type_list = $this->mis_imgmgr['imgmgr_level_1'];
		$this->smarty->assign('img_type_list',$img_type_list);
		$this->smarty->assign('list_data',$list_data);
		$this->smarty->assign('pages',$pages);
		$this->smarty->assign('class_1',$class_1);
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('uploadbatch/uploadbatch_list.html');
	}

	public function edit_one()
	{
		$tid=$this->input->post('id');
		$val=$this->input->post('val');
		if(!is_numeric($tid) || empty($val))
		{
			echo 0;exit;
		}
		$info = $this->uploadbatch_model->update_info(array('content'=>$val),$tid);
		if($info)
			echo 1;
		else echo 0;
	}

	public function addClass()
	{
		$id=$this->input->post('id');
		$tag=$this->input->post('tag');
		if(empty($id)) return "";
		$arr = array();
		if($tag == "")
		{
			foreach(self::$cls as $key=>$value)
			{
				if($id == $value['id'])
				{
					foreach($value['catalog'] as $k=>$v)
					{
						array_push($arr,array('id'=>$v['id'],'name'=>$v['name']));
					}
					echo json_encode($arr);
					break;
				}
			}
		}
		else
		{
			foreach(self::$cls as $key=>$value)
			{
					foreach($value['catalog'] as $k=>$v)
					{
						if($id == $v['id'])
						{
							foreach($v['tag_group'] as $kn=>$vn)
							{
								$arr[$vn['name']] = array();
								foreach($vn['tag'] as $vp)
								{
									if($vp)
									array_push($arr[$vn['name']],array('name'=>$vp));
								}
							}
							if(count($arr) > 0)
							{
								echo json_encode($arr);
								break;
							}
						}
					}
			}
		}
	}

	public function editClass()
	{
		$tid=$this->input->post('id');
		$val=$this->input->post('val');
		$class=$this->input->post('class');
		if(!is_numeric($tid) || empty($val) || !is_numeric($class))
		{
			echo 0;exit;
		}
		$data = array();
		if($class == 3){
			if($val != ',')
			{
				$tag = str_replace("undefined","",$val);
				$data['tags']	= substr($tag,0,strlen($tag)-1);
			}else{
				$data['tags'] = "";
			}

		}
		elseif($class == 2){
			$data['s_catalog']	= $val;
		}elseif($class == 1){
			$data['f_catalog']	= $val;
		}
		$data['ctime']	= time();
		$info = $this->uploadbatch_model->update_info($data,$tid);
		if($info)
			echo 1;
		else echo 0;

	}

	//获取二级分类名称
	private function getClassName($id)
	{
		foreach(self::$cls as $key=>$value)
		{
				foreach($value['catalog'] as $k=>$v)
				{
					if($id == $v['id'])
					{
						return $v['name'];
						break;
					}
				}
			}

	}

	public function pushData()
	{
		//获取原始数据
         $list = $this->uploadbatch_model->get_data_by_parm(" where is_ok = 0");
         $num = 0;
         if(!empty($list))
		         {
		             foreach($list as $key=>$value)
			             {
			                 if( $value['f_catalog']!="" && $value['s_catalog']!="")
				                 {
                                     $tid = strval($this->uidclient->get_id("tweet"));
                                     $res_id = strval($this->uidclient->get_id("resource"));
                     $img = json_decode($value['img'],true);
                     $img['content'] = $value['content'];
                     $img = array($img);
                     $data = array(
					      'tid'       => $tid,
                         'uid'       => 1,
                         //'type'        => $value['type'],
                         'type'      => 1,
                         'f_catalog' => $value['f_catalog'],
                         'content'   => '',
                         'ctime'     => $value['ctime'],
                         //'img'       => json_encode($img,JSON_UNESCAPED_UNICODE),
                         's_catalog' => $value['s_catalog'],
                         'tags'      => $value['tags'],
                         'resource_id'  => $res_id
                     );
                     $b_tweet = $this->uploadbatch_model->offline_create_info($data);
                     if (!$b_tweet) {
                         log_message('error', 'offline_create_info error.');
                         return ;
                     }
                     $arr_res = array(
                         'rid'  => $res_id,
                         'img'  => $value['img'],
                         'description'  => $value['content'],
                     );
                     $b_res = $this->uploadbatch_model->insert_resource_info($arr_res);
                     if($b_tweet && $b_res) {
					     $num++;
                         $this->uploadbatch_model->update_info(array('is_ok'=>1),$value['tid']);
                     }
                 }
             }
         }
         echo 1;
	}

}
