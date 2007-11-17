<?php
class ServerApiTest extends PHPUnit_Framework_TestCase
{
	
	/**
	 * @var pmq_Server
	 */
    private $server;
    
    /**
     * @var pmq_Client_Storage_Abstract
     */
    private $storage;
    
    
	
	public function setUp()
	{
        require '../../server/classes/autoload.php';		

        $this->server = pmq_Server_Transport_Abstract::factory(
        	'HttpUpload',
            $this->storage = pmq_Server_Storage_Abstract::factory('filesystem', '/tmp/myserverqueue')
        );
	}
	
	public function testPut()
	{
	    $this->server->handle();
	}
	
}