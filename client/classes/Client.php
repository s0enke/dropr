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
	
	public function sendMessage($message)
	{
		// notify queue via ipc ?!
		$this->storage->put($message);
	}
	
	
}