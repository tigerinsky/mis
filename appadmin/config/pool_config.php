<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Memcache资源配置,idc为资源分组名称
 * $config['mc']['idc']['servers']                                 = $_SERVER['SINASRV_MEMCACHED_SERVERS'];
 * $config['mc']['idc']['keyPrefix']                               = $_SERVER['SINASRV_MEMCACHED_KEY_PREFIX']; 
 * $config['mc']['idc']['option'][Memcached::OPT_PREFIX_KEY]       = $_SERVER['SINASRV_MEMCACHED_KEY_PREFIX']; 
 * $config['mc']['idc']['option'][Memcached::OPT_COMPRESSION]      = false; //default is true
 * $config['mc']['idc']['option'][Memcached::OPT_BINARY_PROTOCOL]  = true;  //default is false, need memcached version 1.4+
 * $config['mc']['idc']['option'][Memcached::OPT_SERIALIZER]       = Memcached::SERIALIZER_IGBINARY; //default is Memcached::SERIALIZER_PHP
 */
//默认memcache分组

$config['mc']['default'] = 'joy'; // 默认使用名称为default的mc
$config['mc']['joy']['keyPrefix'] = $_SERVER['LJSRV_MEMCACHE_KEY_PREFIX'];
$config['mc']['joy']['servers']   = $_SERVER['LJSRV_MEMCACHE_SERVERS'];
$config['mc']['joy']['username']  = $_SERVER['LJSRV_MEMCACHE_USER'];
$config['mc']['joy']['password']  = $_SERVER['LJSRV_MEMCACHE_PASS'];
//$config['mc']['joy']['option'][Memcached::OPT_PREFIX_KEY]       = $_SERVER['LJSRV_MEMCACHE_KEY_PREFIX'];
//$config['mc']['joy']['option'][Memcached::OPT_BINARY_PROTOCOL]  = true; 
//$config['mc']['joy']['option'][Memcached::OPT_SERIALIZER]       = Memcached::SERIALIZER_PHP; 

$config['redis']['default']      = 'bw'; // 默认使用名称为default的bw
$config['redis']['bw']['server'] = $_SERVER['LJSRV_REDIS_SERVER'];
$config['redis']['bw']['port']   = $_SERVER['LJSRV_REDIS_PORT'];
$config['redis']['bw']['connect_timeout_redis'] = 0.1; //单位秒

/* End of file config.php */
/* Location: ./application/config/config.php */
