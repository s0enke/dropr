<?php
abstract class pmq_Client_Storage_Abstract
{
    private static $instances = array();
    
    private $dsn;
    
    public static function factory($type, $dsn)
    {
        if (!isset(self::$instances[$dsn])) {
            // Guess the classname from the dsn
            $className = 'pmq_Client_Storage_' . ucfirst($type);
            self::$instances[$dsn] = new $className($dsn);
        }
        
        return self::$instances[$dsn];
    }

    
	/**
     * @return int	identifier of the message in queue
     */
    abstract public function put(pmq_Client_Peer $peer, $message);
    
    /**
     * returns the most recent messages  out of the storage ordered by 
     * 
     * 
     * @return array	An array of pmq_Client_Message objects 
     */
    abstract public function getRecentMessages($limit = null);
    
    /**
     * @return pmq_Client_Message
     */
    abstract public function getMessage($messageId);
    
}