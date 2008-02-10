<?php
class dropr_Server_Message
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
    
    private $channel;
    private $priority;

    private $time;

    private $sync;
    
    public function __construct($client, $messageId, &$message, $channel = 'common', $priority = 9, $time = null, dropr_Server_Storage_Abstract $storage = null)
    {
        $this->client = $client;
        $this->messageId = $messageId;
        $this->message =& $message;
        $this->channel = $channel;
        $this->priority = $priority;
        $this->time = $time;
        $this->storage = $storage;
    }
    
    public function getId()
    {
        return $this->messageId;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getState()
    {
        return $this->state;   
    }
    
    public function getClient()
    {
        return $this->client;
    }
    
    public function __toString()
    {
        if (!$this->storage) {
            throw new dropr_Server_Exception("cannot output state if no storage is given!");
        }
        
        if ($this->message instanceof SplFileInfo) {
            return file_get_contents($this->message->getPathname());
        }
        
        // type not implemented
        throw new Exception("not implemented");
    }
    
    /**
     * @return mixed
     */
    public function &getMessage()
    {
        return $this->message;
    }
    
    public function getTime()
    {
        return $this->time;
    }
    
    
}
