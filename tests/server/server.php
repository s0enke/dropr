<?php
require '../../server/classes/autoload.php';		

ini_set('error_log', '/tmp/erdmann_dropr_server/errorlog');
ini_set('log_errors', 1);

$server = dropr_Server_Transport_Abstract::factory(
	'HttpUpload',
    dropr_Server_Storage_Abstract::factory('filesystem', '/tmp/erdmann_dropr_server/myserverqueue3')
);

#$directClass = new ServerDirectInvokeTest();

#$server->addDirectInvocationHandler($directClass);
        
$server->handle();

class ServerDirectInvokeTest implements dropr_Server_DirectInvocation 
{
    
    public function invokeMessage(dropr_Server_Message $message)
    {
        echo $message;
        return true;
    }
    
}
