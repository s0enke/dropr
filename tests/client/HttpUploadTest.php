<?php
/**
 * you have to run a server at http://localhost/droprserver/
 * 
 * @author Soenke Ruempler
 *
 */
class HttpUploadTest extends PHPUnit_Framework_TestCase
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

        $this->storage = dropr_Client_Storage_Abstract::factory('filesystem', '/var/spool/dropr/client');
        $this->queue = new dropr_Client($this->storage);
	}

	public function testPut()
	{
		$peer = dropr_Client_Peer_Abstract::getInstance('HttpUpload', 'http://localhost/droprserver/');

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
