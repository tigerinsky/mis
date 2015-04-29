<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<title>提示窗口</title>
<style type="text/css">
a:link,a:visited{text-decoration:none;color:#0068a6}
a:hover,a:active{color:#ff6600;text-decoration: underline}
#showMsg{border: 5px solid #c3c3c3; zoom:1; width:440px; height:230px;margin:162px auto 0;}
#msg_header{height:50px;border-bottom:1px solid #e5e5e5; background-color:#f7f7f7;}
#msg_header .pt{margin:10px 0 0 10px;}
#showMsg .msg_center{margin:30px 5px 0 95px;height:65px;padding-left:50px;background: url(<?php echo config_item('exten_pub_path');?>images/msg_img/msg_ico.png) no-repeat 0px 0px;}
#showMsg .msg_info{position:relative;left:20px;top:18px;font-weight:bold;}
#showMsg .msg_bottom{text-align:center; font-size:14px; font-size:12px; font-weight:bold;}
</style>
<script type="text/javaScript" src="<?php echo config_item('exten_pub_path');?>js/jquery.min.js"></script>
<script language="JavaScript" src="<?php echo config_item('exten_pub_path');?>js/admin_common.js"></script>
</head>
<body>
<div id="showMsg">
	<?php if ($dialog){?><script style="text/javascript">window.top.right.location.reload();window.top.art.dialog({id:"<?php echo $dialog?>"}).close();</script><?php }?>
	<div id="msg_header"><img src="<?php echo config_item('exten_pub_path');?>images/msg_img/msg_title.png" class="pt"></div>
    <div class="msg_center"><span class="msg_info"><?php echo $msg?></span></div>
    <div class="msg_bottom">
    <?php if($url_forward=='goback') {?>
	<a href="javascript:history.back();" >[返回上一页]</a>
	<?php } elseif($url_forward=="close") {?>
	<input type="button" name="close" value="关闭" onClick="window.close();">
	<?php } elseif($url_forward=="blank") {?>
	
	<?php } elseif($url_forward){
		if(intval($ms)==0){$ms=1250;}
	?>
		<a href="<?php echo $url_forward?>">如果没有自动跳转,请点击这里</a>
		<script language="javascript">setTimeout("redirect('<?php echo $url_forward?>');",<?php echo $ms?>);</script> 
	<?php }?>
		<?php if($returnjs) { ?> <script style="text/javascript"><?php echo $returnjs;?></script><?php } ?>
    </div>
</div>
<script style="text/javascript">
	function middle_msg(msgName){
		var _scrollHeight = $(document).scrollTop(),//获取当前窗口距离页面顶部高度
		_windowHeight = $(window).height(),//获取当前窗口高度
		_windowWidth = $(window).width(),//获取当前窗口宽度
		_posiTop = (_windowHeight - 230)/2 + _scrollHeight;
		$("#"+msgName).css({"margin-top":_posiTop + "px"});//设置position
	}
	middle_msg('showMsg');
	function close_dialog() {
		window.top.right.location.reload();window.top.art.dialog({id:"<?php echo $dialog?>"}).close();
	}
	function redirect(url){
		window.location.href=url;
	}
</script>
</body>
</html>