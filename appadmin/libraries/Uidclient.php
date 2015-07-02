<?php
require_once __DIR__."/Thrift/ClassLoader/ThriftClassLoader.php";

use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TBinaryProtocol as TBinaryProtocol;
use Thrift\Transport\TSocket as TSocket;
use Thrift\Transport\TFramedTransport as TFramedTransport;

class UidClient {
    private $socket = null;
    private $transport = null;
    private $protocol = null;
    private $client = null;

    function __construct() {
        $loader = new ThriftClassLoader();
        $loader->registerNamespace('Thrift', __DIR__);
        $loader->registerDefinition('uis', realpath(__DIR__.'/..').'/service');
        $loader->register();
    }

    function __destruct() {
    }

    public function connect() {
        $this->socket = new TSocket('myb-ol-back2', 6060);
        $this->socket->setSendTimeout(10000);
        $this->socket->setRecvTimeout(20000);

        $this->transport = new TFramedTransport($this->socket);
        $this->protocol = new TBinaryProtocol($this->transport);
        $this->client = new \uis\UidServerClient($this->protocol);

        $this->transport->open();
    }

    public function dis_connect() {
        $this->transport->close();
        $this->client = null;
        $this->protocol = null;
        $this->transport = null;
        $this->socket = null;
    }

    public function get_id($prefix) {
        try {
            $ps_request = $prefix;
            $this->connect();
            $res = $this->client->get_id($ps_request);
            return $res;
        } catch (Exception $e) {
            log_message('error', 'send uis post error, msg['.$e->getMessage().']');
            return false;
        }
    }
}
