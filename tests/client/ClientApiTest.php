<?php
class ClientApiTest extends PHPUnit_Framework_TestCase
{
	
	/**
	 * @var pmq_Client
	 */
    private $client;
	
	public function setUp()
	{
        require '../../client/classes/autoload.php';		

        $this->client = new pmq_Client(
            pmq_Client_Storage_Abstract::factory('filesystem', '/tmp/myqueue')
        );
	}
	
	public function testPut()
	{
		$peer = pmq_Client_Peer_Abstract::getInstance('HttpUpload', 'tcp://192.168.0.1:8000');
	    $this->client->sendMessage(new pmq_Client_Message($blah = 'bernd', $peer));
	}
	
}