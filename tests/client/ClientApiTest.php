<?php
class ClientApiTest extends PHPUnit_Framework_TestCase
{
	
	private $client;
	
	public function setUp()
	{
        require '../../client/classes/autoload.php';		

        $this->client = new pmq_Client(new pmq_Client_Storage_Filesystem('/tmp/queue'));
	}
	
	public function testBlah()
	{
		
	}
	
}