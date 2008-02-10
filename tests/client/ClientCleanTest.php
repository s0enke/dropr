<?php
class ClientCleanTest extends PHPUnit_Framework_TestCase
{
	
	/**
	 * @var dropr_Client
	 */
    private $queue;
    
    /**
     * @var dropr_Client_Storage_Abstract
     */
    private $storage;

    public function setUp()
	{
        require '../../classes/dropr.php';		

        $this->storage = dropr_Client_Storage_Abstract::factory('filesystem', realpath('./clientqueue'));
        $this->queue = new dropr_Client($this->storage);
	}

	public function testPut()
	{
	    echo $this->storage->countQueuedMessages() . " unbearbeitete Nachrichten\n";
	    echo $this->storage->countSentMessages()   . " gesendete Nachrichten\n";
	    echo $this->storage->wipeSentMessages(5)   . " geloeschte Nachrichten\n";
	}
	
}
