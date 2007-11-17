<?php
abstract class pmq_Client_Peer_Abstract
{
    private static $instances = array();

    private $transportMethod;
    private $peerUrl;
    private $key;

    public static function getInstance($method, $url=false)
    {
        if ($url === false) {
            list($method, $url) = explode(';', $method);
        }

        $key = $method.';'.$url;
        if (!isset(self::$instances[$key])) {
            // Guess the classname from transport method
            $className = 'pmq_Client_Peer_' . ucfirst($method);
            self::$instances[$key] = new $className($method, $url);
        }

        return self::$instances[$key];
    }
    
    protected function __construct($method, $url)
    {
        $this->transportMethod = $method;
        $this->peerUrl = $url;
        $this->key = $method.';'.$url;
    }

    public function getTransportMethod()
    {
        return $this->transportMethod;
    }

    public function getUrl()
    {
        return $this->peerUrl;
    }

    public function getKey() {
        return $this->key;
    }

    abstract public function send(array &$handles, pmq_Client_Storage_Abstract $storage);    
}
