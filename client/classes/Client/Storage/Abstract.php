<?php
abstract class pmq_Client_Storage_Abstract
{
    const TYPE_STREAM = 1;
    const TYPE_MEMORY = 2;
    const TYPE_FILE   = 3;

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
    abstract public function saveMessage(pmq_Client_Message $message);
    
    /**
     * returns the most recent messages  out of the storage ordered by 
     * 
     * 
     * @return array	An array of pmq_Client_Message objects 
     */
    abstract public function getQueuedMessages($limit = null);
    
    /**
     * @return pmq_Client_Message
     */
    abstract public function getMessage($messageId, pmq_Client_Peer $peer);
    
    abstract public function getType();

    abstract public function checkSentHandles(pmq_Client_Peer_Abstract $peer, array $handles, $result); 
}