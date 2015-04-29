<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
function round_up($number, $precision = 2)
{
    $temp = $precision + 1;                                                                                                          
    $fig = (int) str_pad('1', $temp, '0');
    return number_format((ceil($number * $fig) / $fig), $precision, '.', '');
}

function round_down($number, $precision = 2)
{
    $temp = $precision + 1;
    $fig = (int) str_pad('1', $temp, '0');
    return number_format((floor($number * $fig) / $fig), $precision, '.', '');
}

/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string) {
	if(!is_array($string)) return addslashes($string);
	foreach($string as $key => $val) $string[$key] = new_addslashes($val);
	return $string;
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
	if(!is_array($string)) return stripslashes($string);
	foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
	return $string;
}

/**
 * 返回经addslashe处理过的字符串或数组
 * @param $obj 需要处理的字符串或数组
 * @return mixed
 */
function new_html_special_chars($string) {
	if(!is_array($string)) return htmlspecialchars($string);
	foreach($string as $key => $val) $string[$key] = new_html_special_chars($val);
	return $string;
}

/**
 * 安全过滤函数
 *
 * @param $string
 * @return string
 */
function safe_replace($string) {
	$string = str_replace('%20','',$string);
	$string = str_replace('%27','',$string);
	$string = str_replace('%2527','',$string);
	$string = str_replace('*','',$string);
	$string = str_replace('"','&quot;',$string);
	$string = str_replace("'",'',$string);
	$string = str_replace('"','',$string);
	$string = str_replace(';','',$string);
	$string = str_replace('<','&lt;',$string);
	$string = str_replace('>','&gt;',$string);
	$string = str_replace("{",'',$string);
	$string = str_replace('}','',$string);
	$string = str_replace('\\','',$string);
	$string = remove_xss($string);
	return $string;
}


/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $dot
 */
function str_cut($string, $length, $dot = '') {
	$strlen = strlen($string);
	if($strlen <= $length) return $string;
	$string = str_replace(array(' ','&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵',' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
	$strcut = '';
	if(strtolower(CHARSET) == 'utf-8') {
		$length = intval($length-strlen($dot)-$length/3);
		$n = $tn = $noc = 0;
		while($n < strlen($string)) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}
			if($noc >= $length) {
				break;
			}
		}
		if($noc > $length) {
			$n -= $tn;
		}
		$strcut = substr($string, 0, $n);
		$strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
	} else {
		$dotlen = strlen($dot);
		$maxi = $length - $dotlen - 1;
		$current_str = '';
		$search_arr = array('&',' ', '"', "'", '“', '”', '—', '<', '>', '·', '…','∵');
		$replace_arr = array('&amp;','&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;',' ');
		$search_flip = array_flip($search_arr);
		for ($i = 0; $i < $maxi; $i++) {
			$current_str = ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
			if (in_array($current_str, $search_arr)) {
				$key = $search_flip[$current_str];
				$current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
			}
			$strcut .= $current_str;
		}
	}
	return $strcut.$dot;
}

/**
 * xss过滤函数
 *
 * @param $string
 * @return string
 */
function remove_xss($string) { 
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);

    $parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');

    $parm2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');

    $parm = array_merge($parm1, $parm2); 

	for ($i = 0; $i < sizeof($parm); $i++) { 
		$pattern = '/'; 
		for ($j = 0; $j < strlen($parm[$i]); $j++) { 
			if ($j > 0) { 
				$pattern .= '('; 
				$pattern .= '(&#[x|X]0([9][a][b]);?)?'; 
				$pattern .= '|(&#0([9][10][13]);?)?'; 
				$pattern .= ')?'; 
			}
			$pattern .= $parm[$i][$j]; 
		}
		$pattern .= '/i';
		$string = preg_replace($pattern, '', $string); 
	}
	return $string;
}

/**
 * 获取请求ip
 *
 * @return ip地址
 */
function ip() {
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$ip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$ip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}

/**
 * 
 * @return 获取微妙数
 */
function get_cost_time() {
	$microtime = microtime ( TRUE );
	return $microtime - SYS_START_TIME;
}

/**
 * 计算程序耗费时间
 *
 * @return	int	单位ms
 */
function execute_time() {
	$stime = explode ( ' ', SYS_START_TIME );
	$etime = explode ( ' ', microtime () );
	return number_format ( ($etime [1] + $etime [0] - $stime [1] - $stime [0]), 6 );
}

/**
* 产生随机字符串
*
* @param    int        $length  输出长度
* @param    string     $chars   可选的 ，默认为 0123456789
* @return   string     字符串
*/
function random($length, $chars = '0123456789') {
	$hash = '';
	$max = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

/**
* 将字符串转换为数组
*
* @param	string	$data	字符串
* @return	array	返回数组格式，如果，data为空，则返回空数组
*/
function string2array($data) {
	if($data == '') return array();
	@eval("\$array = $data;");
	return $array;
}
/**
* 将数组转换为字符串
*
* @param	array	$data		数组
* @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
* @return	string	返回字符串，如果，data为空，则返回空
*/
function array2string($data, $isformdata = 1) {
	if($data == '') return '';
	if($isformdata) $data = new_stripslashes($data);
	return addslashes(var_export($data, TRUE));
}

/**
 * 获取当前页面完整URL地址
 */
function get_url() {
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? safe_replace($_SERVER['PHP_SELF']) : safe_replace($_SERVER['SCRIPT_NAME']);
	$path_info = isset($_SERVER['PATH_INFO']) ? safe_replace($_SERVER['PATH_INFO']) : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? safe_replace($_SERVER['REQUEST_URI']) : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.safe_replace($_SERVER['QUERY_STRING']) : $path_info);
	return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}

/**
 * @return 密码加密函数
 **/
function encrypt($character,$keys="Tra"){
	return md5(md5(md5('eof'.$character.$keys)));
} 

/**
 * 通过curl方式获取指定地址内容
 * @param $put_url 远程URL请求地址
 */		
function curl_get_contents($put_url,$get_cook=0){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $put_url);            //设置访问的url地址
	//curl_setopt($ch,CURLOPT_HEADER,1);            //是否显示头部信息
	if($get_cook==1){curl_setopt($ch,CURLOPT_COOKIE,get_domain_cookie());}
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 连接超时（秒） 
	curl_setopt($ch, CURLOPT_TIMEOUT, 2); // 执行超时（秒）
	curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);   //用户访问代理 User-Agent
	curl_setopt($ch, CURLOPT_REFERER,_REFERER_);        //设置 referer
	//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);      //跟踪301
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //返回结果
	$r = curl_exec($ch);
	curl_close($ch);
	return $r;
}

/**
 * 通过curl以POST方式获取指定地址内容
 * @param $put_url 远程URL请求地址
 */		
function curl_post_contents($put_url,$post_data,$post_cook=0){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $put_url);            //设置访问的url地址
	curl_setopt($ch, CURLOPT_POST, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
	if($post_cook==1){curl_setopt($ch,CURLOPT_COOKIE,get_domain_cookie());}
	//curl_setopt($ch,CURLOPT_HEADER,1);            //是否显示头部信息
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 连接超时（秒） 
	curl_setopt($ch, CURLOPT_TIMEOUT, 2); // 执行超时（秒）
	//curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);   //用户访问代理 User-Agent
	//curl_setopt($ch, CURLOPT_REFERER,_REFERER_);        //设置 referer
	//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);      //跟踪301
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //返回结果
	$r = curl_exec($ch);
	curl_close($ch);
	return $r;
}

/**
 * 把键值数据拼装为连接字符串
 */

function array_key_tostr($data_array){
    $data_str='';
    if(is_array($data_array)){
        $data_temp=array();
        foreach ($data_array as $key => $value) {
            $data_temp[]="{$key}={$value}";
        }
        $data_str=join('&',$data_temp);
    }
    return $data_str;
}

/**
 *把数组组装为连接字符串
**/
function array_tourl($str_arr){
    $result_str='';
    if (count($str_arr)>0 and is_array($str_arr)){
        $result_str=join('&',$str_arr);
    }
    return $result_str;
}

/**
*获取cookie 并url转码
**/
function get_domain_cookie(){
	$sue = $_COOKIE['SUE'];
	$sup = $_COOKIE['SUP'];
	if(strpos($sue, "=") !== false) $sue= urlencode($sue);
	if(strpos($sup, "=") !== false) $sup= urlencode($sup);
	$cookie = "SUE=$sue; SUP=$sup";
	return $cookie;
}

/**
*获取cookie
**/
function get_domain_cookies(){
	$sue = $_COOKIE['SUE'];
	$sup = $_COOKIE['SUP'];
	if(strpos($sue, "=") !== false){
		$sue= explode('&',$sue);
		foreach($sue as $vale){
			$sueval=explode('=',$vale);
			$sue_new[$sueval[0]]=$sueval[1];
		}
	}
	$cookie['sue']=$sue_new;
	if(strpos($sup, "=") !== false){
		$sup= explode('&',$sup);
		foreach($sup as $valp){
			$supval=explode('=',$valp);
			$sup_new[$supval[0]]=$supval[1];
		}
	}
	$cookie['sup']=$sup_new;
	return $cookie;
}

/**
 * 计算某个时间与当前时间相比，已经过去多久
 * @param $unixtime 当前时间的unix时间戳
 * @return 返回如：1天21小时5分4秒前
 */		
function havetime($unixtime){
	$second=time()-$unixtime;
	if($second<1){return "";}
	$day = floor($second/(3600*24));
	$second = $second%(3600*24);//除去整天之后剩余的时间
	$hour = floor($second/3600);
	$second = $second%3600;//除去整小时之后剩余的时间
	$minute = floor($second/60);
	$second = $second%60;//除去整分钟之后剩余的时间
	//返回字符串
	$timestr='前';
	if($second>0){$timestr=$second.'秒'.$timestr;}
	if($minute>0){$timestr=$minute.'分'.$timestr;}
	if($hour>0){$timestr=$hour.'小时'.$timestr;}
	if($day>0){$timestr=$day.'天'.$timestr;}
	
	return $timestr;
}

/**
 * 计算某个时间与当前时间相比，已经过去多久
 * @param $unixtime 当前时间的unix时间戳
 * @return 返回如：1天21小时5分4秒前
 */		
function showtime($unixtime){
	$time=time();
	$second=time()-$unixtime;
	if($second<1){return "";}
	$day = floor($second/(3600*24));
	$second = $second%(3600*24);//除去整天之后剩余的时间
	$hour = floor($second/3600);
	$second = $second%3600;//除去整小时之后剩余的时间
	$minute = floor($second/60);
	$second = $second%60;//除去整分钟之后剩余的时间
	//返回字符串
	
	$timestr='';
	if($second>0){$timestr=$second.'秒';}
	if($minute>0){$timestr=$minute.'分钟';}
	if($hour>0){$timestr=$hour.'小时';}
	if($day>0){$timestr=$day.'天';}
	if($day>10){$timestr='10天';}
	return $timestr.'前';
}


/**
 * 产生唯一字符串，用作tiket或token
 * @param int $uid 用户uid
 * @return str chars 产生的唯一标识
 */

function born_token($uid=''){
    $uniqids=uniqid('',true);
    $uid_str=md5(random(5,'abcdefghijklmnopqrstuvwxyx').$uid);
    $token=trim($uid_str.base64_encode($uniqids),'=');
    return $token;
}

/**
 * 以json格式输出字符串
 **/
function showjson($data){
	$callback=safe_replace(trim($_GET['callback']));
	if($callback!=''){
		$res_json=sprintf("%s(%s);",$callback,json_encode($data));
	}else{
		$res_json=json_encode($data);
	}
	exit($res_json);
}

/**
 * 分页函数
 *
 * @param $num 信息总数
 * @param $curr_page 当前分页
 * @param $perpage 每页显示数
 * @param $urlrule URL规则
 * @param $array 需要传递的数组，用于增加额外的参数
 * @param $setpages 显示的页码数量
 * @return 分页
 */
function pages($num, $curr_page, $perpage = 20, $urlrule = '', $array = array(),$setpages = 10) {
	if(defined('URLRULE') && $urlrule == '') {
		$urlrule = URLRULE;
		$array = $GLOBALS['URL_ARRAY'];
	} elseif($urlrule == '') {
		$urlrule = url_par('page={$page}');
	}
	$multipage = '';
	if($num > $perpage) {
		$page = $setpages+1;
		$offset = ceil($setpages/2-1);
		$pages = ceil($num / $perpage);
		$from = $curr_page - $offset;
		$to = $curr_page + $offset;
		$more = 0;
		if($page >= $pages) {
			$from = 2;
			$to = $pages-1;
		} else {
			if($from <= 1) {
				$to = $page-1;
				$from = 2;
			}  elseif($to >= $pages) {
				$from = $pages-($page-2);
				$to = $pages-1;
			}
			$more = 1;
		}
		$multipage .= '<a class="a1">'.$num.'条</a>';
		if($curr_page>0) {
			$multipage .= ' <a href="'.pageurl($urlrule, $curr_page-1, $array).'" class="a1">上一页</a>';
			if($curr_page==1) {
				$multipage .= ' <span>1</span>';
			} elseif($curr_page>6 && $more) {
				$multipage .= ' <a href="'.pageurl($urlrule, 1, $array).'">1</a>..';
			} else {
				$multipage .= ' <a href="'.pageurl($urlrule, 1, $array).'">1</a>';
			}
		}
		for($i = $from; $i <= $to; $i++) {
			if($i != $curr_page) {
				$multipage .= ' <a href="'.pageurl($urlrule, $i, $array).'">'.$i.'</a>';
			} else {
				$multipage .= ' <span>'.$i.'</span>';
			}
		}
		if($curr_page<$pages) {
			if($curr_page<$pages-5 && $more) {
				$multipage .= ' ..<a href="'.pageurl($urlrule, $pages, $array).'">'.$pages.'</a> <a href="'.pageurl($urlrule, $curr_page+1, $array).'" class="a1">下一页</a>';
			} else {
				$multipage .= ' <a href="'.pageurl($urlrule, $pages, $array).'">'.$pages.'</a> <a href="'.pageurl($urlrule, $curr_page+1, $array).'" class="a1">下一页</a>';
			}
		} elseif($curr_page==$pages) {
			$multipage .= ' <span>'.$pages.'</span> <a href="'.pageurl($urlrule, $curr_page, $array).'" class="a1">下一页</a>';
		} else {
			$multipage .= ' <a href="'.pageurl($urlrule, $pages, $array).'">'.$pages.'</a> <a href="'.pageurl($urlrule, $curr_page+1, $array).'" class="a1">下一页</a>';
		}
	}
	return $multipage;
}
/**
 * 返回分页路径
 *
 * @param $urlrule 分页规则
 * @param $page 当前页
 * @param $array 需要传递的数组，用于增加额外的方法
 * @return 完整的URL路径
 */
function pageurl($urlrule, $page, $array = array()) {
	if(strpos($urlrule, '~')) {
		$urlrules = explode('~', $urlrule);
		$urlrule = $page < 2 ? $urlrules[0] : $urlrules[1];
	}
	$findme = array('{$page}');
	$replaceme = array($page);
	if (is_array($array)) foreach ($array as $k=>$v) {
		$findme[] = '{$'.$k.'}';
		$replaceme[] = $v;
	}
	$url = str_replace($findme, $replaceme, $urlrule);
	$url = str_replace(array('http://','//','~'), array('~','/','http://'), $url);
	return $url;
}

/**
 * URL路径解析，pages 函数的辅助函数
 *
 * @param $par 传入需要解析的变量 默认为，page={$page}
 * @param $url URL地址
 * @return URL
 */
function url_par($par, $url = '') {
	if($url == '') $url = get_url();
	$pos = strpos($url, '?');
	if($pos === false) {
		$url .= '?'.$par;
	} else {
		$querystring = substr(strstr($url, '?'), 1);
		parse_str($querystring, $pars);
		$query_array = array();
		foreach($pars as $k=>$v) {
			if($k != 'page') $query_array[$k] = $v;
		}
		$querystring = http_build_query($query_array).'&'.$par;
		$url = substr($url, 0, $pos).'?'.$querystring;
	}
	return $url;
}

//把IP地址转换为数值型用于判断是否在某个IP段类
function myip2long($ip){  
   $ip_arr = explode('.',$ip);  
   $iplong = (16777216 * intval($ip_arr[0])) + (65536 * intval($ip_arr[1])) + (256 * intval($ip_arr[2])) + intval($ip_arr[3]);  
   return $iplong;  
}

/**
 * IE浏览器判断
 */
function is_ie() {
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if((strpos($useragent, 'opera') !== false) || (strpos($useragent, 'konqueror') !== false)) return false;
	if(strpos($useragent, 'msie') !== false) return true;
	return false;
}

/**
 * 根据图片http地址返回图片上传路径
 */
function get_old_filenames($url){
	$filenames=str_replace('http://storage.travel.sina.com.cn/event/','',$url);
	if($filenames!=''){
		return $filenames;
	}else{
		return '';
	}
}

function get_msg_by_errcode($result,$by=1,$param=''){
    if($result['error_code']>0){
        $CI =& get_instance();
        $CI->load->config("error_code", TRUE);
        $error_info=$CI->config->item('error_code', 'error_code');
        if($error_info[$result['error_code']]['error_code']!=''){
            $error_info=$error_info[$result['error_code']];
            if(is_array($param) && count($param)>0){
               $error_info['en']=vsprintf($error_info['en'],$param);
               $error_info['cn']=vsprintf($error_info['cn'],$param);
            }
            $result['error_code']=$error_info['error_code'];
            switch($by){
                case 2:
                    $result['error']=$error_info['en'];
                    break;
                case 3:
                    $result['error']=$error_info['cn'];
                    break;
                default:
            }
        }
    }
    return $result;
}

if(!function_exists('phone_encode'))
{
    function phone_encode($decrypted, Array $cfg)
    {
    	$CI =& get_instance();
    	$CI->load->library('mcrypt_3des');
    	return $CI->mcrypt_3des->en3des(trim($decrypted), $cfg);
    }
}
if(!function_exists('phone_decode'))
{
    function phone_decode($encrypted, Array $cfg)
    {
    	$CI =& get_instance();
    	$CI->load->library('mcrypt_3des');
    	return $CI->mcrypt_3des->de3des($encrypted, $cfg);
    }
}
if(!function_exists('phone_md5')) {
	function phone_md5($mobile) {
		$modone  = $mobile%3;
		$modtwo  = $mobile%2;
		$rnd_len = $modone+$modtwo+4;
		$mobile_arr = str_split($mobile,$rnd_len);//截取位数
		$mobile_str = '';
		foreach ($mobile_arr as $key => $value) {
			$mobile_str .= $value;
			if($key == 0){$mobile_str .= $rnd_len;}
		}
		return md5($mobile_str);		
	}
}
if(!function_exists('get_username'))
{
    function get_username($uid)
    {
		if ($uid == 0) return '管理员';
        $CI =& get_instance();
        $q = $CI->db->select('sname')->get_where('user', array('id'=>$uid));
		$r = $q->row()->sname;
        return $r;
    }
}
