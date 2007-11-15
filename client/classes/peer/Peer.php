<?php
class pmq_Client_Peer
{
	
	/**
	 * @param	$type		mixed		DSN
	 * @param 	$format		string		Transport-Format (JSON, XML, ...)	
	 */
	public static function factory($dsn, $transportFormat)
	{
		// @todo md5 hash !
		
		if (!is_string($type)) {
			throw new InvalidArgumentException("Type $type is no string!");
		}
		
		@include 'client/peer/' . $type . '.php';
		
		$className = 'pmq_Client_Peer_' . $type;
		
		if (!class_exists($className)) {
		    throw new Exception("Class $className could not be loaded!");
		}
		
		return new $className($dsn, $transportFormat);
		
		
	}
	
	protected function __construct()
	{
		
	}
	
}