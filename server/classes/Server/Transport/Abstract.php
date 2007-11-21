<?php
abstract class pmq_Server_Transport_Abstract
{
    
    /**
     * Transport gets called for new messages
     */
    const CALL_PUT = 1;
    
    /**
     * Transport gets called for new messages
     */
    const CALL_DIRECT = 2;

    /**
     * Transport gets polled for processed messages
     */
    const CALL_POLL = 3;
    
	/**
     * @var pmq_Server_Storage
     */
    private $storage;
    
    
    private $directInvocationHandlers = array();

    /**
     * @return pmq_Server_Transport_Abstract
     */
    public static function factory($type, pmq_Server_Storage_Abstract $storage)
    {
        $className = 'pmq_Server_Transport_' . ucfirst($type);
        return new $className($storage);
    }
    
    public function addDirectInvocationHandler(pmq_Server_DirectInvocation $handler, $type = null)
    {
        $this->directInvocationHandlers[$type] = $handler;
    }
    
    /**
     * this function is called by the transport layer if a direct message
     * has to be processed.
     * 
     * @throws pmq_Server_Exception
     */
    protected function invoke(pmq_Server_Message $message, $type)
    {
        // check if a handler for this type of message is registered
        if (!isset($this->directInvocationHandlers[$type])) {
            throw new pmq_Server_Exception("No handler set for type '$type'");
        }
        
        /* @var $this->directInvocationHandlers[$type] pmq_Server_DirectInvocation */
        
        return $this->directInvocationHandlers[$type]->invokeMessage($message);
    }
    
    
    
    protected function __construct(pmq_Server_Storage_Abstract $storage)
    {
        $this->storage = $storage;
    }
    

    
	/**
     * @return pmq_Server_Storage_Abstract
     */
    protected function getStorage()
    {
        return $this->storage;
    }
        
    /**
     * Handles the Server process
     */
    abstract public function handle();
    
}