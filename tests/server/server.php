<?php
require '../../server/classes/autoload.php';		

ini_set('error_log', '/tmp/erdmann_pmq_server/errorlog');
ini_set('log_errors', 1);

$server = pmq_Server_Transport_Abstract::factory(
	'HttpUpload',
    pmq_Server_Storage_Abstract::factory('filesystem', '/tmp/erdmann_pmq_server/myserverqueue3')
);

#$directClass = new ServerDirectInvokeTest();

#$server->addDirectInvocationHandler($directClass);
        
$server->handle();

class ServerDirectInvokeTest implements pmq_Server_DirectInvocation 
{
    
    public function invokeMessage(pmq_Server_Message $message)
    {
        echo $message;
        return true;
    }
    
}