<?php
abstract class pmq_Client_Peer_Abstract
{
    private static $instances = array();
    
    private $dsn;
    
    public static function getInstance($dsn)
    {
        if (!isset(self::$instances[$dsn])) {
            // Guess the classname from the DSN
            $transportName = ucfirst(substr($dsn, 0, strpos($dsn, ':'))); 
            $className = 'pmq_Peer_' . $transportName;
            self::$instances[$dsn] = new $transportName($dsn);
        }
        
        return self::$instances[$dsn];
    }
    
    protected function __construct($dsn)
    {
        $this->dsn = $dsn;
    }
    
    abstract public function put(array $messages);    
    
}
