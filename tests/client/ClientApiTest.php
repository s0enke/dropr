<?php
class ClientApiTest extends PHPUnit_Framework_TestCase
{
	
	/**
	 * @var pmq_Client
	 */
    private $client;
    
    /**
     * @var pmq_Client_Storage_Abstract
     */
    private $storage;

    public function setUp()
	{
        require '../../client/classes/autoload.php';		

        $this->client = new pmq_Client(
            $this->storage = pmq_Client_Storage_Abstract::factory('filesystem', '/tmp/myqueue')
        );
	}

	public function testPut()
	{
		$peer = pmq_Client_Peer_Abstract::getInstance('HttpUpload', 'http://soenkepmqserver/server/server.php');
	    
		
        $dt = time();
        
        $i=0;
		while ($i < 10000) {
            $m = $this->createMessage(1000);
			$this->client->sendMessage(new pmq_Client_Message($m, $peer));
            $i++;
            echo '.';
        }

        $dt = time() - $dt;
        echo $dt."\n";

	    
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