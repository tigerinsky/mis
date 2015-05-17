<?php
$path = '/home/meihua/athena/app/amytian/admin.amytian.com/uploadImages/images/';
$req = 'http://182.92.212.76:8081/uploadImages/post.php';
//$req = 'http://182.92.212.76/upload/tweet_pic';

header('content-type:text/html;charset=utf8');

$ch = curl_init();

//加@符号curl就会把它当成是文件上传处理
$data = array('img'=>'@'. 'images/img3.jpg;type=image/jpeg;');
curl_setopt($ch,CURLOPT_URL,$req);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POST,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
$result = curl_exec($ch);
curl_close($ch);
echo json_decode($result);



exit;
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
//					echo "<b><font color='red'>文件名：</font></b>",$file,"<br><hr>";
					upImg($path."/".$file."/",$req);
				}
				else
				{
					if($file!="." && $file!=".." && $file != null)
					{
						//获取后缀名
						$extName = substr(strrchr($file, '.'), 1);
						//文件名
						$fileName = pathinfo($file);
						$fileName = $fileName['filename'];
						var_dump(upload_file($req,$fileName,$path,$extName));
					}
				}
			}
			closedir($dh);
		}
	}
}

function upload_file($url,$filename,$path,$type){
	$data = array(
		'file'=>'@'.realpath($path).";type=image/".$type.";filename=".$filename
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true );
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_getinfo($ch);
	$return_data = curl_exec($ch);
	curl_close($ch);
	return $return_data;
}

