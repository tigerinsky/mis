<?php
/**
 * HTTP类
 * @Copyright (c) 2014, 蜂乐科技
 * All rights reserved.
 * @author          
 * @time            2014/11/01 19:30
 * @version         Id: 1.1
 */

class Http2 {

    const HTTP_TIMEOUT = 3; // curl超时设置，单位是秒，可用小数指定毫秒，基类方法可自定义重试次数，故而如果接口超时，最大重试次数倍此设置时间。
    const HTTP_MAXREDIRECT = 2; // 301、302、303、307最大跳转次数。
    const HTTP_REDO = 2; // 访问失败后的重试次数, 默认0次为不重试。
    const HTTP_USERAGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 Dagger/2.0';// 默认UA头
    const HTTP_REFERER = 'http://lanjinger.com/';
    const HTTP_MC_SERVER_KEY = 'LANJING';
    const HTTP_FLASE_LOCK_TIMES = 0;

    private static $http_useragent  = self::HTTP_USERAGENT;
    private static $http_lock_times = self::HTTP_FLASE_LOCK_TIMES;
    private $CI;
    public function __construct() {
        $this->CI =& get_instance(); 
    }
    private static $last_header_info;

    /**
     * 设置请求失败的锁的次数阈值
     * @param int $times
     * @return void
     */
    public function setLockTimes($times = self::HTTP_FLASE_LOCK_TIMES) {
        self::$http_lock_times = $times;
    }

    /**
     * 设置User-Agent头信息
     * @param $userAgent string 发送请求url的User-Agent头,default = ''
     * @return void
     */
    public function setUserAgent($userAgent = self::HTTP_USERAGENT) {
        self::$http_useragent = $userAgent;
    }

    /**
     * 获取最后一次请求的header头信息
     * @param void
     * @return mix
     */
    public function getLastHeader() {
        return self::$last_header_info;
    }

    /**
     * 发送post请求获取结果
     * @param $args['req'] mix 发送请求url，必传参数 **
     * @param $args['post'] mix 发送请求post数据
     * @param $args['header'] array 发送请求自定义header头，$args['header'] = array('Host: www.dagger.com')
     * @param $args['timeout'] int 发送请求超时设定
     * @param $args['cookie'] string 发送请求cookie
     * @param $args['maxredirect'] int 发送请求最大跳转次数
     * @return mix 失败返回false，成功返回array(抓取结果已解析成数组)
     */
    public function post($req, $post, array $header = array(), $timeout = self::HTTP_TIMEOUT, $cookie = '', $redo = self::HTTP_REDO, $maxredirect = self::HTTP_MAXREDIRECT) {
        $args['req']            = $req;
        $args['post']           = $post;
        $args['header']         = $header;
        $args['timeout']        = $timeout;
        $args['cookie']         = $cookie;
        $args['redo']           = $redo;
        $args['maxredirect']    = $maxredirect;
        return $this->_http_exec($args);
    }

    /**
     * 发送get请求获取结果
     * @param $args['req'] mix 发送请求url，必传参数 **
     * @param $args['header'] array 发送请求自定义header头，$args['header'] = array('Host: www.dagger.com')
     * @param $args['timeout'] int 发送请求超时设定
     * @param $args['cookie'] string 发送请求cookie
     * @param $args['maxredirect'] int 发送请求最大跳转次数
     * @param $args['headOnly'] bool 发送请求是否只抓取header头
     * @return mix 失败返回false，成功返回抓取结果
     */
    public function get($req, array $header = array(), $timeout = self::HTTP_TIMEOUT, $cookie = '', $redo = self::HTTP_REDO, $maxredirect = self::HTTP_MAXREDIRECT) {
        $args['req']            = $req;
        $args['header']         = $header;
        $args['timeout']        = $timeout;
        $args['cookie']         = $cookie;
        $args['redo']           = $redo;
        $args['maxredirect']    = $maxredirect;
        return $this->_http_exec($args);
    }


    /**
     * 发送请求获取header头信息，推荐使用
     * @param $args['req'] mix 发送请求url，必传参数 **
     * @param $args['post'] mix 发送请求post数据
     * @param $args['header'] array 发送请求自定义header头，$args['header'] = array('Host: www.dagger.com')
     * @param $args['timeout'] int 发送请求超时设定
     * @param $args['cookie'] string 发送请求cookie
     * @param $args['maxredirect'] int 发送请求最大跳转次数
     * @param $args['headOnly'] bool 发送请求是否只抓取header头
     * @return mix 失败返回false，成功返回array(抓取结果已解析成数组)
     */
    public function header($req, $post = array(), array $header = array(), $timeout = self::HTTP_TIMEOUT, $cookie = '', $redo = self::HTTP_REDO, $maxredirect = self::HTTP_MAXREDIRECT) {
        $args['req'] = $req;
        $args['post'] = $post;
        $args['header'] = $header;
        $args['timeout'] = $timeout;
        $args['cookie'] = $cookie;
        $args['redo'] = $redo;
        $args['maxredirect'] = $maxredirect;
        $args['headOnly'] = true;
        return $this->_http_exec($args);
    }

    /**
     * 发送get/post并发请求获取结果
     * by 
     * @param array  包含urls|post|header|timeout|cookie|redo|maxredirect|callback|headOnly等为key的参数
     * @return array 返回以请求的url为key的数组，失败时该url对应的值是false，成功返回请求结果(header已解析为数据)
     */
    public function curlMulti(array $args) {
        if (empty($args['urls']) || !is_array($args['urls'])) {
            return $this->_error(90401, '页面抓取请求url缺失');
        }
        if(count($args['urls']) === count($args['urls'], true)) {
            $_tmp = array();
            foreach($args['urls'] as $k => $url) {
                $_tmp[$k]['url'] = $url;
            }
            $args['urls'] = $_tmp;
        }
        return $this->_multi_http_exec($args);
    }

    /**
     * 发送get/post并发请求获取结果
     * by 
     * @param array  包含req|post|header|timeout|cookie|redo|maxredirect|headOnly等为key的参数
     * @return mix 失败返回false，成功返回抓取结果(header已解析成数组)
     */
    public function curl(array $args) {
        return $this->_http_exec($args);
    }

    /**
     * 发送请求不等待接收（支持post）
     * by 
     * @param $req string 发送请求url
     * @param $post string or array 发送post数据
     * @param $header array 发送header头, array('Host' => 'test.sina.com.cn', 'Referer' => 'http://test.sina.com.cn')
     * @return boolen
     */
    public function sendPostRequest($req, $post = array(), $header = array()) {
        $url = $this->_makeUri($req);
        $urlArr = parse_url($url);
        if(empty($urlArr['host'])) {
            return $this->_error(90402, 'url参数错误');
        }
        //$startRunTime = microtime(true);
        $this->CI->benchmark->mark('post_begin');
        $port = isset($urlArr['port']) ? $urlArr['port'] : 80;
        $fp = @stream_socket_client($urlArr['host'] . ':' . $port, $errno, $error, 1);
        $ret = false;
        if ($fp) {
            $out = array();
            empty($urlArr['path']) && $urlArr['path'] = '';
            $urlArr['query'] = empty($urlArr['query']) ? '' : '?' . $urlArr['query'];
            $out[] = (empty($post) ? 'GET' : 'POST') . " {$urlArr['path']}{$urlArr['query']} HTTP/1.1";
            $out['host'] = "Host: {$urlArr['host']}";
            $out['user-agent'] = "User-Agent: " . self::$http_useragent;
            if (!empty($header) && is_array($header)) {
                foreach($header as $k => $v) {
                    if(is_numeric($k)) {
                        list($k, $v) = explode(':', $v, 2);
                    }
                    $k = strtolower(trim($k));
                    if($k === 'set-cookie') {
                        $out[] = "{$k}: {$v}";
                    } else {
                        $out[$k] = "{$k}: {$v}";
                    }
                }
             }
            if(!empty($post)) {
                debug_show($post, 'request_post');
                if(is_array($post)) {
                    $post = http_build_query($post);
                }
                $out[] = 'Content-type: application/x-www-form-urlencoded';
                $out[] = 'Content-Length: ' . strlen($post);
            }
            $out = implode("\r\n", $out) . "\r\nConnection: Close\r\n\r\n" . (empty($post) ? '' : $post . "\r\n");
            stream_set_timeout($fp, 1);
            fwrite($fp, $out);
            fclose ($fp);
            $ret = true;
        } else {
            debug_show("[errno] {$errno} [error] {$error}", 'request_send_error');
            $ret = false;
        }
        //$runTime = BaseModelCommon::addStatInfo('request', $startRunTime);
        $this->CI->benchmark->mark('post_end');
        $run_time = $this->CI->benchmark->elapsed_time('post_begin', 'post_end');
        debug_show($run_time * 1000 . "ms", 'request_time');
        return $ret;
    }

    /**
     * 发送请求获取结果
     * @param $args['req'] mix 发送请求url，必传参数 **
     * @param $args['post'] mix 发送请求post数据
     * @param $args['header'] array 发送请求自定义header头，$args['header'] = array('Host: www.dagger.com')
     * @param $args['timeout'] int 发送请求超时设定
     * @param $args['cookie'] string 发送请求cookie
     * @param $args['maxredirect'] int 发送请求最大跳转次数
     * @param $args['headOnly'] bool 发送请求是否只抓取header头
     * @return mix 失败返回false，成功返回抓取结果
     */
    private function _http_exec($args) {
        if (!extension_loaded('curl')) {
            return $this->_error(90400, '服务器没有安装curl扩展！');
        }

        // $args['req'] = isset($args['req']) ? $args['req'] : array(); // 必传
        $args['post'] = isset($args['post']) ? $args['post'] : array();
        $args['header'] = isset($args['header']) ? $args['header'] : array();
        $args['timeout'] = isset($args['timeout']) && is_numeric($args['timeout']) && $args['timeout'] > 0 ? $args['timeout'] : self::HTTP_TIMEOUT;
        $args['cookie'] = isset($args['cookie']) ? $args['cookie'] : '';
        $args['redo'] = isset($args['redo']) ? $args['redo'] : self::HTTP_REDO;
        $args['maxredirect'] = isset($args['maxredirect']) ? intval($args['maxredirect']) : null;
        $args['headOnly'] = isset($args['headOnly']) ? $args['headOnly'] : false;

        $url = $this->_makeUri($args['req']);
        if (empty($url)) {
            return $this->_error(90401, '页面抓取请求url缺失');
        }

        // mc处理
        if(self::$http_lock_times > 0) {
            $CI =& get_instance();
            $this->CIload->library('memcached', array('native' => TRUE), 'mcd');  
            $mc_http_key_suffix = md5(strpos($url, '?') ? substr($url, 0, strpos($url, '?')) : $url);
            $mc_http_false_key = "http_false_{$mc_http_key_suffix}";// 存放最近连续失败累计时间、次数、最后一次正确结果。
            $mc_http_lock_key = "http_lock_{$mc_http_key_suffix}";
            $mc_lock = $this->CI->mcd->getByKey(self::HTTP_MC_SERVER_KEY, $mc_http_lock_key);
            if ($this->CI->mcd->getResultCode() === Memcached::RES_SUCCESS) {
                debug_show('接口在10秒内出现'. self::$http_lock_times .'次错误，锁定30秒返回false', 'request_return');
                $this->_error(90403, "请求连续" . self::$http_lock_times . "次失败[{$url}]");
                return false;
            }
        }

        $ch = curl_init();
        $this->_set_curl_opts($ch, $args);
        $rs = curl_setopt($ch, CURLOPT_URL, $url);

        //$startRunTime = microtime(true);
        $this->CI->benchmark->mark('http_exec_begin');
        $header = $ret = false;
        do {
            $ret = $this->_get_content($ch, $args['maxredirect']);
            if(strpos($ret, "\r\n\r\n") !== false) {
                list($header, $ret) = explode("\r\n\r\n", $ret, 2);
                break;
            }
            debug_show($url, 'request_redo');
        } while ($args['redo']-- > 0);
        curl_close($ch);
        //$runTime = BaseModelCommon::addStatInfo('request', $startRunTime, 0);
        $this->CI->benchmark->mark('http_exec_end');
        $run_time = $this->CI->benchmark->elapsed_time('http_exec_begin', 'http_exec_end');
        debug_show($run_time * 1000 . "ms", 'request_time');

        self::$last_header_info = $header;
        // 抓取header时，解析header头
        if ($args['headOnly'] && $header !== false) {
            $ret = $this->_parse_header($header);
        }

        if(self::$http_lock_times > 0) {
            // mc缓存处理
            // 10秒钟内连续失败指定次数，30秒钟锁定，入口直接返回false;
            if ($ret === false) {
                if (!$this->CI->mcd->addByKey(self::_HTTP_MC_SERVER_KEY, $mc_http_false_key, 1, 10)) {
                    $falseCount = $this->CI->mcd->incrementByKey(self::HTTP_MC_SERVER_KEY, $mc_http_false_key, 1, 1);
                    if ($falseCount > self::$http_lock_times - 1) {
                        $this->CI->mcd->addByKey(self::HTTP_MC_SERVER_KEY, $mc_http_lock_key, 1, 30);
                    }
                }
            } else {
                $this->CI->mcd->deleteMultiByKey(self::HTTP_MC_SERVER_KEY, array($mc_http_false_key, $mc_http_lock_key));
            }
        }

        //if (defined('LIVEJOY_DEBUG') && 1 === LIVEJOY_DEBUG) {
            //is_string($ret) && strlen($ret) > 2000 && $tmpret = (substr($ret, 0, 2000) . '......超长，截取2000字节');
            //BaseModelCommon::debug(array(array('运行时间', '执行结果'), array($runTime, (empty($tmpret) ? $ret : $tmpret))), 'request_return');
        //}
        return $ret;
    }

    private function _makeUri($req) {
        $url = '';
        if (is_array($req)) {
            switch (count($req)) {
                case 1:
                    $url = $req[0];
                    break;
                case 2:
                    list($url, $params) = $req;
                    $paramStr = http_build_query($params);
                    $url .= strpos($url, '?') !== false ? "&{$paramStr}" : "?{$paramStr}";
                    break;
                default:
                    return $this->_error(90402, 'url参数错误');
            }
        } else if(is_string($req)) {
            $url = $req;
        } else {
            return $this->_error(90402, 'url参数错误');
        }
        debug_show($url, 'request_url');
        return $url;
    }

    private function _multi_http_exec($args) {
        if (!extension_loaded('curl')) {
            return $this->_error(20800, '服务器没有安装curl扩展！');
        }
        if (empty($args['urls']) || !is_array($args['urls'])) {
            return $this->_error(20801, '页面抓取请求url缺失');
        }

        $args['post'] = isset($args['post']) ? $args['post'] : array();
        $args['header'] = isset($args['header']) ? $args['header'] : array();
        $args['timeout'] = isset($args['timeout']) ? $args['timeout'] : self::HTTP_TIMEOUT;
        $args['cookie'] = isset($args['cookie']) ? $args['cookie'] : '';
        $args['redo'] = isset($args['redo']) ? $args['redo'] : self::HTTP_REDO;
        $args['maxredirect'] = isset($args['maxredirect']) ? intval($args['maxredirect']) : self::HTTP_MAXREDIRECT;
        $args['headOnly'] = isset($args['headOnly']) ? $args['headOnly'] : false;
        $args['callback'] = isset($args['callback']) ? $args['callback'] : null;
        $urls = array_filter($args['urls']);
        debug_show($urls, 'request_multi_urls');

        $ch = curl_init();
        $this->_set_curl_opts($ch, $args);

        $header = $ret = $_ch = self::$last_header_info = array();

        //为避免重复请求已经失效的接口，可通过设置mc锁来避免锁有效期内不会再次访问，直接返回上一次的结果
        if(self::$http_lock_times > 0) {
            // mc锁处理
            $this->CI->load->library('memcached', array('native' => TRUE), 'mcd');
            $mc_http_false_keys = $mc_http_lock_keys = array();
            foreach($urls as $k => $urlinfo) {
                $url = is_array($urlinfo) ? $urlinfo['url'] : $urlinfo;
                $mc_http_key_suffix = md5(($pos = strpos($url, '?')) ? substr($url, 0, $pos) : $url);
                $mc_http_false_keys[$k] = "http_false_" . $mc_http_key_suffix;// 存放最近连续失败累计时间、次数、最后一次正确结果。
                $mc_http_lock_keys[$k] = "http_lock_" . $mc_http_key_suffix;
            }
            $http_lock_values = $this->CI->mcd->getMultiByKey(self::HTTP_MC_SERVER_KEY, array_unique($mc_http_lock_keys));
        }

        //根据url列表，对返回结果进行初始化为false，同时根据mc锁情况，决定需要初始化哪些curl句柄
        foreach($urls as $k => $urlinfo) {
            $ret[$k] = false;   //此处初始化所有返回结果为false的目的，是避免在发生超时等异常情况时，后续逻辑中curl_multi_info_read有时在超时下返回false而不是result=28(Timout)的数组，导致最终返回结果中，丢失了相应url的结果值(false)
            if(self::$http_lock_times > 0) {
                // mc锁处理
                if(is_array($http_lock_values) && isset($http_lock_values[$mc_http_lock_keys[$k]])) {
                    $url = is_array($urlinfo) ? $urlinfo['url'] : $urlinfo;
                    debug_show('接口[' . $url . ']在10秒内出现'. self::$http_lock_times .'次错误，锁定30秒返回false', 'request_multi_warn');
                    $this->_error(90403, "请求连续" . self::$http_lock_times ." 次失败[{$url}]");
                    self::$last_header_info[$k] = $header[$k] = $ret[$k] = false;
                    continue;
                }
            }
            $_ch[$k] = curl_copy_handle($ch);
            if(is_array($urlinfo)) {
                //== multi时，不同请求的私有化参数设置 ==//
                $this->_set_curl_opts($_ch[$k], $urlinfo, $first = false);
                // url
                curl_setopt($_ch[$k], CURLOPT_URL, $urlinfo['url']);
            } else {
                curl_setopt($_ch[$k], CURLOPT_URL, $urlinfo);
            }
            if(!isset($mh)) {
                $mh = curl_multi_init();
            }
            curl_multi_add_handle($mh, $_ch[$k]);
            //BaseModelCommon::addStatInfo('request');
        }


        if(isset($mh)) {
            $this->CI->benchmark->mark('multi_http_exec_begin');
            //curl_multi_exec主循环
            do {
                $redoUrls = array();
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc === CURLM_CALL_MULTI_PERFORM);

                while($active && $mrc === CURLM_OK) {
                    //启用select阻塞来避免cpu 100%
                    if(curl_multi_select($mh) == -1)
                    {
                        ;
                    }

                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
             
                    //这里需要注意，如果发生了超时等异常情况，curl_multi_info_read函数有时会返回一个result=28(Timeout)的结果，有时直接返回false，也就是说该判断里关于超时的剩余逻辑将有可能不会被执行
                    if($mhinfo = curl_multi_info_read($mh)) {
                        $k = array_search($mhinfo['handle'], $_ch);

                        if($mhinfo['result'] === CURLE_OK) {
                            $ret[$k] = curl_multi_getcontent($mhinfo['handle']);
                            $code = curl_getinfo($mhinfo['handle'], CURLINFO_HTTP_CODE);

                            // $chinfo = curl_getinfo($mhinfo['handle']);
                            if (in_array($code, array(301, 302, 303, 307), true)) {
                                $redirect[$k] = empty($redirect[$k]) ? 1 : ++$redirect[$k];
                                if($redirect[$k] > $args['maxredirect']) {
                                    $msg = "redirect larger than {$maxredirect} [{$k}][{$urls[$k]['url']}]";
                                    debug_show($msg , 'request_redirect_warn');
                                    $this->_error(90406, $msg);
                                } else {
                                    debug_show("redirect times:{$redirect[$k]},url:{$urls[$k]['url']}", 'request_redirect_info');
                                    preg_match('/Location:(.*?)\n/i', $ret[$k], $matches);
                                    $newurl = trim($matches[1]);
                                    if($newurl{0} === '/') {
                                        preg_match("@^([^/]+://[^/]+)/@", curl_getinfo($mhinfo['handle'], CURLINFO_EFFECTIVE_URL), $matches);
                                        $newurl = $matches[1] . $newurl;
                                    }
                                    $redoUrls[$k] = $urls[$k];
                                    $redoUrls[$k]['url'] = $newurl;
                                    debug_show("[{$k}][old]{$urls[$k]['url']}[new]{$newurl}", 'request_redirect_url');
                                }
                            } else if($code !== 200) {
                                $msg = "http code unnormal : [{$code}] [{$k}][{$urls[$k]['url']}]";
                                debug_show($msg, 'request_http_warn');
                                $this->_error(90405, $msg);
                            }

                            if(strpos($ret[$k], "\r\n\r\n") !== false) {
                                if(empty($args['callback'])) {
                                    list($header[$k], $ret[$k]) = explode("\r\n\r\n", $ret[$k], 2);
                                    self::$last_header_info[$k] = $header[$k];
                                    // 抓取header时，解析header头
                                    if($args['headOnly']) {
                                        $ret[$k] = $this->_parse_header($header[$k]);
                                    }
                                } else {
                                    call_user_func($args['callback'], $k, $ret[$k]);
                                }
                            } else {
                                $header[$k] = $ret[$k] = false;
                                empty($args['callback']) || call_user_func($args['callback'], $k, $ret[$k]);
                            }

                            if(in_array($code, array(403,404), true)) {
                                $ret[$k] = false;
                            }
                        } else {
                            $redo[$k] = empty($redo[$k]) ? 1 : ++$redo[$k];
                            if($redo[$k] <= $args['redo']) {
                                $redoUrls[$k] = $urls[$k];
                            } else {
                                self::$last_header_info[$k] = $header[$k] = $ret[$k] = false;
                                is_callable($args['callback']) && call_user_func($args['callback'], $k, $ret[$k]);
                            }
                            $curl_error = curl_error($mhinfo['handle']);
                            debug_show("[errno] {$mhinfo['result']} [error] {$curl_error}",'request_curl_error');
                            $this->_error(90404, "curl内部错误信息[{$mhinfo['result']}][{$curl_error}][{$urls[$k]['url']}]");
                        }
                        curl_multi_remove_handle($mh, $mhinfo['handle']);
                        curl_close($mhinfo['handle']);
                    }
                };

                // 添加需要再次请求的句柄，包括redirect和redo
                if(!empty($redoUrls)) {
                    debug_show($redoUrls, 'request_redoUrl_info');
                    foreach($redoUrls as $k => $urlinfo) {
                        $_chs = curl_copy_handle($ch);
                        curl_setopt($_chs, CURLOPT_URL, $urlinfo['url']);
                        $_ch[$k] = $_chs;
                        curl_multi_add_handle($mh, $_ch[$k]);
                        //BaseModelCommon::addStatInfo('request');
                    }
                }
            } while(!empty($redoUrls));

            curl_multi_close($mh);
            $this->CI->benchmark->mark("multi_http_exec_end");
            $run_time = $this->benchmark->elapsed_time('multi_http_exec_begin', 'multi_http_exec_end');
            //$runTime = BaseModelCommon::addStatInfo('request', $startRunTime, 0);

            // mc缓存处理
            if(self::$http_lock_times > 0) {
                foreach($ret as $k => $_r) {
                    if($_r === false) {
                        // 10秒钟内连续失败20次，30秒钟锁定，直接返回false;
                        if (!$this->CI->mcd->addByKey(self::HTTP_MC_SERVER_KEY, $mc_http_false_keys[$k], 1, 10)) {
                            $falseCount = $this->CI->mcd->incrementByKey(self::HTTP_MC_SERVER_KEY, $mc_http_false_keys[$k], 1, 1);
                            if ($falseCount > self::$http_lock_times - 1) {
                                $this->CI->mcd->addByKey(self::HTTP_MC_SERVER_KEY, $mc_http_lock_keys[$k], 1, 30);
                            }
                        }
                    } else {
                        $this->CI->mcd->deleteMultiByKey(self::HTTP_MC_SERVER_KEY, array($mc_http_false_keys[$k], $mc_http_lock_keys[$k]));
                    }
                }
            }

            // 生成调试信息
            //if (defined('DAGGER_DEBUG') && 1 === DAGGER_DEBUG) {
                //$d = array();
                //foreach($ret as $k => $v) {
                    //is_string($v) && ($len = strlen($v)) > 2000 && $v = (substr($v, 0, 2000) . '......超长('.$len.')，截取2000字节');
                    //$d[$k] = array($k, $urls[$k]['url'], $v);
                //}
                //asort($d);
                //array_unshift($d, array('序号', '请求地址', '执行结果'));
                //$d[] = array('', '运行时间', $runTime);
                //BaseModelCommon::debug($d, 'request_multi_return');
            //}
        }

        return $ret;
    }

    private function _error($errno, $error) {
        if(!in_array($errno, array(20801,20802), true) || defined('QUEUE')) {
            debug_show("[errro code] {$errno} [errro msg] {$error}", 'request_error');
        } else {
            log_message('error', 'CURL error: '.$errno . '-'.$error_msg);
        }
        return false;
    }

    private function _parse_header(&$header) {
        if($header !== false) {
            $ret = array();
            $_headers = explode("\n", str_replace("\r", '', $header));
            foreach ($_headers as $value) {
                $_header = array_map('trim', explode(':', $value, 2));
                if (!empty($_header[0])) {
                    $_header[0] = trim($_header[0]);
                    $_header[0] = trim($_header[0]);
                    if (empty($_header[1])) {
                        $ret['status'] = $_header[0];
                    } else {
                        $ret[$_header[0]] = isset($ret[$_header[0]]) ? $ret[$_header[0]] . '; ' . $_header[1] : $_header[1];
                    }
                }
            }
            $header = $ret;
        }
        return $header;
    }

    private function _set_curl_opts(&$ch, $args, $first = true) {
        // 本函数不设置url
        $opt = array();
        if($first) {
            $opt[CURLOPT_RETURNTRANSFER] = true;
            $opt[CURLOPT_SSL_VERIFYPEER] = false;
            $opt[CURLOPT_SSL_VERIFYHOST] = false;
            $opt[CURLOPT_MAXCONNECTS] = 100;
            $opt[CURLOPT_HEADER] = true;

            //自Dagger2.0起，超时在内部改为毫秒级超时，设置时可用带小数的秒，若不支持毫秒级超时，则取整为秒
            
            if(defined('CURLOPT_TIMEOUT_MS'))
            {
                $opt[CURLOPT_NOSIGNAL] = 1;
                $opt[CURLOPT_TIMEOUT_MS] = round($args['timeout'] * 1000);
            }
            else
            {
                $opt[CURLOPT_TIMEOUT] = round($args['timeout']);
            }

            // useragent头
            if(!empty(self::$http_useragent)) {
                $opt[CURLOPT_USERAGENT] = self::$http_useragent;
            }
            // 只抓header头
            if ($args['headOnly']) {
                $opt[CURLOPT_NOBODY] = true;
            }
        }
        if($first || !empty($args['header'])) {
            // header头
            $setheader = array();
            if (!empty($args['header']) && is_array($args['header'])) {
                foreach($args['header'] as $k => $v) {
                    if(is_numeric($k)) {
                        list($k, $v) = explode(':', $v);
                    }
                    $k = strtolower(trim($k));
                    if($k === 'set-cookie') {
                        $setheader[] = "{$k}: {$v}";
                    } else {
                        $setheader[$k] = "{$k}: {$v}";
                    }
                }
            }
            $setheader['expect'] = 'Expect:'; // 解决100问题
            $opt[CURLOPT_HTTPHEADER] = $setheader;
        }
        // post数据
        if (!empty($args['post'])) {
            //用http_build_query强制转换，curl不支持多维，转换后，无法支持文件提交
            debug_show($args['post'], 'request_post');
            if (is_array($args['post'])) {
                $args['post'] = http_build_query($args['post']);
            }
            $opt[CURLOPT_POST] = true;
            $opt[CURLOPT_POSTFIELDS] = $args['post'];
        }
        // cookie设置
        if (!empty($args['cookie'])) {
            $opt[CURLOPT_COOKIE] = $args['cookie'];
        }
        return curl_setopt_array($ch, $opt);
    }

    private function _get_content($ch, $maxredirect) {
        $redirect = 0;
        do {
            $retry = false;
            $ret = curl_exec($ch);
            $this->CI->benchmark->mark('request_begin');
            if(!$this->_curl_check($ch)) {
                return false;
            }
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            if(in_array($code, array(301, 302, 303, 307), true)) {
                if(++$redirect <= $maxredirect) {
                    debug_show($redirect, 'request_redirect_times');
                    preg_match('/Location:(.*?)\n/i', $ret, $matches);
                    $newurl = trim($matches[1]);
                    if($newurl{0} === '/') {
                        preg_match("@^([^/]+://[^/]+)/@", $url, $matches);
                        $newurl = $matches[1] . $newurl;
                    }
                    curl_setopt($ch, CURLOPT_URL, $newurl);
                    debug_show($newurl, 'request_redirect_url');
                    $retry = true;
                } else {
                    $msg = "redirect larger than {$maxredirect} [{$url}]";
                    debug_show($msg , 'request_redirect_warn');
                    $this->_error(90406, $msg);
                }
            } else if($code !== 200) {
                $msg = "http code unnormal : [{$code}] [{$url}] [{$ret}]";
                debug_show($msg, 'request_http_warn');
                $this->_error(90405, $msg);
                if(in_array($code, array(403,404), true)) {
                    return false;
                }
            }
        } while($retry);
        return $ret;
    }

    private function _curl_check($ch) {
        $curl_errno = curl_errno($ch);
        if ($curl_errno) {
            $curl_error = curl_error($ch);
            $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            debug_show("[errno] {$curl_errno} [error] {$curl_error}", 'request_curl_error');
            $this->_error(90404, "curl内部错误信息[{$curl_errno}][{$curl_error}][{$url}]");
            return false;
        }
        return true;
    }

}

