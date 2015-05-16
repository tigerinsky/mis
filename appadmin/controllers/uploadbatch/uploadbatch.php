<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 二级分类图片管理
 */
class uploadbatch extends MY_Controller{

	function __construct(){
		parent::__construct();
		$this->dbr=$this->load->database('dbr',TRUE);
		$this->load->config('mis_imgmgr',TRUE);
		$this->mis_imgmgr = $this->config->item('mis_imgmgr');
		// $this->mis_imgmgr['imgmgr_level_1']
		$this->load->library('redis');
		$this->key_img = 'mis_img_timestamp';
		$this->load->model('imgmgr/imgmgr_model','imgmgr_model');
	}

	//默认调用控制器
	function index(){
		$this->upload_add();
	}

	//显示图片列表，同时有检索功能
	private function upload_add(){
		$this->load->library('form');
		$this->smarty->assign('show_dialog','true');
		$this->smarty->display('upload/upload_add.html');
	}

	public function doUpload()
	{
		if($_POST)
		{
			$path  = $this->input->post('path');
			//上传图片
			$picList = $this->upImg($path);
			var_dump($picList);exit;

			$data_values = "";
			$path  = $this->input->post('path');
			$filename = $_FILES['file']['tmp_name'];
			if (empty ($filename)) {
				show_tips('请选择您要导入的csv文件');
			}
			$handle = fopen($filename, 'r');
			$result = input_csv($handle); //解析csv
			$len_result = count($result);
			if($len_result==0){show_tips('没有任何数据');
			}
			for ($i = 1; $i < $len_result; $i++) { //循环获取各字段值
				$name = iconv('gb2312', 'utf-8', $result[$i][0]); //中文转码
				$sex = iconv('gb2312', 'utf-8', $result[$i][1]);
				$age = $result[$i][2];
				$data_values .= "('$name','$sex','$age'),";
			}
			$data_values = substr($data_values,0,-1); //去掉最后一个逗号
			fclose($handle); //关闭指针
			$query = mysql_query("insert into student (name,sex,age) values $data_values");//批量插入数据表中
			if($query){
				show_tips('导入成功');			}else{
				show_tips('导入失败');			}

		}
	}

	private function upImg($path)
	{
		if(is_dir($path))
		{
			if ($dh = opendir($path))
			{
				while (($file = readdir($dh)) !== false)
				{
					if((is_dir($path."/".$file)) && $file!="." && $file!="..")
					{
						echo "<b><font color='red'>文件名：</font></b>",$file,"<br><hr>";
						$this->upImg($path."/".$file."/");
					}
					else
					{
						if($file!="." && $file!="..")
						{
							echo $file."<br>";
						}
					}
				}
				closedir($dh);
			}
		}
	}


}
