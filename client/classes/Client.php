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
		// notify queue via ipc ?!
		$this->storage->saveMessage(&$message);
	}

	public function createMessage(&$message = NULL, $peer = NULL, $priority = NULL, $sync = NULL)
	{
	    return new pmq_Client_Message($this, &$message, $peer, $priority, $sync);
	}
	
}