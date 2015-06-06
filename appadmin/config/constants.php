<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


// 价格小数点后几位
define('PRICE_PRECISION',		1);
define('PRICE_UNIT_PRECISION',	2);
define('PRICE_TAX_RATE',		0.03477);

//tips  
/*
 *  替换原来的                                                                                                                       
    const TIPS_STYLE_ID     = 1;
    const TIPS_TYPE_ID      = 2;
    const TIPS_KIND_ID      = 3;
    const TIPS_POSITION_ID  = 4;
    const TIPS_STAGE_ID     = 5;
 * 标准间类型、标准间风格、材料类型、材料位置、标准间焦点图阶段
 */
define('TIPS_ROOM_STYLE',        1);
define('TIPS_ROOM_TYPE',         2);
define('TIPS_MATERIAL_TYPE',     3);
define('TIPS_MATERIAL_POSITION', 4);
define('TIPS_FOCUS_STAGE',       5);
/* End of file constants.php */
/* Location: ./application/config/constants.php */
define('ULR_PROX',       'http://mis.meiyuanbang.com');

