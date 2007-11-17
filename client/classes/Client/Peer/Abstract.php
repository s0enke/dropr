<?php
abstract class pmq_Client_Peer_Abstract
{
    private static $instances = array();

    private $transportMethod;
    private $peerUrl;

    public static function getInstance($method, $url)
    {
        if (!isset(self::$instances[$method][$url])) {
            // Guess the classname from transport method
            $className = 'pmq_Client_Peer_' . ucfirst($type);
            self::$instances[$method][$url] = new $className($method, $url);
        }

        return self::$instances[$method][$url];
    }

    protected function __construct($method, $url)
    {
        $this->transportMethod = $method;
        $this->peerUrl = $url;
    }

    public function getTransportMethod()
    {
        return $this->transportMethod;
    }

    public function getUrl()
    {
        return $this->peerUrl;
    }

    public function serializedKey() {
        return $this->transportMethod().';'.$this->peerUrl();
    }

    public static function getInstanceBySerializedKey() {
        if (!list($method, $url) = explode(';', $peerDsn)) {
            echo "Peer $peer is obstructed!\n";
            return NULL;
        }
        return self::getInstance();
    }

    abstract public function send(array &$handles, pmq_Client_Storage_Abstract $storage);    
}
