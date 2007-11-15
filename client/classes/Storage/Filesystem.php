<?php
require_once 'Abstract.php';

class pmq_Client_Storage_Filesystem implements pmq_Client_Storage_Abstract
{
    
    private $path;
	
	public function __construct($path)
	{
	    $this->path = $path;
	}
	
    public function put(pmq_Client_Peer $peer, $message)
    {
        return true;
    }
		
}