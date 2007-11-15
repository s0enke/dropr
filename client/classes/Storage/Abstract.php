<?php
interface pmq_Client_Storage_Abstract
{
    /**
     * @return int	identifier of the message in queue
     */
    public function put(pmq_Client_Peer $peer, $message);
    
    /**
     * returns the most recent messages  out of the storage ordered by 
     * 
     * 
     * @return array	An array of pmq_Client_Message objects 
     */
    public function getRecentMessages($limit = null);
    
    /**
     * @return pmq_Client_Message
     */
    public function getMessage($messageId);
    
}