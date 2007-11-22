<?php
class pmq_Client
{

	/**
	 * @var	pmq_Client_Storage
	 */
    private $storage;

	public function __construct(pmq_Client_Storage_Abstract $storage)
	{
		$this->storage = $storage;
	}

	public function putMessage(&$message)
	{

		$messageId = $this->storage->saveMessage(&$message);
		// notify queue via ipc ?!
		return $messageId;
	}

	public function createMessage(
	    &$message = NULL,
	    $peer = NULL,
	    $channel = 'common',
	    $priority = 9,
	    $sync = NULL)
	{
	    return new pmq_Client_Message(
	        $this,
	        &$message,
	        $peer,
	        $channel,
	        $priority,
	        $sync);
	}
	
}