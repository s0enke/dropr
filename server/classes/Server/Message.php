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
    
    public function __construct($client, $messageId, &$message, $priority, pmq_Server_Storage_Abstract $storage = null)
    {
        $this->client = $client;
        $this->messageId = $messageId;
        $this->message =& $message;
        $this->priority = $priority;
        $this->storage = $storage;
    }
    
    public function getId()
    {
        return $this->messageId;
    }
    
    public function getPriority()
    {
        return $this->priority;
    }

    public function getState()
    {
        return $this->state;   
    }
    
    public function __toString()
    {
        
    }
    
    /**
     * @return mixed
     */
    public function &getMessage()
    {
        return $this->message;
    }
    
    /**
     * sets the message to the processed state 
     */
    public function setProcessed()
    {
        if (!$this->storage) {
            throw new pmq_Server_Exception("cannot set message to processed state if no storage is given!");
        }
    }
}