<?php
abstract class pmq_Client_Peer_Abstract
{
    private static $instances = array();

    private $url;

    private $transportMethod;

    public static function getInstance($type, $url)
    {
        if (!isset(self::$instances[$url])) {
            // Guess the classname from the url
            $className = 'pmq_Client_Peer_' . ucfirst($type);
            self::$instances[$url] = new $className($url);
            self::$instances[$url]->transportMethod = $type;
        }
        
        return self::$instances[$url];
    }
    
    protected function __construct($url)
    {
        $this->url = $url;
    }
    
    public function getUrl()
    {
        return $this->url;
    }

    public function getTransportMethod()
    {
        return $this->transportMethod;
    }
    
    abstract public function send(array &$handles, pmq_Client_Storage_Abstract $storage);    
    
}
