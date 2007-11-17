<?php
class pmq_Client_Message
{
    /**
	 * @var pmq_Client
	 */
    private $queue;
    private $message;    

    /**
	 * @var pmq_Client_Peer
	 */
    private $peer;

    private $priority;
    private $sync;

    private $messageId = NULL;
    private $state = NULL;

    public function __construct(pmq_Client $queue = NULL, &$message = NULL, pmq_Client_Peer_Abstract $peer = NULL, $priority = 9, $sync = NULL) 
    {
        $this->queue = $queue;
        $this->message = &$message;
        $this->peer = $peer;
        $this->priority = $priority;
        $this->sync = $sync;
    }

    public function &getMessage()
    {
        return $this->message;
    }

    /**
	 * @return pmq_Client_Peer_Abstract
	 */
    
    public function getPeer()
    {
        return $this->peer;
    }

    public function getId()
    {
        return $this->messageId;
    }
    public function restoreId($messageId)
    {
        $this->messageId = $messageId;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function queue()
    {
        $this->messageId = $this->queue->putMessage($this);
    }
}
