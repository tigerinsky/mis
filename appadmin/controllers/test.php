<?php
/**
 * @Copyright (c) 2015 Rd.Lanjinger.com. All Rights Reserved.
 * @author          gaozhenan <gaozhenan@lanjinger.com>   
 * @version         $Id$
 * @desc            test
 */

class test extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('http2');
    }
    //private static $get_url = "http://www.livejoy.cn/api/count/obj_nums/1/1,6,3,9,4/";
    private static $get_url = "http://221.176.30.209/op/open/index.php/token";
    public function test_get() {
        $ret = $this->http2->get(self::$get_url);
        print_r($ret);
    }
    public function test_post() {
        $ret = $this->http2->post(self::$get_url, array(
            'grant_type' => 'client_credential',
            'appid' => '10804',
            'secret' => '8816bbd32c11d16b9c81ce30f6c73590',
            ));
        echo "<pre>";
        print_r($ret);
    }
    public function test_head() {
        $ret = $this->http2->head(self::$get_url);
        echo "<pre>";
        print_r($ret);
    }
    public function test_header() {
        $ret = $this->http2->header(self::$get_url, array('id'=>2), array('Host:zhenan.www.livejoy.cn'));
        echo "<pre>";
        print_r($ret);
    }
    public function test_curl_multi() {
        $ret = $this->http2->curlMulti(array('urls'=>array(self::$get_url,self::$get_url)));
        echo "<pre>";
        print_r($ret);
    }
    public function test_curl() {
        $ret = $this->http2->curl(array('req'=>self::$get_url));
        echo "<pre>";
        print_r($ret);
    }
    public function test_send_post_request() {
        $url = "http://zhenan.www.livejoy.cn/api/log/add";
        $ret = $this->http2->sendPostRequest($url, array('script'=>'34444555aa','time'=>1415266233),'livejoy.cn');
        echo "<pre>";
        print_r($ret);
    }
    public function test_rpc() {
        $this->load->library("xmlrpc");
        $this->load->library('xmlrpcs'); 
        $this->xmlrpc->server('http://rpc.pingomatic.com/', 80);
        $this->xmlrpc->method('weblogUpdates.ping');

        $request = array('My Photoblog', 'http://www.my-site.com/photoblog/');
        $this->xmlrpc->request($request);

        if ( ! $this->xmlrpc->send_request())
        {
            echo $this->xmlrpc->display_error();
        } else {
            $arr = $this->xmlrpc->display_response();
            print_r($arr);exit;
        }
    
    }

    public function wap() {
        echo "this is a wap test";
    }
    public function web() {
        echo "this is a web test";
    }
    public function common() {
        echo "this is a web/wap test";                                                                                               
    }

    public function test_mc() {
    
        $this->load->library('mcd');
        $arr = array('b',2334);
        $this->mcd->set($arr[0], $arr[1], 3600);

        if ($ret = $this->mcd->get($arr[0])) {
            echo "OK:" . $ret;
        } else {
            echo "Failed:" . $ret;
        }
    }

    public function test_break() {

        global $i;
        $i = 1;
        while(true) {
            $this->_break();
        }
        exit("aaa");
    }

    private function _break() {
        global $i;
        $i++;
        if ($i >=10) {
            
            echo "333";
            exit;
        }
        echo $i. "<br/>";
    }

    public function test_cron() {
        //print_r($argv);
        $a = "123456";
        echo $a;
    }
}
