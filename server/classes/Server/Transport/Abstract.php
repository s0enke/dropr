<?php
abstract class pmq_Server_Transport_Abstract
{
    
    public static function factory($type, pmq_Server_Storage_Abstract $storage)
    {
        $className = 'pmq_Server_Transport_' . ucfirst($type);
        return new $className($storage);
    }
    
	/**
     * @var pmq_Server_Storage
     */
    private $storage;
    
    protected function __construct(pmq_Server_Storage_Abstract $storage)
    {
        $this->storage = $storage;
    }
    
    /**
     * @return pmq_Server_Storage
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