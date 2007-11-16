<?php
class pmq_Client_Storage_Filesystem extends pmq_Client_Storage_Abstract
{
    
    private $path;
	
	public function __construct($path)
	{
	    $this->path = $path;
	}
	
    public function put(pmq_Client_Peer $peer, $message)
    {
        return true;
    }
		
    /**
     * returns the most recent messages  out of the storage ordered by 
     * 
     * 
     * @return array	An array of pmq_Client_Message objects 
     */
    public function getRecentMessages($limit = null)
    {
        return array();
    }
    
    /**
     * @return pmq_Client_Message
     */
    public function getMessage($messageId)
    {
        
    }
}