<?php
abstract class pmq_Client_Peer_Abstract
{
    private static $instances = array();
    
    private $url;
    
    public static function getInstance($type, $url)
    {
        if (!isset(self::$instances[$url])) {
            // Guess the classname from the url
            $className = 'pmq_Client_Peer_' . ucfirst($type);
            self::$instances[$url] = new $className($url);
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
    
    
    abstract public function put(array $messages);    
    
}
