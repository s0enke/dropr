<?php
class pmq_Server_Message
{
    private $id;
    
    /**
     * The Message, can be content, stream or a file
     * 
     * @var mixed 
     */
    private $handle;
    
    private $storage;
    
    private $state;
    
    public function __construct(pmq_Server_Storage_Abstract $storage)
    {
        $this->storage = $storage;
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
}