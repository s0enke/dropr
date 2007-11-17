<?php
class ClientApiTest extends PHPUnit_Framework_TestCase
{
	
	/**
	 * @var pmq_Client
	 */
    private $queue;
    
    /**
     * @var pmq_Client_Storage_Abstract
     */
    private $storage;

    public function setUp()
	{
        require '../../client/classes/autoload.php';		

        $this->storage = pmq_Client_Storage_Abstract::factory('filesystem', '/tmp/myqueue2');
        $this->queue = new pmq_Client($this->storage);
	}

	public function testPut()
	{
		$peer = pmq_Client_Peer_Abstract::getInstance('HttpUpload', 'http://soenkepmqserver/server/server.php');
	    $msg = $this->queue->createMessage($_message = 'bernd', $peer);
	    $msg->queue();
	    
	    echo "\n\n";
	    var_dump($this->storage->getQueuedMessages());
	}
	
}