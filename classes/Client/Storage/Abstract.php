<?php
abstract class dropr_Client_Storage_Abstract
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
            $className = 'dropr_Client_Storage_' . ucfirst($type);
            self::$instances[$dsn] = new $className($dsn);
        }
        
        return self::$instances[$dsn];
    }

    public function getDsn() {
        return $this->dsn;
    }
    
	/**
     * @return int	identifier of the message in queue
     */
    abstract public function saveMessage(dropr_Client_Message $message);
    
    /**
     * returns the most recent messages  out of the storage ordered by
     * priority and create-time
     * 
     * @return array	An array of dropr_Client_Message objects 
     */
    abstract public function getQueuedMessages($limit = null, &$peerKeyBlackList = null);
    
    /**
     * @return dropr_Client_Message
     */
    abstract public function getMessage($messageId, dropr_Client_Peer $peer);
    
    abstract public function getType();

    abstract public function checkSentMessages(array &$messages, array &$result);

    abstract public function countQueuedMessages();

    abstract public function countSentMessages();

    abstract public function wipeSentMessages($olderThanMinutes);
}
