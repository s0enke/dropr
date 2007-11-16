<?php
class pmq_Client_Message
{
    private $peer;
    
    private $message;
    
    private $state;
    
    public function __construct(&$message, pmq_Client_Peer_Abstract $peer) 
    {
        $this->message =& $message;
        $this->peer = $peer;
    }
    
    public function getPeer()
    {
        return $this->peer;
    }
    
    public function &getMessage()
    {
        return $this->message;
    }
}
