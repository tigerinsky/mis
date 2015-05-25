<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once dirname(__FILE__).'/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Transport\TSocket;
use Thrift\Transport\TBufferedTransport;
use Thrift\Protocol\TBinaryProtocol;

class Thrift {

    public function __construct($server_name) {
    
        print_r($server_name);exit;
        try {
            $GEN_DIR = realpath(__DIR__.'/..').'/service/';
            $loader = new ThriftClassLoader();
            $loader->registerNamespace('Thrift', __DIR__ . '/../libraries');
            $loader->registerDefinition($server_name, $GEN_DIR);
            $loader->register();

            $socket = new TSocket('back1', 8999);
            $transport = new TBufferedTransport($socket, 1024, 1024);
            $protocol = new TBinaryProtocol($transport);
            $client = new lj\MessageServerClient($protocol);

            $transport->open();
            $client->ping();
            print "ping()\n";

            $transport->close();
        } catch (TException $tx) {
            print 'TException: '.$tx->getMessage()."\n";
        }

    
    }



}




/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */
