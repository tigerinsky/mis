<?php
@ini_set('post_max_size', '2020M');
@ini_set('upload_max_filesize', '500M');
$path = '/home/meihua/athena/app/amytian/admin.amytian.com/uploadImages/images/';
$req = 'http://api.meiyuanbang.com/upload/tweet_pic';

upImg($path,$req);

//上传图片
function upImg($path,$req)
{
	if(is_dir($path))
	{
		if ($dh = opendir($path))
		{
			while (($file = readdir($dh)) !== false)
			{
				if((is_dir($path."/".$file)) && $file!="." && $file!="..")
				{
					upImg($path."/".$file."/",$req);
				}
				else
				{
					if($file!="." && $file!=".." && $file != null)
					{
						//获取后缀名
//						$extName = substr(strrchr($file, '.'), 1);
						//文件名
//						$fileName = pathinfo($file);
//						$fileName = $fileName['filename'];
						$rs = json_decode(upload_file($req,$path.$file),true);
						if($rs['errno'] == 0)
						{
//							echo json_encode($rs['data']['img']);
							setDB(json_encode($rs['data']['img']),$file);
						}
					}
				}
			}
			closedir($dh);
		}
	}
}

function upload_file($url,$filename){
	$data = array(
		'file'=>'@'.realpath($filename)
//		'file'=>'@'.realpath($path).";type=image/".$type.";filename=".$filename
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
	curl_setopt($ch, CURLOPT_POST, true );
	@curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_getinfo($ch);
	$return_data = curl_exec($ch);
	curl_close($ch);
	return $return_data;
}


function setDB($arr,$file)
{
	$con = @mysql_connect("rdsa3ztcm25jcbq937ryh.mysql.rds.aliyuncs.com","myb","MhxzKhl");
	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db("myb", $con);
	mysql_query("set names 'utf8mb4'");
	echo "INSERT INTO ci_tweet_offline (uid, img, img_oname) VALUES (0, '$arr',)";
	mysql_query("INSERT INTO ci_tweet_offline (uid, img, img_oname) VALUES (0, '$arr','$file')");

	mysql_close($con);
}

