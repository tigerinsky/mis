<?php
$path = '/home/meihua/athena/app/amytian/admin.amytian.com/uploadbatch/images/';
echo 1;
var_dump(upImg($path));


//上传图片
function upImg($path)
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
					upImg($path."/".$file."/");
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