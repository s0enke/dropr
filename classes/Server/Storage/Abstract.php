<?php
abstract class dropr_Server_Storage_Abstract
{

    const TYPE_STREAM = 1;
    const TYPE_MEMORY = 2;
    const TYPE_FILE   = 3;

    
    public static function factory($type, $dsn)
    {
        $className = 'dropr_Server_Storage_' . ucfirst($type);
        return new $className($dsn);
    }
    
    abstract public function getType();
    
    abstract public function put(dropr_Server_Message $message);
    
    /**
     * @return bool
     */
    abstract public function pollProcessed($messageId);
    
    /**
     * @return array
     */
    abstract public function getMessages($type = null, $limit = null);
    
    /**
     * Sets a message to processed state - the implementation must move it out
     * from the list of active messages to it's not in list of getMessages
     * anymore
     *
     * @param 	pmq_Server_Message $message
     * 
     * @throws 	pmq_Server_Exception
     */
    abstract public function setProcessed(dropr_Server_Message $message);

}
