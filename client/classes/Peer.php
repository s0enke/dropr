<?php
class pmq_Client_Peer
{
	
	private $transportFormat;
	
	/**
	 * @param	$type		string		Der Server-Typ (REST, ...)
	 * @param	$config		array		Configuration for the Peer
	 * @param 	$format		string		Das Format des Transports (JSON, XML ...)	
	 */
	public static function factory($type, array $config, $transportFormat)
	{
		// @todo md5 hash !
		
		if (!is_string($type)) {
			throw new InvalidArgumentException("Type $type is no string!");
		}
		
		require 'client/peer/' . $type . '.php';
		
		$className = 'pmq_Client_Peer_'
	}
	
	protected function __construct()
	{
		
	}
	
	private function setTransportFormat(pmq_Client_TransportFormat $transportFormat)
	{
		$this->transportFormat = $transportFormat;
	}
	
	abstract public function transport();
}