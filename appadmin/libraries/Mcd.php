<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 阿里云OCS 类库
 * @Copyright (c) 2014, 蜂乐科技 
 * All rights reserved.
 * @author          zhenan <zhenan@houzjoy.co,>
 * @time            2014/10/22 20:24
 * @version         Id: 1.1
*/

class Mcd {

    /**
     * 主动刷新时间key后缀
     * @var string
     */
    const CACHE_TIME_CTL = '_@t';

    /**
     * 锁key后缀
     * @var string
     */
    const CACHE_LOCK_CTL = '_@l';

    /**
     *  poolconfig配置信息
     */ 
    private $poolConfig = array();
    /**
     * OCS 密码
     */
    /**
     * MC连接池
     * @var array
     */
    private static $memcached = array();

    /**
     * 当前MC服务器
     * @var string
     */
    private $servers;

    /**
     * mc的key前缀
     * @var stringt
     */
    private $keyPrefix;

    /**
     * 当前MC链接
     * @var resource
     */
    private $mcd;

    /**
     * 原生函数控制
     * @var bool
     */
    private $native;

    /**
     * 强制刷新
     * @var bool
     */
    private $flush_cache = false;

    /**
     * 最后一次操作的结果代码
     * @var int
     */
    private $lastResultCode;

    /**
     * 最后一次操作的结果描述
     * @var string
     */
    private $lastResultMessage;

    /**
     * 链接检测
     * @var bool
     */
    private $checkLink;

    /**
     * 链接检测池
     * @var bool
     */
    private static $checkLinks = array();
    /**
     * 统计信息
     */
    private static $statInfo = array();

    private $startRunTime;

    /**
     * 构造函数
     * @params string $mcName MC名称
     * @params array $mcConfig eg:array('servers'=>'192.168.1.1:7600 192.168.1.2:7700', 'keyPrefix' => 'test-');
     * @params bool $native 是否使用原生mc函数| default is false
     * @params bool $compress 是否启用压缩| default is true
     * @return void
     */
    public function __construct($params = array('mcName' => '', 'mcConfig' => array(), 'native' => false, 'compress' => TRUE)) {
        $mcName   = $params['mcName'];
        $mcConfig = $params['mcConfig'];
        $native   = $params['native'];
        $this->getPoolConfig();
        empty($mcName) && $mcName = $this->mcName;
        $this->servers = empty($mcConfig['servers']) ? $this->poolConfig['mc'][$mcName]['servers'] : $mcConfig['servers'];
        $options = array();
        if(isset($this->poolConfig['mc'][$mcName]['option'])) {
            $options = isset($mcConfig['option'])
                ? array_merge($this->poolConfig['mc'][$mcName]['option'], $mcConfig['option'])
                : $this->poolConfig['mc'][$mcName]['option'];
        }
        $mcKey = md5($this->servers . serialize($options));
        if (!isset(self::$memcached[$mcKey]) || !(self::$memcached[$mcKey] instanceof Memcached)) {
            $instance = new Memcached();
            // 一致性hash
            $instance->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);
            $instance->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
            // http://www.lnmpblog.com/archives/709
            $instance->setOption(Memcached::OPT_TCP_NODELAY, true);
            $serverInfo = array($this->servers);
            foreach($options as $k => $v) {
                $r = $instance->setOption($k, $v);
                $serverInfo[] = "{$k}:{$v}";
            }
            if (!empty($this->poolConfig['mc'][$mcName]['username']) && !empty($this->poolConfig['mc'][$mcName]['password'])) {
                $instance->setSaslAuthData($this->poolConfig['mc'][$mcName]['username'], $this->poolConfig['mc'][$mcName]['password']);
            }
            $serverArr = explode(' ', $this->servers);
            $serverArr = array_map('explode', array_pad(array(), count($serverArr), ':'), $serverArr);
            $instance->addServers($serverArr);
            self::$memcached[$mcKey] = $instance;
            debug_show(implode(',', $serverInfo), 'mcd_servers');
        }
        $this->mcd = self::$memcached[$mcKey];
        $this->checkLink = true;
        $this->native = (bool)$native;
        $this->flush_cache = isset($_GET['_flush_cache']) && $_GET['_flush_cache'] == '1' ? true : false;
    }

    private function getPoolConfig() {

        if ( ! defined('ENVIRONMENT') OR ! file_exists($file_path = APPPATH.'config/'.ENVIRONMENT.'/pool_config.php'))
        {
            if ( ! file_exists($file_path = APPPATH.'config/pool_config.php'))
            {
                show_error('The configuration file pool_config.php does not exist.');
            }                                                                                                                        
        }

        include($file_path);
        if ( ! isset($config['mc']) OR count($config['mc']) == 0)
        {
            show_error('No memcached/ocs connection settings were found in the pool config file.');                                   
        }
        $this->mcName = $config['mc']['default'];
        $this->poolConfig = $config;
    }
    /**
     * 魔术方法，调用原生的方法
     * @param string $name
     * @param array $args
     * @return mix
     */
    public function __call($name, $args) {
        $ret = call_user_func_array(array($this->mcd, $name), $args);
        return $ret;
    }

    public function __destruct() {
        $this->mcd->quit();
    }
    /**
     * 作废缓存中的所有元素
     * @param int $delay 在作废所有元素之前等待的秒数，默认为0
     * @return false 不允许操作直接返回false
     */
    public function flush($delay = 0) {
        return false;
    }

    /**
     * 获取最后一次操作结果
     * @param void
     * @return int
     */
    public function getResultCode() {
        return $this->native && !$this->flush_cache ? $this->mcd->getResultCode() : $this->lastResultCode;
    }

    /**
     * 获取最后一次操作结果描述
     * @param void
     * @return string
     */
    public function getResultMessage() {
        return $this->native && !$this->flush_cache ? $this->mcd->getResultMessage() : $this->lastResultMessage;
    }

    /**
     * 设置缓存
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int $time 缓存时间
     * @retrun bool
     */
    public function set($key, $value, $time = 0) {
        $calls = 1;
        if($this->native) {//使用原生的set
            $ret = $this->mcd->set($key, $value, $time);
        } else {
            if(($ret = $this->mcd->set($this->_buildKey($key, 'TIME_CTL'), 1, $time))) {
                $ret = $this->mcd->set($key, $value, $time + 86400);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
            $this->_releaseLock($key);
        }
        $this->_checkStats(__FUNCTION__, $calls);
        debug_show($value, "mcd_set({$key}),ttl({$time})");
        return $ret;
    }

    /**
     * 指定服务器设置缓存
     * @param string $server_key 服务器标识
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int $time 缓存时间
     * @retrun bool
     */
    public function setByKey($server_key, $key, $value, $time = 0) {
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->setByKey($server_key, $key, $value, $time);
        } else {
            if(($ret = $this->mcd->setByKey($server_key, $this->_buildKey($key, 'TIME_CTL'), 1, $time))) {
                $ret = $this->mcd->setByKey($server_key, $key, $value, $time + 86400);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
            $this->_releaseLock($key, $server_key);
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 批量设置缓存
     * @param array $dataArr 缓存值
     * @param int $time 缓存时间
     * @retrun bool
     */
    public function setMulti(array $dataArr, $time = 0) {
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->setMulti($dataArr, $time);
        } else {
            $ctlArr = array_combine($this->_buildKey(array_keys($dataArr), 'TIME_CTL'), array_pad(array(), count($dataArr), 1));
            if(($ret = $this->mcd->setMulti($ctlArr, $time))) {
                $ret = $this->mcd->setMulti($dataArr, $time + 86400);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
            $this->_releaseLock(array_keys($dataArr));
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 指定服务器批量设置缓存
     * @param string $server_key 服务器标识
     * @param array $dataArr 缓存值
     * @param int $time 缓存时间
     * @retrun bool
     */
    public function setMultiByKey($server_key, array $dataArr, $time = 0) {
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->setMultiByKey($server_key, $dataArr, $time);
        } else {
            $ctlArr = array_combine($this->_buildKey(array_keys($dataArr), 'TIME_CTL'), array_pad(array(), count($dataArr), 1));
            if(($ret = $this->mcd->setMultiByKey($server_key, $ctlArr, $time))) {
                $ret = $this->mcd->setMultiByKey($server_key, $dataArr, $time + 86400);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
            $this->_releaseLock(array_keys($dataArr), $server_key);
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 获取缓存
     * @param string $key 缓存键
     * @param callback $cache_cb 通读缓存回调函数(未取到才触发回调)
     * @param float $cas_token CAS标记值
     * @param int $lockTime  缓存锁失效时间 | native=false有效
     * @return mixed
     */
    public function get($key, $cache_cb = NULL, &$cas_token = NULL, $lockTime = 3) {
        $this->startRunTime = microtime(true);
        $calls = 0;
        if($this->flush_cache || !$this->checkLink) {
            $ret = false;
            $this->lastResultCode = Memcached::RES_NOTFOUND;
            $this->lastResultMessage = 'NOT FOUND';
        } else {
            if($this->native) {
                $ret = $this->mcd->get($key, $cache_cb, $cas_token);
                $calls++;
            } else {
                $ctlKey = $this->_buildKey($key, 'TIME_CTL');
                $ctlRet = $this->mcd->get($ctlKey, NULL, $cas_token);
                $ctlResultCode = $this->mcd->getResultCode();
                $ret = $this->mcd->get($key, NULL, $cas_token);
                $this->lastResultCode = $this->mcd->getResultCode();
                $this->lastResultMessage = $this->mcd->getResultMessage();
                $calls += 2;
                if($ctlResultCode !== Memcached::RES_SUCCESS || $this->lastResultCode !== Memcached::RES_SUCCESS) {
                    if($this->_getLock($key, $lockTime)) {
                        $ret = false;
                        $this->lastResultCode = Memcached::RES_NOTFOUND;
                        $this->lastResultMessage = 'NOT FOUND';
                    } else {
                        $redo = 3;
                        $do = 0;
                        do {
                            usleep(1000*100);
                            $this->mcd->get($ctlKey, NULL, $cas_token);
                        } while($this->mcd->getResultCode() !== Memcached::RES_SUCCESS && ++$do < $redo);
                        $calls += $do - 1;
                        if($this->mcd->getResultCode() === Memcached::RES_SUCCESS) {
                            $ret = $this->mcd->get($key, NULL, $cas_token);
                            $this->lastResultCode = $this->mcd->getResultCode();
                            $this->lastResultMessage = $this->mcd->getResultMessage();
                            $calls++;
                        }
                    }
                }
            }
        }
        if(!$this->native && is_callable($cache_cb) && $this->lastResultCode !== Memcached::RES_SUCCESS) {
            call_user_func_array($cache_cb, array($this, $key, &$ret));
        }
        $this->_checkStats(__FUNCTION__, $calls);
        debug_show($ret, "mcd_get({$key})");
        return $ret;
    }

    /**
     * 指定服务器获取缓存
     * @param string $server_key 服务器标识
     * @param string $key 缓存键
     * @param callback $cache_cb 通读缓存回调函数(未取到才触发回调)
     * @param float $cas_token CAS标记值
     * @param int $lockTime  缓存锁失效时间 | native=false有效
     * @return mixed
     */
    public function getByKey($server_key, $key, $cache_cb = NULL, &$cas_token = NULL, $lockTime = 3) {
        $this->startRunTime = microtime(true);
        $calls = 0;
        if($this->flush_cache || !$this->checkLink) {
            $ret = false;
            $this->lastResultCode = Memcached::RES_NOTFOUND;
            $this->lastResultMessage = 'NOT FOUND';
        } else {
            if($this->native) {
                $ret = $this->mcd->getByKey($server_key, $key, $cache_cb, $cas_token);
                $calls++;
            } else {
                $ctlKey = $this->_buildKey($key, 'TIME_CTL');
                $ctlRet = $this->mcd->getByKey($server_key, $ctlKey, NULL, $cas_token);
                $ctlResultCode = $this->mcd->getResultCode();
                $ret = $this->mcd->getByKey($server_key, $key, NULL, $cas_token);
                $this->lastResultCode = $this->mcd->getResultCode();
                $this->lastResultMessage = $this->mcd->getResultMessage();
                $calls += 2;
                if($ctlResultCode !== Memcached::RES_SUCCESS || $this->lastResultCode !== Memcached::RES_SUCCESS) {
                    if($this->_getLock($key, $lockTime, $server_key)) {
                        $ret = false;
                        $this->lastResultCode = Memcached::RES_NOTFOUND;
                        $this->lastResultMessage = 'NOT FOUND';
                    } else {
                        $redo = 3;
                        do {
                            usleep(100*1000);
                            $this->mcd->getByKey($server_key, $ctlKey, NULL, $cas_token);
                        } while($this->mcd->getResultCode() !== Memcached::RES_SUCCESS && --$redo > 0);
                        $calls += $do - 1;
                        if($this->mcd->getResultCode() === Memcached::RES_SUCCESS) {
                            $ret = $this->mcd->getByKey($server_key, $key, NULL, $cas_token);
                            $this->lastResultCode = $this->mcd->getResultCode();
                            $this->lastResultMessage = $this->mcd->getResultMessage();
                            $calls++;
                        }

                    }
                }
            }
        }
        if(!$this->native && is_callable($cache_cb) && $this->lastResultCode !== Memcached::RES_SUCCESS) {
            call_user_func_array($cache_cb, array($this, $key, &$ret));
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 批量获取缓存
     * @param array $keys 缓存键
     * @param float $cas_tokens CAS标记值
     * @param int $flags get操作的附加选项| default Memcached::GET_PRESERVE_ORDER
     * @param int $lockTime  缓存锁失效时间 | native=false有效
     * @return mixed
     */
    public function getMulti(array $keys, &$cas_tokens = NULL, $flags = NULL, $lockTime = 3) {
        $this->startRunTime = microtime(true);
        $calls = 0;
        if($this->flush_cache || !$this->checkLink) {
            $ret = false;
            $this->lastResultCode = Memcached::RES_NOTFOUND;
            $this->lastResultMessage = 'NOT FOUND';
        } else {
            if($this->native) {
                $ret = $this->mcd->getMulti($keys, $cas_tokens, $flags);
                $calls++;
            } else {
                $ctlKeys = $this->_buildKey($keys, 'TIME_CTL');
                $ret = array();
                $ctlRet = $this->mcd->getMulti($ctlKeys, $cas_tokens, $flags);
                $ctlResultCode = $this->mcd->getResultCode();
                $data = $this->mcd->getMulti($keys, $cas_tokens, $flags);
                $this->lastResultCode = $this->mcd->getResultCode();
                $this->lastResultMessage = $this->mcd->getResultMessage();
                $calls += 2;
                foreach($keys as $k => $key) {
                    if(isset($ctlRet[$ctlKeys[$k]]) && isset($data[$key])) {
                        $ret[$key] = $data[$key];
                    } else if(!$this->_getLock($key, $lockTime)) {
                        isset($data[$key]) && $ret[$key] = $data[$key];
                    }
                }
                if(empty($ret)) {
                    $ret = false;
                    $this->lastResultCode = Memcached::RES_NOTFOUND;
                    $this->lastResultMessage = 'NOT FOUND';
                }
            }
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 指定服务器批量获取缓存
     * @param string $server_key 服务器标识
     * @param array $keys 缓存键
     * @param float $cas_tokens CAS标记值
     * @param int $flags get操作的附加选项| default Memcached::GET_PRESERVE_ORDER
     * @param int $lockTime  缓存锁失效时间 | native=false有效
     * @return mixed
     */
    public function getMultiByKey($server_key, array $keys, &$cas_tokens = NULL, $flags = Memcached::GET_PRESERVE_ORDER, $lockTime = 3) {
        $this->startRunTime = microtime(true);
        $calls = 0;
        if($this->flush_cache || !$this->checkLink) {
            $ret = false;
            $this->lastResultCode = Memcached::RES_NOTFOUND;
            $this->lastResultMessage = 'NOT FOUND';
        } else {
            if($this->native) {
                $ret = $this->mcd->getMultiByKey($server_key, $keys, $cas_tokens, $flags);
                $calls++;
            } else {
                $ctlKeys = $this->_buildKey($keys, 'TIME_CTL');
                $ret = array();
                $ctlRet = $this->mcd->getMultiByKey($server_key, $ctlKeys, $cas_tokens, $flags);
                $data = $this->mcd->getMultiByKey($server_key, $keys, $cas_tokens, $flags);
                $this->lastResultCode = $this->mcd->getResultCode();
                $this->lastResultMessage = $this->mcd->getResultMessage();
                $calls += 2;
                foreach($keys as $k => $key) {
                    if(isset($ctlRet[$ctlKeys[$k]]) && isset($data[$key])) {
                        $ret[$key] = $data[$key];
                    } else if(!$this->_getLock($key, $lockTime)) {
                        isset($data[$key]) && $ret[$key] = $data[$key];
                    }
                }
                if(empty($ret)) {
                    $ret = false;
                    $this->lastResultCode = Memcached::RES_NOTFOUND;
                    $this->lastResultMessage = 'NOT FOUND';
                }
            }
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 增加缓存
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int $time 缓存时间
     * @retrun bool
     */
    public function add($key, $value, $time = 0) {
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->add($key, $value, $time);
        } else {
            if(($ret = $this->mcd->add($this->_buildKey($key, 'TIME_CTL'), $value, $time))) {
                $ret = $this->mcd->set($key, $value, $time + 86400);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 指定服务器增加缓存
     * @param string $server_key 服务器标识
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int $time 缓存时间
     * @retrun bool
     */
    public function addByKey($server_key, $key, $value, $time = 0) {
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->addByKey($server_key, $key, $value, $time);
        } else {
            if(($ret = $this->mcd->addByKey($server_key, $this->_buildKey($key, 'TIME_CTL'), $value, $time))) {
                $ret = $this->mcd->setByKey($server_key, $key, $value, $time + 86400);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    public function append($key, $value) {
        //append(),prepend() can't work with compression option on 
        if($this->mcd->getOption(Memcached::OPT_COMPRESSION))
        {
            return false;
        }
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->append($key, $value);
        } else {
            $ret = $this->mcd->get($this->_buildKey($key, 'TIME_CTL'));
            if($this->mcd->getResultCode() === Memcached::RES_SUCCESS) {
                $ret = $this->mcd->append($key, $value);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    public function appendByKey($server_key, $key, $value) {
        //append(),prepend() can't work with compression option on 
        if($this->mcd->getOption(Memcached::OPT_COMPRESSION))
        {
            return false;
        }
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->appendByKey($server_key, $key, $value);
        } else {
            $ret = $this->mcd->getByKey($server_key, $this->_buildKey($key, 'TIME_CTL'));
            if($this->mcd->getResultCode() === Memcached::RES_SUCCESS) {
                $ret = $this->mcd->appendByKey($server_key, $key, $value);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    public function prepend($key, $value) {
        //append(),prepend() can't work with compression option on 
        if($this->mcd->getOption(Memcached::OPT_COMPRESSION))
        {
            return false;
        }
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->prepend($key, $value);
        } else {
            $ret = $this->mcd->get($this->_buildKey($key, 'TIME_CTL'));
            if($this->mcd->getResultCode() === Memcached::RES_SUCCESS) {
                $ret = $this->mcd->prepend($key, $value);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    public function prependByKey($server_key, $key, $value) {
        //append(),prepend() can't work with compression option on 
        if($this->mcd->getOption(Memcached::OPT_COMPRESSION))
        {
            return false;
        }
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->prependByKey($server_key, $key, $value);
        } else {
            $ret = $this->mcd->getByKey($server_key, $this->_buildKey($key, 'TIME_CTL'));
            if($this->mcd->getResultCode() === Memcached::RES_SUCCESS) {
                $ret = $this->mcd->prependByKey($server_key, $key, $value);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 自增
     * @param string $key 缓存键
     * @param int $offset 自增值
     * @return int
     */
    public function increment($key, $offset = 1) {
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->increment($key, $offset);
        } else {
            $ret = $this->mcd->get($this->_buildKey($key, 'TIME_CTL'));
            if($this->mcd->getResultCode() === Memcached::RES_SUCCESS) {
                $ret = $this->mcd->increment($key, $offset);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 指定服务器自增
     * @param string $server_key 服务器标识
     * @param string $key 缓存键
     * @param int $offset 自增值
     * @return int
     */
    public function incrementByKey($server_key, $key, $offset = 1, $initial_value = 0, $expiry = 0) {
        $rs = $this->increment($key, $offset);
        if($this->getResultCode() === Memcached::RES_NOTFOUND) {
            $rs = $this->set($key, $initial_value, $expiry);
            if($this->getResultCode() === Memcached::RES_SUCCESS) {
                $rs = $initial_value;
            }
        }
        return $rs;
    }

    /**
     * 指定服务器自减
     * @param string $server_key 服务器标识
     * @param string $key 缓存键
     * @param int $incre 自减值
     * @return int
     */
    public function decrement($key, $incre = 1) {
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->decrement($key, $incre);
        } else {
            $ret = $this->mcd->get($this->_buildKey($key, 'TIME_CTL'));
            if($this->mcd->getResultCode() === Memcached::RES_SUCCESS) {
                $ret = $this->mcd->decrement($key, $incre);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 自减
     * @param string $key 缓存键
     * @param int $incre 自减值
     * @return int
     */
    public function decrementByKey($server_key, $key, $offset= 1, $initial_value = 0, $expiry = 0) {
        $rs = $this->decrement($key, $offset);
        if($this->getResultCode() === Memcached::RES_NOTFOUND) {
            $rs = $this->set($key, $initial_value, $expiry);
            if($this->getResultCode() === Memcached::RES_SUCCESS) {
                $rs = $initial_value;
            }
        }
        return $rs;
    }

    /**
     * 删除缓存
     * @param string $key 缓存键
     * @param int $time 服务端等待删除该元素的总时间(或一个Unix时间戳表明的实际删除时间)
     * @return bool
     */
    public function delete($key, $time = 0) {
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->delete($key, $time);
        } else {
            if(($ret = $this->mcd->delete($this->_buildKey($key, 'TIME_CTL'), $time))) {
                $ret = $this->mcd->delete($key, $time);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 指定服务器删除缓存
     * @param string $server_key 服务器标识
     * @param string $key 缓存键
     * @param int $time 服务端等待删除该元素的总时间(或一个Unix时间戳表明的实际删除时间)
     * @return bool
     */
    public function deleteByKey($server_key, $key, $time = 0) {
        $this->startRunTime = microtime(true);
        $calls = 1;
        if($this->native) {
            $ret = $this->mcd->deleteByKey($server_key, $key, $time);
        } else {
            if(($ret = $this->mcd->deleteByKey($server_key, $this->_buildKey($key, 'TIME_CTL'), $time))) {
                $ret = $this->mcd->deleteByKey($server_key, $key, $time);
                $calls++;
            }
            $this->lastResultCode = $this->mcd->getResultCode();
            $this->lastResultMessage = $this->mcd->getResultMessage();
        }
        $this->_checkStats(__FUNCTION__, $calls);
        return $ret;
    }

    /**
     * 批量删除缓存
     * @param string $key 缓存键
     * @param int $time 服务端等待删除该元素的总时间(或一个Unix时间戳表明的实际删除时间)
     * @return bool
     */
    public function deleteMulti(array $keys, $time = 0) {
        if(in_array(ENVIRONMENT, array('testing', 'development', 'production'), true)) {
            // memcached.so版本大于2.0
            $this->startRunTime = microtime(true);
            $calls = 1;
            if($this->native) {
                $ret = $this->mcd->deleteMulti($keys, $time);
            } else {
                if(($ret = $this->mcd->deleteMulti($this->_buildKey($keys, 'TIME_CTL'), $time))) {
                    $ret = $this->mcd->deleteMulti($keys, $time);
                    $calls++;
                }
                $this->lastResultCode = $this->mcd->getResultCode();
                $this->lastResultMessage = $this->mcd->getResultMessage();
            }
            $this->_checkStats(__FUNCTION__, $calls);
            return $ret;
        } else {
            $rs = false;
            foreach($keys as $key) {
                $rs = $this->delete($key, $time);
                if($rs) {
                    if(empty($do)) {
                        $ret = $rs;
                        $do = true;
                    }
                } else if($ret) {
                    $ret = $rs;
                }
            }
            return $rs;
        }
    }

    /**
     * 指定服务器批量删除缓存
     * @param string $server_key 服务器标识
     * @param string $key 缓存键
     * @param int $time 服务端等待删除该元素的总时间(或一个Unix时间戳表明的实际删除时间)
     * @return bool
     */
    public function deleteMultiByKey($server_key, array $keys, $time = 0) {
        if(in_array(ENVIRONMENT, array('testing', 'development', 'production'), true)) {
            // memcached.so版本大于2.0
            $this->startRunTime = microtime(true);
            $calls = 1;
            if($this->native) {
                $ret = $this->mcd->deleteMultiByKey($server_key, $keys, $time);
            } else {
                if(($ret = $this->mcd->deleteMultiByKey($server_key, $this->_buildKey($keys, 'TIME_CTL'), $time))) {
                    $ret = $this->mcd->deleteMultiByKey($server_key, $keys, $time);
                    $calls++;
                }
                $this->lastResultCode = $this->mcd->getResultCode();
                $this->lastResultMessage = $this->mcd->getResultMessage();
            }
            return $ret;
        } else {
            $rs = false;
            foreach($keys as $key) {
                $rs = $this->deleteByKey($server_key, $key, $time);
                if($rs) {
                    if(empty($do)) {
                        $ret = $rs;
                        $do = true;
                    }
                } else if($ret) {
                    $ret = $rs;
                }
            }
            return $rs;
        }
    }

    /**
     * 对资源加锁
     * @param string $key 缓存锁键
     * @param int $lockTime 缓存锁失效时间
     * @param string $server_key 指定服务器
     * @return bool
     */
    private function _getLock($key, $lockTime = 3, $server_key = NULL) {
        if(is_null($server_key)) {
            $ret = $this->mcd->add($this->_buildKey($key, 'LOCK_CTL'), 1, $lockTime);
        } else {
            $ret = $this->mcd->addByKey($server_key, $this->_buildKey($key, 'LOCK_CTL'), 1, $lockTime);
        }
        return $ret;
    }

    /**
     * 释放资源锁
     * @param string $key 缓存锁键
     * @param string $server_key 指定服务器
     * @return bool
     */
    private function _releaseLock($keys, $server_key = null) {
        $keys = $this->_buildKey($keys, 'LOCK_CTL');
        $calls = 0;
        if(is_array($keys)) {
            if(in_array(ENVIRONMENT, array('testing', 'development', 'production'), true)) {
                // memcached.so版本大于2.0
                if(is_null($server_key)) {
                    $this->mcd->deleteMulti($keys);
                } else {
                    $this->mcd->deleteMultiByKey($server_key, $keys);
                }
                $calls++;
            } else {
                if(is_null($server_key)) {
                    array_map(array($this->mcd, 'delete'), $keys);
                } else {
                    array_map(array($this->mcd, 'deleteByKey'), array_pad(array(), count($keys), $server_key), $keys);
                }
                $calls += count($keys);
            }
        } else {
            if(is_null($server_key)) {
                $this->mcd->delete($keys);
            } else {
                $this->mcd->deleteByKey($server_key, $keys);
            }
            $calls++;
        }
    }

    /**
     * 检测memcache是否正常运行
     * @param void
     * @return bool
     */
    private function _checkConnection() {
        $timeout = self::$mcConfig['connect_timeout_mc'];
        $startTime = microtime(true);
        $rs = $this->mcd->set('ci_test_mcd', 1, 1);
        $runTime = self::addStatInfo('mc', $startTime, 1);
        if($runTime > $timeout*1000) {
            $errno = 20702;
            $error = "mcd[{$this->servers}],runtime[{$runTime}/{$timeout}s]";
            $this->_error($errno, $error);
        }
        if($this->mcd->getResultCode === Memcached::RES_SUCCESS) {
            return true;
        }
        $this->_error(90500, "MCD服务器: {$this->servers} 无法响应");
        return false;
    }

    public static function addStatInfo($type, $startTime = 0, $offset = 1) {
        if(!isset(self::$statInfo[$type])) {
            self::$statInfo[$type]['count'] = self::$statInfo[$type]['time'] = 0;
        }
        self::$statInfo[$type]['count'] += $offset;
        if($startTime > 0) {
            $runTime = sprintf("%0.2f", (microtime(true) - $startTime) * 1000);
            self::$statInfo[$type]['time'] += $runTime;
            return $runTime . " ms";
        }
        return true;
    }

    private function _buildKey($key, $type) {
        if(is_array($key)) {
            foreach($key as $k => $v) {
                $key[$k] = $this->_buildKey($v, $type);
            }
        } else {
            switch($type) {
                case 'TIME_CTL' :
                    $key = $key . self::CACHE_TIME_CTL;
                    break;
                case 'LOCK_CTL' :
                    $key = $key . self::CACHE_LOCK_CTL;
                    break;
            }
        }
        return $key;
    }

    private function _checkStats($method, $times = 0, $native = false) {
        if(!empty($times)) {
            $runTime = 0;
        }
        $native = $this->native || $native;
        $code = $native ? $this->mcd->getResultCode() : $this->lastResultCode;
        if(in_array($code, array(Memcached::RES_SUCCESS, Memcached::RES_NOTFOUND), true)) {
            return 0;
        } else {
            if(in_array($method, array('add', 'addByKey', '_getLock'), true) && in_array($code, array(Memcached::RES_DATA_EXISTS, Memcached::RES_NOTSTORED), true)) {
                return 0;
            }
        }
        $errno = 20703;
        $error = $native ? $this->mcd->getResultMessage() : $this->lastResultMessage;
        $this->_error($errno, "[code]{$code}[msg]{$error}[method]{$method}[server]{$this->servers}");
        return 0;
    }

    private function _error($errno, $msg) {
        $this->CI =& get_instance();
        $this->CI->load->library("log");
        $this->CI->log->write_log('error', $msg);
    }

}

