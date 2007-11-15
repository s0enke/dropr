<?php
class pmq_Client
{
	
	/**
	 * @var	PMQ_Client_Storage_Abstract
	 */
    private $storage;
	
	/**
	 * @var string
	 */
    private $peer;
	
	public function __construct(PMQ_Client_Storage $storage, $peer)
	{
		$this->storage = $storage;
		
		/*
		 * @todo 	check peer!
		 */
		
		$this->peer = $peer;
	}
	
	public function sendMessage($message)
	{
		// notify queue via ipc ?!
		$this->storage->put($this->peer, message);
	}
	
}