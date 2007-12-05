<?php
class ClientCleanTest extends PHPUnit_Framework_TestCase
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

        $this->storage = pmq_Client_Storage_Abstract::factory('filesystem', '/home/erdmann/pmq/clientqueue');
        $this->queue = new pmq_Client($this->storage);
	}

	public function testPut()
	{
	    echo $this->storage->countQueuedMessages() . " unbearbeitete Nachrichten\n";
	    echo $this->storage->countSentMessages()   . " gesendete Nachrichten\n";
	    echo $this->storage->wipeSentMessages(60*24*7+925)   . " geloeschte Nachrichten\n";
	}
	
}
