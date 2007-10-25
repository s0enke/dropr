<?php
class pmq_Client
{
	
	private $storage;
	
	public function __construct(PMQ_Client_Storage $storage)
	{
		$this->storage = $storage;
	}
	
	public function sendMessage(PMQ_Client_Peer $peer, $message)
	{
		
	}
	
}