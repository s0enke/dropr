<?php
require '../../server/classes/autoload.php';		

ini_set('error_log', '/tmp/pmq_server');
ini_set('log_errors', 1);

$server = pmq_Server_Transport_Abstract::factory(
	'HttpUpload',
    pmq_Server_Storage_Abstract::factory('filesystem', '/tmp/myserverqueue3')
);
        
$server->handle();
