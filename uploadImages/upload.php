<?php
$path = '/home/meihua/athena/app/amytian/admin.amytian.com/uploadImages/images/';
$req = 'http://182.92.212.76/upload/tweet_pic';

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
						echo $req."@".$path.$file.";type=".$extName;
//						var_dump(_curl_post($req,"@".$path.$file.";type=".$extName));
					}
				}
			}
			closedir($dh);
		}
	}
}

function _curl_post($url, $data){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


	if( ($data = curl_exec($ch)) === false)
	{
		echo 'error: ' . curl_error($ch);
	}
	curl_close($ch);
	return $data;
}
