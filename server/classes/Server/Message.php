<?php
class pmq_Server_Message
{
    private $storage;
    
    private $client;
    
    private $messageId;
    
    /**
     * The Message, can be content, stream or a file
     * 
     * @var mixed 
     */
    private $message;
    
    private $state = 'wurst';
    
    private $priority;

    private $sync;
    
    public function __construct(pmq_Server_Storage_Abstract $storage, $client, $messageId, &$message, $priority)
    {
        $this->storage = $storage;
        $this->client = $client;
        $this->messageId = $messageId;
        $this->message =& $message;
        $this->priority = $this->priority;
    }
    
    public function getId()
    {
        
    }
    
    public function getState()
    {
        
    }
    
    public function __toString()
    {
        
    }
    
    /**
     * @return mixed
     */
    public function getMessage()
    {
        
    }
    
    /**
     * sets the message to the processed state 
     */
    public function setProcessed()
    {
        
    }
}