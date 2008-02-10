<?php
class dropr_Client_Message
{
    /**
	 * @var dropr_Client
	 */
    private $queue;
    private $message;    

    /**
	 * @var dropr_Client_Peer
	 */
    private $peer;

    private $channel;
    private $priority;
    private $sync;

    private $messageId = NULL;
    private $state = NULL;

    public function __construct(
        dropr_Client $queue = NULL,
        &$message = NULL,
        dropr_Client_Peer_Abstract $peer = NULL,
        $channel = 'common',
        $priority = 9,
        $sync = NULL)
    {
        $this->queue = $queue;
        $this->message = &$message;
        $this->peer = $peer;
        $this->channel = $channel;
        $this->priority = $priority;
        $this->sync = $sync;
    }

    public function &getMessage()
    {
        return $this->message;
    }

    /**
	 * @return dropr_Client_Peer_Abstract
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

    public function getChannel()
    {
        return $this->channel;
    }

    public function queue()
    {
        $this->messageId = $this->queue->putMessage($this);
    }
}
