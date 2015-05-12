<?php
/**
 * 加载sdk包以及错误代码包
 */

require_once( BASEPATH.'third_party/oss/sdk.class.php' );

/**
 * $acl = ALIOSS::OSS_ACL_TYPE_PRIVATE;
 * $acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ;
 * $acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ_WRITE;
 */
class Oss {
    private $CI;
    private $oss_service;
    private $file_ext = array('jpg', 'png', 'gif', 'JPG', 'PNG', 'GIF');
    private $project_arr = array('meiyuanbangapp','myb-img','jinglietou','hongzuan','lanjinger','lanjingshandong','lanjinghenan', 'lanjingapp');
    private $file_size   = 409600; //1024*400;

    //这些样式需在Ossbucket定义
    private $image_style  = array(
        "thumbnail"       => "r180-135",
        "bmiddle"   => "r400-300", 
        "big"       => "r-mark",
        "big_watermark" => "mark",
    );
    
    //水印
    private $watermark_object;
    private $watermark_transparency = 20;

    private $watermark_arr = array(
        "watermark_110_31"   => "common/201410281810_544f6bad55dba.png",   
    );

    const OSS_ACCESS_ID      = "z2BPrGgOY9gFKEjx";
    const OSS_ACCESS_KEY     = "BI2egA7gIRaSgBiBUMef4gsNgmZabK";
    const OSS_DEFAULT_BUCKET = "myb-img";
    const OSS_IMG_URL        = "http://img.tianyi2000.com/";

    public function __construct($config = array()){
        $this->CI =& get_instance();
        $this->CI->load->library('xml');
        $this->CI->load->helper('extends');

        $this->CI->load->config('upload', TRUE);
        $this->upload_config=$this->CI->config->item('upload');

        $this->_verify_project();
		$this->_init($config);
        $this->oss_service = new ALIOSS(self::OSS_ACCESS_ID, self::OSS_ACCESS_KEY);
    }
	
	private function _init(Array $config) {
		if (empty($config)) return;
        isset($config['size'])      && $this->file_size = $config['size'];
        isset($config['ext'])       && $this->file_ext = $config['ext'];	
	}
    /**
     * 魔术方法，调用原生的方法
     * @param string $name list_bucket,create_bucket,delete_bucket,set_bucket_acl,get_bucket_acl
     * @param array $args
     * @return mix
     */
    public function __call($name, $args) {
        $r = call_user_func_array(array($this->oss_service, $name), $args);
        if ($r->status == 200) {
            return $r->body ? $this->CI->xml->decode($r->body) : "";
        }
    }
    
    /**
     * $this->oss->upload($_FILES['pic'])
     *array(5) {
     * ["name"]=>
     *  string(36) "20111007191900_ZnwZf.thumb.600_0.jpg"
     *  ["type"]=>
     *  string(10) "image/jpeg"
     *  ["tmp_name"]=>
     *   string(24) "D:\xampp\tmp\php6C3E.tmp"
     *  ["error"]=>
     *  int(0)
     *  ["size"]=>
     *  int(37413)
     * }
     */
    /**
     * 上传图片
     * @param array  $_FILES
     * @param string  图片目录
     * @return mix string("/design/201410202206_544516f6b957b.jpg") or false
     */
    public function upload($field = "pic", $param = array('dir' => 'default', 'tag' => '', 'manual' => FALSE, 'header' => array())) {
        $file_arr = $_FILES[$field];
        if ((isset($file_arr['error']) && $file_arr['error'] != 0) || $file_arr['tmp_name'] == '') {
            switch ($file_arr['error']) {
                case 1:
                    $error_code = 20501;
                    break;
                case 2:
                    $error_code = 20502;
                    break;
                case 3:
                    $error_code = 20503;
                    break;
                case 4:
                    $error_code = 20504;
                    break;
                case 6:
                    $error_code = 20506;
                    break;
                case 7:
                    $error_code = 20507;
                    break;
                default:
                    $error_code = 20500;
            }
            return get_msg_by_errcode(array("error_code" => $error_code), 2);
        }
        //验证文件大小；
        if ($file_arr['size'] > $this->file_size) {
            return get_msg_by_errcode(array("error_code" => 20510), 2);
        }
        $date = date("Ymd");
        $dir = $this->upload_config['appname'] . '/' . rtrim($param['dir'], '/') . '/' . $date . '/';

        $file_format = pathinfo($file_arr['name'], PATHINFO_EXTENSION);
        if (!in_array($file_format, $this->file_ext)) {
            return get_msg_by_errcode(array("error_code" => 20511), 2);
        }


        //图片名称是否自动生成
        if (isset($param['manual']) && !empty($param['manual'])) {
            $object = $dir . $file_arr['name'];
        } else {
            $object = $dir . uniqid($param['tag'] . date('His') . '_') . '.' . $file_format;
        }
        $content = file_get_contents($file_arr['tmp_name']);
        if ($content == "") {
            return get_msg_by_errcode(array("error_code" => 20512), 2);
        }
		$header = array('Cache-control' => 'max-age=259200');
		if (isset($param['header']) && !empty($param['header'])) {
			$header = $param['header'];
		}
        $options = array(
            'content' => $content,
            'length' => strlen($content),
			ALIOSS::OSS_HEADERS => $header,
        );
        $response = $this->oss_service->upload_file_by_content(self::OSS_DEFAULT_BUCKET, $object, $options);

        if ($response->isOk()) {
            return self::OSS_IMG_URL . $object;
        } 
    }

    /**
     *  设置图片样式
     */
    public function set_style($pic_url, $style) {

        return $pic_url . '@' . $style . '.' . pathinfo($pic_url, PATHINFO_EXTENSION);
    }

    public function set_style_name($pic_url, $type) {
        $style_name = $this->image_style[$type];
        return $pic_url . "@!" . $style_name;
    }

    public function set_watermark_format ($transparency = 20) {
        $this->watermark_transparency = $transparency;
    } 

    public function object_encode($watermark_type = "production_110_31") {
        if (array_key_exists($watermark_type, $this->watermark_arr)) {
            $this->watermark_object = base64_encode($this->watermark_arr[$watermark_type]);
            return $this->watermark_object;
        }
    }

    private function _verify_project() {
        if (!in_array($this->upload_config['appname'], $this->project_arr, TRUE)) {
            show_error("Please config the right project name.");
        }
    }

    private function _format($response) {
        echo '|-----------------------Start----------------------------------------------------------------------------------------------
    -----'."\n";
        echo '|-Status:' . $response->status . "\n";
        echo '|-Body:' ."\n"; 
        echo $response->body . "\n";
        echo "|-Header:\n";
        print_r ( $response->header );
        echo '-----------------------End-------------------------------------------------------------------------------------------------
    ----'."\n\n";
    }

}
