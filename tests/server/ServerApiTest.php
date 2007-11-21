<?php
require '../../server/classes/autoload.php';		

ini_set('error_log', '/tmp/pmq_server');
ini_set('log_errors', 1);

$storage = pmq_Server_Storage_Abstract::factory('filesystem', '/tmp/myserverqueue3');

echo '<pre>';
foreach ($storage->getMessages() as $message) {
    
    
    echo "Time: " . date("H:i:s", $message->getTime()) . "\n";
    echo "ID: " . $message->getId() . "\n";
    echo "---------------------------------------------\n";
    print_r(json_decode($message));
    $storage->setProcessed($message);
    
    echo "\n-------------------------------------------\n\n\n";
    
}