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

        $this->storage = pmq_Client_Storage_Abstract::factory('filesystem', '/home/erdmann/pmq/clientqueue');
        $this->queue = new pmq_Client($this->storage);
	}

	public function testPut()
	{
		$peer = pmq_Client_Peer_Abstract::getInstance('HttpUpload', 'http://pmq.erdmann.test/tests/server/server.php');

    	$dt = time();
        $i=0;
        // $m = $this->createMessage(1000);
        
        $m = "ich bin eine test message von " . date("H:m:i");
        
        while ($i < 1) {

		    $msg = $this->queue->createMessage($m, $peer);
	        $msg->queue();
            $i++;
            echo '.';
        }
        
        $dt = time() - $dt;
        echo $dt."\n";
	    echo "\n\n";
	}
	

    function createMessage($len,
        $chars = '0123456789 ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz')
    {
        $charsSize = strlen($chars)-1;
        $string = '';
        for ($i = 0; $i < $len; $i++)
        {
            $pos = rand(0, $charsSize);
            $string .= $chars{$pos};
        }
        return $string;
    }
	
	
}