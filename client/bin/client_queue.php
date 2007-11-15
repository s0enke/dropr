<?php
require realpath(dirname(__FILE__) . '/..') . '/classes//autoload.php';

/*
 * Config
 */
$storage = new pmq_Client_Storage_Abstract();


while (true) {
    
    $messageSort = array();
    
    if (!$messages = $storage->getRecentMessages()) {
        // wait for ipc signal or sleep
        sleep(1);
        echo "sleeping\n";
    }
    
    foreach ($messages as $message) {
        $messageSort[$message->getDsn()][] = $message;
    }
    
    foreach ($messageSort as $dsn => $peerMessages) {
        $peer = pmq_Client_Peer_ConnectionPool::getPeer($dsn);
        $peer->put($peerMessages);
    }
    
    unset($messages);
    unset($messageSort);
    
}
