<?php
class ServerApiTest extends PHPUnit_Framework_TestCase
{
	
	/**
	 * @var pmq_Server_Transport_Abstract
	 */
    private $server;
    
    /**
     * @var pmq_Server_Storage_Abstract
     */
    private $storage;
    
    
	
	public function setUp()
	{
        require '../../server/classes/autoload.php';		

        $this->server = pmq_Server_Transport_Abstract::factory(
        	'HttpUpload',
            $this->storage = pmq_Server_Storage_Abstract::factory('filesystem', '/tmp/myserverqueue')
        );
        
        $_SERVER['X-pmq-client'] = 'testhorst1';
        
        $_FILES['schnulli'] = array(
            'tmp_name' => '/tmp/blubb',
            'name' => 'schnulli',
        );
	}
	
	public function testPut()
	{
	    $this->server->handle();
	}
	
}